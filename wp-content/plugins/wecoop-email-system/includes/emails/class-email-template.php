<?php
/**
 * Email Template
 * 
 * @package WeCoop_Email_System
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Email_Template {
    
    public static function benvenuto_socio($nome, $email, $password, $numero_tessera, $tessera_url, $lang = 'it', $username = null) {
        $subject = WECOOP_Email_i18n::translate('welcome', $lang) . ' - WECOOP';
        
        $message = "
            <h2>" . WECOOP_Email_i18n::translate('hello', $lang) . " $nome!</h2>
            <p>" . WECOOP_Email_i18n::translate('welcome', $lang) . " WECOOP.</p>
            <p><strong>" . WECOOP_Email_i18n::translate('username', $lang) . ":</strong> " . ($username ?: $email) . "</p>
            <p><strong>" . WECOOP_Email_i18n::translate('password', $lang) . ":</strong> $password</p>
            <p><strong>" . WECOOP_Email_i18n::translate('membership_card', $lang) . ":</strong> $numero_tessera</p>
            <p><a href='$tessera_url'>" . WECOOP_Email_i18n::translate('view_card', $lang) . "</a></p>
        ";
        
        return wp_mail($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }
    
    public static function send_custom($to, $subject, $message, $template = 'default') {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        return wp_mail($to, $subject, $message, $headers);
    }
}
