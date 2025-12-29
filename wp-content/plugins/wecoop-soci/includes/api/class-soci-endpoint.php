<?php
/**
 * REST API Endpoint: Gestione Soci
 * 
 * Endpoint completo per app Dart/Flutter
 * Base URL: /wp-json/wecoop/v1/soci
 * 
 * @package WECOOP_CRM
 * @since 2.1.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Soci_Endpoint {
    
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
     * Registra tutte le rotte
     */
    public static function register_routes() {
        
        // 1. CREATE: Primo Accesso - Registrazione Semplificata (solo 4 campi)
        // Endpoint principale per app Flutter
        register_rest_route('wecoop/v1', '/utenti/primo-accesso', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'crea_richiesta_adesione'],
            'permission_callback' => '__return_true', // Pubblico
            'args' => [
                'nome' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'cognome' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'prefix' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'telefono' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);
        
        // 1b. CREATE: Alias per retrocompatibilità
        register_rest_route('wecoop/v1', '/soci/richiesta', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'crea_richiesta_adesione'],
            'permission_callback' => '__return_true', // Pubblico
            'args' => [
                'nome' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'cognome' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'prefix' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'telefono' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);
        
        // 2. READ: Lista richieste adesione (con filtri)
        register_rest_route('wecoop/v1', '/soci/richieste', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richieste_adesione'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'status' => ['type' => 'string', 'default' => 'any'],
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'search' => ['type' => 'string']
            ]
        ]);
        
        // 3. READ: Dettaglio singola richiesta
        register_rest_route('wecoop/v1', '/soci/richiesta/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_richiesta_dettaglio'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 4. UPDATE: Approva richiesta e crea socio
        register_rest_route('wecoop/v1', '/soci/richiesta/(?P<id>\d+)/approva', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'approva_richiesta'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'numero_tessera' => ['required' => true, 'type' => 'string'],
                'data_adesione' => ['type' => 'string'],
                'quota_pagata' => ['type' => 'boolean', 'default' => false]
            ]
        ]);
        
        // 5. UPDATE: Rifiuta richiesta
        register_rest_route('wecoop/v1', '/soci/richiesta/(?P<id>\d+)/rifiuta', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'rifiuta_richiesta'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'motivo' => ['type' => 'string']
            ]
        ]);
        
        // 6. READ: Lista soci attivi
        register_rest_route('wecoop/v1', '/soci', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_soci'],
            'permission_callback' => [__CLASS__, 'check_jwt_auth'], // JWT auth
            'args' => [
                'status' => ['type' => 'string', 'default' => 'attivo'],
                'per_page' => ['type' => 'integer', 'default' => 50],
                'page' => ['type' => 'integer', 'default' => 1],
                'search' => ['type' => 'string'],
                'order_by' => ['type' => 'string', 'default' => 'cognome']
            ]
        ]);
        
        // 6b. READ: Dati utente corrente (me)
        register_rest_route('wecoop/v1', '/soci/me', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_current_user_data'],
            'permission_callback' => [__CLASS__, 'check_jwt_auth']
        ]);
        
        // 7. READ: Dettaglio socio
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_socio_dettaglio'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 8. UPDATE: Modifica dati socio
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [__CLASS__, 'update_socio'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 9. UPDATE: Attiva/Disattiva socio
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)/status', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'toggle_socio_status'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'status' => ['required' => true, 'type' => 'string', 'enum' => ['attivo', 'sospeso', 'cessato']],
                'motivo' => ['type' => 'string'],
                'data_effetto' => ['type' => 'string']
            ]
        ]);
        
        // 10. READ: Verifica se utente è socio attivo
        register_rest_route('wecoop/v1', '/soci/verifica/(?P<email>[^/]+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'verifica_socio_attivo'],
            'permission_callback' => '__return_true' // Pubblico ma con rate limiting
        ]);
        
        // 11. READ: Stats soci
        register_rest_route('wecoop/v1', '/soci/stats', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_stats_soci'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 12. READ: Storico pagamenti socio
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)/pagamenti', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_pagamenti_socio'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 13. CREATE: Aggiungi pagamento quota
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)/pagamenti', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'aggiungi_pagamento'],
            'permission_callback' => [__CLASS__, 'check_admin_permission'],
            'args' => [
                'importo' => ['required' => true, 'type' => 'number'],
                'tipo' => ['required' => true, 'type' => 'string'],
                'data_pagamento' => ['type' => 'string'],
                'metodo' => ['type' => 'string'],
                'note' => ['type' => 'string']
            ]
        ]);
        
        // 14. READ: Documenti socio
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)/documenti', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_documenti_socio'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 15. CREATE: Upload documento
        register_rest_route('wecoop/v1', '/soci/(?P<id>\d+)/documenti', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'upload_documento'],
            'permission_callback' => [__CLASS__, 'check_admin_permission']
        ]);
        
        // 16. UPDATE: Completa profilo socio (self-service)
        register_rest_route('wecoop/v1', '/soci/me/completa-profilo', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'completa_profilo_socio'],
            'permission_callback' => [__CLASS__, 'check_jwt_auth'],
            'args' => [
                'nome' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'cognome' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'email' => ['type' => 'string', 'sanitize_callback' => 'sanitize_email'],
                'telefono' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'prefix' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'citta' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'indirizzo' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'cap' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'provincia' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'codice_fiscale' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'data_nascita' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'luogo_nascita' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'professione' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'paese_provenienza' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'nazionalita' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
            ]
        ]);
        
        // 17. CREATE: Upload documento identità (self-service)
        register_rest_route('wecoop/v1', '/soci/me/upload-documento', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'upload_documento_identita'],
            'permission_callback' => [__CLASS__, 'check_jwt_auth']
        ]);
        
        // 18. CHECK: Verifica se username esiste (pubblico per debug app)
        register_rest_route('wecoop/v1', '/soci/check-username', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'check_username_exists'],
            'permission_callback' => '__return_true',
            'args' => [
                'username' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
            ]
        ]);
        
        // 19. POST: Reset password (recupera password)
        register_rest_route('wecoop/v1', '/soci/reset-password', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'reset_password'],
            'permission_callback' => '__return_true',
            'args' => [
                'telefono' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'email' => ['type' => 'string', 'sanitize_callback' => 'sanitize_email']
            ]
        ]);
        
        // 20. POST: Cambia password (autenticato)
        register_rest_route('wecoop/v1', '/soci/me/change-password', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'change_password'],
            'permission_callback' => [__CLASS__, 'check_jwt_auth'],
            'args' => [
                'old_password' => ['required' => true, 'type' => 'string'],
                'new_password' => ['required' => true, 'type' => 'string']
            ]
        ]);
    }
    
    /**
     * Verifica autenticazione JWT o sessione WordPress
     */
    public static function check_jwt_auth($request) {
        // Prova prima con sessione WordPress standard
        if (is_user_logged_in()) {
            return true;
        }
        
        // Altrimenti prova con JWT token
        $auth_header = $request->get_header('Authorization');
        
        if (!$auth_header) {
            return new WP_Error('no_auth', 'Autenticazione richiesta', ['status' => 401]);
        }
        
        // Valida JWT token usando WeCoop_Auth_Handler
        if (class_exists('WeCoop_Auth_Handler')) {
            $token = str_replace('Bearer ', '', $auth_header);
            $user_id = WeCoop_Auth_Handler::validate_jwt_token($token);
            
            if ($user_id) {
                wp_set_current_user($user_id);
                return true;
            }
        }
        
        return new WP_Error('invalid_token', 'Token non valido o scaduto', ['status' => 401]);
    }
    
    /**
     * 1. PRIMO ACCESSO - Registrazione Completa con Creazione Utente
     * 
     * Endpoint: POST /wp-json/wecoop/v1/utenti/primo-accesso
     * 
     * Crea immediatamente un utente WordPress con:
     * - Username: telefono completo (es: +393331234567)
     * - Password: generata automaticamente (memorabile)
     * - Ruolo: subscriber (non socio)
     * 
     * L'utente può fare login subito con le credenziali ricevute.
     * Potrà diventare socio successivamente tramite operatore CRM.
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function crea_richiesta_adesione($request) {
        error_log('========== INIZIO PRIMO ACCESSO (CREAZIONE UTENTE) ==========');
        
        try {
            $params = $request->get_params();
            error_log('[SOCI] Parametri ricevuti: ' . json_encode($params, JSON_UNESCAPED_UNICODE));
            
            // Validazione campi obbligatori: SOLO Nome, Cognome, Prefix, Telefono
            if (empty($params['nome']) || empty($params['cognome']) || empty($params['prefix']) || empty($params['telefono'])) {
                error_log('[SOCI] ERROR: Campi obbligatori mancanti');
                error_log('[SOCI]   - nome: ' . (empty($params['nome']) ? 'MANCANTE' : 'OK'));
                error_log('[SOCI]   - cognome: ' . (empty($params['cognome']) ? 'MANCANTE' : 'OK'));
                error_log('[SOCI]   - prefix: ' . (empty($params['prefix']) ? 'MANCANTE' : 'OK'));
                error_log('[SOCI]   - telefono: ' . (empty($params['telefono']) ? 'MANCANTE' : 'OK'));
                return new WP_Error(
                    'invalid_data', 
                    'Nome, cognome, prefix e telefono sono obbligatori', 
                    ['status' => 400]
                );
            }
        
        error_log('[SOCI] Dati utente:');
        error_log('[SOCI]   - Nome: ' . $params['nome'] . ' ' . $params['cognome']);
        error_log('[SOCI]   - Prefix: ' . $params['prefix']);
        error_log('[SOCI]   - Telefono: ' . $params['telefono']);
        
        // Combina prefix + telefono
        $telefono_completo = $params['prefix'] . $params['telefono'];
        error_log('[SOCI] Telefono completo: ' . $telefono_completo);
        
        // Verifica se esiste già una richiesta ATTIVA con questo numero di telefono
        // IMPORTANTE: Escludiamo 'trash' per permettere re-registrazione dopo eliminazione
        error_log('[SOCI] Verifica telefono esistente...');
        $existing_by_phone = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'telefono_completo',
            'meta_value' => $telefono_completo,
            'post_status' => ['publish', 'pending'], // NO 'trash' - permetti re-registrazione
            'posts_per_page' => 1
        ]);
        
        if (!empty($existing_by_phone)) {
            $existing_post = $existing_by_phone[0];
            error_log('[SOCI] Trovato post esistente: ID=' . $existing_post->ID . ', Status=' . $existing_post->post_status);
            
            // Verifica se ha un user_id collegato e se l'utente esiste ancora
            $existing_user_id = get_post_meta($existing_post->ID, 'user_id_socio', true);
            error_log('[SOCI] User ID collegato: ' . ($existing_user_id ?: 'nessuno'));
            
            if ($existing_user_id) {
                $user_exists = get_user_by('id', $existing_user_id);
                if ($user_exists) {
                    error_log('[SOCI] ERROR: Utente WordPress ancora esistente (ID: ' . $existing_user_id . ')');
                    return new WP_Error(
                        'duplicate_phone', 
                        'Telefono già registrato', 
                        ['status' => 400]
                    );
                } else {
                    error_log('[SOCI] Utente WordPress non esiste più, permetto re-registrazione');
                }
            } else {
                // Post esiste ma senza utente collegato (caso anomalo)
                error_log('[SOCI] ERROR: Post esiste ma senza utente collegato');
                return new WP_Error(
                    'duplicate_phone', 
                    'Telefono già registrato', 
                    ['status' => 400]
                );
            }
        } else {
            error_log('[SOCI] Nessun post esistente trovato con questo telefono');
        }
        
        // ===== CREAZIONE UTENTE WORDPRESS =====
        error_log('[SOCI] Creazione utente WordPress...');
        
        $nome = sanitize_text_field($params['nome']);
        $cognome = sanitize_text_field($params['cognome']);
        
        // Username = telefono completo (unico e facile da ricordare)
        $username = $telefono_completo;
        error_log('[SOCI] Username: ' . $username);
        
        // Genera password memorabile
        $password = self::generate_memorable_password();
        error_log('[SOCI] Password generata (lunghezza: ' . strlen($password) . ')');
        
        // Crea utente WordPress (email opzionale, può essere aggiunta dopo)
        $user_id = wp_create_user($username, $password, null);
        
        if (is_wp_error($user_id)) {
            error_log('[SOCI] ERROR: wp_create_user fallito: ' . $user_id->get_error_message());
            return new WP_Error(
                'user_creation_failed', 
                'Errore nella creazione dell\'account: ' . $user_id->get_error_message(), 
                ['status' => 500]
            );
        }
        
        error_log('[SOCI] Utente WordPress creato con ID: ' . $user_id);
        
        // Assegna ruolo subscriber (utente base, non socio)
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        // Aggiorna nome e cognome
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $nome,
            'last_name' => $cognome,
            'display_name' => $nome . ' ' . $cognome
        ]);
        
        // Salva dati aggiuntivi come user meta
        update_user_meta($user_id, 'prefix', sanitize_text_field($params['prefix']));
        update_user_meta($user_id, 'telefono', sanitize_text_field($params['telefono']));
        update_user_meta($user_id, 'telefono_completo', $telefono_completo);
        update_user_meta($user_id, 'profilo_completo', false);
        update_user_meta($user_id, 'is_socio', false); // Può diventare socio dopo
        
        // Crea post richiesta collegato all'utente
        error_log('[SOCI] Creazione post richiesta_socio...');
        $post_id = wp_insert_post([
            'post_type' => 'richiesta_socio',
            'post_title' => $nome . ' ' . $cognome,
            'post_status' => 'publish', // Pubblicato perché utente già creato
            'post_author' => $user_id // Collegato all'utente creato
        ]);
        
        if (is_wp_error($post_id)) {
            error_log('[SOCI] ERROR: wp_insert_post fallito: ' . $post_id->get_error_message());
            // Rollback: elimina utente appena creato
            wp_delete_user($user_id);
            return new WP_Error(
                'server_error', 
                'Errore nella creazione del profilo: ' . $post_id->get_error_message(), 
                ['status' => 500]
            );
        }
        
        error_log('[SOCI] Post richiesta creato con ID: ' . $post_id);
        
        // Salva i 4 campi obbligatori nel post
        update_post_meta($post_id, 'nome', $nome);
        update_post_meta($post_id, 'cognome', $cognome);
        update_post_meta($post_id, 'prefix', sanitize_text_field($params['prefix']));
        update_post_meta($post_id, 'telefono', sanitize_text_field($params['telefono']));
        update_post_meta($post_id, 'telefono_completo', $telefono_completo);
        update_post_meta($post_id, 'user_id_socio', $user_id); // Collegamento utente
        update_post_meta($post_id, 'profilo_completo', false);
        
        // Genera numero pratica
        $numero_pratica = 'RS-' . date('Y') . '-' . str_pad($post_id, 5, '0', STR_PAD_LEFT);
        update_post_meta($post_id, 'numero_pratica', $numero_pratica);
        
        error_log('[SOCI] Registrazione completata con successo');
        error_log('[SOCI] User ID: ' . $user_id . ', Post ID: ' . $post_id . ', Numero Pratica: ' . $numero_pratica);
        error_log('========== CREDENZIALI PER LOGIN AUTOMATICO ==========');
        error_log('[LOGIN-AUTO] Username: ' . $username);
        error_log('[LOGIN-AUTO] Password generata: ' . $password);
        error_log('[LOGIN-AUTO] User ID WordPress: ' . $user_id);
        error_log('[LOGIN-AUTO] Verifico se utente esiste in wp_users...');
        $wp_user = get_userdata($user_id);
        if ($wp_user) {
            error_log('[LOGIN-AUTO] ✓ Utente trovato in wp_users: ' . $wp_user->user_login);
            error_log('[LOGIN-AUTO] ✓ Email: ' . ($wp_user->user_email ?: 'nessuna'));
            error_log('[LOGIN-AUTO] ✓ Ruolo: ' . implode(', ', $wp_user->roles));
        } else {
            error_log('[LOGIN-AUTO] ✗ ERRORE: Utente NON trovato in wp_users!');
        }
        error_log('[LOGIN-AUTO] Invio credenziali all\'app per login automatico...');
        error_log('========== FINE PRIMO ACCESSO (UTENTE CREATO) ==========');
        
        // Risposta con credenziali di accesso
        return rest_ensure_response([
            'success' => true,
            'message' => 'Registrazione completata! Usa queste credenziali per accedere.',
            'data' => [
                'id' => $post_id,
                'user_id' => $user_id,
                'numero_pratica' => $numero_pratica,
                'username' => $username,
                'password' => $password,
                'nome' => $nome,
                'cognome' => $cognome,
                'prefix' => $params['prefix'],
                'telefono' => $params['telefono'],
                'telefono_completo' => $telefono_completo,
                'is_socio' => false,
                'profilo_completo' => false
            ]
        ]);
        
        } catch (Exception $e) {
            error_log('[SOCI] EXCEPTION CRITICA: ' . $e->getMessage());
            error_log('[SOCI] Stack trace: ' . $e->getTraceAsString());
            error_log('========== FINE PRIMO ACCESSO (EXCEPTION) ==========');
            return new WP_Error(
                'server_error', 
                'Errore critico: ' . $e->getMessage(), 
                ['status' => 500]
            );
        }
    }
    
    /**
     * 2. Get lista richieste adesione
     */
    public static function get_richieste_adesione($request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'richiesta_socio',
            'post_status' => $params['status'],
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        if (!empty($params['search'])) {
            $args['s'] = $params['search'];
        }
        
        $query = new WP_Query($args);
        
        $richieste = [];
        foreach ($query->posts as $post) {
            $richieste[] = [
                'id' => $post->ID,
                'numero_pratica' => get_post_meta($post->ID, 'numero_pratica', true),
                'nome' => get_post_meta($post->ID, 'nome', true),
                'cognome' => get_post_meta($post->ID, 'cognome', true),
                'email' => get_post_meta($post->ID, 'email', true),
                'telefono' => get_post_meta($post->ID, 'telefono', true),
                'citta' => get_post_meta($post->ID, 'citta', true),
                'status' => $post->post_status,
                'data_richiesta' => get_the_date('c', $post)
            ];
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $richieste,
            'pagination' => [
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages,
                'current_page' => $params['page'],
                'per_page' => $params['per_page']
            ]
        ]);
    }
    
    /**
     * 3. Get dettaglio richiesta
     */
    public static function get_richiesta_dettaglio($request) {
        $post_id = $request['id'];
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'richiesta_socio') {
            return new WP_Error('not_found', 'Richiesta non trovata', ['status' => 404]);
        }
        
        $fields = ['nome', 'cognome', 'email', 'telefono', 'data_nascita', 
                   'luogo_nascita', 'codice_fiscale', 'indirizzo', 'citta', 
                   'cap', 'provincia', 'professione', 'motivazione', 'note_admin'];
        
        $data = ['id' => $post_id, 'status' => $post->post_status];
        foreach ($fields as $field) {
            $data[$field] = get_post_meta($post_id, $field, true);
        }
        
        return rest_ensure_response(['success' => true, 'data' => $data]);
    }
    
    /**
     * 4. Approva richiesta e crea socio
     */
    public static function approva_richiesta($request) {
        $richiesta_id = $request['id'];
        $params = $request->get_params();
        
        $richiesta = get_post($richiesta_id);
        if (!$richiesta || $richiesta->post_type !== 'richiesta_socio') {
            return new WP_Error('not_found', 'Richiesta non trovata', ['status' => 404]);
        }
        
        // Recupera dati
        $email = get_post_meta($richiesta_id, 'email', true);
        $nome = get_post_meta($richiesta_id, 'nome', true);
        $cognome = get_post_meta($richiesta_id, 'cognome', true);
        
        // Verifica se esiste già utente con questa email
        if (email_exists($email)) {
            return new WP_Error('user_exists', 'Esiste già un utente con questa email', ['status' => 409]);
        }
        
        // Username = email (WordPress permette email come username)
        $username = $email;
        
        // Genera password temporanea facile (2 parole + 2 numeri)
        $parole = ['Casa', 'Mare', 'Sole', 'Luna', 'Fiore', 'Cielo', 'Terra', 'Acqua', 
                   'Vento', 'Stella', 'Neve', 'Pioggia', 'Notte', 'Giorno', 'Estate'];
        $password = $parole[array_rand($parole)] . $parole[array_rand($parole)] . wp_rand(10, 99);
        
        // Crea utente WordPress con email come username
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Aggiungi ruolo socio
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        $user->add_role('socio');
        
        // Genera UUID per numero tessera se non fornito
        if (empty($params['numero_tessera'])) {
            $params['numero_tessera'] = wp_generate_uuid4();
        }
        
        // Salva dati socio come user meta
        update_user_meta($user_id, 'numero_tessera', $params['numero_tessera']);
        update_user_meta($user_id, 'data_adesione', $params['data_adesione'] ?? current_time('Y-m-d'));
        update_user_meta($user_id, 'status_socio', 'attivo');
        update_user_meta($user_id, 'quota_pagata', $params['quota_pagata']);
        
        // Copia campi dalla richiesta
        $fields = ['nome', 'cognome', 'telefono', 'data_nascita', 'luogo_nascita',
                   'codice_fiscale', 'indirizzo', 'citta', 'cap', 'provincia', 'professione'];
        foreach ($fields as $field) {
            $value = get_post_meta($richiesta_id, $field, true);
            if ($value) {
                update_user_meta($user_id, $field, $value);
            }
        }
        
        // Aggiorna stato richiesta
        wp_update_post([
            'ID' => $richiesta_id,
            'post_status' => 'publish'
        ]);
        update_post_meta($richiesta_id, 'user_id_socio', $user_id);
        update_post_meta($richiesta_id, 'data_approvazione', current_time('mysql'));
        
        // Genera token per cambio password (valido 7 giorni)
        $reset_token = wp_generate_password(64, false);
        update_user_meta($user_id, 'password_reset_token', $reset_token);
        update_user_meta($user_id, 'password_reset_token_time', time());
        
        // Invia email di benvenuto con credenziali
        $tessera_url = home_url('/tessera-socio/?id=' . $params['numero_tessera']);
        $reset_url = home_url('/cambia-password.php?token=' . $reset_token);
        
        $message = "Ciao $nome,\n\n";
        $message .= "La tua richiesta di adesione a WECOOP è stata approvata! 🎉\n\n";
        $message .= "--- CREDENZIALI DI ACCESSO ---\n";
        $message .= "Email: $email\n";
        $message .= "Password temporanea: $password\n\n";
        $message .= "--- LA TUA TESSERA DIGITALE ---\n";
        $message .= "Numero Tessera: {$params['numero_tessera']}\n\n";
        $message .= "Visualizza la tua tessera digitale con QR Code:\n";
        $message .= "$tessera_url\n\n";
        $message .= "--- CAMBIA PASSWORD ---\n";
        $message .= "Ti consigliamo di cambiare subito la password temporanea:\n";
        $message .= "$reset_url\n";
        $message .= "(Link valido per 24 ore)\n\n";
        $message .= "⚠️ IMPORTANTE:\n";
        $message .= "- Salva il link della tessera tra i preferiti\n";
        // URL tessera digitale
        $tessera_url = home_url('/tessera-socio/?id=' . $params['numero_tessera']);
        
        // Log inizio invio email
        error_log("WECOOP: Tentativo invio email benvenuto a {$email}");
        
        // Invia email MULTILINGUA con template (usa Accept-Language header)
        $email_sent = false;
        if (class_exists('WeCoop_Multilingual_Email')) {
            error_log("WECOOP: Usando sistema email multilingua");
            $email_sent = WeCoop_Multilingual_Email::send(
                $email,
                'member_approved',
                [
                    'nome' => $nome,
                    'email' => $email,
                    'password' => $password,
                    'numero_tessera' => $params['numero_tessera'],
                    'tessera_url' => $tessera_url,
                    'button_url' => wp_login_url()
                ],
                $user_id,
                $request // Pass request per ottenere Accept-Language header
            );
            error_log("WECOOP: Email multilingua result: " . ($email_sent ? 'SUCCESS' : 'FAILED'));
        } else {
            error_log("WECOOP: Sistema multilingua non disponibile, uso fallback");
            // Fallback al vecchio sistema
            $content = "
                <h1>Benvenuto in WECOOP, {$nome}! 🎉</h1>
                <p>La tua richiesta di adesione è stata <strong>approvata</strong>!</p>
                
                <h2>📋 I tuoi dati di accesso:</h2>
                <div style='background: #f8f9fa; padding: 20px; border-left: 4px solid #3498db; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 5px 0;'><strong>Email / Username:</strong> {$email}</p>
                    <p style='margin: 5px 0;'><strong>Password temporanea:</strong> <span style='font-family: monospace; background: #e9ecef; padding: 5px 10px; border-radius: 3px;'>{$password}</span></p>
                    <p style='margin: 5px 0;'><strong>Numero tessera:</strong> {$params['numero_tessera']}</p>
                </div>
            ";
            
            if (class_exists('WeCoop_Email_Template_Unified')) {
                $email_sent = WeCoop_Email_Template_Unified::send(
                    $email,
                    '🎉 Benvenuto in WECOOP',
                    $content,
                    [
                        'button_text' => '🔐 Accedi alla Piattaforma',
                        'button_url' => wp_login_url()
                    ]
                );
            } else {
                $email_sent = wp_mail($email, '🎉 Benvenuto in WECOOP', strip_tags($content));
            }
        }
        
        // Log risultato finale
        if ($email_sent) {
            error_log("WECOOP: ✓ Email INVIATA con successo a {$email}");
        } else {
            error_log("WECOOP: ✗ ERRORE invio email a {$email}");
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Richiesta approvata e socio creato',
            'data' => [
                'user_id' => $user_id,
                'email' => $email,
                'numero_tessera' => $params['numero_tessera']
            ]
        ]);
    }
    
    /**
     * 5. Rifiuta richiesta
     */
    public static function rifiuta_richiesta($request) {
        $richiesta_id = $request['id'];
        $motivo = $request['motivo'] ?? '';
        
        wp_update_post([
            'ID' => $richiesta_id,
            'post_status' => 'draft'
        ]);
        
        update_post_meta($richiesta_id, 'motivo_rifiuto', $motivo);
        update_post_meta($richiesta_id, 'data_rifiuto', current_time('mysql'));
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Richiesta rifiutata'
        ]);
    }
    
    /**
     * 6. Get lista soci
     */
    public static function get_soci($request) {
        $params = $request->get_params();
        
        $args = [
            'number' => $params['per_page'],
            'offset' => ($params['page'] - 1) * $params['per_page'],
            'orderby' => $params['order_by'],
            'meta_query' => []
        ];
        
        // Filtra solo chi ha numero_tessera (= è socio)
        $args['meta_query'][] = [
            'key' => 'numero_tessera',
            'compare' => 'EXISTS'
        ];
        
        if ($params['status'] !== 'all') {
            $args['meta_query'][] = [
                'key' => 'status_socio',
                'value' => $params['status']
            ];
        }
        
        if (!empty($params['search'])) {
            $args['search'] = '*' . $params['search'] . '*';
        }
        
        $user_query = new WP_User_Query($args);
        
        $soci = [];
        foreach ($user_query->get_results() as $user) {
            $soci[] = [
                'id' => $user->ID,
                'numero_tessera' => get_user_meta($user->ID, 'numero_tessera', true),
                'nome' => get_user_meta($user->ID, 'nome', true),
                'cognome' => get_user_meta($user->ID, 'cognome', true),
                'email' => $user->user_email,
                'telefono' => get_user_meta($user->ID, 'telefono', true),
                'status' => get_user_meta($user->ID, 'status_socio', true) ?: 'attivo',
                'data_adesione' => get_user_meta($user->ID, 'data_adesione', true)
            ];
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $soci,
            'pagination' => [
                'total' => $user_query->get_total(),
                'current_page' => $params['page'],
                'per_page' => $params['per_page']
            ]
        ]);
    }
    
    /**
     * 6b. Get dati utente corrente
     */
    public static function get_current_user_data($request) {
        $current_user = wp_get_current_user();
        
        if (!$current_user || $current_user->ID === 0) {
            return new WP_Error('not_authenticated', 'Utente non autenticato', ['status' => 401]);
        }
        
        $user_id = $current_user->ID;
        
        // Recupera tutti i metadati del socio
        $fields = [
            'numero_tessera', 'nome', 'cognome', 'prefix', 'telefono', 'telefono_completo',
            'citta', 'indirizzo', 'cap', 'provincia', 'codice_fiscale',
            'data_nascita', 'luogo_nascita', 'professione', 
            'paese_provenienza', 'nazionalita',
            'status_socio', 'data_adesione', 'quota_pagata',
            'profilo_completo', 'campi_mancanti'
        ];
        
        $data = [
            'id' => $user_id,
            'username' => $current_user->user_login,
            'email' => $current_user->user_email,
            'display_name' => $current_user->display_name,
            'roles' => $current_user->roles
        ];
        
        // Aggiungi metadati
        foreach ($fields as $field) {
            $value = get_user_meta($user_id, $field, true);
            $data[$field] = $value ?: null;
        }
        
        // Calcola anzianità se ha data adesione
        if (!empty($data['data_adesione'])) {
            try {
                $data_start = new DateTime($data['data_adesione']);
                $data_now = new DateTime();
                $data['anni_socio'] = $data_start->diff($data_now)->y;
            } catch (Exception $e) {
                $data['anni_socio'] = 0;
            }
        }
        
        // URL tessera digitale
        if (!empty($data['numero_tessera'])) {
            $data['tessera_url'] = home_url('/tessera-socio/?id=' . $data['numero_tessera']);
        }
        
        // Documenti caricati
        $documenti_caricati = get_user_meta($user_id, 'documenti_caricati', true);
        if (!empty($documenti_caricati) && is_array($documenti_caricati)) {
            $data['documenti'] = array_map(function($doc) {
                return [
                    'id' => $doc['id'],
                    'tipo' => $doc['tipo'],
                    'data' => $doc['data'],
                    'url' => wp_get_attachment_url($doc['id']),
                    'filename' => basename(get_attached_file($doc['id']))
                ];
            }, $documenti_caricati);
            $data['has_documento_identita'] = true;
        } else {
            $data['documenti'] = [];
            $data['has_documento_identita'] = false;
        }
        
        // Calcola percentuale completamento profilo
        if (!empty($data['campi_mancanti']) && is_array($data['campi_mancanti'])) {
            $total_fields = 9; // campi obbligatori per profilo completo
            $missing = count($data['campi_mancanti']);
            $data['percentuale_completamento'] = round((1 - ($missing / $total_fields)) * 100);
        } else {
            $data['percentuale_completamento'] = $data['profilo_completo'] ? 100 : 0;
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * 7. Get dettaglio socio
     */
    public static function get_socio_dettaglio($request) {
        $user_id = $request['id'];
        $user = get_userdata($user_id);
        
        if (!$user) {
            return new WP_Error('not_found', 'Socio non trovato', ['status' => 404]);
        }
        
        $fields = ['numero_tessera', 'nome', 'cognome', 'telefono', 'data_nascita',
                   'luogo_nascita', 'codice_fiscale', 'indirizzo', 'citta', 'cap',
                   'provincia', 'professione', 'status_socio', 'data_adesione', 'quota_pagata'];
        
        $data = ['id' => $user_id, 'email' => $user->user_email];
        foreach ($fields as $field) {
            $data[$field] = get_user_meta($user_id, $field, true);
        }
        
        return rest_ensure_response(['success' => true, 'data' => $data]);
    }
    
    /**
     * 8. Update dati socio
     */
    public static function update_socio($request) {
        $user_id = $request['id'];
        $params = $request->get_json_params();
        
        $allowed_fields = ['nome', 'cognome', 'telefono', 'indirizzo', 'citta', 
                          'cap', 'provincia', 'professione', 'numero_tessera'];
        
        foreach ($allowed_fields as $field) {
            if (isset($params[$field])) {
                update_user_meta($user_id, $field, sanitize_text_field($params[$field]));
            }
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Dati aggiornati'
        ]);
    }
    
    /**
     * 9. Attiva/Disattiva socio
     */
    public static function toggle_socio_status($request) {
        $user_id = $request['id'];
        $status = $request['status'];
        $motivo = $request['motivo'] ?? '';
        $data_effetto = $request['data_effetto'] ?? current_time('Y-m-d');
        
        update_user_meta($user_id, 'status_socio', $status);
        update_user_meta($user_id, 'data_cambio_status', $data_effetto);
        
        if ($motivo) {
            update_user_meta($user_id, 'motivo_cambio_status', $motivo);
        }
        
        // Log storico
        $storico = get_user_meta($user_id, 'storico_status', true) ?: [];
        $storico[] = [
            'status' => $status,
            'data' => $data_effetto,
            'motivo' => $motivo,
            'timestamp' => current_time('mysql')
        ];
        update_user_meta($user_id, 'storico_status', $storico);
        
        return rest_ensure_response([
            'success' => true,
            'message' => "Socio {$status}",
            'data' => ['new_status' => $status]
        ]);
    }
    
    /**
     * 10. Verifica se email è socio attivo
     */
    public static function verifica_socio_attivo($request) {
        $email = urldecode($request['email']);
        
        $user = get_user_by('email', $email);
        if (!$user) {
            return rest_ensure_response([
                'success' => true,
                'is_socio' => false
            ]);
        }
        
        $status = get_user_meta($user->ID, 'status_socio', true);
        
        return rest_ensure_response([
            'success' => true,
            'is_socio' => true,
            'is_attivo' => ($status === 'attivo'),
            'status' => $status ?: 'attivo',
            'numero_tessera' => get_user_meta($user->ID, 'numero_tessera', true),
            'data_adesione' => get_user_meta($user->ID, 'data_adesione', true)
        ]);
    }
    
    /**
     * 11. Get statistiche soci
     */
    public static function get_stats_soci($request) {
        global $wpdb;
        
        // Conta soci per status
        $attivi = count_users_by_meta('status_socio', 'attivo');
        $sospesi = count_users_by_meta('status_socio', 'sospeso');
        $cessati = count_users_by_meta('status_socio', 'cessato');
        
        // Richieste pending
        $richieste_pending = wp_count_posts('richiesta_socio')->pending;
        
        // Nuovi soci questo mese
        $data_inizio_mese = date('Y-m-01');
        $nuovi_mese = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key = 'data_adesione' 
             AND meta_value >= %s",
            $data_inizio_mese
        ));
        
        return rest_ensure_response([
            'success' => true,
            'data' => [
                'totale_soci' => $attivi + $sospesi,
                'attivi' => $attivi,
                'sospesi' => $sospesi,
                'cessati' => $cessati,
                'richieste_pending' => $richieste_pending,
                'nuovi_questo_mese' => (int)$nuovi_mese
            ]
        ]);
    }
    
    /**
     * 12. Get pagamenti socio
     */
    public static function get_pagamenti_socio($request) {
        $user_id = $request['id'];
        
        $pagamenti = get_user_meta($user_id, 'pagamenti', true) ?: [];
        
        return rest_ensure_response([
            'success' => true,
            'data' => $pagamenti
        ]);
    }
    
    /**
     * 13. Aggiungi pagamento
     */
    public static function aggiungi_pagamento($request) {
        $user_id = $request['id'];
        $params = $request->get_params();
        
        $pagamenti = get_user_meta($user_id, 'pagamenti', true) ?: [];
        
        $pagamenti[] = [
            'id' => uniqid('pag_'),
            'importo' => $params['importo'],
            'tipo' => $params['tipo'],
            'data_pagamento' => $params['data_pagamento'] ?? current_time('Y-m-d'),
            'metodo' => $params['metodo'] ?? '',
            'note' => $params['note'] ?? '',
            'timestamp' => current_time('mysql')
        ];
        
        update_user_meta($user_id, 'pagamenti', $pagamenti);
        
        // Aggiorna quota_pagata se è quota associativa
        if ($params['tipo'] === 'quota_associativa') {
            update_user_meta($user_id, 'quota_pagata', true);
            update_user_meta($user_id, 'data_ultimo_pagamento', $params['data_pagamento']);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Pagamento registrato'
        ]);
    }
    
    /**
     * 14. Get documenti socio
     */
    public static function get_documenti_socio($request) {
        $user_id = $request['id'];
        
        $documenti = get_posts([
            'post_type' => 'attachment',
            'author' => $user_id,
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => 'documento_socio',
                'value' => 'yes'
            ]]
        ]);
        
        $result = [];
        foreach ($documenti as $doc) {
            $result[] = [
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'filename' => basename(get_attached_file($doc->ID)),
                'url' => wp_get_attachment_url($doc->ID),
                'tipo' => get_post_meta($doc->ID, 'tipo_documento', true),
                'data_upload' => get_the_date('c', $doc)
            ];
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * 15. Upload documento
     */
    public static function upload_documento($request) {
        $user_id = $request['id'];
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $files = $request->get_file_params();
        
        if (empty($files['file'])) {
            return new WP_Error('no_file', 'Nessun file caricato', ['status' => 400]);
        }
        
        $attachment_id = media_handle_sideload($files['file'], 0);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Meta
        update_post_meta($attachment_id, 'documento_socio', 'yes');
        update_post_meta($attachment_id, 'socio_id', $user_id);
        update_post_meta($attachment_id, 'tipo_documento', $request['tipo_documento'] ?? 'altro');
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Documento caricato',
            'data' => [
                'id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id)
            ]
        ]);
    }
    
    /**
     * Check admin permission
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * 16. Completa profilo socio (self-service)
     */
    public static function completa_profilo_socio($request) {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'Utente non autenticato', ['status' => 401]);
        }
        
        $params = $request->get_params();
        
        // Campi aggiornabili
        $updatable_fields = [
            'nome', 'cognome', 'email', 'telefono', 'prefix', 'citta', 'indirizzo',
            'cap', 'provincia', 'codice_fiscale', 'data_nascita', 'luogo_nascita',
            'professione', 'paese_provenienza', 'nazionalita'
        ];
        
        // Validazione email se fornita
        if (!empty($params['email'])) {
            if (!is_email($params['email'])) {
                return new WP_Error('invalid_email', 'Email non valida', ['status' => 400]);
            }
            
            // Verifica che l'email non sia già usata da un altro utente
            $email_user = get_user_by('email', $params['email']);
            if ($email_user && $email_user->ID !== $user_id) {
                return new WP_Error('email_exists', 'Questa email è già utilizzata da un altro utente', ['status' => 409]);
            }
            
            // Aggiorna email WordPress
            wp_update_user([
                'ID' => $user_id,
                'user_email' => $params['email']
            ]);
        }
        
        // Aggiorna nome completo display_name se forniti nome e cognome
        if (!empty($params['nome']) || !empty($params['cognome'])) {
            $nome = !empty($params['nome']) ? $params['nome'] : get_user_meta($user_id, 'nome', true);
            $cognome = !empty($params['cognome']) ? $params['cognome'] : get_user_meta($user_id, 'cognome', true);
            
            if (!empty($nome) && !empty($cognome)) {
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $nome . ' ' . $cognome,
                    'first_name' => $nome,
                    'last_name' => $cognome
                ]);
            }
        }
        
        // Gestione telefono completo
        if (!empty($params['prefix']) && !empty($params['telefono'])) {
            $telefono_completo = '+' . $params['prefix'] . $params['telefono'];
            update_user_meta($user_id, 'telefono_completo', $telefono_completo);
        } elseif (!empty($params['prefix'])) {
            $telefono = get_user_meta($user_id, 'telefono', true);
            if (!empty($telefono)) {
                $telefono_completo = '+' . $params['prefix'] . $telefono;
                update_user_meta($user_id, 'telefono_completo', $telefono_completo);
            }
        } elseif (!empty($params['telefono'])) {
            $prefix = get_user_meta($user_id, 'prefix', true);
            if (!empty($prefix)) {
                $telefono_completo = '+' . $prefix . $params['telefono'];
                update_user_meta($user_id, 'telefono_completo', $telefono_completo);
            }
        }
        
        // Aggiorna user meta
        $updated_fields = [];
        foreach ($updatable_fields as $field) {
            if (isset($params[$field]) && !empty($params[$field])) {
                update_user_meta($user_id, $field, $params[$field]);
                $updated_fields[] = $field;
            }
        }
        
        // Verifica se profilo è completo (campi minimi richiesti)
        $required_for_complete = [
            'nome' => get_user_meta($user_id, 'nome', true),
            'cognome' => get_user_meta($user_id, 'cognome', true),
            'email' => $current_user->user_email,
            'telefono' => get_user_meta($user_id, 'telefono', true),
            'citta' => get_user_meta($user_id, 'citta', true),
            'indirizzo' => get_user_meta($user_id, 'indirizzo', true),
            'codice_fiscale' => get_user_meta($user_id, 'codice_fiscale', true),
            'data_nascita' => get_user_meta($user_id, 'data_nascita', true),
            'nazionalita' => get_user_meta($user_id, 'nazionalita', true)
        ];
        
        $profilo_completo = true;
        $campi_mancanti = [];
        
        foreach ($required_for_complete as $field => $value) {
            if (empty($value)) {
                $profilo_completo = false;
                $campi_mancanti[] = $field;
            }
        }
        
        update_user_meta($user_id, 'profilo_completo', $profilo_completo);
        update_user_meta($user_id, 'campi_mancanti', $campi_mancanti);
        
        // Aggiorna anche nella richiesta_socio associata
        $richieste = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richieste)) {
            $richiesta_id = $richieste[0]->ID;
            foreach ($updated_fields as $field) {
                if (isset($params[$field])) {
                    update_post_meta($richiesta_id, $field, $params[$field]);
                }
            }
            update_post_meta($richiesta_id, 'profilo_completo', $profilo_completo);
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => $profilo_completo ? 'Profilo completato con successo!' : 'Profilo aggiornato. Completa i campi rimanenti.',
            'data' => [
                'user_id' => $user_id,
                'updated_fields' => $updated_fields,
                'profilo_completo' => $profilo_completo,
                'campi_mancanti' => $campi_mancanti,
                'percentuale_completamento' => round((1 - (count($campi_mancanti) / count($required_for_complete))) * 100)
            ]
        ]);
    }
    
    /**
     * 17. Upload documento identità (self-service)
     */
    public static function upload_documento_identita($request) {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'Utente non autenticato', ['status' => 401]);
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $files = $request->get_file_params();
        
        if (empty($files['file'])) {
            return new WP_Error('no_file', 'Nessun file caricato', ['status' => 400]);
        }
        
        // Tipo documento (carta_identita, codice_fiscale, altro)
        $tipo_documento = $request->get_param('tipo_documento') ?? 'carta_identita';
        
        // Upload file
        $attachment_id = media_handle_upload('file', 0);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Salva metadata
        update_post_meta($attachment_id, 'documento_socio', 'yes');
        update_post_meta($attachment_id, 'socio_id', $user_id);
        update_post_meta($attachment_id, 'tipo_documento', $tipo_documento);
        update_post_meta($attachment_id, 'data_upload', current_time('mysql'));
        
        // Segna che l'utente ha caricato documenti
        $documenti_caricati = get_user_meta($user_id, 'documenti_caricati', true) ?: [];
        $documenti_caricati[] = [
            'id' => $attachment_id,
            'tipo' => $tipo_documento,
            'data' => current_time('Y-m-d H:i:s')
        ];
        update_user_meta($user_id, 'documenti_caricati', $documenti_caricati);
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Documento caricato con successo',
            'data' => [
                'id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'tipo' => $tipo_documento,
                'filename' => basename(get_attached_file($attachment_id))
            ]
        ]);
    }
    
    /**
     * 18. Verifica se username esiste (debug per app)
     */
    public static function check_username_exists($request) {
        $username = $request->get_param('username');
        
        if (empty($username)) {
            return new WP_Error('missing_username', 'Username richiesto', ['status' => 400]);
        }
        
        // Pulisci username (solo numeri)
        $username_clean = preg_replace('/[^0-9]/', '', $username);
        
        // Cerca utente
        $user = get_user_by('login', $username_clean);
        
        $result = [
            'username_cercato' => $username,
            'username_pulito' => $username_clean,
            'esiste' => (bool) $user
        ];
        
        if ($user) {
            $result['user_id'] = $user->ID;
            $result['user_email'] = $user->user_email;
            $result['display_name'] = $user->display_name;
            $result['telefono_completo'] = get_user_meta($user->ID, 'telefono_completo', true);
            $result['prefix'] = get_user_meta($user->ID, 'prefix', true);
            $result['telefono'] = get_user_meta($user->ID, 'telefono', true);
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * 19. Reset password (recupera password via telefono o email)
     */
    public static function reset_password($request) {
        $telefono = $request->get_param('telefono');
        $email = $request->get_param('email');
        
        if (empty($telefono) && empty($email)) {
            return new WP_Error('missing_info', 'Fornisci telefono o email', ['status' => 400]);
        }
        
        $user = null;
        
        // Cerca per telefono
        if (!empty($telefono)) {
            $telefono_clean = preg_replace('/[^0-9]/', '', $telefono);
            
            global $wpdb;
            $sql = $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} 
                WHERE meta_key = 'telefono_completo' 
                AND meta_value LIKE %s
                LIMIT 1",
                '%' . $telefono_clean
            );
            
            $user_id = $wpdb->get_var($sql);
            if ($user_id) {
                $user = get_user_by('id', $user_id);
            }
        }
        
        // Cerca per email se non trovato
        if (!$user && !empty($email)) {
            $user = get_user_by('email', $email);
        }
        
        if (!$user) {
            return new WP_Error('user_not_found', 'Utente non trovato', ['status' => 404]);
        }
        
        // Genera nuova password memorabile
        $new_password = self::generate_memorable_password();
        
        // Aggiorna password
        wp_set_password($new_password, $user->ID);
        
        // Invia email con nuova password
        if (class_exists('WeCoop_Email_Template_Unified')) {
            $nome = get_user_meta($user->ID, 'nome', true) ?: $user->display_name;
            $numero_tessera = get_user_meta($user->ID, 'numero_tessera', true);
            $tessera_url = home_url('/tessera-socio/?id=' . $numero_tessera);
            
            WeCoop_Email_Template_Unified::send_password_reset(
                $nome,
                $user->user_email,
                $new_password,
                $numero_tessera,
                $tessera_url,
                null,
                $user->user_login
            );
        }
        
        // Log
        error_log('WECOOP: Password reset per utente ' . $user->user_login . ' (ID: ' . $user->ID . ')');
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Password resettata. Controlla la tua email.',
            'email_sent_to' => $user->user_email
        ]);
    }
    
    /**
     * 20. Cambia password (utente autenticato)
     */
    public static function change_password($request) {
        $user_id = get_current_user_id();
        $old_password = $request->get_param('old_password');
        $new_password = $request->get_param('new_password');
        
        if (empty($old_password) || empty($new_password)) {
            return new WP_Error('missing_passwords', 'Vecchia e nuova password richieste', ['status' => 400]);
        }
        
        $user = get_user_by('id', $user_id);
        
        // Verifica vecchia password
        if (!wp_check_password($old_password, $user->user_pass, $user_id)) {
            return new WP_Error('incorrect_password', 'Password attuale non corretta', ['status' => 401]);
        }
        
        // Valida nuova password (minimo 6 caratteri)
        if (strlen($new_password) < 6) {
            return new WP_Error('weak_password', 'La nuova password deve essere almeno 6 caratteri', ['status' => 400]);
        }
        
        // Aggiorna password
        wp_set_password($new_password, $user_id);
        
        // Log
        error_log('WECOOP: Password cambiata per utente ' . $user->user_login . ' (ID: ' . $user_id . ')');
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Password cambiata con successo'
        ]);
    }
    
    /**
     * Valida e normaliza código de país ISO 3166-1 alpha-2
     */
    public static function validate_country_code($value, $request, $param) {
        if (empty($value)) {
            return true; // Campo opcional en algunos contextos
        }
        
        // Normaliza (uppercase, trim)
        $normalized = strtoupper(trim($value));
        
        // Verifica formato (2 lettere)
        if (!preg_match('/^[A-Z]{2}$/', $normalized)) {
            return new WP_Error(
                'invalid_country_code',
                'Codice nazionalità non valido. Usa formato ISO a 2 lettere (es: IT, EC, ES)',
                ['status' => 400]
            );
        }
        
        return true;
    }
    
    /**
     * Genera password memorabile e sicura
     * Formato: Parola-Numero-Parola (es: Sole-2025-Luna)
     */
    public static function generate_memorable_password() {
        // Liste di parole facili da ricordare
        $parole_it = [
            'Sole', 'Luna', 'Mare', 'Cielo', 'Casa', 'Fiore', 'Verde', 'Rosso',
            'Blu', 'Oro', 'Stella', 'Neve', 'Fuoco', 'Acqua', 'Terra', 'Vento',
            'Mela', 'Pera', 'Uva', 'Limone', 'Rosa', 'Gatto', 'Cane', 'Uccello',
            'Piano', 'Forte', 'Dolce', 'Felice', 'Bello', 'Grande', 'Nuovo', 'Vero',
            'Pace', 'Amore', 'Vita', 'Sogno', 'Luce', 'Onda', 'Volo', 'Canto'
        ];
        
        // Seleziona 2 parole casuali
        $parola1 = $parole_it[array_rand($parole_it)];
        $parola2 = $parole_it[array_rand($parole_it)];
        
        // Assicura che siano diverse
        while ($parola1 === $parola2) {
            $parola2 = $parole_it[array_rand($parole_it)];
        }
        
        // Numero casuale (anno corrente o numeri facili)
        $numeri = [
            date('Y'),          // Anno corrente
            wp_rand(100, 999),     // Numero a 3 cifre
            wp_rand(2020, 2030)    // Anno vicino
        ];
        $numero = $numeri[array_rand($numeri)];
        
        // Combina: Parola-Numero-Parola
        $password = $parola1 . $numero . $parola2;
        
        return $password;
    }
}

/**
 * Helper: Conta utenti per meta value
 */
function count_users_by_meta($meta_key, $meta_value) {
    $args = [
        'meta_query' => [[
            'key' => $meta_key,
            'value' => $meta_value
        ]],
        'count_total' => true
    ];
    $user_query = new WP_User_Query($args);
    return $user_query->get_total();
}
