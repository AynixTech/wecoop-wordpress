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
        $email_sent = self::send_otp_via_email($email, $otp_code, $richiesta_id, $user_id);

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

        $normalized_input_code = self::normalize_otp_code($otp_code);
        if ($normalized_input_code === null) {
            return [
                'success' => false,
                'message' => 'Formato OTP non valido'
            ];
        }
        
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
        
        // Se Twilio Verify e' attivo, valida esclusivamente il codice SMS Verify.
        if (self::is_twilio_verify_enabled()) {
            $twilio_verified = self::verify_otp_via_twilio_verify($otp_record->telefono, $normalized_input_code);

            if (!$twilio_verified) {
                $new_attempts = $otp_record->otp_attempts + 1;
                $wpdb->update(
                    self::$table_name,
                    ['otp_attempts' => $new_attempts],
                    ['id' => $otp_id],
                    ['%d'],
                    ['%d']
                );

                error_log('[WECOOP OTP] ⚠️ OTP Twilio Verify non valido: otp_id=' . $otp_id . ', attempts=' . $new_attempts);

                return [
                    'success' => false,
                    'message' => 'OTP non valido',
                    'attempts_left' => self::MAX_ATTEMPTS - $new_attempts
                ];
            }
        }
        // Altrimenti usa verifica OTP locale
        elseif (!hash_equals((string) $otp_record->otp_code, (string) $normalized_input_code)) {
            $new_attempts = $otp_record->otp_attempts + 1;
            $wpdb->update(
                self::$table_name,
                ['otp_attempts' => $new_attempts],
                ['id' => $otp_id],
                ['%d'],
                ['%d']
            );

            error_log('[WECOOP OTP] ⚠️ OTP non valido: otp_id=' . $otp_id . ', attempts=' . $new_attempts);
            
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
     * Canale SMS via Twilio (Verify o SMS classico).
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

        $message = "Codice OTP WECOOP: {$otp_code}. Valido per " . intval(self::OTP_EXPIRY_TIME / 60) . " minuti.";

        if (self::is_twilio_verify_enabled()) {
            return self::send_sms_via_twilio_verify($normalized_phone);
        }

        error_log('[WECOOP OTP] ❌ Twilio Verify non configurato: invio SMS OTP disabilitato (policy: solo Verify)');

        return false;
    }

    /**
     * Invio OTP via email.
     */
    private static function send_otp_via_email($email, $otp_code, $richiesta_id = null, $user_id = null) {
        if (!$email || !is_email($email)) {
            error_log('[WECOOP OTP] ⚠️ Email non valida o assente per invio OTP');
            return false;
        }

        $expiry_minutes = intval(self::OTP_EXPIRY_TIME / 60);

        $lang = 'it';
        if (class_exists('WeCoop_Multilingual_Email')) {
            $lang = WeCoop_Multilingual_Email::get_user_language($user_id);
        }

        $subject = class_exists('WeCoop_Multilingual_Email')
            ? WeCoop_Multilingual_Email::get_translation('otp_email_subject', $lang)
            : '🔐 Codice OTP Firma Documento WECOOP';

        if ($lang === 'en') {
            $email_intro_fallback = 'For security reasons, the OTP code is sent only by SMS to your verified phone number.';
        } elseif ($lang === 'es') {
            $email_intro_fallback = 'Por motivos de seguridad, el código OTP se envía solo por SMS a tu número verificado.';
        } elseif ($lang === 'fr') {
            $email_intro_fallback = 'Pour des raisons de sécurité, le code OTP est envoyé uniquement par SMS à votre numéro vérifié.';
        } else {
            $email_intro_fallback = 'Per motivi di sicurezza, il codice OTP viene inviato solo via SMS al tuo numero verificato.';
        }

        $email_intro = $email_intro_fallback;

        $content = '<h1 style="margin:0 0 12px; color:#2c3e50;">' . esc_html(class_exists('WeCoop_Multilingual_Email') ? WeCoop_Multilingual_Email::get_translation('otp_email_title', $lang) : 'Codice OTP') . '</h1>';
        $content .= '<p style="margin:0 0 14px;">' . $email_intro . '</p>';
        if ($lang === 'en') {
            $content .= '<p style="margin:0 0 8px;">The OTP is valid for <strong>' . $expiry_minutes . ' minutes</strong>.</p>';
        } elseif ($lang === 'es') {
            $content .= '<p style="margin:0 0 8px;">El OTP es válido durante <strong>' . $expiry_minutes . ' minutos</strong>.</p>';
        } elseif ($lang === 'fr') {
            $content .= '<p style="margin:0 0 8px;">Le code OTP est valable pendant <strong>' . $expiry_minutes . ' minutes</strong>.</p>';
        } else {
            $content .= '<p style="margin:0 0 8px;">Il codice OTP e\' valido per <strong>' . $expiry_minutes . ' minuti</strong>.</p>';
        }

        if ($richiesta_id) {
            $request_label = class_exists('WeCoop_Multilingual_Email')
                ? WeCoop_Multilingual_Email::get_translation('otp_email_request_id', $lang, ['richiesta_id' => intval($richiesta_id)])
                : ('ID richiesta: <strong>' . intval($richiesta_id) . '</strong>');
            $content .= '<p style="margin:10px 0 0; color:#666; font-size:14px;">' . $request_label . '</p>';
        }

        $content .= '<p style="margin-top:16px; color:#666; font-size:14px;">' . (class_exists('WeCoop_Multilingual_Email') ? WeCoop_Multilingual_Email::get_translation('otp_email_warning', $lang) : 'Se non hai richiesto questo codice, ignora questa email.') . '</p>';

        if (class_exists('WeCoop_Email_Template_Unified')) {
            $sent = WeCoop_Email_Template_Unified::send($email, $subject, $content, [
                'lang' => $lang,
                'preheader' => class_exists('WeCoop_Multilingual_Email') ? WeCoop_Multilingual_Email::get_translation('otp_email_preheader', $lang) : 'Codice OTP per completare la firma digitale',
                'button_text' => '',
                'button_url' => ''
            ]);
        } else {
            if ($lang === 'en') {
                $fallback_message = "Hello,\n\n";
                $fallback_message .= "For security reasons, the OTP code is sent only by SMS to your verified phone number.\n";
                $fallback_message .= "The OTP is valid for {$expiry_minutes} minutes.\n";
            } elseif ($lang === 'es') {
                $fallback_message = "Hola,\n\n";
                $fallback_message .= "Por motivos de seguridad, el código OTP se envía solo por SMS a tu número verificado.\n";
                $fallback_message .= "El OTP es válido durante {$expiry_minutes} minutos.\n";
            } elseif ($lang === 'fr') {
                $fallback_message = "Bonjour,\n\n";
                $fallback_message .= "Pour des raisons de sécurité, le code OTP est envoyé uniquement par SMS à votre numéro vérifié.\n";
                $fallback_message .= "Le code OTP est valable pendant {$expiry_minutes} minutes.\n";
            } else {
                $fallback_message = "Ciao,\n\n";
                $fallback_message .= "Per motivi di sicurezza, il codice OTP viene inviato solo via SMS al tuo numero verificato.\n";
                $fallback_message .= "Il codice OTP è valido per {$expiry_minutes} minuti.\n";
            }
            if ($richiesta_id) {
                $fallback_message .= "ID richiesta: {$richiesta_id}\n";
            }
            $fallback_message .= "\nTeam WECOOP";
            $sent = wp_mail($email, $subject, $fallback_message);
        }

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
        $messaging_service_sid = self::get_otp_setting('WECOOP_TWILIO_MESSAGING_SERVICE_SID');

        if (!$sid || !$token || (!$from && !$messaging_service_sid)) {
            error_log('[WECOOP OTP] ❌ Config Twilio incompleta (SID/TOKEN e FROM oppure MESSAGING_SERVICE_SID)');
            return false;
        }

        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $auth = base64_encode($sid . ':' . $token);

        $body = [
            'To' => $to,
            'Body' => $message
        ];

        if ($messaging_service_sid) {
            $body['MessagingServiceSid'] = $messaging_service_sid;
        } else {
            $body['From'] = $from;
        }

        $response = wp_remote_post($endpoint, [
            'timeout' => self::SMS_HTTP_TIMEOUT,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => $body
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
     * Invio OTP via Twilio Verify API.
     */
    private static function send_sms_via_twilio_verify($to) {
        $sid = self::get_otp_setting('WECOOP_TWILIO_ACCOUNT_SID');
        $token = self::get_otp_setting('WECOOP_TWILIO_AUTH_TOKEN');
        $verify_service_sid = self::get_otp_setting('WECOOP_TWILIO_VERIFY_SERVICE_SID');

        if (!$sid || !$token || !$verify_service_sid) {
            error_log('[WECOOP OTP] ❌ Config Twilio Verify incompleta (SID/TOKEN/VERIFY_SERVICE_SID)');
            return false;
        }

        $endpoint = "https://verify.twilio.com/v2/Services/{$verify_service_sid}/Verifications";
        $auth = base64_encode($sid . ':' . $token);

        $response = wp_remote_post($endpoint, [
            'timeout' => self::SMS_HTTP_TIMEOUT,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'To' => $to,
                'Channel' => 'sms'
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('[WECOOP OTP] ❌ Twilio Verify error: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            error_log("[WECOOP OTP] ❌ Twilio Verify HTTP {$status_code}: " . substr($response_body, 0, 500));
            return false;
        }

        error_log('[WECOOP OTP] ✅ OTP avviato via Twilio Verify a ' . self::mask_phone($to));
        return true;
    }

    /**
     * Verifica OTP via Twilio Verify Check API.
     */
    private static function verify_otp_via_twilio_verify($to, $otp_code) {
        $sid = self::get_otp_setting('WECOOP_TWILIO_ACCOUNT_SID');
        $token = self::get_otp_setting('WECOOP_TWILIO_AUTH_TOKEN');
        $verify_service_sid = self::get_otp_setting('WECOOP_TWILIO_VERIFY_SERVICE_SID');

        if (!$sid || !$token || !$verify_service_sid || !$to) {
            error_log('[WECOOP OTP] ❌ Verifica Twilio impossibile: config o telefono mancante');
            return false;
        }

        $to = self::normalize_phone($to);
        if (!$to) {
            error_log('[WECOOP OTP] ❌ Verifica Twilio: telefono non valido');
            return false;
        }

        $endpoint = "https://verify.twilio.com/v2/Services/{$verify_service_sid}/VerificationCheck";
        $auth = base64_encode($sid . ':' . $token);

        $response = wp_remote_post($endpoint, [
            'timeout' => self::SMS_HTTP_TIMEOUT,
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => [
                'To' => $to,
                'Code' => $otp_code
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('[WECOOP OTP] ❌ Twilio VerificationCheck error: ' . $response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            error_log("[WECOOP OTP] ❌ Twilio VerificationCheck HTTP {$status_code}: " . substr($response_body, 0, 500));
            return false;
        }

        $json = json_decode($response_body, true);
        $status = isset($json['status']) ? strtolower((string) $json['status']) : '';

        return $status === 'approved';
    }

    /**
     * Verifica se Twilio Verify e' configurato e attivo.
     */
    private static function is_twilio_verify_enabled() {
        $sid = self::get_otp_setting('WECOOP_TWILIO_ACCOUNT_SID');
        $token = self::get_otp_setting('WECOOP_TWILIO_AUTH_TOKEN');
        $verify_service_sid = self::get_otp_setting('WECOOP_TWILIO_VERIFY_SERVICE_SID');

        return !empty($sid) && !empty($token) && !empty($verify_service_sid);
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
     * Normalizza codice OTP in formato a 6 cifre.
     */
    private static function normalize_otp_code($otp_code) {
        if ($otp_code === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $otp_code);
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) > 6) {
            return null;
        }

        return str_pad($digits, 6, '0', STR_PAD_LEFT);
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
