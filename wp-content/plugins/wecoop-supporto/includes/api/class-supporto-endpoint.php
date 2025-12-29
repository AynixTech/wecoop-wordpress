<?php
/**
 * REST API Endpoint per Richieste Supporto
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Supporto_Endpoint {
    
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    public static function register_routes() {
        // Crea nuova richiesta supporto
        register_rest_route('wecoop/v1', '/supporto/richiesta', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'create_richiesta'],
            'permission_callback' => '__return_true', // JWT authentication via header
        ]);
        
        // Lista richieste (con filtri)
        register_rest_route('wecoop/v1', '/supporto/lista', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_lista'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
        ]);
        
        // Dettagli richiesta
        register_rest_route('wecoop/v1', '/supporto/richiesta/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richiesta'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
        ]);
        
        // Aggiorna status richiesta
        register_rest_route('wecoop/v1', '/supporto/richiesta/(?P<id>\d+)/status', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'update_status'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
        ]);
        
        // Richieste utente
        register_rest_route('wecoop/v1', '/supporto/mie-richieste', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_mie_richieste'],
            'permission_callback' => [__CLASS__, 'check_user_logged_in'],
        ]);
    }
    
    /**
     * Crea nuova richiesta supporto
     */
    public static function create_richiesta($request) {
        error_log('========== NUOVA RICHIESTA SUPPORTO ==========');
        
        $params = $request->get_json_params();
        
        error_log('[SUPPORTO] Dati ricevuti: ' . print_r($params, true));
        
        // Validazione campi OBBLIGATORI (solo user_id e user_phone)
        if (empty($params['user_id'])) {
            error_log('[SUPPORTO] Campo mancante: user_id');
            return new WP_Error(
                'missing_field',
                'Campo obbligatorio mancante: user_id',
                ['status' => 400]
            );
        }
        
        if (empty($params['user_phone'])) {
            error_log('[SUPPORTO] Campo mancante: user_phone');
            return new WP_Error(
                'missing_field',
                'Campo obbligatorio mancante: user_phone',
                ['status' => 400]
            );
        }
        
        // Verifica che l'utente esista
        $user = get_userdata($params['user_id']);
        if (!$user) {
            error_log('[SUPPORTO] Utente non trovato: ' . $params['user_id']);
            return new WP_Error(
                'user_not_found',
                'Utente non trovato',
                ['status' => 404]
            );
        }
        
        error_log('[SUPPORTO] Utente verificato: ' . $user->user_login);
        
        // Recupera dati utente dal database
        $user_name = !empty($params['user_name']) ? $params['user_name'] : $user->display_name;
        $user_email = !empty($params['user_email']) ? $params['user_email'] : $user->user_email;
        
        // Usa username come telefono se user_phone non fornito (ma è obbligatorio ora)
        $user_phone = $params['user_phone'];
        
        // Valori di default per campi opzionali
        $service_name = !empty($params['service_name']) ? $params['service_name'] : 'Servizio Generico';
        $service_category = !empty($params['service_category']) ? $params['service_category'] : 'generico';
        $current_screen = !empty($params['current_screen']) ? $params['current_screen'] : 'N/A';
        $tipo_richiesta = !empty($params['tipo_richiesta']) ? $params['tipo_richiesta'] : 'aiuto_automatico';
        $priorita = !empty($params['priorita']) ? $params['priorita'] : 'media';
        $messaggio = !empty($params['messaggio']) ? $params['messaggio'] : 'Richiesta di supporto';
        $timestamp = !empty($params['timestamp']) ? $params['timestamp'] : current_time('c');
        
        error_log('[SUPPORTO] Dati compilati automaticamente:');
        error_log('[SUPPORTO]   - user_name: ' . $user_name);
        error_log('[SUPPORTO]   - user_email: ' . $user_email);
        error_log('[SUPPORTO]   - user_phone: ' . $user_phone);
        
        // Crea post
        $title = sprintf(
            '%s - %s (%s)',
            $service_name,
            $user_name,
            date('d/m/Y H:i', strtotime($timestamp))
        );
        
        $post_data = [
            'post_type' => 'richiesta_supporto',
            'post_title' => $title,
            'post_content' => sanitize_textarea_field($messaggio),
            'post_status' => 'publish',
            'post_author' => 1, // Admin
        ];
        
        error_log('[SUPPORTO] Creazione post...');
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            error_log('[SUPPORTO] Errore creazione post: ' . $post_id->get_error_message());
            return new WP_Error(
                'post_creation_failed',
                'Errore nella creazione della richiesta',
                ['status' => 500]
            );
        }
        
        error_log('[SUPPORTO] Post creato con ID: ' . $post_id);
        
        // Salva meta
        update_post_meta($post_id, 'user_id', intval($params['user_id']));
        update_post_meta($post_id, 'service_name', sanitize_text_field($service_name));
        update_post_meta($post_id, 'service_category', sanitize_text_field($service_category));
        update_post_meta($post_id, 'current_screen', sanitize_text_field($current_screen));
        update_post_meta($post_id, 'user_email', sanitize_email($user_email));
        update_post_meta($post_id, 'user_name', sanitize_text_field($user_name));
        update_post_meta($post_id, 'user_phone', sanitize_text_field($user_phone));
        update_post_meta($post_id, 'tipo_richiesta', sanitize_text_field($tipo_richiesta));
        update_post_meta($post_id, 'priorita', sanitize_text_field($priorita));
        update_post_meta($post_id, 'timestamp', sanitize_text_field($timestamp));
        update_post_meta($post_id, 'status', 'aperta');
        
        error_log('[SUPPORTO] Meta salvati');
        
        // Genera numero ticket
        $numero_ticket = 'SUP-' . date('Y') . '-' . str_pad($post_id, 5, '0', STR_PAD_LEFT);
        update_post_meta($post_id, 'numero_ticket', $numero_ticket);
        
        error_log('[SUPPORTO] Numero ticket: ' . $numero_ticket);
        error_log('========== RICHIESTA SUPPORTO CREATA ==========');
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Richiesta supporto creata con successo',
            'data' => [
                'id' => $post_id,
                'numero_ticket' => $numero_ticket,
                'status' => 'aperta',
                'created_at' => current_time('mysql')
            ]
        ]);
    }
    
    /**
     * Lista richieste con filtri
     */
    public static function get_lista($request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'richiesta_supporto',
            'posts_per_page' => isset($params['per_page']) ? intval($params['per_page']) : 20,
            'paged' => isset($params['page']) ? intval($params['page']) : 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Filtro per status
        if (!empty($params['status'])) {
            $args['meta_query'] = [
                [
                    'key' => 'status',
                    'value' => sanitize_text_field($params['status'])
                ]
            ];
        }
        
        // Filtro per user_id
        if (!empty($params['user_id'])) {
            $args['meta_query'][] = [
                'key' => 'user_id',
                'value' => intval($params['user_id'])
            ];
        }
        
        // Filtro per categoria servizio
        if (!empty($params['service_category'])) {
            $args['meta_query'][] = [
                'key' => 'service_category',
                'value' => sanitize_text_field($params['service_category'])
            ];
        }
        
        $query = new WP_Query($args);
        $richieste = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $richieste[] = [
                    'id' => $post_id,
                    'numero_ticket' => get_post_meta($post_id, 'numero_ticket', true),
                    'user_id' => get_post_meta($post_id, 'user_id', true),
                    'user_name' => get_post_meta($post_id, 'user_name', true),
                    'user_phone' => get_post_meta($post_id, 'user_phone', true),
                    'user_email' => get_post_meta($post_id, 'user_email', true),
                    'service_name' => get_post_meta($post_id, 'service_name', true),
                    'service_category' => get_post_meta($post_id, 'service_category', true),
                    'current_screen' => get_post_meta($post_id, 'current_screen', true),
                    'tipo_richiesta' => get_post_meta($post_id, 'tipo_richiesta', true),
                    'priorita' => get_post_meta($post_id, 'priorita', true),
                    'status' => get_post_meta($post_id, 'status', true),
                    'messaggio' => get_the_content(),
                    'timestamp' => get_post_meta($post_id, 'timestamp', true),
                    'created_at' => get_the_date('c'),
                ];
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $richieste,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages
        ]);
    }
    
    /**
     * Dettagli singola richiesta
     */
    public static function get_richiesta($request) {
        $post_id = $request['id'];
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'richiesta_supporto') {
            return new WP_Error('not_found', 'Richiesta non trovata', ['status' => 404]);
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'id' => $post_id,
                'numero_ticket' => get_post_meta($post_id, 'numero_ticket', true),
                'user_id' => get_post_meta($post_id, 'user_id', true),
                'user_name' => get_post_meta($post_id, 'user_name', true),
                'user_phone' => get_post_meta($post_id, 'user_phone', true),
                'user_email' => get_post_meta($post_id, 'user_email', true),
                'service_name' => get_post_meta($post_id, 'service_name', true),
                'service_category' => get_post_meta($post_id, 'service_category', true),
                'current_screen' => get_post_meta($post_id, 'current_screen', true),
                'tipo_richiesta' => get_post_meta($post_id, 'tipo_richiesta', true),
                'priorita' => get_post_meta($post_id, 'priorita', true),
                'status' => get_post_meta($post_id, 'status', true),
                'messaggio' => $post->post_content,
                'timestamp' => get_post_meta($post_id, 'timestamp', true),
                'created_at' => get_the_date('c', $post_id),
            ]
        ]);
    }
    
    /**
     * Aggiorna status richiesta
     */
    public static function update_status($request) {
        $post_id = $request['id'];
        $params = $request->get_json_params();
        
        if (empty($params['status'])) {
            return new WP_Error('missing_status', 'Status mancante', ['status' => 400]);
        }
        
        $allowed_statuses = ['aperta', 'in_lavorazione', 'risolta', 'chiusa'];
        if (!in_array($params['status'], $allowed_statuses)) {
            return new WP_Error('invalid_status', 'Status non valido', ['status' => 400]);
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'richiesta_supporto') {
            return new WP_Error('not_found', 'Richiesta non trovata', ['status' => 404]);
        }
        
        update_post_meta($post_id, 'status', sanitize_text_field($params['status']));
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Status aggiornato',
            'data' => [
                'id' => $post_id,
                'status' => $params['status']
            ]
        ]);
    }
    
    /**
     * Richieste dell'utente loggato
     */
    public static function get_mie_richieste($request) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'Utente non autenticato', ['status' => 401]);
        }
        
        $request->set_param('user_id', $user_id);
        return self::get_lista($request);
    }
    
    /**
     * Verifica permessi admin
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Verifica utente loggato
     */
    public static function check_user_logged_in() {
        return is_user_logged_in();
    }
}
