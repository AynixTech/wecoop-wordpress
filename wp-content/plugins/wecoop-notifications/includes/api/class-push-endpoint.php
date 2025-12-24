<?php
/**
 * REST API Endpoint: Push Notifications
 * 
 * Endpoint per registrazione token FCM e gestione notifiche push
 * Base URL: /wp-json/wecoop/v1/push
 * 
 * @package WECOOP_Notifications
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Push_Endpoint {
    
    /**
     * Inizializza endpoint
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    /**
     * Registra tutte le rotte
     */
    public static function register_routes() {
        
        // 1. CREATE: Registra FCM token utente
        register_rest_route('wecoop/v1', '/push/register-token', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'register_fcm_token'],
            'permission_callback' => 'is_user_logged_in',
            'args' => [
                'token' => [
                    'required' => true, 
                    'type' => 'string', 
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [__CLASS__, 'validate_fcm_token']
                ],
                'device_type' => [
                    'type' => 'string', 
                    'default' => 'unknown',
                    'sanitize_callback' => 'sanitize_text_field',
                    'enum' => ['android', 'ios', 'web', 'unknown']
                ],
                'device_info' => [
                    'type' => 'object',
                    'default' => []
                ]
            ]
        ]);
        
        // 2. DELETE: Rimuovi FCM token (logout)
        register_rest_route('wecoop/v1', '/push/unregister-token', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'unregister_fcm_token'],
            'permission_callback' => 'is_user_logged_in'
        ]);
        
        // 3. READ: Ottieni token corrente utente
        register_rest_route('wecoop/v1', '/push/my-token', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_my_token'],
            'permission_callback' => 'is_user_logged_in'
        ]);
        
        // 4. CREATE: Invia notifica push (solo admin)
        register_rest_route('wecoop/v1', '/push/send', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'send_push_notification'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'recipient_type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['all', 'role', 'user_ids', 'single']
                ],
                'recipient_value' => [
                    'type' => 'string'
                ],
                'user_ids' => [
                    'type' => 'array'
                ],
                'title' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'body' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ],
                'data' => [
                    'type' => 'object',
                    'default' => []
                ],
                'schedule_for' => [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // 5. READ: Log notifiche inviate
        register_rest_route('wecoop/v1', '/push/logs', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_push_logs'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'status' => ['type' => 'string', 'default' => 'all'],
                'user_id' => ['type' => 'integer']
            ]
        ]);
        
        // 6. READ: Statistiche push
        register_rest_route('wecoop/v1', '/push/stats', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_push_stats'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 7. DELETE: Elimina log
        register_rest_route('wecoop/v1', '/push/logs/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'delete_push_log'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
    }
    
    /**
     * Valida formato token FCM
     */
    public static function validate_fcm_token($token) {
        return !empty($token) && strlen($token) > 20;
    }
    
    /**
     * Verifica permessi admin
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Registra FCM token per utente corrente
     */
    public static function register_fcm_token($request) {
        $user_id = get_current_user_id();
        $token = $request->get_param('token');
        $device_type = $request->get_param('device_type');
        $device_info = $request->get_param('device_info');
        
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User not logged in', ['status' => 401]);
        }
        
        // Salva token in user meta
        $token_data = [
            'token' => $token,
            'device_type' => $device_type,
            'device_info' => $device_info,
            'registered_at' => current_time('mysql'),
            'last_updated' => current_time('mysql')
        ];
        
        update_user_meta($user_id, 'fcm_token', $token);
        update_user_meta($user_id, 'fcm_token_data', $token_data);
        
        // Log registrazione
        error_log(sprintf(
            '[WECOOP Push] Token registered for user %d - Device: %s',
            $user_id,
            $device_type
        ));
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Token registrato con successo',
            'data' => [
                'user_id' => $user_id,
                'device_type' => $device_type,
                'registered_at' => $token_data['registered_at']
            ]
        ]);
    }
    
    /**
     * Rimuovi FCM token utente corrente
     */
    public static function unregister_fcm_token($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User not logged in', ['status' => 401]);
        }
        
        delete_user_meta($user_id, 'fcm_token');
        delete_user_meta($user_id, 'fcm_token_data');
        
        error_log(sprintf('[WECOOP Push] Token unregistered for user %d', $user_id));
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Token rimosso con successo'
        ]);
    }
    
    /**
     * Ottieni token utente corrente
     */
    public static function get_my_token($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('unauthorized', 'User not logged in', ['status' => 401]);
        }
        
        $token = get_user_meta($user_id, 'fcm_token', true);
        $token_data = get_user_meta($user_id, 'fcm_token_data', true);
        
        if (empty($token)) {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Nessun token registrato',
                'data' => null
            ]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'token' => $token,
                'device_type' => $token_data['device_type'] ?? 'unknown',
                'registered_at' => $token_data['registered_at'] ?? null
            ]
        ]);
    }
    
    /**
     * Invia notifica push
     */
    public static function send_push_notification($request) {
        $recipient_type = $request->get_param('recipient_type');
        $recipient_value = $request->get_param('recipient_value');
        $user_ids = $request->get_param('user_ids');
        $title = $request->get_param('title');
        $body = $request->get_param('body');
        $data = $request->get_param('data') ?: [];
        $schedule_for = $request->get_param('schedule_for');
        
        // Determina destinatari
        $target_user_ids = [];
        
        switch ($recipient_type) {
            case 'all':
                $users = get_users(['fields' => 'ID']);
                $target_user_ids = $users;
                break;
                
            case 'role':
                $users = get_users([
                    'role' => $recipient_value,
                    'fields' => 'ID'
                ]);
                $target_user_ids = $users;
                break;
                
            case 'user_ids':
                $target_user_ids = $user_ids;
                break;
                
            case 'single':
                $target_user_ids = [(int)$recipient_value];
                break;
        }
        
        if (empty($target_user_ids)) {
            return new WP_Error('no_recipients', 'Nessun destinatario trovato', ['status' => 400]);
        }
        
        // Se è programmata, salva per cron
        if (!empty($schedule_for)) {
            $scheduled_time = strtotime($schedule_for);
            
            if ($scheduled_time <= time()) {
                return new WP_Error('invalid_schedule', 'Data programmazione deve essere futura', ['status' => 400]);
            }
            
            // Crea evento cron
            $hook = 'wecoop_send_scheduled_push';
            $args = [
                'user_ids' => $target_user_ids,
                'title' => $title,
                'body' => $body,
                'data' => $data
            ];
            
            wp_schedule_single_event($scheduled_time, $hook, [$args]);
            
            // Log programmazione
            global $wpdb;
            $table_name = $wpdb->prefix . 'wecoop_push_logs';
            
            $wpdb->insert($table_name, [
                'recipient_type' => $recipient_type,
                'recipient_value' => is_array($user_ids) ? json_encode($user_ids) : $recipient_value,
                'title' => $title,
                'body' => $body,
                'data' => json_encode($data),
                'status' => 'scheduled',
                'scheduled_for' => date('Y-m-d H:i:s', $scheduled_time),
                'created_at' => current_time('mysql')
            ]);
            
            return rest_ensure_response([
                'success' => true,
                'message' => 'Notifica programmata con successo',
                'data' => [
                    'scheduled_for' => date('Y-m-d H:i:s', $scheduled_time),
                    'recipients_count' => count($target_user_ids)
                ]
            ]);
        }
        
        // Invia immediatamente
        if (!class_exists('WECOOP_Push_Integrations')) {
            return new WP_Error('service_unavailable', 'Servizio push non disponibile', ['status' => 503]);
        }
        
        $result = WECOOP_Push_Integrations::send_push_notification(
            $target_user_ids,
            $title,
            $body,
            $data
        );
        
        return rest_ensure_response($result);
    }
    
    /**
     * Ottieni log notifiche push
     */
    public static function get_push_logs($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_push_logs';
        
        $per_page = $request->get_param('per_page');
        $page = $request->get_param('page');
        $status = $request->get_param('status');
        $user_id = $request->get_param('user_id');
        
        $offset = ($page - 1) * $per_page;
        
        $where = ['1=1'];
        if ($status !== 'all') {
            $where[] = $wpdb->prepare('status = %s', $status);
        }
        if ($user_id) {
            $where[] = $wpdb->prepare('user_id = %d', $user_id);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}");
        
        return rest_ensure_response([
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => ceil($total / $per_page)
            ]
        ]);
    }
    
    /**
     * Statistiche push notifications
     */
    public static function get_push_stats($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_push_logs';
        
        $stats = [
            'total_sent' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'sent'"),
            'total_failed' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'failed'"),
            'total_scheduled' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'scheduled'"),
            'total_pending' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'pending'"),
            'sent_today' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE status = 'sent' AND DATE(sent_at) = %s",
                current_time('Y-m-d')
            )),
            'sent_this_week' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE status = 'sent' AND sent_at >= %s",
                date('Y-m-d', strtotime('-7 days'))
            )),
            'sent_this_month' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE status = 'sent' AND MONTH(sent_at) = %d AND YEAR(sent_at) = %d",
                date('m'),
                date('Y')
            ))
        ];
        
        // Utenti con token registrato
        $stats['users_with_token'] = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'fcm_token'"
        );
        
        return rest_ensure_response([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Elimina log push
     */
    public static function delete_push_log($request) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_push_logs';
        
        $log_id = $request->get_param('id');
        
        $deleted = $wpdb->delete($table_name, ['id' => $log_id], ['%d']);
        
        if ($deleted === false) {
            return new WP_Error('delete_failed', 'Impossibile eliminare il log', ['status' => 500]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Log eliminato con successo'
        ]);
    }
}
