<?php
/**
 * Email Tracker
 * 
 * @package WeCoop_Email_System
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Email_Tracker {
    
    public static function init() {
        add_action('wp_mail_succeeded', [__CLASS__, 'log_email'], 10, 1);
    }
    
    public static function log_email($mail_data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wecoop_email_tracking';
        
        $wpdb->insert($table, [
            'recipient_email' => is_array($mail_data['to']) ? implode(',', $mail_data['to']) : $mail_data['to'],
            'subject' => $mail_data['subject'],
            'status' => 'sent',
            'sent_at' => current_time('mysql')
        ]);
    }
    
    public static function get_stats() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wecoop_email_tracking';
        
        return [
            'total_sent' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status='sent'"),
            'total_opened' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE opened_at IS NOT NULL")
        ];
    }
}

WECOOP_Email_Tracker::init();
