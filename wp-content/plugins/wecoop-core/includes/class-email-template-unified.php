<?php
/**
 * Template Email WeCoop
 * 
 * Template unificato per tutte le email inviate agli utenti
 * 
 * @package WeCoop_Core
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Email_Template_Unified {
    private static $template_translations = [
        'it' => [
            'social_follow' => 'Seguici sui social:',
            'organization_type' => 'Associazione di Promozione Sociale',
            'registered_notice' => 'Questa email è stata inviata a te perché sei registrato sulla nostra piattaforma.',
            'error_notice' => 'Se ritieni di aver ricevuto questa email per errore, ti preghiamo di contattarci.',
            'rights_reserved' => 'Tutti i diritti riservati.',
            'terms_conditions' => 'Termini e Condizioni',
        ],
        'en' => [
            'social_follow' => 'Follow us on social media:',
            'organization_type' => 'Social Promotion Association',
            'registered_notice' => 'You are receiving this email because you are registered on our platform.',
            'error_notice' => 'If you believe you received this email by mistake, please contact us.',
            'rights_reserved' => 'All rights reserved.',
            'terms_conditions' => 'Terms and Conditions',
        ],
        'es' => [
            'social_follow' => 'Síguenos en las redes sociales:',
            'organization_type' => 'Asociación de Promoción Social',
            'registered_notice' => 'Has recibido este correo porque estás registrado en nuestra plataforma.',
            'error_notice' => 'Si crees que has recibido este correo por error, por favor contáctanos.',
            'rights_reserved' => 'Todos los derechos reservados.',
            'terms_conditions' => 'Términos y Condiciones',
        ],
        'fr' => [
            'social_follow' => 'Suivez-nous sur les réseaux sociaux :',
            'organization_type' => 'Association de Promotion Sociale',
            'registered_notice' => 'Vous recevez cet e-mail parce que vous êtes inscrit sur notre plateforme.',
            'error_notice' => 'Si vous pensez avoir reçu cet e-mail par erreur, veuillez nous contacter.',
            'rights_reserved' => 'Tous droits réservés.',
            'terms_conditions' => 'Conditions Générales',
        ],
    ];

    
    /**
     * Genera email HTML con template WeCoop
     * 
     * @param string $subject Oggetto email
     * @param string $content Contenuto principale
     * @param array $args Argomenti aggiuntivi (preheader, button_text, button_url)
     * @return string HTML email
     */
    public static function render($subject, $content, $args = []) {
        $preheader = $args['preheader'] ?? '';
        $button_text = $args['button_text'] ?? '';
        $button_url = $args['button_url'] ?? '';
        $lang = self::resolve_lang($args);
        $t = self::$template_translations[$lang] ?? self::$template_translations['it'];
        
        $logo_url = get_site_url() . '/wp-content/uploads/2025/05/wecooplogo2.png';
        $site_url = get_site_url();
        $site_name = get_bloginfo('name');
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($subject); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: transparent;
            padding: 18px 20px 10px;
            text-align: center;
        }
        .header img {
            max-width: 120px;
            height: auto;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            margin: 20px 0;
            background-color: #3498db;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            background-color: #34495e;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 15px;
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            transition: opacity 0.3s;
        }
        .social-links a:hover {
            opacity: 0.8;
        }
        .social-links i {
            font-size: 24px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .social-links img {
            width: 32px;
            height: 32px;
            vertical-align: middle;
            margin-right: 5px;
        }
        .disclaimer {
            font-size: 12px;
            color: #bdc3c7;
            margin-top: 20px;
            line-height: 1.4;
        }
        .divider {
            border-top: 1px solid #ecf0f1;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php if ($preheader): ?>
    <div style="display: none; max-height: 0; overflow: hidden;">
        <?php echo esc_html($preheader); ?>
    </div>
    <?php endif; ?>
    
    <div class="email-container">
        <!-- Header con Logo -->
        <div class="header">
            <a href="<?php echo esc_url($site_url); ?>">
                <img src="<?php echo esc_url($logo_url); ?>" alt="WeCoop Logo">
            </a>
        </div>
        
        <!-- Contenuto Principale -->
        <div class="content">
            <?php echo $content; ?>
            
            <?php if ($button_text && $button_url): ?>
            <div style="text-align: center;">
                <a href="<?php echo esc_url($button_url); ?>" class="button">
                    <?php echo esc_html($button_text); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer con Social e Disclaimer -->
        <div class="footer">
            <div class="social-links">
                <strong><?php echo esc_html($t['social_follow']); ?></strong><br><br>
                
                <a href="https://www.facebook.com/profile.php?id=61568241435990" target="_blank" style="color: #ffffff;">
                    <i class="fab fa-facebook" style="color: #1877f2;"></i> Facebook
                </a>
                
                <a href="https://www.instagram.com/wecoop_aps" target="_blank" style="color: #ffffff;">
                    <i class="fab fa-instagram" style="color: #e4405f;"></i> Instagram
                </a>
            </div>
            
            <div class="divider"></div>
            
            <div class="disclaimer">
                <p>
                    <strong><?php echo esc_html($site_name); ?></strong><br>
                   <?php echo esc_html($t['organization_type']); ?><br>
                    Via Benefattori dell'Ospedale, 3 - 20159 Milano (MI)<br>
                    Email: <a href="mailto:info@wecoop.org" style="color: #3498db;">info@wecoop.org</a>
                </p>
                
                <p>
                    <?php echo esc_html($t['registered_notice']); ?>
                    <?php echo esc_html($t['error_notice']); ?>
                </p>
                
                <p>
                    &copy; <?php echo date('Y'); ?> WeCoop. <?php echo esc_html($t['rights_reserved']); ?><br>
                    <a href="<?php echo esc_url($site_url . '/privacy-policy'); ?>" style="color: #3498db;">Privacy Policy</a> | 
                    <a href="<?php echo esc_url($site_url . '/termini-e-condizioni'); ?>" style="color: #3498db;"><?php echo esc_html($t['terms_conditions']); ?></a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Invia email con template WeCoop
     * 
     * @param string|array $to Destinatario
     * @param string $subject Oggetto
     * @param string $content Contenuto HTML
     * @param array $args Argomenti aggiuntivi
     * @return bool
     */
    public static function send($to, $subject, $content, $args = []) {
        $html = self::render($subject, $content, $args);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <noreply@wecoop.org>'
        ];
        
        return wp_mail($to, $subject, $html, $headers);
    }

    private static function resolve_lang($args = []) {
        $lang = strtolower((string) ($args['lang'] ?? ''));
        if (isset(self::$template_translations[$lang])) {
            return $lang;
        }

        if (!empty($args['user_id']) && class_exists('WeCoop_Multilingual_Email')) {
            $resolved = WeCoop_Multilingual_Email::get_user_language(intval($args['user_id']));
            if (isset(self::$template_translations[$resolved])) {
                return $resolved;
            }
        }

        return 'it';
    }
    
    /**
     * Template: Email di benvenuto socio con credenziali
     */
    public static function send_welcome($nome, $email, $password, $numero_tessera, $tessera_url, $lang = null, $username = null) {
        error_log("WECOOP Email: send_welcome chiamato per {$email}");
        
        // SEMPRE usa sistema multilingua se disponibile
        if (class_exists('WeCoop_Multilingual_Email')) {
            error_log("WECOOP Email: Usando sistema multilingua");
            
            // Ottieni user_id se possibile
            $user = get_user_by('email', $email);
            $user_id = $user ? $user->ID : null;
            
            return WeCoop_Multilingual_Email::send(
                $email,
                'member_approved',
                [
                    'nome' => $nome,
                    'email' => $email,
                    'password' => $password,
                    'numero_tessera' => $numero_tessera,
                    'tessera_url' => $tessera_url,
                    'button_url' => wp_login_url()
                ],
                $user_id,
                null // request sarà null, ma userà $_SERVER['HTTP_ACCEPT_LANGUAGE']
            );
        }
        
        $lang = in_array($lang, ['it', 'en', 'es', 'fr'], true) ? $lang : 'it';
        error_log("WECOOP Email: Sistema multilingua NON disponibile, uso fallback {$lang}");

        $username_display = $username ?: $email;
        $texts = [
            'it' => [
                'subject' => '🎉 Benvenuto in WECOOP - Credenziali di Accesso',
                'title' => 'Benvenuto in WECOOP, ' . $nome . '! 🎉',
                'approved' => 'La tua richiesta di adesione è stata <strong>approvata</strong>!',
                'access' => '📋 I tuoi dati di accesso:',
                'username' => 'Username:',
                'email' => 'Email:',
                'password' => 'Password temporanea:',
                'card_number' => 'Numero tessera:',
                'card_title' => '🎫 La tua Tessera Digitale',
                'card_text' => 'Visualizza e scarica la tua tessera digitale con QR Code:',
                'card_button' => '📱 Visualizza Tessera',
                'steps_title' => '✅ Primi passi:',
                'steps' => [
                    'Accedi alla piattaforma usando le credenziali sopra',
                    'Cambia la password temporanea per sicurezza',
                    'Completa il tuo profilo con tutti i dati',
                    'Salva il link della tessera digitale tra i preferiti',
                    'Esplora i servizi disponibili per i soci',
                ],
                'warning' => '<strong>⚠️ Importante:</strong> Conserva con cura le tue credenziali di accesso. Ti consigliamo di cambiare la password al primo accesso.',
                'footer' => 'Siamo felici di averti nella nostra cooperativa! Per qualsiasi domanda, non esitare a contattarci.',
                'preheader' => 'La tua richiesta è stata approvata - Ecco le tue credenziali',
                'button' => '🔐 Accedi Subito alla Piattaforma',
            ],
            'en' => [
                'subject' => '🎉 Welcome to WECOOP - Access Credentials',
                'title' => 'Welcome to WECOOP, ' . $nome . '! 🎉',
                'approved' => 'Your membership request has been <strong>approved</strong>!',
                'access' => '📋 Your login credentials:',
                'username' => 'Username:',
                'email' => 'Email:',
                'password' => 'Temporary password:',
                'card_number' => 'Membership card number:',
                'card_title' => '🎫 Your Digital Membership Card',
                'card_text' => 'View and download your digital membership card with QR code:',
                'card_button' => '📱 View Card',
                'steps_title' => '✅ First steps:',
                'steps' => [
                    'Log in using the credentials above',
                    'Change the temporary password for security',
                    'Complete your profile with all your details',
                    'Save the digital card link to your favorites',
                    'Explore the services available for members',
                ],
                'warning' => '<strong>⚠️ Important:</strong> Keep your credentials safe. We recommend changing your password at first login.',
                'footer' => 'We are happy to have you in our cooperative! If you have any questions, feel free to contact us.',
                'preheader' => 'Your request has been approved - Here are your credentials',
                'button' => '🔐 Access the Platform Now',
            ],
            'es' => [
                'subject' => '🎉 Bienvenido a WECOOP - Credenciales de Acceso',
                'title' => '¡Bienvenido a WECOOP, ' . $nome . '! 🎉',
                'approved' => '¡Tu solicitud de adhesión ha sido <strong>aprobada</strong>!',
                'access' => '📋 Tus datos de acceso:',
                'username' => 'Usuario:',
                'email' => 'Email:',
                'password' => 'Contraseña temporal:',
                'card_number' => 'Número de tarjeta:',
                'card_title' => '🎫 Tu Tarjeta Digital',
                'card_text' => 'Visualiza y descarga tu tarjeta digital con código QR:',
                'card_button' => '📱 Ver Tarjeta',
                'steps_title' => '✅ Primeros pasos:',
                'steps' => [
                    'Inicia sesión usando las credenciales anteriores',
                    'Cambia la contraseña temporal por seguridad',
                    'Completa tu perfil con todos tus datos',
                    'Guarda el enlace de la tarjeta digital en favoritos',
                    'Explora los servicios disponibles para los socios',
                ],
                'warning' => '<strong>⚠️ Importante:</strong> Guarda tus credenciales con cuidado. Te recomendamos cambiar la contraseña en el primer acceso.',
                'footer' => '¡Estamos felices de tenerte en nuestra cooperativa! Si tienes preguntas, no dudes en contactarnos.',
                'preheader' => 'Tu solicitud ha sido aprobada - Aquí están tus credenciales',
                'button' => '🔐 Accede a la Plataforma',
            ],
            'fr' => [
                'subject' => '🎉 Bienvenue chez WECOOP - Identifiants d\'accès',
                'title' => 'Bienvenue chez WECOOP, ' . $nome . '! 🎉',
                'approved' => 'Votre demande d\'adhésion a été <strong>approuvée</strong>!',
                'access' => '📋 Vos identifiants de connexion:',
                'username' => 'Nom d\'utilisateur:',
                'email' => 'Email:',
                'password' => 'Mot de passe temporaire:',
                'card_number' => 'Numéro de carte:',
                'card_title' => '🎫 Votre Carte Numérique',
                'card_text' => 'Consultez et téléchargez votre carte numérique avec QR code:',
                'card_button' => '📱 Voir la Carte',
                'steps_title' => '✅ Premiers pas:',
                'steps' => [
                    'Connectez-vous avec les identifiants ci-dessus',
                    'Changez le mot de passe temporaire pour plus de sécurité',
                    'Complétez votre profil avec toutes vos données',
                    'Ajoutez le lien de la carte numérique à vos favoris',
                    'Découvrez les services disponibles pour les membres',
                ],
                'warning' => '<strong>⚠️ Important:</strong> Conservez vos identifiants avec soin. Nous vous recommandons de changer le mot de passe à la première connexion.',
                'footer' => 'Nous sommes heureux de vous compter parmi notre coopérative! Pour toute question, n\'hésitez pas à nous contacter.',
                'preheader' => 'Votre demande a été approuvée - Voici vos identifiants',
                'button' => '🔐 Accéder à la Plateforme',
            ],
        ][$lang];

        $steps_html = '';
        foreach ($texts['steps'] as $step) {
            $steps_html .= '<li>' . $step . '</li>';
        }

        $content = "
            <h1>{$texts['title']}</h1>
            <p>{$texts['approved']}</p>
            
            <h2>{$texts['access']}</h2>
            <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>{$texts['username']}</strong> {$username_display}</p>
                <p style='margin: 5px 0;'><strong>{$texts['email']}</strong> {$email}</p>
                <p style='margin: 5px 0;'><strong>{$texts['password']}</strong> <span style='font-family: monospace; background: #e9ecef; padding: 5px 10px; border-radius: 3px;'>{$password}</span></p>
                <p style='margin: 5px 0;'><strong>{$texts['card_number']}</strong> {$numero_tessera}</p>
            </div>
            
            <h2>{$texts['card_title']}</h2>
            <p>{$texts['card_text']}</p>
            <p style='text-align: center; margin: 20px 0;'>
                <a href='{$tessera_url}' style='display: inline-block; padding: 12px 30px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    {$texts['card_button']}
                </a>
            </p>
            
            <h2>{$texts['steps_title']}</h2>
            <ul style='line-height: 1.8;'>
                {$steps_html}
            </ul>
            
            <p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                {$texts['warning']}
            </p>
            
            <p style='margin-top: 30px;'>{$texts['footer']}</p>
        ";
        
        return self::send($email, $texts['subject'], $content, [
            'lang' => $lang,
            'preheader' => $texts['preheader'],
            'button_text' => $texts['button'],
            'button_url' => wp_login_url()
        ]);
    }
    
    /**
     * Template: Reset password
     */
    public static function send_password_reset($to, $user_name, $reset_url) {
        if (class_exists('WeCoop_Multilingual_Email')) {
            $user = get_user_by('email', $to);
            $user_id = $user ? intval($user->ID) : null;

            return WeCoop_Multilingual_Email::send(
                $to,
                'password_reset',
                [
                    'nome' => $user_name,
                    'button_url' => $reset_url
                ],
                $user_id
            );
        }

        $content = "
            <h1>Ciao {$user_name},</h1>
            <p>Hai richiesto di reimpostare la tua password. Clicca sul pulsante qui sotto per procedere:</p>
            <p><strong>Importante:</strong> Se non hai richiesto tu questa operazione, ignora questa email.</p>
            <p>Il link scadrà tra 24 ore.</p>
        ";
        
        return self::send($to, 'Reimposta la tua password', $content, [
            'preheader' => 'Reimposta la tua password WeCoop',
            'button_text' => 'Reimposta Password',
            'button_url' => $reset_url
        ]);
    }
    
    /**
     * Template: Notifica generica
     */
    public static function send_notification($to, $title, $message, $button_text = '', $button_url = '') {
        $content = "
            <h1>{$title}</h1>
            {$message}
        ";
        
        return self::send($to, $title, $content, [
            'button_text' => $button_text,
            'button_url' => $button_url
        ]);
    }
}
