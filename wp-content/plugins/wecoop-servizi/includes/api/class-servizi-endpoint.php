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
        
        // ===== FIRMA DIGITALE - Nuovi Endpoint =====
        
        // POST /documento-unico/{id}/send - Invia documento unico
        register_rest_route('wecoop/v1', '/documento-unico/(?P<id>\d+)/send', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'send_documento_unico'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'id' => ['required' => true, 'type' => 'integer']
            ]
        ]);
        
        // POST /firma-digitale/otp/generate - Genera OTP per firma
        register_rest_route('wecoop/v1', '/firma-digitale/otp/generate', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'generate_otp_firma'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'richiesta_id' => ['required' => true, 'type' => 'integer'],
                'telefono' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
            ]
        ]);
        
        // POST /firma-digitale/otp/verify - Verifica OTP
        register_rest_route('wecoop/v1', '/firma-digitale/otp/verify', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'verify_otp_firma'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'otp_id' => ['required' => true, 'type' => 'integer'],
                'otp_code' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
            ]
        ]);
        
        // POST /firma-digitale/sign - Firma documento con OTP verificato
        register_rest_route('wecoop/v1', '/firma-digitale/sign', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'sign_documento'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'otp_id' => ['required' => true, 'type' => 'integer'],
                'richiesta_id' => ['required' => true, 'type' => 'integer'],
                'documento_contenuto' => ['required' => true, 'type' => 'string'],
                'device_info' => ['type' => 'object'],
                'app_version' => ['type' => 'string'],
                'ip_address' => ['type' => 'string']
            ]
        ]);
        
        // GET /firma-digitale/{richiesta_id}/status - Stato firma documento
        register_rest_route('wecoop/v1', '/firma-digitale/(?P<richiesta_id>\d+)/status', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_firma_status'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'richiesta_id' => ['required' => true, 'type' => 'integer']
            ]
        ]);
        
        // GET /firma-digitale/{firma_id}/verifica - Verifica integrità firma
        register_rest_route('wecoop/v1', '/firma-digitale/(?P<firma_id>\d+)/verifica', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'verify_firma_integrity'],
            'permission_callback' => [__CLASS__, 'check_jwt_permission'],
            'args' => [
                'firma_id' => ['required' => true, 'type' => 'integer']
            ]
        ]);
    }
    
    /**
     * POST /richiesta-servizio - Crea richiesta con documenti allegati
     */
    public static function crea_richiesta_servizio($request) {
        $servizio = $request->get_param('servizio');
        $categoria = $request->get_param('categoria');
        $dati_param = $request->get_param('dati');
        
        error_log("[WECOOP API] 🎉 ========== NUOVA RICHIESTA SERVIZIO ==========");
        error_log("[WECOOP API] 📝 Servizio: {$servizio}, Categoria: {$categoria}");
        
        // Se dati è una stringa JSON, decodifica
        $dati = is_string($dati_param) ? json_decode($dati_param, true) : $dati_param;
        
        $current_user_id = get_current_user_id();
        
        error_log("[WECOOP API] 👤 User ID: {$current_user_id}");
        
        if (!$current_user_id) {
            error_log("[WECOOP API] ❌ Utente non autenticato");
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Utente non autenticato'
            ], 401);
        }
        
        // Recupera socio_id se disponibile
        $socio_id = get_user_meta($current_user_id, 'socio_id', true);
        error_log("[WECOOP API] 🎫 Socio ID: " . ($socio_id ?: 'non impostato'));
        
        // Crea post
        $post_data = [
            'post_type' => 'richiesta_servizio',
            'post_status' => 'publish',
            'post_title' => 'Richiesta - ' . $servizio
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            error_log("[WECOOP API] ❌ Errore creazione post: " . $post_id->get_error_message());
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Errore nella creazione della richiesta'
            ], 500);
        }
        
        error_log("[WECOOP API] ✅ Richiesta creata con ID: {$post_id}");
        
        // Salva metadati
        update_post_meta($post_id, 'servizio', $servizio);
        update_post_meta($post_id, 'categoria', $categoria);
        update_post_meta($post_id, 'dati', json_encode($dati));
        update_post_meta($post_id, 'stato', 'pending');
        update_post_meta($post_id, 'user_id', $current_user_id);
        if ($socio_id) {
            update_post_meta($post_id, 'socio_id', $socio_id);
        }
        
        error_log("[WECOOP API] 💾 Metadati salvati per richiesta #{$post_id}");
        
        // ⭐ NUOVO: Gestione upload documenti
        $documenti_caricati = [];
        $files = $request->get_file_params();
        
        if (!empty($files)) {
            error_log("[WECOOP API] 📤 Richiesta #{$post_id} - Trovati " . count($files) . " file da caricare nel payload");
            
            // Carica WordPress upload handler
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            foreach ($files as $field_name => $file_data) {
                // Solo file con prefisso 'documento_'
                if (strpos($field_name, 'documento_') !== 0) {
                    error_log("[WECOOP API] ⚠️ Campo ignorato (non inizia con 'documento_'): {$field_name}");
                    continue;
                }
                
                // Estrai tipo documento dal nome campo
                // es: documento_permesso_soggiorno → permesso_soggiorno
                $tipo_documento = str_replace('documento_', '', $field_name);
                
                error_log("[WECOOP API] 📎 Elaborazione documento: {$tipo_documento} - {$file_data['name']}");
                
                // Validazione tipo file
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!in_array($file_data['type'], $allowed_types)) {
                    error_log("[WECOOP API] ⚠️ Tipo file non consentito: {$file_data['type']} per {$tipo_documento}");
                    continue;
                }
                
                // Validazione dimensione (max 10MB)
                $max_size = 10 * 1024 * 1024; // 10MB
                if ($file_data['size'] > $max_size) {
                    error_log("[WECOOP API] ⚠️ File troppo grande: " . round($file_data['size'] / 1024 / 1024, 2) . "MB per {$tipo_documento}");
                    continue;
                }
                
                // Validazione errori upload
                if ($file_data['error'] !== UPLOAD_ERR_OK) {
                    error_log("[WECOOP API] ⚠️ Errore upload (code {$file_data['error']}): {$tipo_documento}");
                    continue;
                }
                
                // Upload file alla Media Library
                // Prepara $_FILES per media_handle_upload
                $_FILES['upload_file'] = $file_data;
                
                $attachment_id = media_handle_upload(
                    'upload_file',
                    $post_id,
                    [],
                    [
                        'test_form' => false,
                        'test_type' => false,
                    ]
                );
                
                if (is_wp_error($attachment_id)) {
                    error_log("[WECOOP API] ❌ Errore upload documento {$tipo_documento}: " . $attachment_id->get_error_message());
                    continue;
                }
                
                // Salva metadati documento
                update_post_meta($attachment_id, 'tipo_documento', $tipo_documento);
                update_post_meta($attachment_id, 'richiesta_id', $post_id);
                
                // Recupera data scadenza se presente
                $scadenza_field = "scadenza_$tipo_documento";
                $data_scadenza = $request->get_param($scadenza_field);
                if ($data_scadenza) {
                    update_post_meta($attachment_id, 'data_scadenza', $data_scadenza);
                }
                
                $file_url = wp_get_attachment_url($attachment_id);
                
                $documenti_caricati[] = [
                    'tipo' => $tipo_documento,
                    'attachment_id' => $attachment_id,
                    'file_name' => basename($file_data['name']),
                    'url' => $file_url,
                    'data_scadenza' => $data_scadenza,
                ];
                
                error_log("[WECOOP API] ✅ Caricato documento: {$tipo_documento} (ID: {$attachment_id}) - {$file_url}");
            }
            
            // Salva riferimenti documenti nella richiesta
            if (!empty($documenti_caricati)) {
                update_post_meta($post_id, 'documenti_allegati', $documenti_caricati);
                error_log("[WECOOP API] 📦 Totale documenti caricati: " . count($documenti_caricati));
            }
        }
        
        // ⭐ RECUPERA documenti già caricati dall'utente se non inviati con la richiesta
        if (empty($documenti_caricati)) {
            error_log("[WECOOP API] 🔍 AUTO-RECOVERY: Nessun documento nel payload, cerco documenti esistenti per user {$current_user_id}");
            
            // Recupera documenti dal profilo utente
            $documenti_utente = get_posts([
                'post_type' => 'attachment',
                'author' => $current_user_id,
                'posts_per_page' => -1,
                'meta_query' => [[
                    'key' => 'documento_socio',
                    'value' => 'yes'
                ]]
            ]);
            
            if (!empty($documenti_utente)) {
                error_log("[WECOOP API] ✅ AUTO-RECOVERY: Trovati " . count($documenti_utente) . " documenti nel profilo utente");
                
                foreach ($documenti_utente as $doc) {
                    $attachment_id = $doc->ID;
                    $tipo_documento = get_post_meta($attachment_id, 'tipo_documento', true);
                    $data_scadenza = get_post_meta($attachment_id, 'data_scadenza', true);
                    $file_name = basename(get_attached_file($attachment_id));
                    $file_url = wp_get_attachment_url($attachment_id);
                    
                    error_log("[WECOOP API] 📄 AUTO-RECOVERY: Documento #{$attachment_id} - Tipo: {$tipo_documento}, File: {$file_name}");
                    
                    // Associa documento alla richiesta
                    update_post_meta($attachment_id, 'richiesta_id', $post_id);
                    error_log("[WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id={$post_id} per attachment #{$attachment_id}");
                    
                    $documenti_caricati[] = [
                        'tipo' => $tipo_documento ?: 'altro',
                        'attachment_id' => $attachment_id,
                        'file_name' => $file_name,
                        'url' => $file_url,
                        'data_scadenza' => $data_scadenza,
                    ];
                    
                    error_log("[WECOOP API] ✅ AUTO-RECOVERY: Collegato documento {$tipo_documento} (ID: {$attachment_id}) alla richiesta #{$post_id}");
                }
                
                // Salva riferimenti documenti nella richiesta
                update_post_meta($post_id, 'documenti_allegati', $documenti_caricati);
                error_log("[WECOOP API] 📦 AUTO-RECOVERY: Salvato meta 'documenti_allegati' con " . count($documenti_caricati) . " documenti per richiesta #{$post_id}");
                error_log("[WECOOP API] 🎉 AUTO-RECOVERY: Totale documenti collegati dal profilo: " . count($documenti_caricati));
            } else {
                error_log("[WECOOP API] ⚠️ AUTO-RECOVERY: Nessun documento trovato nel profilo utente {$current_user_id}");
                error_log("[WECOOP API] 💡 AUTO-RECOVERY: L'utente deve caricare documenti via /soci/me/upload-documento prima di creare richieste");
            }
        } else {
            error_log("[WECOOP API] ✅ Documenti già presenti nel payload, skip auto-recovery");
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
        
        error_log("[WECOOP API] ========== RIEPILOGO RICHIESTA #{$post_id} ==========");
        error_log("[WECOOP API] 📋 Numero Pratica: {$numero_pratica}");
        error_log("[WECOOP API] 🎫 Servizio: {$servizio}");
        error_log("[WECOOP API] 👤 User ID: {$current_user_id}");
        error_log("[WECOOP API] 📎 Documenti collegati: " . count($documenti_caricati));
        error_log("[WECOOP API] 💰 Importo suggerito: " . ($importo ? "€{$importo}" : "Non definito"));
        error_log("[WECOOP API] ✅ Richiesta creata con successo!");
        error_log("[WECOOP API] ================================================");
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Richiesta ricevuta con successo',
            'id' => $post_id,
            'numero_pratica' => $numero_pratica,
            'data_richiesta' => get_the_date('Y-m-d H:i:s', $post_id),
            'documenti_caricati' => $documenti_caricati, // ⭐ NUOVO: Lista documenti allegati
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
                        'id' => $payment ? $payment->id : null,
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
    
    /**
     * POST /documento-unico/{id}/send - Invia documento unico
     * Legge il file documento_unico.txt, lo compila e lo converte in PDF
     */
    public static function send_documento_unico($request) {
        $richiesta_id = intval($request['id']);
        $current_user = wp_get_current_user();
        
        // Verifica richiesta e ownership
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            return new WP_Error('invalid_request', 'Richiesta non valida', ['status' => 404]);
        }
        
        $richiesta_user_id = get_post_meta($richiesta_id, 'user_id', true);
        if ($richiesta_user_id != $current_user->ID && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non hai i permessi', ['status' => 403]);
        }
        
        // Verifica che sia pagata
        $payment_status = get_post_meta($richiesta_id, 'payment_status', true);
        if ($payment_status !== 'paid') {
            return new WP_Error('not_paid', 'La richiesta non è stata pagata', ['status' => 400]);
        }
        
        if (!class_exists('WECOOP_Documento_Unico_PDF')) {
            return new WP_Error('pdf_handler_not_found', 'Modulo PDF non disponibile', ['status' => 500]);
        }
        
        // Genera PDF
        $result = WECOOP_Documento_Unico_PDF::generate_documento_unico($richiesta_id, $current_user->ID);
        
        if (!$result['success']) {
            return new WP_Error('pdf_generation_failed', $result['message'], ['status' => 500]);
        }
        
        error_log("[WECOOP API] ✅ Documento unico PDF inviato per richiesta #{$richiesta_id}");
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Documento unico generato in PDF',
            'richiesta_id' => $richiesta_id,
            'documento' => $result['documento']
        ], 200);
    }
    
    /**
     * POST /firma-digitale/otp/generate - Genera OTP
     */
    public static function generate_otp_firma($request) {
        $richiesta_id = intval($request->get_param('richiesta_id'));
        $telefono = $request->get_param('telefono');
        $current_user_id = get_current_user_id();
        
        if (!class_exists('WECOOP_OTP_Handler')) {
            return new WP_Error('otp_handler_not_found', 'Modulo OTP non disponibile', ['status' => 500]);
        }
        
        $result = WECOOP_OTP_Handler::generate_otp($richiesta_id, $current_user_id, $telefono);
        
        if (!$result['success']) {
            return new WP_Error('otp_generation_failed', $result['message'], ['status' => 400]);
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * POST /firma-digitale/otp/verify - Verifica OTP
     */
    public static function verify_otp_firma($request) {
        $otp_id = intval($request->get_param('otp_id'));
        $otp_code = sanitize_text_field($request->get_param('otp_code'));
        
        if (!class_exists('WECOOP_OTP_Handler')) {
            return new WP_Error('otp_handler_not_found', 'Modulo OTP non disponibile', ['status' => 500]);
        }
        
        $result = WECOOP_OTP_Handler::verify_otp($otp_id, $otp_code);
        
        if (!$result['success']) {
            return new WP_Error('otp_verification_failed', $result['message'], ['status' => 400]);
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * POST /firma-digitale/sign - Firma documento
     */
    public static function sign_documento($request) {
        $otp_id = intval($request->get_param('otp_id'));
        $richiesta_id = intval($request->get_param('richiesta_id'));
        $documento_contenuto = $request->get_param('documento_contenuto');
        $device_info = $request->get_param('device_info');
        $app_version = $request->get_param('app_version');
        $ip_address = $request->get_param('ip_address');
        
        if (!class_exists('WECOOP_Firma_Handler')) {
            return new WP_Error('firma_handler_not_found', 'Modulo firma non disponibile', ['status' => 500]);
        }
        
        $firma_data = [
            'device_info' => $device_info,
            'app_version' => $app_version,
            'ip_address' => $ip_address
        ];
        
        $result = WECOOP_Firma_Handler::sign_document($otp_id, $richiesta_id, $documento_contenuto, $firma_data);
        
        if (!$result['success']) {
            $error_code = !empty($result['code']) ? $result['code'] : 'signing_failed';
            $error_status = !empty($result['status']) ? intval($result['status']) : 400;

            $error_data = ['status' => $error_status];
            foreach (['firma_id', 'firma_timestamp', 'firma_hash'] as $field) {
                if (isset($result[$field])) {
                    $error_data[$field] = $result[$field];
                }
            }

            return new WP_Error($error_code, $result['message'], $error_data);
        }
        
        return new WP_REST_Response($result, 200);
    }
    
    /**
     * GET /firma-digitale/{richiesta_id}/status - Stato firma
     */
    public static function get_firma_status($request) {
        $richiesta_id = intval($request['richiesta_id']);
        $current_user_id = get_current_user_id();
        $documento_url = self::resolve_documento_unico_url($richiesta_id);
        
        // Verifica ownership
        $richiesta_user_id = get_post_meta($richiesta_id, 'user_id', true);
        if ($richiesta_user_id != $current_user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non hai i permessi', ['status' => 403]);
        }
        
        if (!class_exists('WECOOP_Firma_Handler')) {
            return new WP_Error('firma_handler_not_found', 'Modulo firma non disponibile', ['status' => 500]);
        }
        
        $firma = WECOOP_Firma_Handler::get_firma($richiesta_id);
        
        if (!$firma) {
            return new WP_REST_Response([
                'firmato' => false,
                'message' => 'Documento non ancora firmato',
                'richiesta_id' => $richiesta_id,
                'documento_url' => $documento_url,
                'documento_download_url' => $documento_url
            ], 200);
        }
        
        return new WP_REST_Response([
            'firmato' => true,
            'firma_id' => $firma->id,
            'firma_timestamp' => $firma->firma_timestamp,
            'firma_hash' => $firma->firma_hash,
            'firma_tipo' => $firma->firma_tipo,
            'documento_hash_sha256' => $firma->documento_hash,
            'documento_url' => $documento_url,
            'documento_download_url' => $documento_url,
            'metadata' => json_decode($firma->firma_metadata, true),
            'richiesta_id' => $richiesta_id
        ], 200);
    }
    
    /**
     * GET /firma-digitale/{firma_id}/verifica - Verifica firma
     */
    public static function verify_firma_integrity($request) {
        $firma_id = intval($request['firma_id']);
        
        if (!class_exists('WECOOP_Firma_Handler')) {
            return new WP_Error('firma_handler_not_found', 'Modulo firma non disponibile', ['status' => 500]);
        }
        
        $result = WECOOP_Firma_Handler::verify_signature($firma_id);
        
        return new WP_REST_Response($result, 200);
    }

    /**
     * Recupera URL documento unico con fallback al file più recente su disco.
     */
    private static function resolve_documento_unico_url($richiesta_id) {
        $richiesta_id = absint($richiesta_id);
        if (!$richiesta_id) {
            return null;
        }

        $stored_url = trim((string) get_post_meta($richiesta_id, 'documento_unico_url', true));
        if (!empty($stored_url)) {
            return $stored_url;
        }

        $uploads = wp_get_upload_dir();
        if (empty($uploads['basedir']) || empty($uploads['baseurl'])) {
            return null;
        }

        $directory = trailingslashit($uploads['basedir']) . 'wecoop-documenti-unici/';
        if (!is_dir($directory)) {
            return null;
        }

        $files = glob($directory . 'Documento_Unico_' . $richiesta_id . '_*.pdf');
        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $latest_file = wp_normalize_path($files[0]);
        $basedir = wp_normalize_path($uploads['basedir']);
        if (strpos($latest_file, $basedir) !== 0) {
            return null;
        }

        $relative = ltrim(substr($latest_file, strlen($basedir)), '/');
        $resolved_url = trailingslashit($uploads['baseurl']) . str_replace(' ', '%20', $relative);

        update_post_meta($richiesta_id, 'documento_unico_url', $resolved_url);
        return $resolved_url;
    }
}
