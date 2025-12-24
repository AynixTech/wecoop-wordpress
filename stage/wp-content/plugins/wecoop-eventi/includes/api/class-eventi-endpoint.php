<?php
/**
 * REST API Endpoint: Gestione Eventi
 * 
 * @package WECOOP_Eventi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Eventi_Endpoint {
    
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
    
    public static function register_routes() {
        // GET /eventi - Lista eventi con filtri
        register_rest_route('wecoop/v1', '/eventi', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_eventi'],
            'permission_callback' => '__return_true',
            'args' => [
                'lang' => ['type' => 'string', 'default' => 'it'],
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'data_da' => ['type' => 'string'],
                'data_a' => ['type' => 'string'],
                'categoria' => ['type' => 'string'],
                'stato' => ['type' => 'string']
            ]
        ]);
        
        // GET /eventi/{id} - Dettaglio evento
        register_rest_route('wecoop/v1', '/eventi/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_evento_dettaglio'],
            'permission_callback' => '__return_true',
            'args' => [
                'lang' => ['type' => 'string', 'default' => 'it']
            ]
        ]);
        
        // POST /eventi/{id}/iscrizione - Iscrizione evento
        register_rest_route('wecoop/v1', '/eventi/(?P<id>\d+)/iscrizione', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'iscrivi_evento'],
            'permission_callback' => 'is_user_logged_in'
        ]);
        
        // DELETE /eventi/{id}/iscrizione - Cancella iscrizione
        register_rest_route('wecoop/v1', '/eventi/(?P<id>\d+)/iscrizione', [
            'methods' => 'DELETE',
            'callback' => [__CLASS__, 'cancella_iscrizione'],
            'permission_callback' => 'is_user_logged_in'
        ]);
        
        // GET /miei-eventi - Eventi dell'utente
        register_rest_route('wecoop/v1', '/miei-eventi', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_miei_eventi'],
            'permission_callback' => 'is_user_logged_in',
            'args' => [
                'lang' => ['type' => 'string', 'default' => 'it']
            ]
        ]);
        
        // GET /eventi/debug - Debug campi evento
        register_rest_route('wecoop/v1', '/eventi/debug', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'debug_eventi_fields'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * GET /eventi/debug - Restituisce tutti i campi meta di tutti gli eventi
     */
    public static function debug_eventi_fields() {
        $args = [
            'post_type' => 'evento',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ];
        
        $query = new WP_Query($args);
        $eventi = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Ottieni TUTTI i meta fields
                $all_meta = get_post_meta($post_id);
                $meta_clean = [];
                
                foreach ($all_meta as $key => $value) {
                    // Rimuovi meta interni WordPress
                    if (substr($key, 0, 1) !== '_') {
                        $meta_clean[$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
                    }
                }
                
                // Ottieni categoria
                $categorie = get_the_terms($post_id, 'categoria_evento');
                
                // Ottieni thumbnail
                $thumbnail_id = get_post_thumbnail_id($post_id);
                $thumbnail_url = '';
                if ($thumbnail_id) {
                    $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large') ?: '';
                }
                
                $eventi[] = [
                    'id' => $post_id,
                    'post_title' => get_the_title(),
                    'post_name' => get_post($post_id)->post_name,
                    'post_content' => get_the_content(),
                    'post_excerpt' => get_the_excerpt(),
                    'post_date' => get_the_date('Y-m-d H:i:s'),
                    'post_status' => get_post_status(),
                    'thumbnail_id' => $thumbnail_id,
                    'thumbnail_url' => $thumbnail_url,
                    'categoria_terms' => $categorie && !is_wp_error($categorie) ? array_map(function($t) {
                        return ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug];
                    }, $categorie) : [],
                    'meta_fields' => $meta_clean,
                    'meta_fields_count' => count($meta_clean)
                ];
            }
            wp_reset_postdata();
        }
        
        return new WP_REST_Response([
            'success' => true,
            'total_eventi' => count($eventi),
            'eventi' => $eventi,
            'campo_list' => array_unique(array_merge(...array_map(function($e) {
                return array_keys($e['meta_fields']);
            }, $eventi)))
        ], 200);
    }
    
    /**
     * GET /eventi - Lista eventi con filtri e paginazione
     */
    public static function get_eventi($request) {
        $lang = $request->get_param('lang') ?? 'it';
        $per_page = min((int)($request->get_param('per_page') ?? 10), 50); // Max 50
        $page = (int)($request->get_param('page') ?? 1);
        $data_da = $request->get_param('data_da');
        $data_a = $request->get_param('data_a');
        $categoria = $request->get_param('categoria');
        $stato = $request->get_param('stato');
        
        $args = [
            'post_type' => 'evento',
            'post_status' => ['publish', 'future'],
            'posts_per_page' => $per_page,
            'paged' => $page,
            'suppress_filters' => false
        ];
        
        // Meta query per filtri
        $meta_query = [];
        
        // Se stato=futuro, mostra solo eventi futuri
        if ($stato === 'futuro') {
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'data_inizio';
            $args['order'] = 'ASC';
            
            $meta_query[] = [
                'key' => 'data_inizio',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ];
        } elseif ($stato === 'passato') {
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'data_inizio';
            $args['order'] = 'DESC';
            
            $meta_query[] = [
                'key' => 'data_inizio',
                'value' => date('Y-m-d'),
                'compare' => '<',
                'type' => 'DATE'
            ];
        } else {
            // Ordinamento predefinito per data di pubblicazione
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            
            if ($stato) {
                // Filtra per stato meta (attivo, annullato, etc)
                $meta_query[] = [
                    'key' => 'stato',
                    'value' => $stato,
                    'compare' => '='
                ];
            }
        }
        
        if ($data_da) {
            $meta_query[] = [
                'key' => 'data_inizio',
                'value' => $data_da,
                'compare' => '>=',
                'type' => 'DATE'
            ];
        }
        
        if ($data_a) {
            $meta_query[] = [
                'key' => 'data_inizio',
                'value' => $data_a,
                'compare' => '<=',
                'type' => 'DATE'
            ];
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Tax query per categoria
        if ($categoria) {
            $args['tax_query'] = [[
                'taxonomy' => 'categoria_evento',
                'field' => 'slug',
                'terms' => $categoria
            ]];
        }
        
        $query = new WP_Query($args);
        $eventi = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Calcola iscrizione utente
                $user_id = get_current_user_id();
                $iscritti = get_post_meta($post_id, 'iscritti', true) ?: [];
                $is_iscritto = $user_id ? in_array($user_id, $iscritti) : false;
                
                // Ottieni immagine con fallback multipli
                $immagine = '';
                $thumbnail_id = get_post_thumbnail_id($post_id);
                if ($thumbnail_id) {
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
                    if ($image_url) {
                        $immagine = $image_url;
                    }
                }
                
                // Ottieni categoria
                $categorie = get_the_terms($post_id, 'categoria_evento');
                $categoria_nome = $categorie && !is_wp_error($categorie) ? $categorie[0]->name : '';
                
                // Ottieni titolo e descrizione con fallback
                $titolo_lang = get_post_meta($post_id, 'titolo_' . $lang, true);
                $descrizione_lang = get_post_meta($post_id, 'descrizione_' . $lang, true);
                
                $posti_disponibili = (int)get_post_meta($post_id, 'posti_disponibili', true);
                $num_iscritti = count($iscritti);
                
                $eventi[] = [
                    'id' => $post_id,
                    'titolo' => $titolo_lang ?: get_the_title(),
                    'slug' => $query->post->post_name,
                    'descrizione' => $descrizione_lang ?: get_the_excerpt(),
                    'luogo' => get_post_meta($post_id, 'luogo', true) ?: '',
                    'indirizzo' => get_post_meta($post_id, 'indirizzo', true) ?: '',
                    'citta' => get_post_meta($post_id, 'citta', true) ?: '',
                    'data_inizio' => get_post_meta($post_id, 'data_inizio', true) ?: '',
                    'ora_inizio' => get_post_meta($post_id, 'ora_inizio', true) ?: '',
                    'data_fine' => get_post_meta($post_id, 'data_fine', true),
                    'ora_fine' => get_post_meta($post_id, 'ora_fine', true),
                    'immagine_copertina' => $immagine ?: null,
                    'stato' => get_post_meta($post_id, 'stato', true) ?: 'attivo',
                    'categoria' => $categoria_nome ?: null,
                    'sono_iscritto' => $is_iscritto,
                    'max_partecipanti' => $posti_disponibili,
                    'posti_disponibili' => max(0, $posti_disponibili - $num_iscritti),
                    'partecipanti_count' => $num_iscritti,
                    'richiede_iscrizione' => (bool)get_post_meta($post_id, 'richiede_iscrizione', true),
                    'online' => (bool)get_post_meta($post_id, 'evento_online', true),
                    'link_online' => get_post_meta($post_id, 'link_online', true) ?: null,
                    'prezzo' => (float)get_post_meta($post_id, 'prezzo', true),
                    'prezzo_formattato' => '€ ' . number_format((float)get_post_meta($post_id, 'prezzo', true), 2, ',', '.'),
                    'organizzatore' => get_post_meta($post_id, 'organizzatore', true) ?: null,
                    'email_organizzatore' => get_post_meta($post_id, 'email_organizzatore', true) ?: null,
                    'telefono_organizzatore' => get_post_meta($post_id, 'telefono_organizzatore', true) ?: null,
                    'programma' => get_post_meta($post_id, 'programma', true) ?: null,
                    'galleria' => []
                ];
            }
            wp_reset_postdata();
        }
        
        return new WP_REST_Response([
            'success' => true,
            'eventi' => $eventi,
            'pagination' => [
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages,
                'current_page' => $page,
                'per_page' => $per_page
            ]
        ], 200);
    }
    
    /**
     * GET /eventi/{id} - Dettaglio evento
     */
    public static function get_evento_dettaglio($request) {
        $evento_id = $request->get_param('id');
        $lang = $request->get_param('lang') ?? 'it';
        
        $evento = get_post($evento_id);
        
        if (!$evento || $evento->post_type !== 'evento') {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Evento non trovato'
            ], 404);
        }
        
        // Calcola iscrizione utente
        $user_id = get_current_user_id();
        $iscritti = get_post_meta($evento->ID, 'iscritti', true) ?: [];
        $is_iscritto = $user_id ? in_array($user_id, $iscritti) : false;
        
        // Ottieni immagine con fallback
        $immagine = '';
        $thumbnail_id = get_post_thumbnail_id($evento->ID);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            if ($image_url) {
                $immagine = $image_url;
            }
        }
        
        // Ottieni categoria
        $categorie = get_the_terms($evento->ID, 'categoria_evento');
        $categoria_nome = $categorie && !is_wp_error($categorie) ? $categorie[0]->name : '';
        
        $posti_disponibili = (int)get_post_meta($evento->ID, 'posti_disponibili', true);
        $num_iscritti = count($iscritti);
        
        // Restituisce direttamente l'oggetto evento
        return new WP_REST_Response([
            'id' => $evento->ID,
            'titolo' => get_post_meta($evento->ID, 'titolo_' . $lang, true) ?: $evento->post_title,
            'slug' => $evento->post_name,
            'descrizione' => get_post_meta($evento->ID, 'descrizione_' . $lang, true) ?: $evento->post_content,
            'luogo' => get_post_meta($evento->ID, 'luogo', true) ?: '',
            'indirizzo' => get_post_meta($evento->ID, 'indirizzo', true) ?: '',
            'citta' => get_post_meta($evento->ID, 'citta', true) ?: '',
            'data_inizio' => get_post_meta($evento->ID, 'data_inizio', true) ?: '',
            'ora_inizio' => get_post_meta($evento->ID, 'ora_inizio', true) ?: '',
            'data_fine' => get_post_meta($evento->ID, 'data_fine', true),
            'ora_fine' => get_post_meta($evento->ID, 'ora_fine', true),
            'immagine_copertina' => $immagine ?: null,
            'stato' => get_post_meta($evento->ID, 'stato', true) ?: 'attivo',
            'categoria' => $categoria_nome ?: null,
            'sono_iscritto' => $is_iscritto,
            'max_partecipanti' => $posti_disponibili,
            'posti_disponibili' => max(0, $posti_disponibili - $num_iscritti),
            'partecipanti_count' => $num_iscritti,
            'richiede_iscrizione' => (bool)get_post_meta($evento->ID, 'richiede_iscrizione', true),
            'online' => (bool)get_post_meta($evento->ID, 'evento_online', true),
            'link_online' => get_post_meta($evento->ID, 'link_online', true) ?: null,
            'prezzo' => (float)get_post_meta($evento->ID, 'prezzo', true),
            'prezzo_formattato' => '€ ' . number_format((float)get_post_meta($evento->ID, 'prezzo', true), 2, ',', '.'),
            'organizzatore' => get_post_meta($evento->ID, 'organizzatore', true) ?: null,
            'email_organizzatore' => get_post_meta($evento->ID, 'email_organizzatore', true) ?: null,
            'telefono_organizzatore' => get_post_meta($evento->ID, 'telefono_organizzatore', true) ?: null,
            'programma' => get_post_meta($evento->ID, 'programma', true) ?: null,
            'galleria' => [],
            'data_pubblicazione' => $evento->post_date
        ], 200);
    }
    
    /**
     * POST /eventi/{id}/iscrizione - Iscrizione evento
     */
    public static function iscrivi_evento($request) {
        $evento_id = $request->get_param('id');
        $user_id = get_current_user_id();
        $params = $request->get_json_params() ?: [];
        
        $evento = get_post($evento_id);
        if (!$evento || $evento->post_type !== 'evento') {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Evento non trovato'
            ], 404);
        }
        
        // Verifica posti disponibili
        $posti = (int)get_post_meta($evento_id, 'posti_disponibili', true);
        $iscritti = get_post_meta($evento_id, 'iscritti', true) ?: [];
        
        if ($posti > 0 && count($iscritti) >= $posti) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Posti esauriti'
            ], 400);
        }
        
        // Verifica se già iscritto
        if (in_array($user_id, $iscritti)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Sei già iscritto a questo evento'
            ], 400);
        }
        
        // Aggiungi iscrizione
        $iscritti[] = $user_id;
        update_post_meta($evento_id, 'iscritti', $iscritti);
        
        // Salva dettagli partecipante
        $current_user = wp_get_current_user();
        $partecipante = [
            'user_id' => $user_id,
            'nome' => $params['nome'] ?? get_user_meta($user_id, 'nome', true),
            'email' => $params['email'] ?? $current_user->user_email,
            'telefono' => $params['telefono'] ?? get_user_meta($user_id, 'telefono', true),
            'note' => $params['note'] ?? '',
            'data_iscrizione' => current_time('mysql')
        ];
        
        $partecipanti = get_post_meta($evento_id, 'partecipanti', true) ?: [];
        $partecipanti[$user_id] = $partecipante;
        update_post_meta($evento_id, 'partecipanti', $partecipanti);
        
        // Invia email conferma iscrizione multilingua
        if (class_exists('WeCoop_Multilingual_Email')) {
            $data_evento = get_post_meta($evento_id, 'data_evento', true);
            $luogo = get_post_meta($evento_id, 'luogo', true);
            
            WeCoop_Multilingual_Email::send(
                $partecipante['email'],
                'event_registered',
                [
                    'nome' => $partecipante['nome'],
                    'evento' => $evento->post_title,
                    'data' => $data_evento ? date('d/m/Y H:i', strtotime($data_evento)) : '',
                    'luogo' => $luogo,
                    'button_url' => get_permalink($evento_id)
                ],
                $user_id,
                $request
            );
            error_log("WECOOP Eventi: Email iscrizione inviata a {$partecipante['email']}");
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Iscrizione completata con successo',
            'iscrizione' => $partecipante
        ], 200);
    }
    
    /**
     * DELETE /eventi/{id}/iscrizione - Cancella iscrizione
     */
    public static function cancella_iscrizione($request) {
        $evento_id = $request->get_param('id');
        $user_id = get_current_user_id();
        
        $evento = get_post($evento_id);
        if (!$evento || $evento->post_type !== 'evento') {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Evento non trovato'
            ], 404);
        }
        
        // Verifica se iscritto
        $iscritti = get_post_meta($evento_id, 'iscritti', true) ?: [];
        $key = array_search($user_id, $iscritti);
        
        if ($key === false) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Non sei iscritto a questo evento'
            ], 400);
        }
        
        // Rimuovi iscrizione
        unset($iscritti[$key]);
        update_post_meta($evento_id, 'iscritti', array_values($iscritti));
        
        // Rimuovi dettagli partecipante
        $partecipanti = get_post_meta($evento_id, 'partecipanti', true) ?: [];
        $partecipante_rimosso = $partecipanti[$user_id] ?? null;
        unset($partecipanti[$user_id]);
        update_post_meta($evento_id, 'partecipanti', $partecipanti);
        
        // Invia email cancellazione multilingua
        if ($partecipante_rimosso && class_exists('WeCoop_Multilingual_Email')) {
            WeCoop_Multilingual_Email::send(
                $partecipante_rimosso['email'],
                'event_unregistered',
                [
                    'nome' => $partecipante_rimosso['nome'],
                    'evento' => $evento->post_title
                ],
                $user_id,
                $request
            );
            error_log("WECOOP Eventi: Email cancellazione inviata a {$partecipante_rimosso['email']}");
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Iscrizione cancellata con successo'
        ], 200);
    }
    
    /**
     * GET /miei-eventi - Eventi dell'utente
     */
    public static function get_miei_eventi($request) {
        error_log('[MIEI-EVENTI] ========== INIZIO GET MIEI EVENTI ==========');
        
        try {
            $user_id = get_current_user_id();
            error_log('[MIEI-EVENTI] User ID: ' . $user_id);
            
            if (!$user_id) {
                error_log('[MIEI-EVENTI] ERRORE: Utente non autenticato');
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Utente non autenticato'
                ], 401);
            }
            
            $lang = $request->get_param('lang') ?? 'it';
            error_log('[MIEI-EVENTI] Lingua richiesta: ' . $lang);
            
            $args = [
                'post_type' => 'evento',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'meta_value',
                'meta_key' => 'data_inizio',
                'order' => 'ASC',
                'meta_query' => [[
                    'key' => 'iscritti',
                    'value' => serialize(strval($user_id)),
                    'compare' => 'LIKE'
                ]]
            ];
            error_log('[MIEI-EVENTI] Query args: ' . print_r($args, true));
            error_log('[MIEI-EVENTI] Query args: ' . print_r($args, true));
            
            $query = new WP_Query($args);
            error_log('[MIEI-EVENTI] Query completata. Post trovati: ' . $query->found_posts);
            
            $eventi = [];
            
            if ($query->have_posts()) {
                error_log('[MIEI-EVENTI] Inizio elaborazione ' . $query->post_count . ' eventi');
                
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    error_log('[MIEI-EVENTI] Elaborazione evento ID: ' . $post_id);
                    
                    try {
                        $iscritti = get_post_meta($post_id, 'iscritti', true) ?: [];
                        $immagine = '';
                        $thumbnail_id = get_post_thumbnail_id($post_id);
                        if ($thumbnail_id) {
                            $image_url = wp_get_attachment_image_url($thumbnail_id, 'large');
                            if ($image_url) {
                                $immagine = $image_url;
                            }
                        }
                        
                        $categorie = get_the_terms($post_id, 'categoria_evento');
                        $categoria_nome = $categorie && !is_wp_error($categorie) ? $categorie[0]->name : '';
                        
                        $posti_disponibili = (int)get_post_meta($post_id, 'posti_disponibili', true);
                        $num_iscritti = count($iscritti);
                        
                        $eventi[] = [
                            'id' => $post_id,
                            'titolo' => get_post_meta($post_id, 'titolo_' . $lang, true) ?: get_the_title(),
                            'slug' => get_post($post_id)->post_name,
                            'descrizione' => get_post_meta($post_id, 'descrizione_' . $lang, true) ?: get_the_excerpt(),
                            'luogo' => get_post_meta($post_id, 'luogo', true) ?: '',
                            'indirizzo' => get_post_meta($post_id, 'indirizzo', true) ?: '',
                            'citta' => get_post_meta($post_id, 'citta', true) ?: '',
                            'data_inizio' => get_post_meta($post_id, 'data_inizio', true) ?: '',
                            'ora_inizio' => get_post_meta($post_id, 'ora_inizio', true) ?: '',
                            'data_fine' => get_post_meta($post_id, 'data_fine', true),
                            'ora_fine' => get_post_meta($post_id, 'ora_fine', true),
                            'immagine_copertina' => $immagine ?: null,
                            'stato' => get_post_meta($post_id, 'stato', true) ?: 'attivo',
                            'categoria' => $categoria_nome ?: null,
                            'sono_iscritto' => true,
                            'max_partecipanti' => $posti_disponibili,
                            'posti_disponibili' => max(0, $posti_disponibili - $num_iscritti),
                            'partecipanti_count' => $num_iscritti,
                            'richiede_iscrizione' => (bool)get_post_meta($post_id, 'richiede_iscrizione', true),
                            'online' => (bool)get_post_meta($post_id, 'evento_online', true),
                            'link_online' => get_post_meta($post_id, 'link_online', true) ?: null,
                            'prezzo' => (float)get_post_meta($post_id, 'prezzo', true),
                            'prezzo_formattato' => '€ ' . number_format((float)get_post_meta($post_id, 'prezzo', true), 2, ',', '.'),
                            'organizzatore' => get_post_meta($post_id, 'organizzatore', true) ?: null,
                            'email_organizzatore' => get_post_meta($post_id, 'email_organizzatore', true) ?: null,
                            'telefono_organizzatore' => get_post_meta($post_id, 'telefono_organizzatore', true) ?: null,
                            'programma' => get_post_meta($post_id, 'programma', true) ?: null,
                            'galleria' => []
                        ];
                        
                        error_log('[MIEI-EVENTI] Evento ID ' . $post_id . ' elaborato con successo');
                        
                    } catch (Exception $e) {
                        error_log('[MIEI-EVENTI] ERRORE durante elaborazione evento ID ' . $post_id . ': ' . $e->getMessage());
                        error_log('[MIEI-EVENTI] Stack trace: ' . $e->getTraceAsString());
                    }
                }
                wp_reset_postdata();
                error_log('[MIEI-EVENTI] Fine elaborazione eventi. Totale: ' . count($eventi));
            } else {
                error_log('[MIEI-EVENTI] Nessun evento trovato per user_id: ' . $user_id);
            }
            
            error_log('[MIEI-EVENTI] ========== FINE GET MIEI EVENTI - SUCCESS ==========');
            
            return new WP_REST_Response([
                'success' => true,
                'eventi' => $eventi,
                'totale' => count($eventi)
            ], 200);
            
        } catch (Exception $e) {
            error_log('[MIEI-EVENTI] ========== ERRORE CRITICO ==========');
            error_log('[MIEI-EVENTI] Messaggio: ' . $e->getMessage());
            error_log('[MIEI-EVENTI] File: ' . $e->getFile() . ' Linea: ' . $e->getLine());
            error_log('[MIEI-EVENTI] Stack trace: ' . $e->getTraceAsString());
            
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Errore interno del server',
                'error' => WP_DEBUG ? $e->getMessage() : null
            ], 500);
        }
    }
}
