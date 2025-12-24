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
        
        $logo_url = get_site_url() . '/wp-content/uploads/2024/12/logo-wecoop.png';
        $site_url = get_site_url();
        $site_name = get_bloginfo('name');
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($subject); ?></title>
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
            margin: 0 10px;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
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
                <strong>Seguici sui social:</strong><br><br>
                
                <a href="https://www.facebook.com/stage.wecoop.org" target="_blank" style="color: #ffffff;">
                    🔵 Facebook
                </a>
                
                <a href="https://www.instagram.com/stage.wecoop.org" target="_blank" style="color: #ffffff;">
                    📷 Instagram
                </a>
                
                <a href="https://www.linkedin.com/company/wecoop" target="_blank" style="color: #ffffff;">
                    💼 LinkedIn
                </a>
                
                <a href="https://twitter.com/stage.wecoop.org" target="_blank" style="color: #ffffff;">
                    🐦 Twitter
                </a>
            </div>
            
            <div class="divider"></div>
            
            <div class="disclaimer">
                <p>
                    <strong><?php echo esc_html($site_name); ?></strong><br>
                    Cooperativa di Solidarietà Sociale<br>
                    Via Vallarsa 2, 20139 Milano (MI)<br>
                    Email: <a href="mailto:info@stage.wecoop.org" style="color: #3498db;">info@stage.wecoop.org</a>
                </p>
                
                <p>
                    Questa email è stata inviata a te perché sei registrato sulla nostra piattaforma.
                    Se ritieni di aver ricevuto questa email per errore, ti preghiamo di contattarci.
                </p>
                
                <p>
                    &copy; <?php echo date('Y'); ?> WeCoop. Tutti i diritti riservati.<br>
                    <a href="<?php echo esc_url($site_url . '/privacy-policy'); ?>" style="color: #3498db;">Privacy Policy</a> | 
                    <a href="<?php echo esc_url($site_url . '/termini-e-condizioni'); ?>" style="color: #3498db;">Termini e Condizioni</a>
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
            'From: ' . get_bloginfo('name') . ' <noreply@stage.wecoop.org>'
        ];
        
        return wp_mail($to, $subject, $html, $headers);
    }
    
    /**
     * Template: Email di benvenuto
     */
    public static function send_welcome($to, $user_name, $login_url) {
        $content = "
            <h1>Benvenuto in WeCoop, {$user_name}! 🎉</h1>
            <p>Siamo felici di darti il benvenuto nella nostra comunità.</p>
            <p>La tua registrazione è stata completata con successo. Ora puoi accedere alla piattaforma e scoprire tutti i servizi che WeCoop offre.</p>
        ";
        
        return self::send($to, 'Benvenuto in WeCoop!', $content, [
            'preheader' => 'La tua registrazione è stata completata',
            'button_text' => 'Accedi alla Piattaforma',
            'button_url' => $login_url
        ]);
    }
    
    /**
     * Template: Reset password
     */
    public static function send_password_reset($to, $user_name, $reset_url) {
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
