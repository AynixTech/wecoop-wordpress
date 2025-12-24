<?php
/**
 * REST API Endpoint: Push Token Management
 * 
 * Gestisce registrazione e rimozione FCM tokens per Flutter app
 * 
 * Endpoints:
 * - POST /push/v1/token - Salva/aggiorna FCM token utente
 * - DELETE /push/v1/token - Rimuove FCM token utente
 * - GET /push/v1/tokens - Lista tokens (admin only)
 * 
 * @package WECOOP_Notifications
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Push_Token_Endpoint {
    
    /**
     * Inizializza endpoint
     */
    public static function init() {
        // Disabilita notices molto presto se stiamo gestendo una richiesta API
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            @ini_set('display_errors', 0);
            @error_reporting(E_ERROR | E_PARSE);
        }
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    /**
     * Registra rotte API
     */
    public static function register_routes() {
        // POST /push/v1/token - Salva FCM token
        register_rest_route('push/v1', '/token', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'save_token'],
            'permission_callback' => [__CLASS__, 'check_user_permission'],
            'args' => [
                'token' => [
                    'required' => true,
                    'type' => 'string',
                    'description' => 'FCM token',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'device_info' => [
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Informazioni dispositivo',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // DELETE /push/v1/token - Rimuovi FCM token
        register_rest_route('push/v1', '/token', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'delete_token'],
            'permission_callback' => [__CLASS__, 'check_user_permission']
        ]);
        
        // GET /push/v1/tokens - Lista tokens (admin only)
        register_rest_route('push/v1', '/tokens', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_tokens'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
    }
    
    /**
     * Verifica permesso utente autenticato
     */
    public static function check_user_permission($request) {
        // Controlla se utente è loggato (sessione WordPress)
        if (is_user_logged_in()) {
            return true;
        }
        
        // Controlla JWT token per app Flutter
        $auth_header = $request->get_header('authorization');
        if (!$auth_header) {
            return false;
        }
        
        // Estrai Bearer token
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
            
            // Valida JWT usando WeCoop_Auth_Handler
            if (class_exists('WeCoop_Auth_Handler')) {
                $user_data = WeCoop_Auth_Handler::validate_token($token);
                if ($user_data && isset($user_data['user_id'])) {
                    // Setta utente corrente per questa richiesta
                    wp_set_current_user($user_data['user_id']);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Verifica permesso admin
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Salva/aggiorna FCM token
     * 
     * POST /push/v1/token
     * Body: { "token": "FCM_TOKEN", "device_info": "Flutter App - Android" }
     */
    public static function save_token($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
        }
        
        $token = $request->get_param('token');
        $device_info = $request->get_param('device_info') ?: 'Flutter App';
        
        if (empty($token)) {
            return new WP_Error('missing_token', 'Token FCM richiesto', ['status' => 400]);
        }
        
        $table_name = $wpdb->prefix . 'wecoop_push_tokens';
        
        // Verifica se esiste già un token per questo utente
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            // Aggiorna token esistente
            $updated = $wpdb->update(
                $table_name,
                [
                    'token' => $token,
                    'device_info' => $device_info,
                    'updated_at' => current_time('mysql')
                ],
                ['user_id' => $user_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
            
            if ($updated === false) {
                return new WP_Error('db_error', 'Errore aggiornamento token', ['status' => 500]);
            }
            
            return [
                'success' => true,
                'message' => 'Token aggiornato con successo',
                'user_id' => $user_id
            ];
        } else {
            // Inserisci nuovo token
            $inserted = $wpdb->insert(
                $table_name,
                [
                    'user_id' => $user_id,
                    'token' => $token,
                    'device_info' => $device_info,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s']
            );
            
            if ($inserted === false) {
                return new WP_Error('db_error', 'Errore salvataggio token', ['status' => 500]);
            }
            
            return [
                'success' => true,
                'message' => 'Token salvato con successo',
                'user_id' => $user_id
            ];
        }
    }
    
    /**
     * Rimuovi FCM token
     * 
     * DELETE /push/v1/token
     */
    public static function delete_token($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
        }
        
        $table_name = $wpdb->prefix . 'wecoop_push_tokens';
        
        $deleted = $wpdb->delete(
            $table_name,
            ['user_id' => $user_id],
            ['%d']
        );
        
        if ($deleted === false) {
            return new WP_Error('db_error', 'Errore rimozione token', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'message' => 'Token rimosso con successo',
            'user_id' => $user_id
        ];
    }
    
    /**
     * Lista tutti i tokens (admin only)
     * 
     * GET /push/v1/tokens
     */
    public static function get_tokens($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wecoop_push_tokens';
        
        $tokens = $wpdb->get_results("
            SELECT 
                t.id,
                t.user_id,
                t.token,
                t.device_info,
                t.created_at,
                t.updated_at,
                u.user_login,
                u.user_email,
                u.display_name
            FROM {$table_name} t
            LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
            ORDER BY t.updated_at DESC
        ");
        
        return [
            'success' => true,
            'count' => count($tokens),
            'tokens' => $tokens
        ];
    }
}

// Inizializza
WECOOP_Push_Token_Endpoint::init();
