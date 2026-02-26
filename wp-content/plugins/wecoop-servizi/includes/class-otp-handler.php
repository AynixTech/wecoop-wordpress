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
     * Timeout chiamate HTTP verso provider SMS
     */
    const SMS_HTTP_TIMEOUT = 15;
    
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

        // Prendi email utente
        $user = get_userdata($user_id);
        $email = $user ? $user->user_email : null;
        
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
        
        // Invio OTP su canali disponibili
        $sms_sent = self::send_otp_via_sms($telefono, $otp_code, $richiesta_id, $user_id);
        $email_sent = self::send_otp_via_email($email, $otp_code, $richiesta_id);

        // Almeno un canale deve riuscire
        if (!$sms_sent && !$email_sent) {
            $wpdb->update(
                self::$table_name,
                [
                    'status' => 'delivery_failed',
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $otp_id],
                ['%s', '%s'],
                ['%d']
            );

            error_log("[WECOOP OTP] ❌ Invio OTP fallito su tutti i canali (richiesta #{$richiesta_id}, otp_id #{$otp_id})");

            return [
                'success' => false,
                'message' => 'Impossibile inviare OTP via SMS o email. Contatta supporto.'
            ];
        }
        
        $canali = [];
        if ($sms_sent) {
            $canali[] = 'sms';
        }
        if ($email_sent) {
            $canali[] = 'email';
        }

        error_log("[WECOOP OTP] ✅ OTP generato per richiesta #{$richiesta_id}, otp_id #{$otp_id}, canali: " . implode(',', $canali));

        $masked_phone = self::mask_phone($telefono);
        $masked_email = self::mask_email($email);

        if ($sms_sent && $email_sent) {
            $message = 'OTP inviato via SMS ed email';
        } elseif ($sms_sent) {
            $message = 'OTP inviato via SMS al numero ' . $masked_phone;
        } else {
            $message = 'OTP inviato via email a ' . $masked_email;
        }
        
        return [
            'success' => true,
            'message' => $message,
            'otp_id' => $otp_id,
            'delivery_channels' => $canali,
            'masked_phone' => $masked_phone,
            'masked_email' => $masked_email,
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
     * Invia OTP via SMS.
     * Supporta due modalità:
     * - Twilio (provider: twilio)
     * - Webhook custom (provider: webhook)
     */
    private static function send_otp_via_sms($telefono, $otp_code, $richiesta_id = null, $user_id = null) {
        if (!$telefono) {
            error_log('[WECOOP OTP] ⚠️ Nessun numero telefonico fornito');
            return false;
        }

        $normalized_phone = self::normalize_phone($telefono);
        if (!$normalized_phone) {
            error_log("[WECOOP OTP] ❌ Numero telefonico non valido: {$telefono}");
            return false;
        }

        $provider = strtolower((string) self::get_otp_setting('WECOOP_SMS_PROVIDER', 'webhook'));
        $message = "Codice OTP WECOOP: {$otp_code}. Valido per " . intval(self::OTP_EXPIRY_TIME / 60) . " minuti.";

        if ($provider === 'twilio') {
            return self::send_sms_via_twilio($normalized_phone, $message);
        }

        if ($provider === 'webhook') {
            return self::send_sms_via_webhook($normalized_phone, $message, $otp_code, $richiesta_id, $user_id);
        }

        error_log("[WECOOP OTP] ❌ Provider SMS non supportato: {$provider}");
        return false;
    }

    /**
     * Invio OTP via email.
     */
    private static function send_otp_via_email($email, $otp_code, $richiesta_id = null) {
        if (!$email || !is_email($email)) {
            error_log('[WECOOP OTP] ⚠️ Email non valida o assente per invio OTP');
            return false;
        }

        $subject = '🔐 Codice OTP Firma Documento WECOOP';
        $message = "Ciao,\n\n";
        $message .= "Il tuo codice OTP per la firma digitale è: {$otp_code}\n\n";
        $message .= "Il codice è valido per " . intval(self::OTP_EXPIRY_TIME / 60) . " minuti.\n";
        if ($richiesta_id) {
            $message .= "ID richiesta: {$richiesta_id}\n";
        }
        $message .= "\nSe non hai richiesto tu questo codice, ignora questa email.\n\n";
        $message .= "Team WECOOP";

        $sent = wp_mail($email, $subject, $message);
        if (!$sent) {
            error_log("[WECOOP OTP] ❌ Invio email OTP fallito per {$email}");
            return false;
        }

        error_log("[WECOOP OTP] ✅ OTP email inviato a " . self::mask_email($email));
        return true;
    }

    /**
     * Invio SMS via Twilio API.
     */
    private static function send_sms_via_twilio($to, $message) {
        $sid = self::get_otp_setting('WECOOP_TWILIO_ACCOUNT_SID');
        $token = self::get_otp_setting('WECOOP_TWILIO_AUTH_TOKEN');
        $from = self::get_otp_setting('WECOOP_TWILIO_FROM');

        if (!$sid || !$token || !$from) {
            error_log('[WECOOP OTP] ❌ Config Twilio incompleta (SID/TOKEN/FROM)');
            return false;
        }

        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $auth = base64_encode($sid . ':' . $token);

        $response = wp_remote_post($endpoint, [
            'timeout' => self::SMS_HTTP_TIMEOUT,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'To' => $to,
                'From' => $from,
                'Body' => $message
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('[WECOOP OTP] ❌ Twilio error: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            error_log("[WECOOP OTP] ❌ Twilio HTTP {$status_code}: " . substr($body, 0, 500));
            return false;
        }

        error_log('[WECOOP OTP] ✅ SMS inviato via Twilio a ' . self::mask_phone($to));
        return true;
    }

    /**
     * Invio SMS via webhook custom.
     */
    private static function send_sms_via_webhook($to, $message, $otp_code, $richiesta_id = null, $user_id = null) {
        $endpoint = self::get_otp_setting('WECOOP_SMS_WEBHOOK_URL');
        $token = self::get_otp_setting('WECOOP_SMS_WEBHOOK_TOKEN');
        $sender = self::get_otp_setting('WECOOP_SMS_SENDER', 'WECOOP');

        if (!$endpoint) {
            error_log('[WECOOP OTP] ❌ WECOOP_SMS_WEBHOOK_URL non configurato');
            return false;
        }

        $headers = [
            'Content-Type' => 'application/json'
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $payload = [
            'to' => $to,
            'message' => $message,
            'sender' => $sender,
            'otp_code' => $otp_code,
            'richiesta_id' => $richiesta_id,
            'user_id' => $user_id
        ];

        $response = wp_remote_post($endpoint, [
            'timeout' => self::SMS_HTTP_TIMEOUT,
            'headers' => $headers,
            'body' => wp_json_encode($payload)
        ]);

        if (is_wp_error($response)) {
            error_log('[WECOOP OTP] ❌ SMS webhook error: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            error_log("[WECOOP OTP] ❌ SMS webhook HTTP {$status_code}: " . substr($body, 0, 500));
            return false;
        }

        error_log('[WECOOP OTP] ✅ SMS inviato via webhook a ' . self::mask_phone($to));
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
     * Maschera email per output/log.
     */
    private static function mask_email($email) {
        if (!$email || !strpos($email, '@')) {
            return 'email sconosciuta';
        }

        [$local, $domain] = explode('@', $email, 2);
        if (strlen($local) <= 2) {
            $masked_local = substr($local, 0, 1) . '*';
        } else {
            $masked_local = substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));
        }

        return $masked_local . '@' . $domain;
    }

    /**
     * Normalizza numero telefono in formato E.164 semplificato.
     */
    private static function normalize_phone($phone) {
        if (!$phone) {
            return null;
        }

        $phone = trim($phone);
        $phone = str_replace([' ', '-', '(', ')', '.'], '', $phone);

        if (strpos($phone, '00') === 0) {
            $phone = '+' . substr($phone, 2);
        }

        if (strpos($phone, '+') === 0) {
            $digits = preg_replace('/\D/', '', $phone);
            return $digits ? '+' . $digits : null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (!$digits) {
            return null;
        }

        if (strpos($digits, '39') === 0) {
            return '+' . $digits;
        }

        // default Italia
        return '+39' . $digits;
    }

    /**
     * Recupera configurazione OTP da costanti o option.
     */
    private static function get_otp_setting($const_name, $default = null) {
        if (defined($const_name)) {
            return constant($const_name);
        }

        $option_key = strtolower($const_name);
        $option_value = get_option($option_key, null);
        if ($option_value !== null && $option_value !== '') {
            return $option_value;
        }

        return $default;
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
