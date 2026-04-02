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
        [$nome, $cognome, $nome_completo] = self::resolve_nome_cognome($user_id, $dati, $user);
        
        // Codice fiscale: da dati richiesta con fallback a user_meta
        $codice_fiscale = trim($dati['codice_fiscale'] ?? '');
        if (empty($codice_fiscale)) {
            $codice_fiscale = trim((string) get_user_meta($user_id, 'codice_fiscale', true));
        }
        
        // Telefono: da dati richiesta con fallback a user_meta
        $telefono = trim($dati['telefono'] ?? '');
        if (empty($telefono)) {
            $telefono = trim((string) get_user_meta($user_id, 'telefono', true));
            if (empty($telefono)) {
                $telefono = trim((string) get_user_meta($user_id, 'telefono_completo', true));
            }
        }
        
        // Indirizzo e localita'
        $indirizzo = trim((string) get_user_meta($user_id, 'indirizzo', true));
        $cap = trim((string) get_user_meta($user_id, 'cap', true));
        $comune = trim((string) get_user_meta($user_id, 'comune', true));
        if (empty($comune)) {
            $comune = trim((string) get_user_meta($user_id, 'citta', true));
        }
        $provincia = trim((string) get_user_meta($user_id, 'provincia', true));
        
        error_log('[WECOOP DOC UNICO] 📋 Dati compilazione:');
        error_log('[WECOOP DOC UNICO] - Nome: ' . $nome);
        error_log('[WECOOP DOC UNICO] - Cognome: ' . $cognome);
        error_log('[WECOOP DOC UNICO] - CF: ' . $codice_fiscale);
        error_log('[WECOOP DOC UNICO] - Email: ' . $user->user_email);
        error_log('[WECOOP DOC UNICO] - Tel: ' . $telefono);
        error_log('[WECOOP DOC UNICO] - Indirizzo: ' . $indirizzo);
        error_log('[WECOOP DOC UNICO] - Cap: ' . $cap);
        error_log('[WECOOP DOC UNICO] - Comune: ' . $comune);
        error_log('[WECOOP DOC UNICO] - Provincia: ' . $provincia);
        
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
                'indirizzo' => $indirizzo,
                'cap' => $cap,
                'comune' => $comune,
                'provincia' => $provincia,
                'case_id' => $richiesta_id,
                'data' => date('d/m/Y'),
                'timestamp' => current_time('mysql')
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
        $data_ora = wp_date('d/m/Y H:i:s');
        $firma_path = WECOOP_SERVIZI_PLUGIN_DIR . 'assets/img/firma_mary_delgado.png';
        $firma_disponibile = file_exists($firma_path);
        
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
        .footer-signature {
            margin-bottom: 14px;
        }
        .footer-signature p {
            margin: 0 0 6px 0;
            color: #333;
            font-size: 10px;
        }
        .footer-signature img {
            max-width: 180px;
            height: auto;
            display: inline-block;
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
        <div class="footer-signature">
            <p><strong>Firma Legale Rappresentante</strong></p>
            <?php if ($firma_disponibile): ?>
                <img src="<?php echo esc_attr($firma_path); ?>" alt="Firma legale rappresentante">
            <?php else: ?>
                <p>____________________________________</p>
            <?php endif; ?>
        </div>

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
        
        // Se è una richiesta, elimina solo i PDF vecchi non più referenziati.
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
            
        } catch (\Throwable $e) {
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

        $protected_urls = array_filter([
            trim((string) get_post_meta($richiesta_id, 'documento_unico_url', true)),
            trim((string) get_post_meta($richiesta_id, 'documento_unico_url_originale', true)),
            trim((string) get_post_meta($richiesta_id, 'documento_unico_merged_url', true))
        ]);

        $protected_paths = [];
        foreach ($protected_urls as $protected_url) {
            $protected_path = self::upload_url_to_path($protected_url);
            if (!empty($protected_path)) {
                $protected_paths[] = wp_normalize_path($protected_path);
            }
        }

        $protected_paths = array_unique($protected_paths);
        $removed_count = 0;
        $skipped_count = 0;
        
        // Elimina solo file non protetti dai metadati correnti.
        foreach ($files as $file) {
            $normalized_file = wp_normalize_path($file);
            if (in_array($normalized_file, $protected_paths, true)) {
                $skipped_count++;
                error_log("[WECOOP DOC PDF] 🔒 PDF preservato: $file");
                continue;
            }

            if (file_exists($file)) {
                if (unlink($file)) {
                    $removed_count++;
                    error_log("[WECOOP DOC PDF] ✅ PDF rimosso: $file");
                } else {
                    error_log("[WECOOP DOC PDF] ❌ Impossibile rimuovere: $file");
                }
            }
        }
        
        error_log("[WECOOP DOC PDF] ✅ Pulizia completata - rimossi: $removed_count, preservati: $skipped_count");
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

        [$nome, $cognome, $nome_completo] = self::resolve_nome_cognome($user_id, $dati, $user);
        
        // Codice fiscale: da dati richiesta con fallback a user_meta
        $codice_fiscale = trim($dati['codice_fiscale'] ?? '');
        if (empty($codice_fiscale)) {
            $codice_fiscale = trim((string) get_user_meta($user_id, 'codice_fiscale', true));
        }
        
        // Telefono: da dati richiesta con fallback a user_meta
        $telefono = trim($dati['telefono'] ?? '');
        if (empty($telefono)) {
            $telefono = trim((string) get_user_meta($user_id, 'telefono', true));
            if (empty($telefono)) {
                $telefono = trim((string) get_user_meta($user_id, 'telefono_completo', true));
            }
        }
        
        // Indirizzo e localita'
        $indirizzo = trim((string) get_user_meta($user_id, 'indirizzo', true));
        $cap = trim((string) get_user_meta($user_id, 'cap', true));
        $comune = trim((string) get_user_meta($user_id, 'comune', true));
        if (empty($comune)) {
            $comune = trim((string) get_user_meta($user_id, 'citta', true));
        }
        $provincia = trim((string) get_user_meta($user_id, 'provincia', true));
        
        // Compila placeholders
        return self::compila_placeholders(
            $documento_testo,
            [
                'nome' => $nome,
                'cognome' => $cognome,
                'nome_completo' => $nome_completo,
                'codice_fiscale' => $codice_fiscale,
                'email' => $user->user_email,
                'telefono' => $telefono,
                'indirizzo' => $indirizzo,
                'cap' => $cap,
                'comune' => $comune,
                'provincia' => $provincia,
                'case_id' => $richiesta_id,
                'data' => date('d/m/Y'),
                'timestamp' => current_time('mysql')
            ]
        );
    }

    /**
     * Genera un PDF separato con i dati di firma digitale (post-firma).
     */
    public static function generate_attestato_firma_pdf($richiesta_id, array $firma_info) {
        $firma_id = intval($firma_info['firma_id'] ?? 0);
        $user_id = intval($firma_info['user_id'] ?? 0);
        $otp_id = intval($firma_info['otp_id'] ?? 0);
        $firma_timestamp = (string) ($firma_info['firma_timestamp'] ?? current_time('mysql'));
        $firma_hash = (string) ($firma_info['firma_hash'] ?? '');
        $documento_hash = (string) ($firma_info['documento_hash'] ?? '');
        $firma_tipo = (string) ($firma_info['firma_tipo'] ?? 'FES');
        $otp_verified_at = (string) ($firma_info['otp_verified_at'] ?? '');
        $metadata = (array) ($firma_info['metadata'] ?? []);

        if (!$richiesta_id || !$firma_id) {
            return [
                'success' => false,
                'message' => 'Dati attestato firma non validi'
            ];
        }

        $user = $user_id ? get_userdata($user_id) : null;
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        [$nome, $cognome, $nome_completo] = self::resolve_nome_cognome($user_id, $dati, $user);

        $codice_fiscale = trim((string) ($dati['codice_fiscale'] ?? get_user_meta($user_id, 'codice_fiscale', true)));
        $email = $user ? (string) $user->user_email : '';

        $ip_address = (string) ($metadata['ip_address'] ?? '');
        $app_version = (string) ($metadata['app_version'] ?? '');
        $device_info = $metadata['device_info'] ?? '';
        $device_info_text = is_array($device_info) ? wp_json_encode($device_info) : (string) $device_info;

        $firma_hash_short = $firma_hash ? substr($firma_hash, 0, 20) . '...' : '—';
        $doc_hash_short = $documento_hash ? substr($documento_hash, 0, 20) . '...' : '—';

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Attestato Firma Digitale WECOOP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #222; margin: 20px; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        h2 { font-size: 13px; margin: 18px 0 8px 0; }
        .muted { color: #666; font-size: 10px; }
        .box { border: 1px solid #d7d7d7; padding: 10px; margin: 10px 0; }
        .row { margin: 4px 0; }
        .label { font-weight: bold; display: inline-block; min-width: 170px; }
        code { font-family: monospace; font-size: 10px; }
    </style>
</head>
<body>
    <h1>ATTESTATO FIRMA DIGITALE</h1>
    <div class="muted">WECOOP APS — Allegato di attestazione firma</div>

    <div class="box">
        <div class="row"><span class="label">Richiesta (Case ID):</span> #<?php echo intval($richiesta_id); ?></div>
        <div class="row"><span class="label">Firma ID:</span> #<?php echo intval($firma_id); ?></div>
        <div class="row"><span class="label">Tipo firma:</span> <?php echo esc_html($firma_tipo); ?></div>
        <div class="row"><span class="label">Data e ora firma:</span> <?php echo esc_html($firma_timestamp ?: '—'); ?></div>
        <div class="row"><span class="label">OTP ID:</span> <?php echo $otp_id ? ('#' . intval($otp_id)) : '—'; ?></div>
        <div class="row"><span class="label">OTP verificato alle:</span> <?php echo esc_html($otp_verified_at ?: '—'); ?></div>
    </div>

    <h2>Dati Firmatario</h2>
    <div class="box">
        <div class="row"><span class="label">Nome e Cognome:</span> <?php echo esc_html($nome_completo ?: trim($nome . ' ' . $cognome) ?: '—'); ?></div>
        <div class="row"><span class="label">Codice Fiscale:</span> <?php echo esc_html($codice_fiscale ?: '—'); ?></div>
        <div class="row"><span class="label">Email:</span> <?php echo esc_html($email ?: '—'); ?></div>
    </div>

    <h2>Tracciabilità Tecnica</h2>
    <div class="box">
        <div class="row"><span class="label">Hash Firma (SHA-256):</span> <code><?php echo esc_html($firma_hash_short); ?></code></div>
        <div class="row"><span class="label">Hash Documento (SHA-256):</span> <code><?php echo esc_html($doc_hash_short); ?></code></div>
        <div class="row"><span class="label">IP:</span> <?php echo esc_html($ip_address ?: '—'); ?></div>
        <div class="row"><span class="label">App Version:</span> <?php echo esc_html($app_version ?: '—'); ?></div>
        <div class="row"><span class="label">Device Info:</span> <?php echo esc_html($device_info_text ?: '—'); ?></div>
    </div>

    <p class="muted">Documento generato automaticamente in data <?php echo esc_html(wp_date('d/m/Y H:i:s')); ?>.</p>
</body>
</html>
        <?php

        $html = ob_get_clean();
        $pdf_result = self::html_to_pdf($html, 'Attestato_Firma_' . $richiesta_id . '_' . $firma_id);
        if (!$pdf_result['success']) {
            return $pdf_result;
        }

        $file_hash = '';
        if (!empty($pdf_result['filepath']) && file_exists($pdf_result['filepath'])) {
            $file_hash = hash_file('sha256', $pdf_result['filepath']);
        }

        return [
            'success' => true,
            'url' => $pdf_result['url'],
            'filepath' => $pdf_result['filepath'],
            'hash_sha256' => $file_hash,
            'message' => 'Attestato firma PDF generato'
        ];
    }

    /**
     * Unisce il Documento Unico con l'Attestato Firma in un unico PDF finale.
     */
    public static function merge_documento_unico_with_attestato($richiesta_id, $documento_url, $attestato_url) {
        $documento_url = trim((string) $documento_url);
        $attestato_url = trim((string) $attestato_url);

        if (empty($documento_url) || empty($attestato_url)) {
            return [
                'success' => false,
                'message' => 'URL documento o attestato mancanti'
            ];
        }

        $documento_path = self::upload_url_to_path($documento_url);
        $attestato_path = self::upload_url_to_path($attestato_url);

        if (!$documento_path || !file_exists($documento_path)) {
            return [
                'success' => false,
                'message' => 'PDF Documento Unico non trovato su disco',
                'documento_url' => $documento_url,
                'documento_path' => $documento_path,
                'attestato_url' => $attestato_url,
                'attestato_path' => $attestato_path
            ];
        }

        if (!$attestato_path || !file_exists($attestato_path)) {
            return [
                'success' => false,
                'message' => 'PDF attestato firma non trovato su disco',
                'documento_url' => $documento_url,
                'documento_path' => $documento_path,
                'attestato_url' => $attestato_url,
                'attestato_path' => $attestato_path
            ];
        }

        $autoload = WECOOP_SERVIZI_PLUGIN_DIR . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        // Evita di ricostruire il documento da testo: il merge deve preservare il PDF originale
        // e aggiungere in append l'attestato di firma.
        $fpdi_available = class_exists('setasign\\Fpdi\\Fpdi', false);
        $fpdf_available = class_exists('FPDF', false);

        if (!$fpdi_available || !$fpdf_available) {
            error_log('[WECOOP DOC PDF] ❌ FPDI/FPDF non disponibili: merge append non eseguibile senza preservare l\'originale');
            return [
                'success' => false,
                'message' => 'Merge PDF non disponibile: manca il supporto FPDI/FPDF per appendere l\'attestato senza alterare il documento originale'
            ];
        }

        $upload_dir = wp_upload_dir();
        $output_dir = trailingslashit($upload_dir['basedir']) . 'wecoop-documenti-unici/';
        if (!is_dir($output_dir)) {
            wp_mkdir_p($output_dir);
        }

        $merged_path = $output_dir . 'Documento_Unico_Firmato_' . intval($richiesta_id) . '_' . time() . '.pdf';

        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $sources = [$documento_path, $attestato_path];

            foreach ($sources as $source_path) {
                $page_count = $pdf->setSourceFile($source_path);
                for ($page_no = 1; $page_no <= $page_count; $page_no++) {
                    $template_id = $pdf->importPage($page_no);
                    $size = $pdf->getTemplateSize($template_id);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($template_id);
                }
            }

            if (!method_exists($pdf, 'Output')) {
                return [
                    'success' => false,
                    'message' => 'Metodo Output non disponibile nel motore PDF'
                ];
            }

            call_user_func([$pdf, 'Output'], 'F', $merged_path);

            if (!file_exists($merged_path)) {
                return [
                    'success' => false,
                    'message' => 'File PDF merged non creato'
                ];
            }

            $merged_url = self::path_to_upload_url($merged_path);
            $merged_hash = hash_file('sha256', $merged_path);

            return [
                'success' => true,
                'url' => $merged_url,
                'filepath' => $merged_path,
                'hash_sha256' => $merged_hash,
                'message' => 'Documento Unico e attestato firma uniti con successo'
            ];
        } catch (\Throwable $e) {
            error_log('[WECOOP DOC PDF] ❌ Errore merge PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Errore merge PDF: impossibile generare il documento finale mantenendo invariato l\'originale'
            ];
        }
    }

    /**
     * Converte URL upload in path locale.
     */
    private static function upload_url_to_path($url) {
        $upload_dir = wp_upload_dir();
        $baseurl = rtrim((string) $upload_dir['baseurl'], '/');
        $basedir = rtrim((string) $upload_dir['basedir'], '/');
        $url = trim((string) $url);

        if ($url === '' || $basedir === '') {
            return '';
        }

        if ($baseurl !== '' && strpos($url, $baseurl) === 0) {
            $relative = ltrim(substr($url, strlen($baseurl)), '/');
            $relative = rawurldecode($relative);
            return $basedir . '/' . $relative;
        }

        $parsed = wp_parse_url($url);
        $path_component = (string) ($parsed['path'] ?? '');
        if ($path_component !== '') {
            $uploads_pos = strpos($path_component, '/uploads/');
            if ($uploads_pos !== false) {
                $relative = substr($path_component, $uploads_pos + strlen('/uploads/'));
                return $basedir . '/' . ltrim(rawurldecode($relative), '/');
            }

            $basename = basename($path_component);
            if ($basename !== '' && $basename !== '.' && $basename !== '..') {
                return $basedir . '/wecoop-documenti-unici/' . rawurldecode($basename);
            }
        }

        return '';
    }

    /**
     * Converte path locale upload in URL pubblico.
     */
    private static function path_to_upload_url($path) {
        $upload_dir = wp_upload_dir();
        $baseurl = rtrim((string) $upload_dir['baseurl'], '/');
        $basedir = rtrim((string) $upload_dir['basedir'], '/');
        $path = trim((string) $path);

        if ($path === '' || strpos($path, $basedir) !== 0) {
            return '';
        }

        $relative = ltrim(substr($path, strlen($basedir)), '/');
        return $baseurl . '/' . $relative;
    }

    /**
     * Risolve nome/cognome con fallback robusto:
     * 1) dati richiesta
     * 2) nome completo nei dati
     * 3) user meta (nome/cognome, first_name/last_name)
     * 4) campi WP_User
     */
    private static function resolve_nome_cognome($user_id, array $dati, $user = null) {
        $nome = trim((string) ($dati['nome'] ?? $dati['first_name'] ?? ''));
        $cognome = trim((string) ($dati['cognome'] ?? $dati['last_name'] ?? ''));

        if ((empty($nome) || empty($cognome)) && !empty($dati['nome_completo'])) {
            [$nome_full, $cognome_full] = self::split_nome_completo((string) $dati['nome_completo']);
            $nome = $nome ?: $nome_full;
            $cognome = $cognome ?: $cognome_full;
        }

        if (empty($nome)) {
            $nome = trim((string) get_user_meta($user_id, 'nome', true));
        }
        if (empty($cognome)) {
            $cognome = trim((string) get_user_meta($user_id, 'cognome', true));
        }

        if (empty($nome)) {
            $nome = trim((string) get_user_meta($user_id, 'first_name', true));
        }
        if (empty($cognome)) {
            $cognome = trim((string) get_user_meta($user_id, 'last_name', true));
        }

        if ($user instanceof WP_User) {
            if (empty($nome)) {
                $nome = trim((string) $user->first_name);
            }
            if (empty($cognome)) {
                $cognome = trim((string) $user->last_name);
            }

            if ((empty($nome) || empty($cognome)) && !empty($user->display_name)) {
                [$nome_display, $cognome_display] = self::split_nome_completo((string) $user->display_name);
                $nome = $nome ?: $nome_display;
                $cognome = $cognome ?: $cognome_display;
            }
        }

        $nome_completo = trim($nome . ' ' . $cognome);

        return [$nome, $cognome, $nome_completo];
    }

    /**
     * Divide un nome completo in nome e cognome.
     */
    private static function split_nome_completo($nome_completo) {
        $nome_completo = trim((string) $nome_completo);
        if ($nome_completo === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $nome_completo);
        if (count($parts) === 1) {
            return [$parts[0], ''];
        }

        $nome = array_shift($parts);
        $cognome = implode(' ', $parts);

        return [trim($nome), trim($cognome)];
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

I dettagli della firma digitale (OTP, data/ora firma e tracciabilità tecnica) vengono allegati nelle pagine finali di questo stesso PDF dopo la firma.
EOT;
    }
}

