<?php
/**
 * Gestione Firma Digitale Documenti
 * 
 * TIPO DI FIRMA: FES (Firma Elettronica Semplice)
 * La firma è basata su OTP via SMS per garantire l'autenticità.
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Firma_Handler {
    
    /**
     * Tabella database
     */
    private static $table_name = '';
    
    /**
     * Inizializza
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'wecoop_firme_digitali';
        
        add_action('init', [__CLASS__, 'maybe_create_table']);
    }
    
    /**
     * Crea tabella se non esiste
     */
    public static function maybe_create_table() {
        global $wpdb;
        
        if (get_option('wecoop_firme_table_created')) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'wecoop_firme_digitali';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            otp_id bigint(20) UNSIGNED NOT NULL,
            richiesta_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            documento_id varchar(50) NOT NULL,
            documento_hash varchar(64) NOT NULL,
            documento_contenuto longtext,
            firma_tipo varchar(20) DEFAULT 'FES',
            firma_timestamp datetime,
            firma_hash varchar(255) NOT NULL,
            firma_signature_base64 longtext,
            firma_certificate_data longtext,
            firma_metadata longtext COMMENT 'JSON con info firma: device, ip, user_agent, ecc',
            firma_valida tinyint(1) DEFAULT 1,
            verifica_timestamp datetime,
            verifica_ip varchar(45),
            status varchar(50) DEFAULT 'signed',
            created_at datetime,
            updated_at datetime,
            PRIMARY KEY  (id),
            KEY richiesta_id (richiesta_id),
            KEY user_id (user_id),
            KEY otp_id (otp_id),
            KEY status (status),
            UNIQUE KEY documento_unico (richiesta_id, documento_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        update_option('wecoop_firme_table_created', true);
        
        error_log('[WECOOP FIRMA] ✅ Tabella firme digitali creata/verificata');
    }
    
    /**
     * Firma un documento con OTP verificato
     * 
     * FES (Firma Elettronica Semplice):
     * - Autenticità garantita da OTP SMS
     * - Integrità garantita da hash documento
     * - Non ripudio attraverso timestamp e IP
     */
    public static function sign_document($otp_id, $richiesta_id, $documento_contenuto, $firma_data = []) {
        global $wpdb;
        
        // Verifica OTP verificato
        $otp_record = WECOOP_OTP_Handler::get_otp_record($otp_id);
        if (!$otp_record || $otp_record->status !== 'verified') {
            return [
                'success' => false,
                'message' => 'OTP non verificato'
            ];
        }
        
        // Verifica che l'OTP sia per questa richiesta
        if ($otp_record->richiesta_id != $richiesta_id) {
            return [
                'success' => false,
                'message' => 'OTP non corrisponde alla richiesta'
            ];
        }
        
        // Verifica richiesta
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            return [
                'success' => false,
                'message' => 'Richiesta non valida'
            ];
        }
        
        // Crea hash del documento
        $documento_hash = hash('sha256', $documento_contenuto);
        
        // Crea firma hash (concatenazione di hash documento + timestamp + user_id + OTP_id)
        $firma_base_string = $documento_hash . '|' . current_time('mysql') . '|' . $otp_record->user_id . '|' . $otp_id;
        $firma_hash = hash('sha256', $firma_base_string);
        
        // Prepara metadata firma
        $firma_metadata = [
            'device_info' => $firma_data['device_info'] ?? null,
            'app_version' => $firma_data['app_version'] ?? null,
            'ip_address' => $firma_data['ip_address'] ?? self::get_client_ip(),
            'user_agent' => $firma_data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            'timestamp_firma' => current_time('mysql'),
            'otp_verificato_alle' => $otp_record->otp_verified_at
        ];
        
        // Firma base64 (simulazione di certificato - in produzione usare certificato vero)
        $firma_signature = base64_encode(json_encode([
            'hash' => $firma_hash,
            'timestamp' => current_time('mysql'),
            'user_id' => $otp_record->user_id,
            'otp_id' => $otp_id,
            'algo' => 'SHA256-OTP-FES'
        ]));
        
        // Salva firma nel database
        $result = $wpdb->insert(
            self::$table_name,
            [
                'otp_id' => $otp_id,
                'richiesta_id' => $richiesta_id,
                'user_id' => $otp_record->user_id,
                'documento_id' => 'documento_unico',
                'documento_hash' => $documento_hash,
                'documento_contenuto' => $documento_contenuto,
                'firma_timestamp' => current_time('mysql'),
                'firma_hash' => $firma_hash,
                'firma_signature_base64' => $firma_signature,
                'firma_metadata' => json_encode($firma_metadata),
                'status' => 'signed',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$result) {
            error_log('[WECOOP FIRMA] ❌ Errore salvataggio firma: ' . $wpdb->last_error);
            return [
                'success' => false,
                'message' => 'Errore nel salvataggio della firma'
            ];
        }
        
        $firma_id = $wpdb->insert_id;
        
        // Aggiorna OTP status a 'signed'
        $wpdb->update(
            $wpdb->prefix . 'wecoop_firma_otp',
            [
                'status' => 'signed',
                'firma_hash' => $firma_hash,
                'firma_timestamp' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $otp_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );
        
        // Aggiorna post meta richiesta
        update_post_meta($richiesta_id, 'documento_unico_firmato', 'yes');
        update_post_meta($richiesta_id, 'firma_id', $firma_id);
        update_post_meta($richiesta_id, 'data_firma', current_time('mysql'));
        
        error_log("[WECOOP FIRMA] ✅ Documento firmato: richiesta #{$richiesta_id}, firma_id #{$firma_id}");
        
        return [
            'success' => true,
            'message' => 'Documento firmato correttamente',
            'firma_id' => $firma_id,
            'firma_timestamp' => current_time('mysql'),
            'firma_hash' => $firma_hash,
            'firma_tipo' => 'FES'
        ];
    }
    
    /**
     * Verifica una firma digitale
     */
    public static function verify_signature($firma_id) {
        global $wpdb;
        
        $firma = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $firma_id
        ));
        
        if (!$firma) {
            return [
                'success' => false,
                'message' => 'Firma non trovata',
                'valida' => false
            ];
        }
        
        // Verifica integrità documento
        $documento_hash_calcolato = hash('sha256', $firma->documento_contenuto);
        if ($documento_hash_calcolato !== $firma->documento_hash) {
            return [
                'success' => true,
                'message' => 'Firma trovata ma documento è stato alterato',
                'valida' => false,
                'motivo' => 'Hash documento non corrisponde'
            ];
        }
        
        // Verifica OTP associato
        $otp = WECOOP_OTP_Handler::get_otp_record($firma->otp_id);
        if (!$otp || $otp->status !== 'verified' && $otp->status !== 'signed') {
            return [
                'success' => true,
                'message' => 'Firma trovata ma OTP non verificato',
                'valida' => false,
                'motivo' => 'OTP non in stato valido'
            ];
        }
        
        // Verifica timestamp scadenza ragionevole
        $firma_timestamp = strtotime($firma->firma_timestamp);
        $otp_verified_timestamp = strtotime($otp->otp_verified_at);
        
        if ($firma_timestamp < $otp_verified_timestamp || ($firma_timestamp - $otp_verified_timestamp) > 3600) {
            return [
                'success' => true,
                'message' => 'Firma trovata ma timestamp sospetto',
                'valida' => false,
                'motivo' => 'Firma eseguita in tempo sospetto dopo OTP'
            ];
        }
        
        // Firma válida!
        $metadata = json_decode($firma->firma_metadata, true);
        
        return [
            'success' => true,
            'message' => 'Firma digitale valida',
            'valida' => true,
            'firma_id' => $firma->id,
            'richiesta_id' => $firma->richiesta_id,
            'user_id' => $firma->user_id,
            'firma_timestamp' => $firma->firma_timestamp,
            'firma_tipo' => $firma->firma_tipo,
            'documento_hash' => $firma->documento_hash,
            'metadata' => $metadata
        ];
    }
    
    /**
     * Ottieni firma documento
     */
    public static function get_firma($richiesta_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " 
             WHERE richiesta_id = %d AND documento_id = 'documento_unico'
             ORDER BY created_at DESC LIMIT 1",
            $richiesta_id
        ));
    }
    
    /**
     * Ottieni IP client
     */
    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
    
    /**
     * Esporta firma in formato JSON per certificazione
     */
    public static function export_firma_json($firma_id) {
        global $wpdb;
        
        $firma = self::get_firma_by_id($firma_id);
        if (!$firma) {
            return null;
        }
        
        return [
            'id' => $firma->id,
            'tipo' => 'FES (Firma Elettronica Semplice)',
            'richiesta_id' => $firma->richiesta_id,
            'documento' => 'documento_unico',
            'firma_timestamp' => $firma->firma_timestamp,
            'firma_hash' => $firma->firma_hash,
            'documento_hash_sha256' => $firma->documento_hash,
            'metadata' => json_decode($firma->firma_metadata, true),
            'verifica' => self::verify_signature($firma_id)
        ];
    }
    
    /**
     * Ottieni firma per ID
     */
    private static function get_firma_by_id($firma_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $firma_id
        ));
    }
}
