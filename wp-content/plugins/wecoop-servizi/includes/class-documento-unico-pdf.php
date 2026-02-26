<?php
/**
 * Generatore Documento Unico PDF per Firma Digitale
 * 
 * Converte il documento_unico.txt in PDF dinamico da firmare
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Documento_Unico_PDF {
    
    /**
     * Genera PDF del documento unico per firma
     */
    public static function generate_documento_unico($richiesta_id, $user_id) {
        error_log("[WECOOP DOC UNICO] Inizio generazione PDF per richiesta #{$richiesta_id}");
        
        // Verifica richiesta
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            return [
                'success' => false,
                'message' => 'Richiesta non valida'
            ];
        }
        
        // Verifica ownership
        $richiesta_user_id = get_post_meta($richiesta_id, 'user_id', true);
        if ($richiesta_user_id != $user_id) {
            return [
                'success' => false,
                'message' => 'Non hai i permessi'
            ];
        }
        
        // Ottieni il template del documento
        $documento_testo = self::get_documento_template();
        
        // Recupera dati richiesta e utente
        $user = get_userdata($user_id);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        // Prepara dati per compilazione
        $nome = trim($dati['nome'] ?? '');
        $cognome = trim($dati['cognome'] ?? '');
        $nome_completo = trim($nome . ' ' . $cognome);
        $codice_fiscale = trim($dati['codice_fiscale'] ?? '');
        $telefono = trim($dati['telefono'] ?? '');
        
        error_log('[WECOOP DOC UNICO] 📋 Dati compilazione:');
        error_log('[WECOOP DOC UNICO] - Nome: ' . $nome);
        error_log('[WECOOP DOC UNICO] - Cognome: ' . $cognome);
        error_log('[WECOOP DOC UNICO] - CF: ' . $codice_fiscale);
        error_log('[WECOOP DOC UNICO] - Email: ' . $user->user_email);
        error_log('[WECOOP DOC UNICO] - Tel: ' . $telefono);
        
        // Compila placeholders
        $documento_compilato = self::compila_placeholders(
            $documento_testo,
            [
                'nome' => $nome,
                'cognome' => $cognome,
                'nome_completo' => $nome_completo,
                'codice_fiscale' => $codice_fiscale,
                'email' => $user->user_email,
                'telefono' => $telefono,
                'case_id' => $richiesta_id,
                'data' => date('d/m/Y'),
                'timestamp' => current_time('mysql'),
                'timestamp_firma' => current_time('mysql')
            ]
        );
        
        // Genera HTML per PDF
        $html = self::genera_html_documento($documento_compilato, $richiesta_id);
        
        error_log('[WECOOP DOC UNICO] ✅ HTML generato');
        
        // Genera PDF (passa richiesta_id per eliminare i precedenti)
        $result = self::html_to_pdf($html, "Documento_Unico_" . $richiesta_id, $richiesta_id);
        
        if (!$result['success']) {
            error_log('[WECOOP DOC UNICO] ❌ Errore generazione PDF: ' . $result['message']);
            return [
                'success' => false,
                'message' => $result['message']
            ];
        }
        
        error_log('[WECOOP DOC UNICO] ✅ PDF generato: ' . $result['url']);
        
        return [
            'success' => true,
            'message' => 'Documento unico PDF generato',
            'documento' => [
                'url' => $result['url'],
                'filepath' => $result['filepath'],
                'contenuto_testo' => $documento_compilato,
                'hash_sha256' => hash('sha256', $documento_compilato),
                'nome' => 'documento_unico_' . $richiesta_id . '.pdf',
                'timestamp' => current_time('mysql')
            ]
        ];
    }
    
    /**
     * Compila placeholders nel documento
     */
    private static function compila_placeholders($testo, $dati) {
        error_log('[WECOOP DOC UNICO] 🔄 Inizio compilazione placeholders');
        error_log('[WECOOP DOC UNICO] - Testo size: ' . strlen($testo) . ' bytes');
        error_log('[WECOOP DOC UNICO] - Numero dati: ' . count($dati));
        
        $testo_originale = $testo;
        $placeholder_count = 0;
        
        // Compila ogni placeholder
        foreach ($dati as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            
            // Verifica se il placeholder è nel testo
            if (strpos($testo, $placeholder) !== false) {
                $prima = $testo;
                $testo = str_replace($placeholder, (string)$value, $testo);
                $dopo = $testo;
                
                if ($prima !== $dopo) {
                    $placeholder_count++;
                    error_log("[WECOOP DOC UNICO] ✅ Compilato: $placeholder → " . substr((string)$value, 0, 30));
                } else {
                    error_log("[WECOOP DOC UNICO] ⚠️ Placeholder trovato ma non sostituito: $placeholder");
                }
            } else {
                error_log("[WECOOP DOC UNICO] ℹ️ Placeholder non trovato nel testo: $placeholder");
            }
        }
        
        error_log("[WECOOP DOC UNICO] 📊 Placeholders compilati: $placeholder_count");
        
        // Rimuovi placeholder non compilati (rimasti)
        $rimanenti = preg_match_all('/\{\{[^}]+\}\}/', $testo, $matches);
        if ($rimanenti) {
            error_log("[WECOOP DOC UNICO] ⚠️ Placeholder non compilati rimasti: " . implode(', ', $matches[0]));
            $testo = preg_replace('/\{\{[^}]+\}\}/', '', $testo);
        } else {
            error_log('[WECOOP DOC UNICO] ✅ Tutti i placeholders compilati o rimossi');
        }
        
        return $testo;
    }
    
    /**
     * Genera HTML documento formattato per PDF
     */
    private static function genera_html_documento($documento_testo, $richiesta_id) {
        $data_ora = date('d/m/Y H:i:s');
        
        // Formatta il testo in paragrafi HTML
        $html_content = self::formatta_testo_html($documento_testo);
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Documento Unico WECOOP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .header .subtitle {
            font-size: 11px;
            color: #666;
        }
        .document-id {
            background: #f0f8f0;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 11px;
            text-align: center;
            border: 1px solid #4CAF50;
        }
        .document-content {
            line-height: 1.6;
        }
        .document-content h2 {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 8px 0;
        }
        .document-content p {
            margin: 0 0 10px 0;
            text-align: left;
        }
        .document-content ul {
            margin: 0 0 10px 20px;
            padding: 0;
        }
        .document-content li {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .footer-logo {
            text-align: center;
            margin-bottom: 15px;
        }
        .footer-logo img {
            max-width: 80px;
            height: auto;
            display: inline-block;
        }
        .footer-info {
            line-height: 1.6;
            font-size: 10px;
            color: #333;
        }
        .footer-info p {
            margin: 3px 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DOCUMENTO UNICO</h1>
        <div class="subtitle">WECOOP APS - Adesione Socio e Mandato Generale</div>
    </div>
    
    <div class="document-id">
        <strong>ID Servizio:</strong> <?php echo $richiesta_id; ?> | <strong><?php echo $data_ora; ?></strong>
    </div>
    
    <div class="document-content">
        <?php echo $html_content; ?>
    </div>
    
    <div class="footer">
        <!-- Logo WECOOP -->
        <div class="footer-logo">
            <img src="<?php echo WECOOP_SERVIZI_PLUGIN_DIR; ?>assets/img/wecooplogo.png" alt="WECOOP Logo" style="max-width: 60px; height: auto;">
        </div>
        
        <!-- Informazioni organizzazione -->
        <div class="footer-info">
            <p><strong>WECOOP</strong></p>
            <p>Associazione di Promozione Sociale</p>
            <p>Via Benefattori dell'Ospedale, 3 – 20159 Milano (MI)</p>
            <p>Mob. 334 1390175 – info@wecoop.org</p>
            <p>CF 97977210158</p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Formatta il testo in HTML pulito
     */
    private static function formatta_testo_html($testo) {
        error_log("[WECOOP DOC PDF] formatta_testo_html - Input size: " . strlen($testo) . " bytes");
        
        // Pulisci spazi eccedenti all'inizio e fine
        $testo = trim($testo);
        
        // Rimuovi multiple newline consecutive (più di 2)
        $testo = preg_replace('/\n{3,}/', "\n\n", $testo);
        
        // Dividi per righe vuote (paragrafi)
        $paragrafi = preg_split('/\n\s*\n/', $testo);
        
        error_log("[WECOOP DOC PDF] Numero paragrafi: " . count($paragrafi));
        
        $html = '';
        $para_count = 0;
        foreach ($paragrafi as $paragrafo) {
            $paragrafo = trim($paragrafo);
            if (empty($paragrafo)) {
                continue;
            }
            
            $para_count++;
            
            // Controlla se è un heading (contiene numero all'inizio)
            if (preg_match('/^(\d+\)|\d+\.)\s+(.+)$/m', $paragrafo, $matches)) {
                $html .= '<h2>' . esc_html($paragrafo) . '</h2>';
                error_log("[WECOOP DOC PDF] Paragrafo $para_count: HEADING");
            }
            // Controlla se contiene bullet points
            elseif (strpos($paragrafo, '-') === 0 || strpos($paragrafo, '•') === 0 || strpos($paragrafo, '☐') === 0) {
                $linee = explode("\n", $paragrafo);
                $html .= '<ul>';
                foreach ($linee as $linea) {
                    $linea = trim($linea);
                    if (preg_match('/^[-•☐]\s*(.+)$/', $linea, $matches)) {
                        $html .= '<li>' . esc_html($matches[1]) . '</li>';
                    }
                }
                $html .= '</ul>';
                error_log("[WECOOP DOC PDF] Paragrafo $para_count: UL (" . count($linee) . " items)");
            }
            // Paragrafo normale
            else {
                // Gestisci righe multiple dentro il paragrafo
                $linee = explode("\n", $paragrafo);
                $html .= '<p>';
                $line_parts = [];
                foreach ($linee as $linea) {
                    $linea = trim($linea);
                    if (!empty($linea)) {
                        $line_parts[] = esc_html($linea);
                    }
                }
                $html .= implode('<br>', $line_parts);
                $html .= '</p>';
                error_log("[WECOOP DOC PDF] Paragrafo $para_count: P (" . count($line_parts) . " righe)");
            }
        }
        
        error_log("[WECOOP DOC PDF] HTML generato - Output size: " . strlen($html) . " bytes");
        
        return $html;
    }
    
    /**
     * Converte HTML in PDF usando mPDF
     */
    private static function html_to_pdf($html, $filename, $richiesta_id = null) {
        error_log("[WECOOP DOC PDF] html_to_pdf chiamato per: $filename (richiesta_id: " . ($richiesta_id ?? 'null') . ")");
        error_log("[WECOOP DOC PDF] Lunghezza HTML: " . strlen($html) . " bytes");
        error_log("[WECOOP DOC PDF] HTML Preview (primi 800 chars): " . substr($html, 0, 800));
        
        // Se è una richiesta, elimina i PDF vecchi
        if ($richiesta_id) {
            self::elimina_pdf_precedenti($richiesta_id);
        }
        
        // Carica mPDF dal vendor del plugin
        $autoload = WECOOP_SERVIZI_PLUGIN_DIR . 'vendor/autoload.php';
        
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        
        // Verifica disponibilità mPDF
        $mpdf_exists = class_exists('Mpdf\Mpdf');
        error_log("[WECOOP DOC PDF] Classe Mpdf\\Mpdf esiste: " . ($mpdf_exists ? 'SI' : 'NO'));
        
        if (!$mpdf_exists) {
            error_log('[WECOOP DOC PDF] ❌ mPDF non disponibile');
            return [
                'success' => false,
                'message' => 'Libreria mPDF non disponibile'
            ];
        }
        
        try {
            // Crea istanza mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'tempDir' => WECOOP_SERVIZI_PLUGIN_DIR . 'temp_pdf'
            ]);
            
            // Assicurati che la directory temp esista
            if (!is_dir(WECOOP_SERVIZI_PLUGIN_DIR . 'temp_pdf')) {
                mkdir(WECOOP_SERVIZI_PLUGIN_DIR . 'temp_pdf', 0755, true);
            }
            
            // Scrivi HTML
            $mpdf->WriteHTML($html);
            
            // Salva in upload directory
            $upload_dir = wp_upload_dir();
            $firma_dir = $upload_dir['basedir'] . '/wecoop-documenti-unici/';
            
            // Crea directory se non esiste
            if (!is_dir($firma_dir)) {
                mkdir($firma_dir, 0755, true);
            }
            
            // Nome file unique
            $filename_clean = sanitize_file_name($filename);
            $file_path = $firma_dir . $filename_clean . '_' . time() . '.pdf';
            
            // Salva PDF
            $mpdf->Output($file_path, 'F');
            
            if (!file_exists($file_path)) {
                error_log('[WECOOP DOC PDF] ❌ File non creato: ' . $file_path);
                return [
                    'success' => false,
                    'message' => 'Errore durante il salvataggio del PDF'
                ];
            }
            
            // Genera URL
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
            
            error_log("[WECOOP DOC PDF] ✅ PDF salvato: $file_path");
            
            return [
                'success' => true,
                'url' => $file_url,
                'filepath' => $file_path
            ];
            
        } catch (\Exception $e) {
            error_log('[WECOOP DOC PDF] ❌ Errore mPDF: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Errore nella generazione PDF: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina PDF precedenti per la stessa richiesta
     */
    private static function elimina_pdf_precedenti($richiesta_id) {
        error_log("[WECOOP DOC PDF] 🗑️ Controllo PDF precedenti per richiesta #$richiesta_id");
        
        $upload_dir = wp_upload_dir();
        $firma_dir = $upload_dir['basedir'] . '/wecoop-documenti-unici/';
        
        if (!is_dir($firma_dir)) {
            error_log("[WECOOP DOC PDF] ℹ️ Directory non esiste ancora");
            return;
        }
        
        // Cerca file PDF per questa richiesta
        $pattern = 'Documento_Unico_' . $richiesta_id . '_*.pdf';
        $files = glob($firma_dir . $pattern);
        
        if (empty($files)) {
            error_log("[WECOOP DOC PDF] ℹ️ Nessun PDF precedente trovato per richiesta #$richiesta_id");
            return;
        }
        
        // Elimina ogni file trovato
        foreach ($files as $file) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    error_log("[WECOOP DOC PDF] ✅ PDF rimosso: $file");
                } else {
                    error_log("[WECOOP DOC PDF] ❌ Impossibile rimuovere: $file");
                }
            }
        }
        
        error_log("[WECOOP DOC PDF] ✅ Pulizia completata - " . count($files) . " file rimossi");
    }
    
    /**
     * Ottieni contenuto documento unico compilato
     */
    public static function get_documento_contenuto($richiesta_id, $user_id) {
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            return null;
        }
        
        $richiesta_user_id = get_post_meta($richiesta_id, 'user_id', true);
        if ($richiesta_user_id != $user_id) {
            return null;
        }
        
        // Leggi documento template
        $doc_file = WECOOP_SERVIZI_PLUGIN_DIR . 'documento_unico.txt';
        if (!file_exists($doc_file)) {
            return null;
        }
        
        $documento_testo = file_get_contents($doc_file);
        
        // Recupera dati
        $user = get_userdata($user_id);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        // Compila placeholders
        return self::compila_placeholders(
            $documento_testo,
            [
                'nome' => $dati['nome'] ?? '',
                'cognome' => $dati['cognome'] ?? '',
                'nome_completo' => trim(($dati['nome'] ?? '') . ' ' . ($dati['cognome'] ?? '')),
                'codice_fiscale' => $dati['codice_fiscale'] ?? '',
                'email' => $user->user_email,
                'telefono' => $dati['telefono'] ?? '',
                'case_id' => $richiesta_id,
                'data' => date('d/m/Y'),
                'timestamp' => current_time('mysql')
            ]
        );
    }
    
    /**
     * Restituisce il template del documento direttamente
     * Non dipende da file esterno per evitare spazi nascosti
     */
    private static function get_documento_template() {
        return <<<'EOT'
DOCUMENTO UNICO – WECOOP APS
Privacy • Adesione Socio • Mandato

DATI DELL'INTERESSATO
Nome e Cognome: {{nome}} {{cognome}}
Codice Fiscale: {{codice_fiscale}}
Email: {{email}}
Telefono (WhatsApp): {{telefono}}
Case ID: {{case_id}}
Data: {{data}}

1) INFORMATIVA PRIVACY (GDPR)
Il/La sottoscritto/a dichiara di aver ricevuto e compreso l'Informativa Privacy di WECOOP APS e autorizza il trattamento dei dati personali per:
- gestione delle pratiche e dei servizi richiesti;
- comunicazioni operative tramite email e WhatsApp;
- adempimenti amministrativi e fiscali;
- archiviazione digitale della documentazione.

☐ Acconsento alle comunicazioni operative via WhatsApp.

2) ADESIONE A SOCIO WECOOP APS
Il/La sottoscritto/a chiede di aderire a WECOOP APS in qualità di socio/a.
- L'adesione è gratuita.
- La qualità di socio si rinnova annualmente.
- L'adesione non comporta obbligo di usufruire dei servizi.

3) MANDATO / INCARICO
Il/La sottoscritto/a conferisce mandato a WECOOP APS per l'assistenza e la gestione delle pratiche amministrative richieste di volta in volta, inclusa:
- raccolta, verifica e gestione della documentazione;
- compilazione e trasmissione delle istanze agli enti competenti;
- assistenza amministrativa connessa.

Il/La sottoscritto/a dichiara:
- di fornire dati e documenti veritieri;
- di autorizzare la conservazione delle copie digitali;
- che il mandato è revocabile solo per iscritto.

FIRMA DIGITALE
Il presente documento viene firmato una sola volta tramite firma digitale con OTP via SMS ed è valido per adesione a socio WECOOP APS e conferimento del mandato generale per i servizi futuri.

Firma digitale (OTP): _______________________
Data e ora firma: {{timestamp_firma}}
EOT;
    }
}

