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
        
        // DELETE /richiesta-servizio/{id} - Elimina richiesta (solo se pending)
        register_rest_route('wecoop/v1', '/richiesta-servizio/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'delete_richiesta'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'id' => ['required' => true, 'type' => 'integer']
            ]
        ]);
        
        // GET /pagamento/{id}/ricevuta - Download ricevuta PDF
        register_rest_route('wecoop/v1', '/pagamento/(?P<id>\d+)/ricevuta', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_ricevuta_pdf'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
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
        
        // ⚠️ PAGAMENTI GESTITI SOLO DA BACKOFFICE
        // La creazione automatica dei pagamenti è disabilitata
        // Gli admin possono richiedere pagamento dal pannello wp-admin
        $payment_id = null;
        $importo = null;
        
        // Ottieni prezzo dal listino (solo per salvarlo nei meta, NON crea pagamento)
        $prezzi_servizi = get_option('wecoop_listino_servizi', []);
        $prezzi_categorie = get_option('wecoop_listino_categorie', []);
        
        error_log("[WECOOP API] Richiesta #{$post_id} - Servizio: '{$servizio}', Categoria: '{$categoria}'");
        
        // Cerca prezzo per servizio specifico
        if (isset($prezzi_servizi[$servizio])) {
            $importo = floatval($prezzi_servizi[$servizio]);
            error_log("[WECOOP API] ✅ Prezzo trovato per servizio '{$servizio}': €{$importo}");
        }
        // Altrimenti cerca per categoria
        elseif ($categoria && isset($prezzi_categorie[$categoria])) {
            $importo = floatval($prezzi_categorie[$categoria]);
            error_log("[WECOOP API] ✅ Prezzo trovato per categoria '{$categoria}': €{$importo}");
        } else {
            error_log("[WECOOP API] ℹ️ Nessun prezzo predefinito per '{$servizio}' o '{$categoria}'");
        }
        
        // Salva l'importo suggerito (se trovato) ma NON crea il pagamento
        if ($importo && $importo > 0) {
            update_post_meta($post_id, 'importo', $importo);
            error_log("[WECOOP API] ℹ️ Importo suggerito €{$importo} salvato - pagamento da creare manualmente da backoffice");
        }
        
        // Stato iniziale sempre "pending" - admin deciderà se richiedere pagamento
        update_post_meta($post_id, 'stato', 'pending');
        update_post_meta($post_id, 'user_id', $current_user_id);
        
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
                    'button_url' => "wecoop://app/richieste/{$post_id}", // Deep link
                    'web_url' => home_url('/servizi/'),
                    'deep_link_home' => 'wecoop://app/home',
                    'deep_link_richieste' => 'wecoop://app/richieste'
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
            'requires_payment' => false, // Sempre false - pagamenti gestiti da backoffice
            'payment_id' => null,
            'importo' => $importo // Importo suggerito (se disponibile nel listino)
        ], 201);
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
                
                // Ottieni info pagamento dalla tabella wp_wecoop_pagamenti
                global $wpdb;
                $table_name = $wpdb->prefix . 'wecoop_pagamenti';
                $payment = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE richiesta_id = %d ORDER BY created_at DESC LIMIT 1",
                    $post_id
                ));
                
                $pagamento_ricevuto = false;
                $pagamento_metodo = null;
                $pagamento_data = null;
                $pagamento_transazione_id = null;
                
                if ($payment) {
                    $pagamento_ricevuto = in_array($payment->stato, ['paid', 'completed']);
                    $pagamento_metodo = $payment->metodo_pagamento;
                    $pagamento_data = $payment->paid_at;
                    $pagamento_transazione_id = $payment->transaction_id;
                }
                
                // Etichette stato
                $stato_labels = [
                    'pending' => 'In attesa',
                    'awaiting_payment' => 'In attesa di pagamento',
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
                    'payment_id' => $payment ? $payment->id : null,
                    'payment_status' => $payment ? $payment->stato : null,
                    'receipt_url' => $payment ? $payment->receipt_url : null,
                    'pagamento' => [
                        'ricevuto' => $pagamento_ricevuto,
                        'metodo' => $pagamento_metodo,
                        'data' => $pagamento_data,
                        'transazione_id' => $pagamento_transazione_id
                    ],
                    'payment_link' => get_post_meta($post_id, 'payment_link', true),
                    'puo_pagare' => in_array($stato, ['awaiting_payment', 'pending_payment']) && !$pagamento_ricevuto,
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
    
    /**
     * DELETE /richiesta-servizio/{id} - Elimina richiesta (solo se pending)
     */
    public static function delete_richiesta($request) {
        $richiesta_id = intval($request->get_param('id'));
        $current_user_id = self::get_user_id_from_jwt($request);
        
        if (!$current_user_id) {
            return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
        }
        
        // Verifica che la richiesta esista
        $richiesta = get_post($richiesta_id);
        if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
            return new WP_Error('not_found', 'Richiesta non trovata', ['status' => 404]);
        }
        
        // Verifica che l'utente sia il proprietario della richiesta
        $owner_id = get_post_meta($richiesta_id, 'user_id', true);
        if (intval($owner_id) !== $current_user_id) {
            return new WP_Error('forbidden', 'Non hai il permesso di eliminare questa richiesta', ['status' => 403]);
        }
        
        // Verifica che la richiesta sia in stato "pending"
        $stato = get_post_meta($richiesta_id, 'stato', true);
        if ($stato !== 'pending') {
            return new WP_Error(
                'invalid_status', 
                'Puoi eliminare solo richieste in attesa. Questa richiesta è in stato: ' . $stato,
                ['status' => 400]
            );
        }
        
        // Verifica che non ci sia un pagamento associato
        $payment_status = get_post_meta($richiesta_id, 'payment_status', true);
        if ($payment_status === 'paid' || $payment_status === 'pending') {
            return new WP_Error(
                'has_payment',
                'Non puoi eliminare una richiesta con un pagamento associato',
                ['status' => 400]
            );
        }
        
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        
        // Elimina la richiesta (soft delete - va nel cestino)
        $result = wp_trash_post($richiesta_id);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Impossibile eliminare la richiesta', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Richiesta eliminata con successo',
            'numero_pratica' => $numero_pratica,
            'servizio' => $servizio
        ], 200);
    }
    
    /**
     * GET /pagamento/{id}/ricevuta - Download ricevuta PDF
     */
    public static function get_ricevuta_pdf($request) {
        $payment_id = intval($request['id']);
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) {
            return new WP_Error('unauthorized', 'Autenticazione richiesta', ['status' => 401]);
        }
        
        // Recupera dati pagamento
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment) {
            return new WP_Error('payment_not_found', 'Pagamento non trovato', ['status' => 404]);
        }
        
        // Verifica ownership: l'utente deve essere il proprietario della richiesta o admin
        $richiesta_user_id = get_post_meta($payment->richiesta_id, 'user_id', true);
        
        if ($richiesta_user_id != $current_user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non hai i permessi per scaricare questa ricevuta', ['status' => 403]);
        }
        
        // Verifica che il pagamento sia completato
        if (!in_array($payment->stato, ['paid', 'completed'])) {
            return new WP_Error(
                'payment_not_completed',
                'La ricevuta sarà disponibile dopo il completamento del pagamento',
                ['status' => 400]
            );
        }
        
        // Se la ricevuta non esiste, generala
        if (empty($payment->receipt_url)) {
            $result = WeCoop_Ricevuta_PDF::generate_ricevuta($payment_id);
            
            if (!$result['success']) {
                return new WP_Error('generation_failed', $result['message'], ['status' => 500]);
            }
            
            $receipt_url = $result['receipt_url'];
        } else {
            $receipt_url = $payment->receipt_url;
        }
        
        // Converti URL in path filesystem
        $upload_dir = wp_upload_dir();
        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $receipt_url);
        
        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', 'File ricevuta non trovato sul server', ['status' => 404]);
        }
        
        // Leggi il file
        $file_content = file_get_contents($file_path);
        $filename = basename($file_path);
        
        // Ritorna il PDF come response binaria
        $response = new WP_REST_Response($file_content);
        $response->set_status(200);
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->header('Content-Length', filesize($file_path));
        $response->header('Cache-Control', 'private, max-age=3600');
        
        return $response;
    }
}
    }
}
