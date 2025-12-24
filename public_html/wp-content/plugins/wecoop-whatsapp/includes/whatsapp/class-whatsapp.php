<?php
/**
 * WhatsApp Integration
 * 
 * @package WeCoop_WhatsApp
 */

if (!defined('ABSPATH')) exit;

class WECOOP_WhatsApp {
    
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    public static function register_routes() {
        register_rest_route('wecoop/v1', '/whatsapp/send', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'send_message'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
    }
    
    public static function send_message($request) {
        $params = $request->get_json_params();
        $phone = $params['phone'];
        $message = $params['message'];
        
        $api_key = get_option('wecoop_whatsapp_api_key');
        $phone_number_id = get_option('wecoop_whatsapp_phone_number_id');
        
        if (!$api_key || !$phone_number_id) {
            return rest_ensure_response(['success' => false, 'message' => 'WhatsApp non configurato']);
        }
        
        // Integrazione WhatsApp Business API
        $response = wp_remote_post("https://graph.facebook.com/v17.0/$phone_number_id/messages", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => ['body' => $message]
            ])
        ]);
        
        if (is_wp_error($response)) {
            return rest_ensure_response(['success' => false, 'message' => $response->get_error_message()]);
        }
        
        return rest_ensure_response(['success' => true, 'message' => 'Messaggio inviato']);
    }
}

WECOOP_WhatsApp::init();
