<?php
/**
 * REST API Endpoint: Gestione Servizi
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Endpoint {
    
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
        add_action('rest_api_init', [__CLASS__, 'disable_notices_for_api']);
    }
    
    /**
     * Disabilita notice/warning per le API REST
     */
    public static function disable_notices_for_api() {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            @ini_set('display_errors', 0);
            @error_reporting(0);
        }
    }
    
    /**
     * Verifica permesso admin
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Verifica JWT token
     */
    public static function check_jwt_permission($request) {
        // Se l'utente è loggato, OK
        if (is_user_logged_in()) {
            return true;
        }
        
        // Altrimenti verifica JWT
        $auth_header = $request->get_header('authorization');
        if (!$auth_header) {
            return new WP_Error('no_auth', 'Token di autenticazione mancante', ['status' => 401]);
        }
        
        // Estrai token
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $token = $matches[1];
            
            // Verifica token JWT (richiede plugin JWT)
            if (class_exists('WeCoop_Auth_Handler')) {
                $user_id = WeCoop_Auth_Handler::validate_jwt_token($token);
                if ($user_id) {
                    return true;
                }
            }
        }
        
        return new WP_Error('invalid_token', 'Token non valido', ['status' => 401]);
    }
    
    /**
     * Ottieni user_id dal JWT token o dalla sessione
     */
    public static function get_user_id_from_jwt($request) {
        // Se l'utente è loggato normalmente
        if (is_user_logged_in()) {
            return get_current_user_id();
        }
        
        // Altrimenti estrai dal JWT
        $auth_header = $request->get_header('authorization');
        if (!$auth_header) {
            return false;
        }
        
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $token = $matches[1];
            
            if (class_exists('WeCoop_Auth_Handler')) {
                return WeCoop_Auth_Handler::validate_jwt_token($token);
            }
        }
        
        return false;
    }
    
    /**
     * Registra tutte le rotte
     */
    public static function register_routes() {
        
        // POST /richiesta-servizio - Crea richiesta
        register_rest_route('wecoop/v1', '/richiesta-servizio', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'crea_richiesta_servizio'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'servizio' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'categoria' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'dati' => ['type' => 'object']
            ]
        ]);
        
        // GET /richiesta-servizio/{id} - Dettaglio richiesta
        register_rest_route('wecoop/v1', '/richiesta-servizio/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richiesta_dettaglio'],
            'permission_callback' => 'is_user_logged_in'
        ]);
        
        // GET /richieste-servizi/me - Richieste utente corrente
        register_rest_route('wecoop/v1', '/richieste-servizi/me', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richieste_utente'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'stato' => ['type' => 'string']
            ]
        ]);
        
        // GET /mie-richieste - Alias per compatibilità app Flutter
        register_rest_route('wecoop/v1', '/mie-richieste', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richieste_utente'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'stato' => ['type' => 'string']
            ]
        ]);
    }
    
    /**
     * POST /richiesta-servizio - Crea richiesta
     */
    public static function crea_richiesta_servizio($request) {
        $servizio = $request->get_param('servizio');
        $categoria = $request->get_param('categoria');
        $dati = $request->get_param('dati');
        
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Utente non autenticato'
            ], 401);
        }
        
        // Recupera socio_id se disponibile
        $socio_id = get_user_meta($current_user_id, 'socio_id', true);
        
        // Crea post
        $post_data = [
            'post_type' => 'richiesta_servizio',
            'post_status' => 'publish',
            'post_title' => 'Richiesta - ' . $servizio
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Errore nella creazione della richiesta'
            ], 500);
        }
        
        // Salva metadati
        update_post_meta($post_id, 'servizio', $servizio);
        update_post_meta($post_id, 'categoria', $categoria);
        update_post_meta($post_id, 'dati', json_encode($dati));
        update_post_meta($post_id, 'stato', 'pending');
        update_post_meta($post_id, 'user_id', $current_user_id);
        if ($socio_id) {
            update_post_meta($post_id, 'socio_id', $socio_id);
        }
        
        // Genera numero pratica
        $numero_pratica = WECOOP_Richiesta_Servizio_CPT::genera_numero_pratica($post_id);
        update_post_meta($post_id, 'numero_pratica', $numero_pratica);
        
        // Aggiorna titolo
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $numero_pratica . ' - ' . $servizio
        ]);
        
        // 🔥 Crea pagamento se il servizio lo richiede
        $servizi_a_pagamento = self::get_servizi_a_pagamento();
        $payment_id = null;
        $importo = null;
        
        if (isset($servizi_a_pagamento[$servizio])) {
            $importo = $servizi_a_pagamento[$servizio];
            
            // Crea il pagamento usando WeCoop_Payment_System
            if (class_exists('WeCoop_Payment_System')) {
                update_post_meta($post_id, 'stato', 'awaiting_payment');
                $payment_id = WeCoop_Payment_System::create_payment($post_id);
                error_log("[WECOOP API] Pagamento #{$payment_id} creato per richiesta #{$post_id}, importo €{$importo}");
            }
        }
        
        // Invia email di conferma multilingua
        if (class_exists('WeCoop_Multilingual_Email')) {
            $user = get_user_by('ID', $current_user_id);
            $nome = get_user_meta($current_user_id, 'nome', true) ?: $user->display_name;
            
            WeCoop_Multilingual_Email::send(
                $user->user_email,
                'service_created',
                [
                    'nome' => $nome,
                    'servizio' => $servizio,
                    'data' => get_the_date('d/m/Y H:i', $post_id),
                    'numero_pratica' => $numero_pratica,
                    'button_url' => home_url('/servizi/')
                ],
                $current_user_id,
                $request
            );
            error_log("WECOOP Servizi: Email conferma inviata a {$user->user_email}");
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Richiesta ricevuta con successo',
            'id' => $post_id,
            'numero_pratica' => $numero_pratica,
            'data_richiesta' => get_the_date('Y-m-d H:i:s', $post_id),
            'requires_payment' => ($payment_id !== null),
            'payment_id' => $payment_id,
            'importo' => $importo
        ], 201);
    }
    
    /**
     * Definisce quali servizi richiedono pagamento e l'importo
     * 
     * @return array Array associativo servizio => importo
     */
    private static function get_servizi_a_pagamento() {
        return [
            'Richiesta CUD' => 10.00,
            'Richiesta 730' => 50.00,
            'Richiesta ISEE' => 30.00,
            'Richiesta RED' => 25.00,
            'Richiesta Certificazione Unica' => 15.00,
            'Assistenza Fiscale' => 80.00,
            'Compilazione Modello F24' => 20.00,
            // Aggiungi altri servizi qui
        ];
    }
    
    /**
     * GET /richiesta-servizio/{id} - Dettaglio richiesta
     */
    public static function get_richiesta_dettaglio($request) {
        $richiesta_id = $request->get_param('id');
        $current_user_id = get_current_user_id();
        
        $richiesta = get_post($richiesta_id);
        
        if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Richiesta non trovata'
            ], 404);
        }
        
        // Verifica permessi
        $user_id = get_post_meta($richiesta->ID, 'user_id', true);
        if (!current_user_can('manage_options') && $user_id != $current_user_id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Non autorizzato'
            ], 403);
        }
        
        $dati_json = get_post_meta($richiesta->ID, 'dati', true);
        
        // Verifica se esiste un pagamento associato
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        $pagamento = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE richiesta_id = %d ORDER BY created_at DESC LIMIT 1",
            $richiesta->ID
        ));
        
        $data = [
            'id' => $richiesta->ID,
            'numero_pratica' => get_post_meta($richiesta->ID, 'numero_pratica', true),
            'servizio' => get_post_meta($richiesta->ID, 'servizio', true),
            'categoria' => get_post_meta($richiesta->ID, 'categoria', true),
            'dati' => json_decode($dati_json, true),
            'stato' => get_post_meta($richiesta->ID, 'stato', true),
            'user_id' => $user_id,
            'socio_id' => get_post_meta($richiesta->ID, 'socio_id', true),
            'data_creazione' => $richiesta->post_date,
            'has_payment' => ($pagamento !== null),
            'payment_id' => $pagamento ? $pagamento->id : null,
            'payment_status' => $pagamento ? $pagamento->stato : null,
            'importo' => $pagamento ? floatval($pagamento->importo) : null
        ];
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $data
        ], 200);
    }
    
    /**
     * GET /richieste-servizi/me - Richieste utente corrente
     */
    public static function get_richieste_utente($request) {
        // Ottieni user_id dal JWT token
        $user_id = self::get_user_id_from_jwt($request);
        
        if (!$user_id) {
            return new WP_Error('auth_failed', 'Autenticazione fallita', ['status' => 401]);
        }
        
        $per_page = $request->get_param('per_page');
        $page = $request->get_param('page');
        $stato = $request->get_param('stato');
        
        $args = [
            'post_type' => 'richiesta_servizio',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [
                [
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ],
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Filtro per stato
        if ($stato) {
            $args['meta_query'][] = [
                'key' => 'stato',
                'value' => $stato,
                'compare' => '='
            ];
        }
        
        $query = new WP_Query($args);
        
        $richieste = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $stato = get_post_meta($post_id, 'stato', true) ?: 'pending';
                $dati_json = get_post_meta($post_id, 'dati', true);
                $dati = json_decode($dati_json, true) ?: [];
                
                // Etichette stato
                $stato_labels = [
                    'pending' => 'In attesa',
                    'pending_payment' => 'In attesa di pagamento',
                    'processing' => 'In lavorazione',
                    'completed' => 'Completata',
                    'cancelled' => 'Annullata'
                ];
                
                $richieste[] = [
                    'id' => $post_id,
                    'numero_pratica' => get_post_meta($post_id, 'numero_pratica', true),
                    'servizio' => get_post_meta($post_id, 'servizio', true),
                    'categoria' => get_post_meta($post_id, 'categoria', true),
                    'stato' => $stato,
                    'stato_label' => $stato_labels[$stato] ?? ucfirst($stato),
                    'data_richiesta' => get_the_date('Y-m-d H:i:s'),
                    'prezzo' => get_post_meta($post_id, 'prezzo', true),
                    'prezzo_formattato' => get_post_meta($post_id, 'prezzo', true) ? '€ ' . number_format((float)get_post_meta($post_id, 'prezzo', true), 2, ',', '.') : null,
                    'pagamento' => [
                        'ricevuto' => get_post_meta($post_id, 'pagamento_ricevuto', true) == '1',
                        'metodo' => get_post_meta($post_id, 'pagamento_metodo', true),
                        'data' => get_post_meta($post_id, 'pagamento_data', true),
                        'transazione_id' => get_post_meta($post_id, 'pagamento_transazione_id', true)
                    ],
                    'payment_link' => get_post_meta($post_id, 'payment_link', true),
                    'puo_pagare' => $stato === 'pending_payment' && get_post_meta($post_id, 'pagamento_ricevuto', true) != '1',
                    'dati' => $dati
                ];
            }
            wp_reset_postdata();
        }
        
        return new WP_REST_Response([
            'success' => true,
            'richieste' => $richieste,
            'pagination' => [
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages,
                'current_page' => $page,
                'per_page' => $per_page
            ]
        ], 200);
    }
}
