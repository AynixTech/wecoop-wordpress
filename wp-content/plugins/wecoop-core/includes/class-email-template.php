<?php
/**
 * Template Email WeCoop
 * 
 * Template unificato per tutte le email inviate agli utenti
 * 
 * @package WeCoop_Core
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Email_Template {
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
        
        $logo_url = get_site_url() . '/wp-content/uploads/2024/12/logo-wecoop.png';
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
            background-color: #2c3e50;
            padding: 30px 20px;
            text-align: center;
        }
        .header img {
            max-width: 200px;
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
     * Template: Email di benvenuto
     */
    public static function send_welcome($to, $user_name, $login_url) {
        $lang = 'it';
        if (class_exists('WeCoop_Multilingual_Email')) {
            $user = get_user_by('email', $to);
            $lang = WeCoop_Multilingual_Email::get_user_language($user ? intval($user->ID) : null);
        }

        $strings = self::$template_translations[$lang] ?? self::$template_translations['it'];
        $content = "
            <h1>" . str_replace('{name}', $user_name, ($lang === 'en' ? 'Welcome to WeCoop, {name}! 🎉' : ($lang === 'es' ? '¡Bienvenido a WeCoop, {name}! 🎉' : ($lang === 'fr' ? 'Bienvenue chez WeCoop, {name}! 🎉' : 'Benvenuto in WeCoop, {name}! 🎉')))) . "</h1>
            <p>" . ($lang === 'en' ? 'We are happy to welcome you to our community.' : ($lang === 'es' ? 'Estamos felices de darte la bienvenida a nuestra comunidad.' : ($lang === 'fr' ? 'Nous sommes heureux de vous accueillir dans notre communauté.' : 'Siamo felici di darti il benvenuto nella nostra comunità.'))) . "</p>
            <p>" . ($lang === 'en' ? 'Your registration has been completed successfully. You can now access the platform and discover all the services offered by WeCoop.' : ($lang === 'es' ? 'Tu registro se ha completado correctamente. Ahora puedes acceder a la plataforma y descubrir todos los servicios que ofrece WeCoop.' : ($lang === 'fr' ? 'Votre inscription a été complétée avec succès. Vous pouvez maintenant accéder à la plateforme et découvrir tous les services proposés par WeCoop.' : 'La tua registrazione è stata completata con successo. Ora puoi accedere alla piattaforma e scoprire tutti i servizi che WeCoop offre.'))) . "</p>
        ";
        
        return self::send($to, ($lang === 'en' ? 'Welcome to WeCoop!' : ($lang === 'es' ? '¡Bienvenido a WeCoop!' : ($lang === 'fr' ? 'Bienvenue chez WeCoop !' : 'Benvenuto in WeCoop!'))), $content, [
            'lang' => $lang,
            'preheader' => ($lang === 'en' ? 'Your registration has been completed' : ($lang === 'es' ? 'Tu registro se ha completado' : ($lang === 'fr' ? 'Votre inscription a été complétée' : 'La tua registrazione è stata completata'))),
            'button_text' => ($lang === 'en' ? 'Access the Platform' : ($lang === 'es' ? 'Accede a la Plataforma' : ($lang === 'fr' ? 'Accéder à la Plateforme' : 'Accedi alla Piattaforma'))),
            'button_url' => $login_url
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
