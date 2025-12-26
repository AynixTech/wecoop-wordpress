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
        
        $logo_url = get_site_url() . '/wp-content/uploads/2025/05/wecooplogo2.png';
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
                <strong>Seguici sui social:</strong><br><br>
                
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
                    Cooperativa di Solidarietà Sociale<br>
                    Via Pupulonia 8, Milano (MI)<br>
                    Email: <a href="mailto:info@wecoop.org" style="color: #3498db;">info@wecoop.org</a>
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
            'From: ' . get_bloginfo('name') . ' <noreply@wecoop.org>'
        ];
        
        return wp_mail($to, $subject, $html, $headers);
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
        
        error_log("WECOOP Email: Sistema multilingua NON disponibile, uso fallback italiano");
        
        // Fallback italiano
        $username_display = $username ?: $email;
        $content = "
            <h1>Benvenuto in WECOOP, {$nome}! 🎉</h1>
            <p>La tua richiesta di adesione è stata <strong>approvata</strong>!</p>
            
            <h2>📋 I tuoi dati di accesso:</h2>
            <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; border-radius: 5px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>Username:</strong> {$username_display}</p>
                <p style='margin: 5px 0;'><strong>Email:</strong> {$email}</p>
                <p style='margin: 5px 0;'><strong>Password temporanea:</strong> <span style='font-family: monospace; background: #e9ecef; padding: 5px 10px; border-radius: 3px;'>{$password}</span></p>
                <p style='margin: 5px 0;'><strong>Numero tessera:</strong> {$numero_tessera}</p>
            </div>
            
            <h2>🎫 La tua Tessera Digitale</h2>
            <p>Visualizza e scarica la tua tessera digitale con QR Code:</p>
            <p style='text-align: center; margin: 20px 0;'>
                <a href='{$tessera_url}' style='display: inline-block; padding: 12px 30px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                    📱 Visualizza Tessera
                </a>
            </p>
            
            <h2>✅ Primi passi:</h2>
            <ul style='line-height: 1.8;'>
                <li>Accedi alla piattaforma usando le credenziali sopra</li>
                <li>Cambia la password temporanea per sicurezza</li>
                <li>Completa il tuo profilo con tutti i dati</li>
                <li>Salva il link della tessera digitale tra i preferiti</li>
                <li>Esplora i servizi disponibili per i soci</li>
            </ul>
            
            <p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                <strong>⚠️ Importante:</strong> Conserva con cura le tue credenziali di accesso. Ti consigliamo di cambiare la password al primo accesso.
            </p>
            
            <p style='margin-top: 30px;'>Siamo felici di averti nella nostra cooperativa! Per qualsiasi domanda, non esitare a contattarci.</p>
        ";
        
        return self::send($email, '🎉 Benvenuto in WECOOP - Credenziali di Accesso', $content, [
            'preheader' => 'La tua richiesta è stata approvata - Ecco le tue credenziali',
            'button_text' => '🔐 Accedi Subito alla Piattaforma',
            'button_url' => wp_login_url()
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
