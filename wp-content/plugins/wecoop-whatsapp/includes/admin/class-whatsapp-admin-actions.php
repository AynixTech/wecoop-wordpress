<?php
/**
 * WhatsApp Admin Actions
 * 
 * @package WeCoop_WhatsApp
 */

if (!defined('ABSPATH')) exit;

class WECOOP_WhatsApp_Admin_Actions {
    
    public static function init() {
        add_action('admin_post_wecoop_whatsapp_test', [__CLASS__, 'handle_test_message']);
    }
    
    public static function handle_test_message() {
        check_admin_referer('wecoop_whatsapp_test');
        
        if (!current_user_can('manage_options')) {
            wp_die('Non autorizzato');
        }
        
        $test_phone = sanitize_text_field($_POST['test_phone']);
        
        if (empty($test_phone)) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-whatsapp-settings',
                'error' => 'missing_phone'
            ], admin_url('options-general.php')));
            exit;
        }
        
        // Invia messaggio di test
        $result = self::send_test_whatsapp($test_phone);
        
        if ($result) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-whatsapp-settings',
                'test' => 'success'
            ], admin_url('options-general.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-whatsapp-settings',
                'test' => 'error'
            ], admin_url('options-general.php')));
        }
        exit;
    }
    
    private static function send_test_whatsapp($telefono) {
        $api_key = get_option('wecoop_whatsapp_api_key');
        $phone_number_id = get_option('wecoop_whatsapp_phone_number_id');
        
        if (empty($api_key) || empty($phone_number_id)) {
            error_log('[WHATSAPP-TEST] WhatsApp non configurato');
            return false;
        }
        
        $message = "🧪 *Messaggio di test WeCoop*\n\n";
        $message .= "Questo è un messaggio di test dall'integrazione WhatsApp di WeCoop.\n\n";
        $message .= "✅ La configurazione è corretta!\n\n";
        $message .= "_Inviato da: " . get_bloginfo('name') . "_";
        
        // Normalizza telefono
        $phone_clean = preg_replace('/[^\d+]/', '', $telefono);
        
        $url = "https://graph.facebook.com/v17.0/{$phone_number_id}/messages";
        
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $phone_clean,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body),
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            error_log('[WHATSAPP-TEST] Errore: ' . $response->get_error_message());
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        error_log('[WHATSAPP-TEST] Status: ' . $status_code);
        
        return ($status_code === 200);
    }
}

WECOOP_WhatsApp_Admin_Actions::init();
