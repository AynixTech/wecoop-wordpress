<?php
/**
 * Sistema Email Multilingua
 * 
 * Gestisce l'invio di email tradotte basandosi sull'header Accept-Language
 * 
 * @package WeCoop_Core
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Multilingual_Email {
    
    /**
     * Traduzioni email
     */
    private static $translations = [
        'it' => [
            // Approvazione socio
            'member_approved_subject' => '🎉 Benvenuto in WECOOP - Credenziali di Accesso',
            'member_approved_title' => 'Benvenuto in WECOOP, {nome}! 🎉',
            'member_approved_intro' => 'La tua richiesta di adesione è stata <strong>approvata</strong>!',
            'member_approved_credentials_title' => '📋 I tuoi dati di accesso:',
            'member_approved_email_label' => 'Email / Username:',
            'member_approved_password_label' => 'Password temporanea:',
            'member_approved_card_number_label' => 'Numero tessera:',
            'member_approved_card_title' => '🎫 La tua Tessera Digitale',
            'member_approved_card_text' => 'Visualizza e scarica la tua tessera digitale con QR Code:',
            'member_approved_card_button' => '📱 Visualizza Tessera',
            'member_approved_steps_title' => '✅ Primi passi:',
            'member_approved_step1' => 'Accedi alla piattaforma usando le credenziali sopra',
            'member_approved_step2' => 'Cambia la password temporanea per sicurezza',
            'member_approved_step3' => 'Completa il tuo profilo con tutti i dati',
            'member_approved_step4' => 'Salva il link della tessera digitale tra i preferiti',
            'member_approved_step5' => 'Esplora i servizi disponibili per i soci',
            'member_approved_warning' => '<strong>⚠️ Importante:</strong> Conserva con cura le tue credenziali di accesso. Ti consigliamo di cambiare la password al primo accesso.',
            'member_approved_footer' => 'Siamo felici di averti nella nostra cooperativa! Per qualsiasi domanda, non esitare a contattarci.',
            'member_approved_button_text' => '🔐 Accedi Subito alla Piattaforma',
            'member_approved_preheader' => 'La tua richiesta è stata approvata - Ecco le tue credenziali',
            
            // Richiesta servizio creata
            'service_created_subject' => '✅ Richiesta Servizio Ricevuta',
            'service_created_title' => 'Ciao {nome}, la tua richiesta è stata ricevuta!',
            'service_created_intro' => 'Abbiamo ricevuto la tua richiesta per il servizio <strong>{servizio}</strong>.',
            'service_created_details' => 'Dettagli della richiesta:',
            'service_created_service_label' => 'Servizio:',
            'service_created_date_label' => 'Data richiesta:',
            'service_created_status_label' => 'Stato:',
            'service_created_status_pending' => 'In attesa di elaborazione',
            'service_created_next_steps' => 'Ti contatteremo presto per confermare e fissare i dettagli.',
            'service_created_button_text' => 'Vedi le Tue Richieste',
            
            // Richiesta servizio approvata
            'service_approved_subject' => '✅ Richiesta Servizio Approvata',
            'service_approved_title' => 'Ottima notizia, {nome}! 🎉',
            'service_approved_intro' => 'La tua richiesta per il servizio <strong>{servizio}</strong> è stata approvata!',
            'service_approved_details' => 'Dettagli del servizio:',
            'service_approved_next' => 'Verrai contattato a breve per definire i dettagli dell\'appuntamento.',
            
            // Richiesta servizio rifiutata
            'service_rejected_subject' => '❌ Richiesta Servizio Non Approvata',
            'service_rejected_title' => 'Ciao {nome}',
            'service_rejected_intro' => 'Ci dispiace informarti che la tua richiesta per <strong>{servizio}</strong> non può essere accettata al momento.',
            'service_rejected_reason' => 'Motivo:',
            'service_rejected_footer' => 'Per maggiori informazioni, contattaci direttamente.',
            
            // Iscrizione evento
            'event_registered_subject' => '🎉 Iscrizione Evento Confermata',
            'event_registered_title' => 'Iscrizione Confermata!',
            'event_registered_intro' => 'Ciao <strong>{nome}</strong>, la tua iscrizione all\'evento <strong>{evento}</strong> è stata confermata!',
            'event_registered_details' => 'Dettagli dell\'evento:',
            'event_registered_date_label' => 'Data:',
            'event_registered_location_label' => 'Luogo:',
            'event_registered_reminder' => 'Ti invieremo un promemoria prima dell\'evento.',
            'event_registered_button_text' => 'Vedi Dettagli Evento',
            
            // Cancellazione iscrizione evento
            'event_unregistered_subject' => '❌ Cancellazione Iscrizione Evento',
            'event_unregistered_title' => 'Iscrizione Cancellata',
            'event_unregistered_intro' => 'La tua iscrizione all\'evento <strong>{evento}</strong> è stata cancellata.',
            
            // Reset password
            'password_reset_subject' => '🔐 Reimposta la tua Password',
            'password_reset_title' => 'Ciao {nome},',
            'password_reset_intro' => 'Hai richiesto di reimpostare la tua password. Clicca sul pulsante qui sotto per procedere:',
            'password_reset_warning' => '<strong>Importante:</strong> Se non hai richiesto tu questa operazione, ignora questa email.',
            'password_reset_expiry' => 'Il link scadrà tra 24 ore.',
            'password_reset_button_text' => 'Reimposta Password',
            
            // Pagamento servizio richiesto
            'service_payment_required_subject' => '💳 Pagamento Richiesto - Pratica {numero_pratica}',
            'service_payment_required_title' => 'Ciao {nome}, completa il pagamento',
            'service_payment_required_intro' => 'La tua richiesta per il servizio <strong>{servizio}</strong> è stata presa in carico!',
            'service_payment_required_details' => 'Dettagli del servizio:',
            'service_payment_required_service_label' => 'Servizio:',
            'service_payment_required_practice_label' => 'Numero Pratica:',
            'service_payment_required_amount_label' => 'Importo da pagare:',
            'service_payment_required_action' => 'Per procedere, completa il pagamento cliccando sul pulsante qui sotto:',
            'service_payment_required_button_text' => '💳 Paga Ora',
            'service_payment_required_footer' => 'Una volta completato il pagamento, inizieremo immediatamente a lavorare sulla tua richiesta.',
            'service_payment_required_note' => '<strong>Nota:</strong> Il link di pagamento è sicuro e protetto. Accettiamo tutte le principali carte di credito.',
        ],
        
        'en' => [
            // Member approval
            'member_approved_subject' => '🎉 Welcome to WECOOP - Access Credentials',
            'member_approved_title' => 'Welcome to WECOOP, {nome}! 🎉',
            'member_approved_intro' => 'Your membership request has been <strong>approved</strong>!',
            'member_approved_credentials_title' => '📋 Your login credentials:',
            'member_approved_email_label' => 'Email / Username:',
            'member_approved_password_label' => 'Temporary password:',
            'member_approved_card_number_label' => 'Membership card number:',
            'member_approved_card_title' => '🎫 Your Digital Membership Card',
            'member_approved_card_text' => 'View and download your digital membership card with QR Code:',
            'member_approved_card_button' => '📱 View Card',
            'member_approved_steps_title' => '✅ First steps:',
            'member_approved_step1' => 'Login using the credentials above',
            'member_approved_step2' => 'Change your temporary password for security',
            'member_approved_step3' => 'Complete your profile with all details',
            'member_approved_step4' => 'Save the digital card link in your bookmarks',
            'member_approved_step5' => 'Explore available services for members',
            'member_approved_warning' => '<strong>⚠️ Important:</strong> Keep your credentials safe. We recommend changing your password on first login.',
            'member_approved_footer' => 'We are happy to have you in our cooperative! For any questions, don\'t hesitate to contact us.',
            'member_approved_button_text' => '🔐 Login Now',
            'member_approved_preheader' => 'Your request has been approved - Here are your credentials',
            
            // Service request created
            'service_created_subject' => '✅ Service Request Received',
            'service_created_title' => 'Hi {nome}, your request has been received!',
            'service_created_intro' => 'We have received your request for the service <strong>{servizio}</strong>.',
            'service_created_details' => 'Request details:',
            'service_created_service_label' => 'Service:',
            'service_created_date_label' => 'Request date:',
            'service_created_status_label' => 'Status:',
            'service_created_status_pending' => 'Pending',
            'service_created_next_steps' => 'We will contact you soon to confirm and arrange details.',
            'service_created_button_text' => 'View Your Requests',
            
            // Service approved
            'service_approved_subject' => '✅ Service Request Approved',
            'service_approved_title' => 'Great news, {nome}! 🎉',
            'service_approved_intro' => 'Your request for the service <strong>{servizio}</strong> has been approved!',
            'service_approved_details' => 'Service details:',
            'service_approved_next' => 'You will be contacted shortly to arrange the appointment details.',
            
            // Service rejected
            'service_rejected_subject' => '❌ Service Request Not Approved',
            'service_rejected_title' => 'Hi {nome}',
            'service_rejected_intro' => 'We regret to inform you that your request for <strong>{servizio}</strong> cannot be accepted at this time.',
            'service_rejected_reason' => 'Reason:',
            'service_rejected_footer' => 'For more information, please contact us directly.',
            
            // Event registration
            'event_registered_subject' => '🎉 Event Registration Confirmed',
            'event_registered_title' => 'Registration Confirmed!',
            'event_registered_intro' => 'Hi <strong>{nome}</strong>, your registration for the event <strong>{evento}</strong> has been confirmed!',
            'event_registered_details' => 'Event details:',
            'event_registered_date_label' => 'Date:',
            'event_registered_location_label' => 'Location:',
            'event_registered_reminder' => 'We will send you a reminder before the event.',
            'event_registered_button_text' => 'View Event Details',
            
            // Event unregistration
            'event_unregistered_subject' => '❌ Event Registration Cancelled',
            'event_unregistered_title' => 'Registration Cancelled',
            'event_unregistered_intro' => 'Your registration for the event <strong>{evento}</strong> has been cancelled.',
            
            // Password reset
            'password_reset_subject' => '🔐 Reset Your Password',
            'password_reset_title' => 'Hi {nome},',
            'password_reset_intro' => 'You requested to reset your password. Click the button below to proceed:',
            'password_reset_warning' => '<strong>Important:</strong> If you didn\'t request this, please ignore this email.',
            'password_reset_expiry' => 'The link will expire in 24 hours.',
            'password_reset_button_text' => 'Reset Password',
            
            // Service payment required
            'service_payment_required_subject' => '💳 Payment Required - Case {numero_pratica}',
            'service_payment_required_title' => 'Hi {nome}, complete your payment',
            'service_payment_required_intro' => 'Your request for the service <strong>{servizio}</strong> has been accepted!',
            'service_payment_required_details' => 'Service details:',
            'service_payment_required_service_label' => 'Service:',
            'service_payment_required_practice_label' => 'Case Number:',
            'service_payment_required_amount_label' => 'Amount to pay:',
            'service_payment_required_action' => 'To proceed, complete the payment by clicking the button below:',
            'service_payment_required_button_text' => '💳 Pay Now',
            'service_payment_required_footer' => 'Once payment is completed, we will immediately start working on your request.',
            'service_payment_required_note' => '<strong>Note:</strong> The payment link is secure and protected. We accept all major credit cards.',
        ],
        
        'fr' => [
            // Member approval
            'member_approved_subject' => '🎉 Bienvenue chez WECOOP - Identifiants d\'accès',
            'member_approved_title' => 'Bienvenue chez WECOOP, {nome}! 🎉',
            'member_approved_intro' => 'Votre demande d\'adhésion a été <strong>approuvée</strong>!',
            'member_approved_credentials_title' => '📋 Vos identifiants de connexion:',
            'member_approved_email_label' => 'Email / Nom d\'utilisateur:',
            'member_approved_password_label' => 'Mot de passe temporaire:',
            'member_approved_card_number_label' => 'Numéro de carte:',
            'member_approved_card_title' => '🎫 Votre Carte de Membre Numérique',
            'member_approved_card_text' => 'Consultez et téléchargez votre carte de membre numérique avec QR Code:',
            'member_approved_card_button' => '📱 Voir la Carte',
            'member_approved_steps_title' => '✅ Premiers pas:',
            'member_approved_step1' => 'Connectez-vous en utilisant les identifiants ci-dessus',
            'member_approved_step2' => 'Changez votre mot de passe temporaire pour plus de sécurité',
            'member_approved_step3' => 'Complétez votre profil avec tous les détails',
            'member_approved_step4' => 'Enregistrez le lien de la carte numérique dans vos favoris',
            'member_approved_step5' => 'Explorez les services disponibles pour les membres',
            'member_approved_warning' => '<strong>⚠️ Important:</strong> Conservez vos identifiants en sécurité. Nous vous recommandons de changer votre mot de passe lors de votre première connexion.',
            'member_approved_footer' => 'Nous sommes heureux de vous accueillir dans notre coopérative! Pour toute question, n\'hésitez pas à nous contacter.',
            'member_approved_button_text' => '🔐 Connectez-vous Maintenant',
            'member_approved_preheader' => 'Votre demande a été approuvée - Voici vos identifiants',
        ],
        
        'es' => [
            // Member approval
            'member_approved_subject' => '🎉 Bienvenido a WECOOP - Credenciales de Acceso',
            'member_approved_title' => '¡Bienvenido a WECOOP, {nome}! 🎉',
            'member_approved_intro' => '¡Tu solicitud de membresía ha sido <strong>aprobada</strong>!',
            'member_approved_credentials_title' => '📋 Tus credenciales de acceso:',
            'member_approved_email_label' => 'Email / Usuario:',
            'member_approved_password_label' => 'Contraseña temporal:',
            'member_approved_card_number_label' => 'Número de tarjeta:',
            'member_approved_card_title' => '🎫 Tu Tarjeta de Miembro Digital',
            'member_approved_card_text' => 'Visualiza y descarga tu tarjeta de miembro digital con código QR:',
            'member_approved_card_button' => '📱 Ver Tarjeta',
            'member_approved_steps_title' => '✅ Primeros pasos:',
            'member_approved_step1' => 'Inicia sesión usando las credenciales anteriores',
            'member_approved_step2' => 'Cambia tu contraseña temporal por seguridad',
            'member_approved_step3' => 'Completa tu perfil con todos los detalles',
            'member_approved_step4' => 'Guarda el enlace de la tarjeta digital en tus favoritos',
            'member_approved_step5' => 'Explora los servicios disponibles para miembros',
            'member_approved_warning' => '<strong>⚠️ Importante:</strong> Guarda tus credenciales de forma segura. Te recomendamos cambiar tu contraseña en el primer acceso.',
            'member_approved_footer' => '¡Estamos felices de tenerte en nuestra cooperativa! Para cualquier pregunta, no dudes en contactarnos.',
            'member_approved_button_text' => '🔐 Iniciar Sesión Ahora',
            'member_approved_preheader' => 'Tu solicitud ha sido aprobada - Aquí están tus credenciales',
        ],
    ];
    
    /**
     * Ottieni lingua da header Accept-Language o da user meta
     */
    public static function get_user_language($user_id = null, $request = null) {
        // Log per debug
        error_log('WECOOP Multilingual: get_user_language chiamato');
        error_log('WECOOP Multilingual: user_id=' . ($user_id ?? 'null'));
        error_log('WECOOP Multilingual: request=' . (is_object($request) ? get_class($request) : 'null'));
        
        // 1. Prova con header Accept-Language dalla richiesta WP_REST_Request
        if ($request && is_object($request) && method_exists($request, 'get_header')) {
            $accept_language = $request->get_header('Accept-Language');
            error_log('WECOOP Multilingual: Accept-Language da request=' . ($accept_language ?? 'null'));
            if ($accept_language) {
                $lang = self::parse_accept_language($accept_language);
                if ($lang) {
                    error_log('WECOOP Multilingual: Lingua rilevata da header: ' . $lang);
                    // Salva preferenza utente se autenticato
                    if ($user_id) {
                        update_user_meta($user_id, 'preferred_language', $lang);
                    }
                    return $lang;
                }
            }
        }
        
        // 2. Prova con header HTTP diretto (se non c'è WP_REST_Request)
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            error_log('WECOOP Multilingual: Accept-Language da $_SERVER=' . $accept_language);
            $lang = self::parse_accept_language($accept_language);
            if ($lang) {
                error_log('WECOOP Multilingual: Lingua rilevata da $_SERVER: ' . $lang);
                if ($user_id) {
                    update_user_meta($user_id, 'preferred_language', $lang);
                }
                return $lang;
            }
        }
        
        // 3. Prova con preferenza salvata in user meta
        if ($user_id) {
            $saved_lang = get_user_meta($user_id, 'preferred_language', true);
            if ($saved_lang && isset(self::$translations[$saved_lang])) {
                error_log('WECOOP Multilingual: Lingua da user_meta: ' . $saved_lang);
                return $saved_lang;
            }
        }
        
        // 4. Fallback a italiano
        error_log('WECOOP Multilingual: Fallback a italiano');
        return 'it';
    }
    
    /**
     * Parse Accept-Language header
     */
    public static function parse_accept_language($header) {
        // Esempio: "en-US,en;q=0.9,it;q=0.8,fr;q=0.7"
        $languages = explode(',', $header);
        foreach ($languages as $lang_item) {
            $parts = explode(';', trim($lang_item));
            $lang_code = strtolower(substr($parts[0], 0, 2));
            
            if (isset(self::$translations[$lang_code])) {
                return $lang_code;
            }
        }
        return null;
    }
    
    /**
     * Ottieni traduzione
     */
    public static function get_translation($key, $lang = 'it', $replacements = []) {
        $text = self::$translations[$lang][$key] ?? self::$translations['it'][$key] ?? $key;
        
        // Sostituisci placeholders {nome}, {servizio}, ecc.
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
        
        return $text;
    }
    
    /**
     * Invia email tradotta
     */
    public static function send($to, $template_key, $data = [], $user_id = null, $request = null) {
        $lang = self::get_user_language($user_id, $request);
        
        // Ottieni traduzioni
        $subject = self::get_translation($template_key . '_subject', $lang, $data);
        
        // Costruisci contenuto in base al template
        $content = self::build_content($template_key, $lang, $data);
        
        // Invia con template unificato
        if (class_exists('WeCoop_Email_Template_Unified')) {
            $button_text = self::get_translation($template_key . '_button_text', $lang, $data);
            $preheader = self::get_translation($template_key . '_preheader', $lang, $data);
            
            return WeCoop_Email_Template_Unified::send($to, $subject, $content, [
                'preheader' => $preheader,
                'button_text' => $button_text,
                'button_url' => $data['button_url'] ?? wp_login_url()
            ]);
        }
        
        return wp_mail($to, $subject, $content);
    }
    
    /**
     * Costruisci contenuto email
     */
    private static function build_content($template_key, $lang, $data) {
        switch ($template_key) {
            case 'member_approved':
                return self::build_member_approved_content($lang, $data);
            case 'service_created':
                return self::build_service_created_content($lang, $data);
            case 'service_approved':
                return self::build_service_approved_content($lang, $data);
            case 'service_rejected':
                return self::build_service_rejected_content($lang, $data);
            case 'service_payment_required':
                return self::build_service_payment_required_content($lang, $data);
            case 'event_registered':
                return self::build_event_registered_content($lang, $data);
            case 'event_unregistered':
                return self::build_event_unregistered_content($lang, $data);
            case 'password_reset':
                return self::build_password_reset_content($lang, $data);
            default:
                return '';
        }
    }
    
    /**
     * Template: Approvazione socio
     */
    private static function build_member_approved_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('member_approved_title')}</h1>
            <p>{$t('member_approved_intro')}</p>
            
            <h2>{$t('member_approved_credentials_title')}</h2>
            <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>{$t('member_approved_email_label')}</strong> {$data['email']}</p>
                <p style='margin: 5px 0;'><strong>{$t('member_approved_password_label')}</strong> <span style='font-family: monospace; background: #e9ecef; padding: 5px 10px; border-radius: 3px;'>{$data['password']}</span></p>
                <p style='margin: 5px 0;'><strong>{$t('member_approved_card_number_label')}</strong> {$data['numero_tessera']}</p>
            </div>
            
            <h2>{$t('member_approved_steps_title')}</h2>
            <ul style='line-height: 1.8;'>
                <li>{$t('member_approved_step1')}</li>
                <li>{$t('member_approved_step2')}</li>
                <li>{$t('member_approved_step3')}</li>
                <li>{$t('member_approved_step4')}</li>
                <li>{$t('member_approved_step5')}</li>
            </ul>
            
            <p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                {$t('member_approved_warning')}
            </p>
            
            <p style='margin-top: 30px;'>{$t('member_approved_footer')}</p>
        ";
    }
    
    /**
     * Template: Richiesta servizio creata
     */
    private static function build_service_created_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('service_created_title')}</h1>
            <p>{$t('service_created_intro')}</p>
            
            <h2>{$t('service_created_details')}</h2>
            <ul>
                <li><strong>{$t('service_created_service_label')}</strong> {$data['servizio']}</li>
                <li><strong>{$t('service_created_date_label')}</strong> {$data['data']}</li>
                <li><strong>{$t('service_created_status_label')}</strong> {$t('service_created_status_pending')}</li>
            </ul>
            
            <p>{$t('service_created_next_steps')}</p>
        ";
    }
    
    /**
     * Template: Richiesta servizio approvata
     */
    private static function build_service_approved_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('service_approved_title')}</h1>
            <p>{$t('service_approved_intro')}</p>
            <p>{$t('service_approved_next')}</p>
        ";
    }
    
    /**
     * Template: Richiesta servizio rifiutata
     */
    private static function build_service_rejected_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        $reason = isset($data['motivo']) ? "<p><strong>{$t('service_rejected_reason')}</strong> {$data['motivo']}</p>" : '';
        
        return "
            <h1>{$t('service_rejected_title')}</h1>
            <p>{$t('service_rejected_intro')}</p>
            {$reason}
            <p>{$t('service_rejected_footer')}</p>
        ";
    }
    
    /**
     * Template: Iscrizione evento
     */
    private static function build_event_registered_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('event_registered_title')}</h1>
            <p>{$t('event_registered_intro')}</p>
            
            <h2>{$t('event_registered_details')}</h2>
            <ul>
                <li><strong>{$t('event_registered_date_label')}</strong> {$data['data']}</li>
                <li><strong>{$t('event_registered_location_label')}</strong> {$data['luogo']}</li>
            </ul>
            
            <p>{$t('event_registered_reminder')}</p>
        ";
    }
    
    /**
     * Template: Cancellazione iscrizione evento
     */
    private static function build_event_unregistered_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('event_unregistered_title')}</h1>
            <p>{$t('event_unregistered_intro')}</p>
        ";
    }
    
    /**
     * Template: Reset password
     */
    private static function build_password_reset_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('password_reset_title')}</h1>
            <p>{$t('password_reset_intro')}</p>
            <p>{$t('password_reset_warning')}</p>
            <p>{$t('password_reset_expiry')}</p>
        ";
    }
    
    /**
     * Template: Pagamento servizio richiesto
     */
    private static function build_service_payment_required_content($lang, $data) {
        $t = function($key) use ($lang, $data) {
            return self::get_translation($key, $lang, $data);
        };
        
        return "
            <h1>{$t('service_payment_required_title')}</h1>
            <p>{$t('service_payment_required_intro')}</p>
            
            <h2>{$t('service_payment_required_details')}</h2>
            <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 8px 0;'><strong>{$t('service_payment_required_service_label')}</strong> {$data['servizio']}</p>
                <p style='margin: 8px 0;'><strong>{$t('service_payment_required_practice_label')}</strong> {$data['numero_pratica']}</p>
                <p style='margin: 8px 0; font-size: 20px; color: #2c3e50;'><strong>{$t('service_payment_required_amount_label')}</strong> <span style='color: #27ae60; font-weight: bold;'>{$data['importo']}</span></p>
            </div>
            
            <p>{$t('service_payment_required_action')}</p>
            
            <div style='background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60; margin: 20px 0;'>
                <p style='margin: 0;'>✅ <strong>Metodi di pagamento accettati:</strong></p>
                <p style='margin: 5px 0 0 0;'>💳 Carte di credito e debito • 🏦 Bonifico bancario • 💰 PayPal</p>
            </div>
            
            <p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                {$t('service_payment_required_note')}
            </p>
            
            <p style='margin-top: 30px;'>{$t('service_payment_required_footer')}</p>
            
            <p style='font-size: 13px; color: #666; margin-top: 20px;'>
                <strong>Hai bisogno di aiuto?</strong><br>
                Contattaci a <a href='mailto:info@wecoop.org' style='color: #3498db;'>info@wecoop.org</a> o chiamaci al numero verde.
            </p>
        ";
    }
}
