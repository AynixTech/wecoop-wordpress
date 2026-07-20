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

function wecoop_cu_extract_with_ai($text) {
    $api_key = wecoop_cu_openai_api_key();
    if ($api_key === '' || $text === '') {
        return [];
    }

    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'model' => apply_filters('wecoop_cu_openai_model', 'gpt-4o-mini'),
            'temperature' => 0,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Estrai dati anagrafici da testo di una Certificazione Unica italiana. Restituisci solo JSON valido. Non inventare dati: usa stringa vuota se un campo non e presente.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Restituisci esclusivamente queste chiavi: codice_fiscale, cognome, nome, data_nascita, luogo_nascita, provincia_nascita, sesso. Il codice fiscale deve avere 16 caratteri alfanumerici; sesso deve essere M o F. Testo CU: ' . substr($text, 0, 16000),
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
    $content = $body['choices'][0]['message']['content'] ?? '';
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

    error_log('[CU_IMPORT] Estrazione OpenAI completata per: ' . implode(', ', array_keys(array_filter($result))));
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

    $python = wecoop_cu_python_executable();
    if ($python === '') {
        error_log('[CU_IMPORT] Python 3 non trovato. Percorsi controllati: /usr/bin/python3, /usr/local/bin/python3, /opt/homebrew/bin/python3');
        $output['__error'] = 'Python 3 non disponibile sul server. Configura WECOOP_CU_PYTHON con il percorso assoluto dell\'eseguibile.';
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
    error_log('[CU_IMPORT] Output shell: ' . $output_str);
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
    $extracted_text = isset($estratti['__extracted_text']) ? (string) $estratti['__extracted_text'] : '';
    $filename_cognome = isset($estratti['__filename_cognome']) ? (string) $estratti['__filename_cognome'] : '';
    $filename_nome = isset($estratti['__filename_nome']) ? (string) $estratti['__filename_nome'] : '';
    unset($estratti['__extracted_text'], $estratti['__filename_cognome'], $estratti['__filename_nome']);
    $output = array_merge($output, $estratti);
    if (empty($output['nome']) || empty($output['cognome']) || empty($output['codice_fiscale'])) {
        $ai_data = wecoop_cu_extract_with_ai($extracted_text);
        foreach ($ai_data as $key => $value) {
            if (isset($output[$key]) && $output[$key] === '' && $value !== '') {
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
