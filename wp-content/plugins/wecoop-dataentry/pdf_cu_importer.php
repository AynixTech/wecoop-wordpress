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

    $cmd = escapeshellcmd("python3 -m wecoop_cu_parser '".$pdf_path."'");
    $riga = shell_exec($cmd);
    if ($riga) {
        $estratti = json_decode($riga, true);
        if (is_array($estratti)) {
            $output = array_merge($output, $estratti);
        }
    }
    return $output;
}
