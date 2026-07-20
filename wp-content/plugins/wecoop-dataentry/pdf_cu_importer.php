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

    return (string) getenv('OPENAI_API_KEY');
}

function wecoop_cu_extract_with_ai($pdf_path) {
    $api_key = wecoop_cu_openai_api_key();
    if ($api_key === '' || !is_readable($pdf_path)) {
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
        ],
        'required' => ['codice_fiscale', 'cognome', 'nome', 'data_nascita', 'luogo_nascita', 'provincia_nascita', 'sesso'],
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
                    'content' => 'Sei un estrattore di dati da Certificazioni Uniche italiane. Leggi direttamente il PDF, incluse le immagini delle pagine. Estrai solo i dati del percettore/contribuente, non quelli del sostituto d\'imposta. Non inventare dati: restituisci una stringa vuota per ogni campo non leggibile.',
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_file',
                            'filename' => 'certificazione-unica.pdf',
                            'file_data' => base64_encode($pdf_contents),
                            'detail' => 'high',
                        ],
                        [
                            'type' => 'input_text',
                            'text' => 'Mappa i dati anagrafici del percettore nei campi richiesti. Il codice fiscale deve avere 16 caratteri alfanumerici e il sesso deve essere M o F.',
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
        error_log('[CU_IMPORT] OpenAI ha risposto HTTP ' . wp_remote_retrieve_response_code($response));
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

    $allowed_keys = ['codice_fiscale', 'cognome', 'nome', 'data_nascita', 'luogo_nascita', 'provincia_nascita', 'sesso'];
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
    ];

    // L'analisi AI è il percorso principale: può leggere direttamente il PDF e
    // non dipende da Python o dalla presenza di una text layer nel documento.
    $ai_data = wecoop_cu_extract_with_ai($pdf_path);
    foreach ($ai_data as $key => $value) {
        if (isset($output[$key]) && $value !== '') {
            $output[$key] = $value;
        }
    }
    if (!empty($output['nome']) && !empty($output['cognome']) && !empty($output['codice_fiscale'])) {
        return $output;
    }

    // Se l'AI non è configurata o non ha estratto tutti i campi, prova il
    // parser locale come fallback per i PDF con text layer leggibile.
    $python = wecoop_cu_python_executable();
    if ($python === '') {
        error_log('[CU_IMPORT] Python 3 non trovato dopo analisi AI incompleta.');
        $output['__error'] = 'L\'analisi AI non ha recuperato tutti i dati richiesti e il parser locale Python non è disponibile.';
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
