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
        
        // Leggi documento_unico.txt
        $doc_file = WECOOP_SERVIZI_PLUGIN_DIR . 'documento_unico.txt';
        if (!file_exists($doc_file)) {
            error_log('[WECOOP DOC UNICO] ❌ File documento_unico.txt non trovato');
            return [
                'success' => false,
                'message' => 'Documento template non trovato'
            ];
        }
        
        $documento_testo = file_get_contents($doc_file);
        
        // Recupera dati richiesta e utente
        $user = get_userdata($user_id);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        // Prepara dati per compilazione
        $nome = $dati['nome'] ?? '';
        $cognome = $dati['cognome'] ?? '';
        $nome_completo = trim($nome . ' ' . $cognome);
        $codice_fiscale = $dati['codice_fiscale'] ?? '';
        $telefono = $dati['telefono'] ?? '';
        
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
                'timestamp' => current_time('mysql')
            ]
        );
        
        // Genera HTML per PDF
        $html = self::genera_html_documento($documento_compilato, $richiesta_id);
        
        error_log('[WECOOP DOC UNICO] ✅ HTML generato');
        
        // Genera PDF
        $result = self::html_to_pdf($html, "Documento_Unico_" . $richiesta_id);
        
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
        foreach ($dati as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $testo = str_replace($placeholder, $value, $testo);
        }
        
        // Rimuovi placeholder non compilati
        $testo = preg_replace('/\{\{[^}]+\}\}/', '', $testo);
        
        return $testo;
    }
    
    /**
     * Genera HTML documento formattato per PDF
     */
    private static function genera_html_documento($documento_testo, $richiesta_id) {
        $timestamp = date('d/m/Y H:i');
        $wecoop_logo = WECOOP_SERVIZI_PLUGIN_URL . 'assets/logo-wecoop.png';
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Unico WECOOP</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            background: white;
            padding: 20px;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 20px;
        }
        .header img {
            height: 40px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #666;
        }
        .document-content {
            background: #fafafa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
            text-align: center;
        }
        .document-id {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 10px;
        }
        .document-id strong {
            color: #2e7d32;
        }
        @page {
            size: A4;
            margin: 10mm;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 DOCUMENTO UNICO</h1>
            <div class="subtitle">WECOOP APS - Adesione Socio e Mandato Generale</div>
        </div>
        
        <div class="document-id">
            <strong>ID Documento:</strong> <?php echo $richiesta_id; ?> | 
            <strong>Generato:</strong> <?php echo $timestamp; ?>
        </div>
        
        <div class="document-content">
<?php echo esc_html($documento_testo); ?>
        </div>
        
        <div class="footer">
            <p>Questo documento è stato generato digitalmente e sarà firmato tramite autenticazione OTP.</p>
            <p>Firma: _________________________________ Data: _______________________</p>
            <p style="margin-top: 20px; font-size: 8px;">
                © <?php echo date('Y'); ?> WECOOP APS | Documento privato | Dispositione di legge sulla privacy
            </p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Converte HTML in PDF usando mPDF
     */
    private static function html_to_pdf($html, $filename) {
        error_log("[WECOOP DOC PDF] html_to_pdf chiamato per: $filename");
        
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
}
