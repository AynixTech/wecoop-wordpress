<?php
/*
 * Funzione: Estrae i dati anagrafici base da una Certificazione Unica italiana in PDF.
 * Dipendenza: richiede la libreria Python 'pdfplumber'.
 *
 * Esempio d'uso:
 * $result = estrai_dati_da_cu_pdf($pdf_path);
 * Restituisce array associativo nome/campo.
 */

function estrai_dati_cu_pdf($pdf_path) {
    $output = [
        'codice_fiscale' => '',
        'cognome' => '',
        'nome' => '',
        'data_nascita' => '',
        'luogo_nascita' => '',
        'provincia_nascita' => '',
        'sesso' => '',
    ];

    $parser_py = __DIR__ . '/wecoop_cu_parser.py';
    $cmd = escapeshellcmd("python3 '" . $parser_py . "' '" . $pdf_path . "'");
    $output_str = shell_exec($cmd . " 2>&1");
    if (empty($output_str)) {
        error_log('[CU_IMPORT] Nessun output dal parser: ' . $cmd);
        return $output;
    }
    $estratti = json_decode($output_str, true);
    if (!is_array($estratti)) {
        error_log('[CU_IMPORT] Output parser non JSON: ' . $output_str);
        return $output;
    }
    $output = array_merge($output, $estratti);
    return $output;
}
