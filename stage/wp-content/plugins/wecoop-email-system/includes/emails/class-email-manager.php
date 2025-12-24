<?php
/**
 * Email Manager
 * 
 * @package WeCoop_Email_System
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Email_Manager {
    
    public static function init() {
        add_filter('wp_mail_from', [__CLASS__, 'custom_from_email']);
        add_filter('wp_mail_from_name', [__CLASS__, 'custom_from_name']);
    }
    
    public static function custom_from_email($email) {
        return get_option('wecoop_email_from', 'noreply@stage.wecoop.org');
    }
    
    public static function custom_from_name($name) {
        return get_option('wecoop_email_from_name', 'WECOOP');
    }
    
    public static function send_bulk($recipients, $subject, $message) {
        $sent = 0;
        foreach ($recipients as $email) {
            if (WECOOP_Email_Template::send_custom($email, $subject, $message)) {
                $sent++;
            }
        }
        return $sent;
    }
}

WECOOP_Email_Manager::init();
