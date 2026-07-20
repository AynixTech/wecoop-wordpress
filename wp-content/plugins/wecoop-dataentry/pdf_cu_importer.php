<?php
/*
 * Funzione: Estrae i dati anagrafici base da una Certificazione Unica italiana in PDF.
 * Dipendenza: richiede la libreria Python 'pdfplumber'.
 *
 * Esempio d'uso:
 * $result = estrai_dati_da_cu_pdf($pdf_path);
 * Restituisce array associativo nome/campo.
 */

function wecoop_cu_python_executable() {
    // In produzione è possibile impostare nel wp-config.php:
    // define('WECOOP_CU_PYTHON', '/percorso/assoluto/python3');
    $candidates = array_filter([
        defined('WECOOP_CU_PYTHON') ? WECOOP_CU_PYTHON : null,
        getenv('WECOOP_CU_PYTHON') ?: null,
        '/usr/bin/python3',
        '/usr/local/bin/python3',
        '/opt/homebrew/bin/python3',
    ]);

    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }

    return '';
}

function wecoop_cu_openai_api_key() {
    if (defined('OPENAI_API_KEY')) {
        $key = (string) constant('OPENAI_API_KEY');
        if ($key !== '') {
            return $key;
        }
    }

    $environment_key = (string) getenv('OPENAI_API_KEY');
    if ($environment_key !== '') {
        return $environment_key;
    }

    // Compatibilità con la configurazione OpenAI già usata dagli altri plugin WECOOP.
    return (string) get_option('wecoop_openai_api_key', '');
}

function wecoop_cu_extract_from_filename($original_filename) {
    $filename = preg_replace('/\.pdf$/i', '', wp_basename((string) $original_filename));
    $filename = trim((string) $filename);
    $result = ['codice_fiscale' => '', 'cognome' => '', 'nome' => ''];

    // Supporta sia il nome originale del gestore documentale sia la variante
    // normalizzata da WordPress, che trasforma spazi e parentesi in trattini.
    $pattern = '/^([A-Z0-9]{16})[-\s]+\d{4}[-\s]+(.+?)(?:\s*\([A-Z0-9]{16}-\d+\)(?:\s*\(\d+\))?|[-\s]+[A-Z0-9]{16}[-\s]+\d+(?:[-\s]+\d+)?)$/i';
    if (!preg_match($pattern, $filename, $matches)) {
        return $result;
    }

    $words = preg_split('/[-\s]+/', trim($matches[2]));
    $words = array_values(array_filter($words));
    if (count($words) < 2 || count($words) % 2 !== 0) {
        return $result;
    }

    $separator = count($words) / 2;
    $result['codice_fiscale'] = strtoupper($matches[1]);
    $result['cognome'] = strtoupper(implode(' ', array_slice($words, 0, $separator)));
    $result['nome'] = strtoupper(implode(' ', array_slice($words, $separator)));
    return $result;
}

function wecoop_cu_extract_with_ai($pdf_path) {
    $api_key = wecoop_cu_openai_api_key();
    if ($api_key === '') {
        error_log('[CU_IMPORT] OPENAI_API_KEY non configurata: analisi AI non eseguita.');
        return [];
    }
    if (!is_readable($pdf_path)) {
        error_log('[CU_IMPORT] PDF non leggibile: analisi AI non eseguita.');
        return [];
    }

    $max_pdf_bytes = (int) apply_filters('wecoop_cu_openai_max_pdf_bytes', 45 * MB_IN_BYTES);
    $pdf_size = filesize($pdf_path);
    if ($pdf_size === false || $pdf_size > $max_pdf_bytes) {
        error_log('[CU_IMPORT] PDF troppo grande per l\'analisi AI.');
        return [];
    }

    $pdf_contents = file_get_contents($pdf_path);
    if ($pdf_contents === false) {
        error_log('[CU_IMPORT] Impossibile leggere il PDF per l\'analisi AI.');
        return [];
    }

    $schema = [
        'type' => 'object',
        'properties' => [
            'codice_fiscale' => ['type' => 'string'],
            'cognome' => ['type' => 'string'],
            'nome' => ['type' => 'string'],
            'data_nascita' => ['type' => 'string'],
            'luogo_nascita' => ['type' => 'string'],
            'provincia_nascita' => ['type' => 'string'],
            'sesso' => ['type' => 'string'],
            'indirizzo' => ['type' => 'string'],
            'civico' => ['type' => 'string'],
            'cap' => ['type' => 'string'],
            'citta' => ['type' => 'string'],
            'provincia' => ['type' => 'string'],
            'nazione' => ['type' => 'string'],
            'paese_provenienza' => ['type' => 'string'],
            'numero_figli' => ['type' => 'string'],
            'figli_minori' => ['type' => 'string'],
            'figli_minori_numero' => ['type' => 'string'],
            'persone_a_carico' => ['type' => 'string'],
            'categoria_persona_carico' => ['type' => 'string'],
            'percentuale_carico' => ['type' => 'string'],
            'tipo_lavoro' => ['type' => 'string'],
            'professione' => ['type' => 'string'],
            'reddito_annuo' => ['type' => 'string'],
            'altri_redditi' => ['type' => 'string'],
        ],
        'required' => ['codice_fiscale', 'cognome', 'nome', 'data_nascita', 'luogo_nascita', 'provincia_nascita', 'sesso', 'indirizzo', 'civico', 'cap', 'citta', 'provincia', 'nazione', 'paese_provenienza', 'numero_figli', 'figli_minori', 'figli_minori_numero', 'persone_a_carico', 'categoria_persona_carico', 'percentuale_carico', 'tipo_lavoro', 'professione', 'reddito_annuo', 'altri_redditi'],
        'additionalProperties' => false,
    ];
    $response = wp_remote_post('https://api.openai.com/v1/responses', [
        'timeout' => 90,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'model' => apply_filters('wecoop_cu_openai_model', 'gpt-4o-mini'),
            'input' => [
                [
                    'role' => 'system',
                    'content' => 'Sei un estrattore di dati da Certificazioni Uniche italiane. Leggi direttamente il PDF, incluse le immagini delle pagine. Estrai solo dati esplicitamente presenti e riferiti al percettore/contribuente, mai al sostituto d\'imposta. I PDF possono avere layout diversi. Non dedurre né inventare: restituisci stringa vuota per ogni dato non leggibile. Usa data_nascita nel formato YYYY-MM-DD; per figli_minori e altri_redditi usa esclusivamente 1, 0 o stringa vuota; tipo_lavoro può essere solo dipendente, autonomo, disoccupato, studente oppure stringa vuota. reddito_annuo è l\'importo annuo certificato senza simbolo valuta. indirizzo contiene solo la strada, civico solo il numero civico effettivamente stampato. nazione indica esclusivamente il Paese dell\'indirizzo di residenza/domicilio; paese_provenienza indica il Paese di nascita/origine. Non scambiare i due campi.',
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_file',
                            'filename' => 'certificazione-unica.pdf',
                            'file_data' => 'data:application/pdf;base64,' . base64_encode($pdf_contents),
                            'detail' => 'high',
                        ],
                        [
                            'type' => 'input_text',
                            'text' => 'Mappa tutti i dati disponibili della CU nei campi richiesti. Cerca anche domicilio fiscale/residenza, familiari a carico e redditi certificati, quando esplicitamente indicati. Il codice fiscale deve avere 16 caratteri alfanumerici e il sesso deve essere M o F.',
                        ],
                    ],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'dati_anagrafici_cu',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
        ]),
    ]);

    if (is_wp_error($response)) {
        error_log('[CU_IMPORT] Errore di rete OpenAI: ' . $response->get_error_code());
        return [];
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        $error_body = json_decode(wp_remote_retrieve_body($response), true);
        $error_message = isset($error_body['error']['message']) ? sanitize_text_field((string) $error_body['error']['message']) : 'Risposta senza dettaglio errore';
        error_log('[CU_IMPORT] OpenAI ha risposto HTTP ' . wp_remote_retrieve_response_code($response) . ': ' . $error_message);
        return [];
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $content = '';
    foreach (($body['output'] ?? []) as $output_item) {
        foreach (($output_item['content'] ?? []) as $content_item) {
            if (($content_item['type'] ?? '') === 'output_text' && isset($content_item['text'])) {
                $content = $content_item['text'];
                break 2;
            }
        }
    }
    $data = is_string($content) ? json_decode($content, true) : null;
    if (!is_array($data)) {
        error_log('[CU_IMPORT] Risposta OpenAI non valida.');
        return [];
    }

    $allowed_keys = array_keys($schema['properties']);
    $result = [];
    foreach ($allowed_keys as $key) {
        $result[$key] = isset($data[$key]) && is_scalar($data[$key]) ? trim((string) $data[$key]) : '';
    }
    $result['codice_fiscale'] = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $result['codice_fiscale']));
    if (strlen($result['codice_fiscale']) !== 16) {
        $result['codice_fiscale'] = '';
    }
    $result['sesso'] = strtoupper($result['sesso']);
    if (!in_array($result['sesso'], ['M', 'F'], true)) {
        $result['sesso'] = '';
    }
    if (!in_array($result['tipo_lavoro'], ['dipendente', 'autonomo', 'disoccupato', 'studente'], true)) {
        $result['tipo_lavoro'] = '';
    }
    foreach (['figli_minori', 'altri_redditi'] as $key) {
        if (!in_array($result[$key], ['1', '0'], true)) {
            $result[$key] = '';
        }
    }
    if ($result['data_nascita'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $result['data_nascita'])) {
        $result['data_nascita'] = '';
    }

    error_log('[CU_IMPORT] Analisi diretta PDF con OpenAI completata per: ' . implode(', ', array_keys(array_filter($result))));
    return $result;
}

function estrai_dati_cu_pdf($pdf_path, $original_filename = '') {
    $output = [
        'codice_fiscale' => '',
        'cognome' => '',
        'nome' => '',
        'data_nascita' => '',
        'luogo_nascita' => '',
        'provincia_nascita' => '',
        'sesso' => '',
        'indirizzo' => '',
        'civico' => '',
        'cap' => '',
        'citta' => '',
        'provincia' => '',
        'nazione' => '',
        'paese_provenienza' => '',
        'numero_figli' => '',
        'figli_minori' => '',
        'figli_minori_numero' => '',
        'persone_a_carico' => '',
        'categoria_persona_carico' => '',
        'percentuale_carico' => '',
        'tipo_lavoro' => '',
        'professione' => '',
        'reddito_annuo' => '',
        'altri_redditi' => '',
    ];

    // L'analisi AI è il percorso principale: può leggere direttamente il PDF e
    // non dipende da Python o dalla presenza di una text layer nel documento.
    $ai_data = wecoop_cu_extract_with_ai($pdf_path);
    foreach ($ai_data as $key => $value) {
        if (isset($output[$key]) && $value !== '') {
            $output[$key] = $value;
        }
    }
    $filename_data = wecoop_cu_extract_from_filename($original_filename);
    foreach ($filename_data as $key => $value) {
        if (isset($output[$key]) && $output[$key] === '' && $value !== '') {
            $output[$key] = $value;
        }
    }
    if ($output['numero_figli'] === '0' && $output['figli_minori'] === '') {
        $output['figli_minori'] = '0';
        $output['figli_minori_numero'] = '0';
    }
    if (!empty($output['nome']) && !empty($output['cognome']) && !empty($output['codice_fiscale'])) {
        $output['first_name'] = $output['nome'];
        $output['last_name'] = $output['cognome'];
        $output['display_name'] = trim($output['nome'] . ' ' . $output['cognome']);
        $output['doc_cu'] = '1';
        error_log('[CU_IMPORT] Importazione completata senza parser Python.');
        return $output;
    }

    // Se l'AI non è configurata o non ha estratto tutti i campi, prova il
    // parser locale come fallback per i PDF con text layer leggibile.
    $python = wecoop_cu_python_executable();
    if ($python === '') {
        error_log('[CU_IMPORT] Python 3 non trovato dopo analisi AI incompleta.');
        $output['__error'] = wecoop_cu_openai_api_key() === ''
            ? 'OPENAI_API_KEY non configurata: configura la chiave OpenAI per analizzare il PDF.'
            : 'L\'analisi AI non ha recuperato tutti i dati richiesti e il parser locale Python non è disponibile.';
        return $output;
    }

    $parser_py = __DIR__ . '/wecoop_cu_parser.py';
    $cmd = escapeshellarg($python) . ' ' . escapeshellarg($parser_py) . ' ' . escapeshellarg($pdf_path) . ' ' . escapeshellarg($original_filename);
    if (!function_exists('shell_exec')) {
        error_log('[CU_IMPORT] shell_exec() non disponibile: verifica disable_functions nella configurazione PHP.');
        $output['__error'] = 'Il server non consente l\'esecuzione del parser PDF. Abilita shell_exec nella configurazione PHP.';
        return $output;
    }
    error_log('[CU_IMPORT] Avvio comando shell: ' . $cmd);
    $output_str = shell_exec($cmd . " 2>&1");
    error_log('[CU_IMPORT] Parser Python terminato.');
    if (empty($output_str)) {
        error_log('[CU_IMPORT] Nessun output dal parser: ' . $cmd);
        $output['__error'] = 'python3 non trovato o errore parser';
        return $output;
    }
    $estratti = json_decode($output_str, true);
    if (!is_array($estratti)) {
        error_log('[CU_IMPORT] Output parser non JSON: ' . $output_str);
        if (strpos($output_str, 'command not found') !== false) {
            $output['__error'] = 'python3 non trovato: installa Python 3 per abilitare l\'importazione delle CU (vedi log PHP)';
        } else {
            $output['__error'] = 'Errore parser CU: output non valido';
        }
        return $output;
    }
    $filename_cognome = isset($estratti['__filename_cognome']) ? (string) $estratti['__filename_cognome'] : '';
    $filename_nome = isset($estratti['__filename_nome']) ? (string) $estratti['__filename_nome'] : '';
    unset($estratti['__filename_cognome'], $estratti['__filename_nome']);
    $output = array_merge($output, $estratti);
    if (empty($output['nome']) || empty($output['cognome']) || empty($output['codice_fiscale'])) {
        foreach ($ai_data as $key => $value) {
            if (isset($output[$key]) && $value !== '') {
                $output[$key] = $value;
            }
        }
    }
    if (empty($output['cognome']) && $filename_cognome !== '') {
        $output['cognome'] = $filename_cognome;
    }
    if (empty($output['nome']) && $filename_nome !== '') {
        $output['nome'] = $filename_nome;
    }
    error_log('[CU_IMPORT] Campi estratti: ' . implode(', ', array_keys(array_filter($output))));
    return $output;
}
