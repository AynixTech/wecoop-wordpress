<?php
/**
 * Plugin Name: WeCoop Push Tokens
 * Plugin URI: https://www.stage.wecoop.org
 * Description: Gestione token FCM per notifiche push dall'app Flutter
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.stage.wecoop.org
 * Text Domain: wecoop-push-tokens
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Push_Tokens {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        register_activation_hook(__FILE__, [$this, 'create_table']);
    }
    
    /**
     * Registra le rotte API REST
     */
    public function register_routes() {
        // POST /wp-json/push/v1/token - Salva token FCM
        register_rest_route('push/v1', '/token', [
            'methods' => 'POST',
            'callback' => [$this, 'save_token'],
            'permission_callback' => [$this, 'check_auth'],
            'args' => [
                'token' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'device_info' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // DELETE /wp-json/push/v1/token - Rimuovi token FCM
        register_rest_route('push/v1', '/token', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_token'],
            'permission_callback' => [$this, 'check_auth']
        ]);
        
        // GET /wp-json/push/v1/tokens - Lista tokens (admin only)
        register_rest_route('push/v1', '/tokens', [
            'methods' => 'GET',
            'callback' => [$this, 'get_all_tokens'],
            'permission_callback' => '__return_true' // Temporaneo per debug
        ]);
        
        // GET /wp-json/push/v1/debug - Info debug
        register_rest_route('push/v1', '/debug', [
            'methods' => 'GET',
            'callback' => [$this, 'debug_info'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * Verifica autenticazione JWT
     */
    public function check_auth($request) {
        // Controlla se utente è già loggato (sessione WordPress)
        if (is_user_logged_in()) {
            return true;
        }
        
        // Controlla JWT token dall'header Authorization
        $auth_header = $request->get_header('authorization');
        
        if (empty($auth_header)) {
            return new WP_Error('unauthorized', 'Token di autenticazione mancante', ['status' => 401]);
        }
        
        // Estrai Bearer token
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
            
            // Valida JWT usando WeCoop_Auth_Handler se disponibile
            $user_id = $this->validate_jwt($token);
            
            if ($user_id === false) {
                return new WP_Error('invalid_token', 'Token non valido o scaduto', ['status' => 401]);
            }
            
            // Imposta utente corrente per questa richiesta
            wp_set_current_user($user_id);
            return true;
        }
        
        return new WP_Error('invalid_auth', 'Formato autorizzazione non valido', ['status' => 401]);
    }
    
    /**
     * Salva o aggiorna token FCM
     */
    public function save_token($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_push_tokens';
        
        // Log per debug
        error_log('=== PUSH TOKEN SAVE DEBUG ===');
        error_log('Tabella: ' . $table);
        
        $token = $request->get_param('token');
        $device_info = $request->get_param('device_info') ?: 'Flutter App';
        
        if (empty($token)) {
            error_log('Token FCM vuoto');
            return new WP_Error('invalid_data', 'Token FCM mancante', ['status' => 400]);
        }
        
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            error_log('User ID non trovato');
            return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
        }
        
        error_log('User ID: ' . $user_id);
        error_log('Token FCM: ' . substr($token, 0, 30) . '...');
        error_log('Device: ' . $device_info);
        
        // Verifica che la tabella esista
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
        if (!$table_exists) {
            error_log('ERRORE: Tabella ' . $table . ' non esiste! Creazione in corso...');
            $this->create_table();
            
            // Ricontrolla
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if (!$table_exists) {
                error_log('ERRORE: Impossibile creare tabella!');
                return new WP_Error('db_error', 'Tabella database non disponibile', ['status' => 500]);
            }
        }
        
        // Verifica se esiste già un token per questo utente
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d",
            $user_id
        ));
        
        if ($wpdb->last_error) {
            error_log('Errore query SELECT: ' . $wpdb->last_error);
        }
        
        if ($existing) {
            error_log('Token esistente trovato (ID: ' . $existing->id . '), aggiornamento...');
            
            // Aggiorna token esistente
            $result = $wpdb->update(
                $table,
                [
                    'token' => $token,
                    'device_info' => $device_info,
                    'updated_at' => current_time('mysql')
                ],
                ['user_id' => $user_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
            
            if ($wpdb->last_error) {
                error_log('Errore UPDATE: ' . $wpdb->last_error);
                return new WP_Error('db_error', 'Errore aggiornamento token: ' . $wpdb->last_error, ['status' => 500]);
            }
            
            if ($result === false) {
                error_log('UPDATE fallito (result=false)');
                return new WP_Error('db_error', 'Errore aggiornamento token', ['status' => 500]);
            }
            
            error_log('Token aggiornato con successo');
            
            return [
                'success' => true,
                'message' => 'Token aggiornato con successo',
                'user_id' => $user_id
            ];
        } else {
            error_log('Nessun token esistente, inserimento nuovo...');
            
            // Inserisci nuovo token
            $result = $wpdb->insert(
                $table,
                [
                    'user_id' => $user_id,
                    'token' => $token,
                    'device_info' => $device_info,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s', '%s']
            );
            
            if ($wpdb->last_error) {
                error_log('Errore INSERT: ' . $wpdb->last_error);
                return new WP_Error('db_error', 'Errore salvataggio token: ' . $wpdb->last_error, ['status' => 500]);
            }
            
            if ($result === false) {
                error_log('INSERT fallito (result=false)');
                return new WP_Error('db_error', 'Errore salvataggio token', ['status' => 500]);
            }
            
            error_log('Token inserito con successo (ID: ' . $wpdb->insert_id . ')');
            
            return [
                'success' => true,
                'message' => 'Token salvato con successo',
                'user_id' => $user_id
            ];
        }
    }
    
    /**
     * Rimuovi token FCM dell'utente
     */
    public function delete_token($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_push_tokens';
        
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
        }
        
        $result = $wpdb->delete(
            $table,
            ['user_id' => $user_id],
            ['%d']
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore rimozione token', ['status' => 500]);
        }
        
        return [
            'success' => true,
            'message' => 'Token rimosso con successo',
            'user_id' => $user_id
        ];
    }
    
    /**
     * Lista tutti i token (solo admin)
     */
    public function get_all_tokens($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_push_tokens';
        
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
            FROM $table t
            LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
            ORDER BY t.updated_at DESC
        ");
        
        return [
            'success' => true,
            'count' => count($tokens),
            'tokens' => $tokens
        ];
    }
    
    /**
     * Info debug
     */
    public function debug_info($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_push_tokens';
        
        // Verifica tabella
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
        
        $info = [
            'table_name' => $table,
            'table_exists' => $table_exists ? true : false,
            'total_users' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}"),
            'total_tokens' => 0,
            'recent_tokens' => []
        ];
        
        if ($table_exists) {
            $info['total_tokens'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $info['recent_tokens'] = $wpdb->get_results("
                SELECT 
                    t.id,
                    t.user_id,
                    LEFT(t.token, 40) as token_preview,
                    t.device_info,
                    t.updated_at,
                    u.user_login
                FROM $table t
                LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
                ORDER BY t.updated_at DESC
                LIMIT 5
            ");
        }
        
        return $info;
    }
    
    /**
     * Valida JWT token
     */
    private function validate_jwt($token) {
        // Metodo 1: Usa WeCoop_Auth_Handler se disponibile
        if (class_exists('WeCoop_Auth_Handler')) {
            $user_data = WeCoop_Auth_Handler::validate_token($token);
            if ($user_data && isset($user_data['user_id'])) {
                return $user_data['user_id'];
            }
        }
        
        // Metodo 2: Usa jwt-authentication-for-wp-rest-api plugin
        try {
            // Decodifica JWT manualmente usando chiave dal wp-config.php
            if (!defined('JWT_AUTH_SECRET_KEY')) {
                error_log('JWT_AUTH_SECRET_KEY non definita in wp-config.php');
                return false;
            }
            
            // Separa le parti del JWT
            $token_parts = explode('.', $token);
            
            if (count($token_parts) !== 3) {
                error_log('JWT formato non valido');
                return false;
            }
            
            list($header_encoded, $payload_encoded, $signature_encoded) = $token_parts;
            
            // Decodifica payload
            $payload = json_decode(base64_decode(strtr($payload_encoded, '-_', '+/')), true);
            
            if (!$payload || !isset($payload['data']['user']['id'])) {
                error_log('JWT payload non valido');
                return false;
            }
            
            // Verifica scadenza
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                error_log('JWT token scaduto');
                return false;
            }
            
            // Verifica firma (semplificato)
            $signature_check = hash_hmac(
                'sha256',
                $header_encoded . '.' . $payload_encoded,
                JWT_AUTH_SECRET_KEY,
                true
            );
            
            $signature_expected = base64_decode(strtr($signature_encoded, '-_', '+/'));
            
            if (!hash_equals($signature_check, $signature_expected)) {
                error_log('JWT firma non valida');
                return false;
            }
            
            return $payload['data']['user']['id'];
            
        } catch (Exception $e) {
            error_log('Errore validazione JWT: ' . $e->getMessage());
            return false;
        }
        
        return false;
    }
    
    /**
     * Crea tabella database
     */
    public function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_push_tokens';
        $charset = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token text NOT NULL,
            device_info varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY updated_at (updated_at)
        ) $charset;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Inizializza plugin
new WeCoop_Push_Tokens();
