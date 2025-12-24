<?php
/**
 * Push Integrations: Firebase Cloud Messaging (FCM)
 * 
 * Gestisce invio notifiche push via FCM con supporto:
 * - Legacy Server Key (HTTP v1)
 * - Service Account JSON (OAuth2)
 * 
 * @package WECOOP_Notifications
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Push_Integrations {
    
    /**
     * Inizializza hooks
     */
    public static function init() {
        // Hook per notifiche programmate
        add_action('wecoop_send_scheduled_push', [__CLASS__, 'send_scheduled_push'], 10, 1);
        
        // Hook per eventi WordPress
        add_action('wecoop_new_evento_published', [__CLASS__, 'on_new_evento'], 10, 1);
        add_action('wecoop_new_lead_created', [__CLASS__, 'on_new_lead'], 10, 1);
        add_action('wecoop_socio_approved', [__CLASS__, 'on_socio_approved'], 10, 2);
    }
    
    /**
     * Invia notifica push a lista utenti
     * 
     * @param array $user_ids Array di user ID destinatari
     * @param string $title Titolo notifica
     * @param string $body Corpo notifica
     * @param array $data Dati custom (opzionale)
     * @return array Risultato invio
     */
    public static function send_push_notification($user_ids, $title, $body, $data = []) {
        global $wpdb;
        
        if (empty($user_ids)) {
            return [
                'success' => false,
                'message' => 'Nessun destinatario specificato',
                'sent_count' => 0,
                'failed_count' => 0
            ];
        }
        
        // Ottieni configurazione FCM
        $fcm_config = self::get_fcm_config();
        
        if (empty($fcm_config['server_key']) && empty($fcm_config['service_account'])) {
            return [
                'success' => false,
                'message' => 'Configurazione FCM non trovata',
                'sent_count' => 0,
                'failed_count' => 0
            ];
        }
        
        $sent_count = 0;
        $failed_count = 0;
        $errors = [];
        
        // Recupera token per ogni utente e invia
        foreach ($user_ids as $user_id) {
            // Prova prima dalla tabella wecoop_push_tokens
            global $wpdb;
            $token_row = $wpdb->get_row($wpdb->prepare(
                "SELECT token FROM {$wpdb->prefix}wecoop_push_tokens WHERE user_id = %d LIMIT 1",
                $user_id
            ));
            
            $token = $token_row ? $token_row->token : null;
            
            // Fallback a user_meta
            if (empty($token)) {
                $token = get_user_meta($user_id, 'fcm_token', true);
            }
            
            if (empty($token)) {
                $failed_count++;
                $errors[] = "User $user_id: nessun token registrato";
                continue;
            }
            
            // Invia notifica
            $result = self::send_to_token($token, $title, $body, $data, $fcm_config);
            
            // Log risultato
            $log_data = [
                'user_id' => $user_id,
                'recipient_type' => 'single',
                'recipient_value' => $user_id,
                'title' => $title,
                'body' => $body,
                'data' => json_encode($data),
                'status' => $result['success'] ? 'sent' : 'failed',
                'sent_at' => current_time('mysql'),
                'response' => json_encode($result['response'] ?? []),
                'error_message' => $result['error'] ?? null,
                'created_at' => current_time('mysql')
            ];
            
            $wpdb->insert($wpdb->prefix . 'wecoop_push_logs', $log_data);
            
            if ($result['success']) {
                $sent_count++;
            } else {
                $failed_count++;
                $errors[] = "User $user_id: " . ($result['error'] ?? 'Unknown error');
            }
            
            // Rate limiting: max 500 msg/sec per FCM
            if (count($user_ids) > 10) {
                usleep(20000); // 20ms delay
            }
        }
        
        return [
            'success' => $sent_count > 0,
            'message' => sprintf('Inviate %d notifiche, %d fallite', $sent_count, $failed_count),
            'sent_count' => $sent_count,
            'failed_count' => $failed_count,
            'errors' => $errors
        ];
    }
    
    /**
     * Invia notifica a singolo token FCM
     */
    private static function send_to_token($token, $title, $body, $data, $fcm_config) {
        // Usa Service Account JSON se disponibile (preferito)
        if (!empty($fcm_config['service_account'])) {
            return self::send_via_v1($token, $title, $body, $data, $fcm_config['service_account']);
        }
        
        // Fallback a Legacy Server Key
        if (!empty($fcm_config['server_key'])) {
            return self::send_via_legacy($token, $title, $body, $data, $fcm_config['server_key']);
        }
        
        return [
            'success' => false,
            'error' => 'Nessun metodo FCM configurato'
        ];
    }
    
    /**
     * Invia via FCM v1 API (Service Account JSON + OAuth2)
     */
    private static function send_via_v1($token, $title, $body, $data, $service_account) {
        if (!is_array($service_account) || !isset($service_account['project_id'])) {
            return [
                'success' => false,
                'error' => 'Service Account non valido'
            ];
        }
        
        // Ottieni OAuth2 access token
        $access_token = self::get_oauth2_access_token($service_account);
        
        if (!$access_token) {
            return [
                'success' => false,
                'error' => 'Impossibile ottenere OAuth2 token'
            ];
        }
        
        $project_id = $service_account['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send";
        
        $notification_data = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ]
            ]
        ];
        
        if (!empty($data)) {
            $notification_data['message']['data'] = $data;
        }
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($notification_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        return [
            'success' => $response_code === 200,
            'response' => $response_body,
            'error' => $response_code !== 200 ? ($response_body['error']['message'] ?? 'Unknown error') : null
        ];
    }
    
    /**
     * Invia via FCM Legacy API (Server Key)
     */
    private static function send_via_legacy($token, $title, $body, $data, $server_key) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $notification_data = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1
            ],
            'priority' => 'high'
        ];
        
        if (!empty($data)) {
            $notification_data['data'] = $data;
        }
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'key=' . $server_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($notification_data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message()
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        $success = $response_code === 200 && isset($response_body['success']) && $response_body['success'] > 0;
        
        return [
            'success' => $success,
            'response' => $response_body,
            'error' => !$success ? ($response_body['results'][0]['error'] ?? 'Unknown error') : null
        ];
    }
    
    /**
     * Ottieni OAuth2 access token per FCM v1
     */
    private static function get_oauth2_access_token($service_account) {
        // Cache token per 1 ora
        $cache_key = 'wecoop_fcm_oauth_token';
        $cached = get_transient($cache_key);
        
        if ($cached) {
            return $cached;
        }
        
        // Crea JWT
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'iss' => $service_account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];
        
        $jwt = self::create_jwt($header, $payload, $service_account['private_key']);
        
        // Richiedi access token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]
        ]);
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            // Cache per 50 minuti (il token dura 1 ora)
            set_transient($cache_key, $body['access_token'], 3000);
            return $body['access_token'];
        }
        
        return null;
    }
    
    /**
     * Crea JWT per OAuth2
     */
    private static function create_jwt($header, $payload, $private_key) {
        $header_encoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payload_encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        
        $signature_input = $header_encoded . '.' . $payload_encoded;
        
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        $signature_encoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        
        return $signature_input . '.' . $signature_encoded;
    }
    
    /**
     * Ottieni configurazione FCM
     */
    public static function get_fcm_config() {
        $service_account_json = get_option('wecoop_fcm_service_account_json', '');
        $service_account = null;
        
        if (!empty($service_account_json)) {
            $service_account = json_decode($service_account_json, true);
        }
        
        return [
            'server_key' => get_option('wecoop_fcm_server_key', ''),
            'service_account' => $service_account
        ];
    }
    
    /**
     * Invia notifica programmata (chiamata da wp-cron)
     */
    public static function send_scheduled_push($args) {
        if (empty($args['user_ids']) || empty($args['title']) || empty($args['body'])) {
            return;
        }
        
        self::send_push_notification(
            $args['user_ids'],
            $args['title'],
            $args['body'],
            $args['data'] ?? []
        );
    }
    
    /**
     * Notifica per nuovo evento pubblicato
     */
    public static function on_new_evento($evento_id) {
        $evento = get_post($evento_id);
        
        if (!$evento) {
            return;
        }
        
        // Invia a tutti i soci
        $soci = get_users(['role' => 'socio', 'fields' => 'ID']);
        
        if (empty($soci)) {
            return;
        }
        
        self::send_push_notification(
            $soci,
            'Nuovo Evento WeCoop',
            'È stato pubblicato un nuovo evento: ' . $evento->post_title,
            [
                'type' => 'new_evento',
                'evento_id' => $evento_id,
                'url' => get_permalink($evento_id)
            ]
        );
    }
    
    /**
     * Notifica per nuovo lead
     */
    public static function on_new_lead($lead_id) {
        // Invia solo agli admin
        $admins = get_users(['role' => 'administrator', 'fields' => 'ID']);
        
        if (empty($admins)) {
            return;
        }
        
        self::send_push_notification(
            $admins,
            'Nuovo Lead',
            'È arrivato un nuovo lead da gestire',
            [
                'type' => 'new_lead',
                'lead_id' => $lead_id
            ]
        );
    }
    
    /**
     * Notifica per socio approvato
     */
    public static function on_socio_approved($user_id, $tessera_numero) {
        self::send_push_notification(
            [$user_id],
            'Richiesta Approvata!',
            'La tua richiesta di adesione è stata approvata. Numero tessera: ' . $tessera_numero,
            [
                'type' => 'socio_approved',
                'tessera_numero' => $tessera_numero
            ]
        );
    }
}
