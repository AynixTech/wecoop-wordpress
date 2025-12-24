<?php
/**
 * Automation System
 * 
 * @package WeCoop_Automation
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Automation {
    
    public static function init() {
        add_action('wecoop_new_socio', [__CLASS__, 'on_new_socio'], 10, 1);
        add_action('wecoop_new_lead', [__CLASS__, 'on_new_lead'], 10, 1);
    }
    
    public static function on_new_socio($user_id) {
        // Trigger: nuovo socio registrato
        // Action: invia email benvenuto + push notification
        
        $user = get_userdata($user_id);
        
        if (class_exists('WECOOP_Email_Template')) {
            WECOOP_Email_Template::send_custom(
                $user->user_email,
                'Benvenuto in WECOOP',
                '<p>Grazie per esserti registrato!</p>'
            );
        }
        
        if (class_exists('WECOOP_Push_Integrations')) {
            WECOOP_Push_Integrations::send_push_notification(
                [$user_id],
                'Benvenuto in WECOOP!',
                'La tua registrazione è stata completata.',
                ['screen' => 'Home']
            );
        }
    }
    
    public static function on_new_lead($lead_id) {
        // Trigger: nuovo lead creato
        // Action: notifica admin
        
        $admin_email = get_option('admin_email');
        
        if (class_exists('WECOOP_Email_Template')) {
            WECOOP_Email_Template::send_custom(
                $admin_email,
                'Nuovo Lead',
                '<p>È stato creato un nuovo lead nel CRM.</p>'
            );
        }
    }
    
    public static function create_automation($trigger, $action, $config = []) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wecoop_automations';
        
        return $wpdb->insert($table, [
            'name' => $config['name'] ?? 'Automation',
            'trigger_type' => $trigger,
            'action_type' => $action,
            'trigger_config' => json_encode($config['trigger'] ?? []),
            'action_config' => json_encode($config['action'] ?? []),
            'is_active' => 1,
            'created_at' => current_time('mysql')
        ]);
    }
}

WECOOP_Automation::init();
