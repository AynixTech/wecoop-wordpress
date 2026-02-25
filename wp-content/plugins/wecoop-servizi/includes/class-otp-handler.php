<?php
/**
 * Gestione OTP per firma digitale documenti
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_OTP_Handler {
    
    /**
     * Tabella database
     */
    private static $table_name = '';
    
    /**
     * Tempo di scadenza OTP (5 minuti)
     */
    const OTP_EXPIRY_TIME = 300;
    
    /**
     * Massimi tentativi falliti
     */
    const MAX_ATTEMPTS = 3;
    
    /**
     * Inizializza
     */
    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'wecoop_firma_otp';
        
        add_action('init', [__CLASS__, 'maybe_create_table']);
    }
    
    /**
     * Crea tabella se non esiste
     */
    public static function maybe_create_table() {
        global $wpdb;
        
        if (get_option('wecoop_firma_otp_table_created')) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'wecoop_firma_otp';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            richiesta_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            documento_id varchar(50) NOT NULL,
            telefono varchar(20),
            otp_code varchar(6) NOT NULL,
            otp_attempts int(11) DEFAULT 0,
            otp_sent_at datetime,
            otp_verified_at datetime,
            status varchar(50) DEFAULT 'pending',
            firma_hash varchar(255),
            firma_timestamp datetime,
            firma_firma_data longtext,
            firma_metadata longtext,
            created_at datetime,
            updated_at datetime,
            PRIMARY KEY  (id),
            KEY richiesta_id (richiesta_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        update_option('wecoop_firma_otp_table_created', true);
        
        error_log('[WECOOP OTP] ✅ Tabella firma OTP creata/verificata');
    }
    
    /**
     * Genera OTP per una richiesta
     */
    public static function generate_otp($richiesta_id, $user_id, $telefono = null) {
        global $wpdb;
        
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            return [
                'success' => false,
                'message' => 'Richiesta non valida'
            ];
        }
        
        // Verifica che la richiesta sia pagata
        $payment_status = get_post_meta($richiesta_id, 'payment_status', true);
        if ($payment_status !== 'paid') {
            return [
                'success' => false,
                'message' => 'La richiesta non è stata pagata'
            ];
        }
        
        // Verifica che non esista un OTP recente non scaduto
        $recent_otp = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " 
             WHERE richiesta_id = %d AND user_id = %d 
             AND status = 'pending'
             AND otp_sent_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
             ORDER BY created_at DESC LIMIT 1",
            $richiesta_id,
            $user_id
        ));
        
        if ($recent_otp) {
            return [
                'success' => false,
                'message' => 'OTP già generato. Riprova tra 1 minuto',
                'otp_id' => $recent_otp->id
            ];
        }
        
        // Genera OTP a 6 cifre
        $otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Prendi numero telefonico se non fornito
        if (!$telefono) {
            $dati = get_post_meta($richiesta_id, 'dati', true);
            $dati_array = json_decode($dati, true);
            $telefono = $dati_array['telefono'] ?? null;
        }
        
        // Salva OTP in database
        $result = $wpdb->insert(
            self::$table_name,
            [
                'richiesta_id' => $richiesta_id,
                'user_id' => $user_id,
                'documento_id' => 'documento_unico',
                'telefono' => $telefono,
                'otp_code' => $otp_code,
                'otp_sent_at' => current_time('mysql'),
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$result) {
            error_log('[WECOOP OTP] ❌ Errore salvataggio OTP: ' . $wpdb->last_error);
            return [
                'success' => false,
                'message' => 'Errore nella generazione OTP'
            ];
        }
        
        $otp_id = $wpdb->insert_id;
        
        // Invia OTP via SMS (integrazione con servizio SMS)
        $sms_sent = self::send_otp_via_sms($telefono, $otp_code);
        
        error_log("[WECOOP OTP] ✅ OTP generato per richiesta #{$richiesta_id}: {$otp_code}");
        
        return [
            'success' => true,
            'message' => 'OTP inviato al numero ' . self::mask_phone($telefono),
            'otp_id' => $otp_id,
            'masked_phone' => self::mask_phone($telefono),
            'expires_in' => self::OTP_EXPIRY_TIME
        ];
    }
    
    /**
     * Verifica OTP
     */
    public static function verify_otp($otp_id, $otp_code) {
        global $wpdb;
        
        $otp_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $otp_id
        ));
        
        if (!$otp_record) {
            return [
                'success' => false,
                'message' => 'OTP non trovato'
            ];
        }
        
        // Verifica scadenza
        $sent_at = strtotime($otp_record->otp_sent_at);
        if (time() - $sent_at > self::OTP_EXPIRY_TIME) {
            $wpdb->update(
                self::$table_name,
                ['status' => 'expired'],
                ['id' => $otp_id],
                ['%s'],
                ['%d']
            );
            return [
                'success' => false,
                'message' => 'OTP scaduto'
            ];
        }
        
        // Verifica tentativi falliti
        if ($otp_record->otp_attempts >= self::MAX_ATTEMPTS) {
            $wpdb->update(
                self::$table_name,
                ['status' => 'blocked'],
                ['id' => $otp_id],
                ['%s'],
                ['%d']
            );
            return [
                'success' => false,
                'message' => 'Troppi tentativi falliti. Genera un nuovo OTP'
            ];
        }
        
        // Verifica codice
        if ($otp_record->otp_code !== $otp_code) {
            $new_attempts = $otp_record->otp_attempts + 1;
            $wpdb->update(
                self::$table_name,
                ['otp_attempts' => $new_attempts],
                ['id' => $otp_id],
                ['%d'],
                ['%d']
            );
            
            return [
                'success' => false,
                'message' => 'OTP non valido',
                'attempts_left' => self::MAX_ATTEMPTS - $new_attempts
            ];
        }
        
        // OTP verificato
        $wpdb->update(
            self::$table_name,
            [
                'status' => 'verified',
                'otp_verified_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $otp_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
        
        error_log("[WECOOP OTP] ✅ OTP verificato per richiesta #{$otp_record->richiesta_id}");
        
        return [
            'success' => true,
            'message' => 'OTP verificato correttamente',
            'otp_id' => $otp_id
        ];
    }
    
    /**
     * Invia OTP via SMS (stub - implementare con servizio SMS preferito)
     */
    private static function send_otp_via_sms($telefono, $otp_code) {
        if (!$telefono) {
            error_log('[WECOOP OTP] ⚠️ Nessun numero telefonico fornito');
            return false;
        }
        
        // TODO: Implementare integrazione SMS
        // Possibili servizi: Twilio, Signalwire, Trengo, ecc.
        
        error_log("[WECOOP OTP] 📱 SMS con OTP inviato a {$telefono}: {$otp_code}");
        
        return true;
    }
    
    /**
     * Maschera numero telefonico
     */
    private static function mask_phone($phone) {
        if (!$phone) return 'numero sconosciuto';
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) < 4) return '****';
        return substr($phone, 0, -4) . '****';
    }
    
    /**
     * Ottieni OTP record
     */
    public static function get_otp_record($otp_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE id = %d",
            $otp_id
        ));
    }
}
