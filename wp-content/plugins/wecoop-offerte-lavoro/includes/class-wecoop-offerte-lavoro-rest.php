<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Offerte_Lavoro_REST {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('wecoop/v1', '/lavoro/offerte', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'get_offers'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/offerte/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'get_offer'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/categorie', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'get_categories'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/candidature', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'create_application'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/annunci', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'create_job_submission'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/annunci/miei', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'get_my_submissions'],
            'permission_callback' => [__CLASS__, 'ensure_authenticated_user'],
        ]);

        register_rest_route('wecoop/v1', '/lavoro/annunci/miei/(?P<id>\d+)', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [__CLASS__, 'delete_my_submission'],
            'permission_callback' => [__CLASS__, 'ensure_authenticated_user'],
        ]);

        register_rest_route('wecoop/v1', '/lavoro/annunci/suggest-category', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'suggest_category'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/lavoro/annunci/improve-description', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'improve_description'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function improve_description(WP_REST_Request $request) {
        $payload = $request->get_json_params();
        if (!is_array($payload)) {
            $payload = $request->get_params();
        }

        $title_offer = sanitize_text_field((string) ($payload['title_offer'] ?? ''));
        $city = sanitize_text_field((string) ($payload['city'] ?? ''));
        $contact_phone = sanitize_text_field((string) ($payload['contact_phone'] ?? ''));
        $description = sanitize_textarea_field((string) ($payload['description'] ?? ''));
        $category_scope = sanitize_text_field((string) ($payload['category_scope'] ?? 'job'));
        $category_direction = sanitize_text_field((string) ($payload['category_direction'] ?? 'offer'));

        if (strlen($description) < 12) {
            return new WP_Error('short_description', 'Inserisci una descrizione piu dettagliata per generare il testo AI', ['status' => 400]);
        }

        $api_key = self::openai_api_key();
        if ($api_key === '') {
            return new WP_REST_Response([
                'success' => true,
                'data' => [
                    'ai_description' => self::fallback_improved_description([
                        'title_offer' => $title_offer,
                        'city' => $city,
                        'contact_phone' => $contact_phone,
                        'description' => $description,
                        'category_scope' => $category_scope,
                        'category_direction' => $category_direction,
                    ]),
                    'source' => 'template_fallback',
                    'note' => 'OPENAI_API_KEY non configurata: usato template locale',
                ],
            ], 200);
        }

        $improved = self::openai_improve_description([
            'api_key' => $api_key,
            'title_offer' => $title_offer,
            'city' => $city,
            'contact_phone' => $contact_phone,
            'description' => $description,
            'category_scope' => $category_scope,
            'category_direction' => $category_direction,
        ]);

        if (is_wp_error($improved)) {
            return new WP_REST_Response([
                'success' => true,
                'data' => [
                    'ai_description' => self::fallback_improved_description([
                        'title_offer' => $title_offer,
                        'city' => $city,
                        'contact_phone' => $contact_phone,
                        'description' => $description,
                        'category_scope' => $category_scope,
                        'category_direction' => $category_direction,
                    ]),
                    'source' => 'template_fallback',
                    'note' => 'Fallback usato per errore AI: ' . $improved->get_error_message(),
                ],
            ], 200);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'ai_description' => $improved,
                'source' => 'openai',
            ],
        ], 200);
    }

    public static function ensure_authenticated_user() {
        return is_user_logged_in();
    }

    public static function get_my_submissions(WP_REST_Request $request) {
        $user_id = (int) get_current_user_id();
        if ($user_id <= 0) {
            return new WP_Error('forbidden', 'Utente non autenticato', ['status' => 401]);
        }

        $direction = sanitize_text_field((string) $request->get_param('category_direction'));

        $meta_query = [
            [
                'key' => 'submitted_from_app',
                'value' => '1',
                'compare' => '=',
            ],
            [
                'relation' => 'OR',
                [
                    'key' => 'submitted_user_id',
                    'value' => (string) $user_id,
                    'compare' => '=',
                ],
                [
                    'key' => 'submitted_user_id',
                    'value' => $user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
            ],
        ];

        if ($direction !== '' && in_array($direction, ['seek', 'offer'], true)) {
            $meta_query[] = [
                'key' => 'category_direction',
                'value' => $direction,
                'compare' => '=',
            ];
        }

        $query = new WP_Query([
            'post_type' => [WeCoop_Offerte_Lavoro_CPT::OFFER_CPT, 'post'],
            'post_status' => ['publish', 'pending', 'draft'],
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => $meta_query,
        ]);

        $items = [];
        foreach ($query->posts as $post) {
            $items[] = self::serialize_offer($post);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $items,
        ], 200);
    }

    public static function delete_my_submission(WP_REST_Request $request) {
        $user_id = (int) get_current_user_id();
        if ($user_id <= 0) {
            return new WP_Error('forbidden', 'Utente non autenticato', ['status' => 401]);
        }

        $post_id = (int) $request['id'];
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('not_found', 'Annuncio non trovato', ['status' => 404]);
        }

        $is_from_app = (string) get_post_meta($post_id, 'submitted_from_app', true) === '1';
        if (!$is_from_app) {
            return new WP_Error('forbidden', 'Annuncio non eliminabile da app', ['status' => 403]);
        }

        $owner_meta = (int) get_post_meta($post_id, 'submitted_user_id', true);
        $post_author = (int) $post->post_author;
        $is_owner = ($owner_meta > 0 && $owner_meta === $user_id)
            || ($post_author > 0 && $post_author === $user_id);

        if (!$is_owner) {
            return new WP_Error('forbidden', 'Puoi eliminare solo i tuoi annunci', ['status' => 403]);
        }

        $deleted = wp_trash_post($post_id);
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Impossibile eliminare annuncio', ['status' => 500]);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Annuncio eliminato con successo',
            'data' => ['id' => $post_id],
        ], 200);
    }

    public static function suggest_category(WP_REST_Request $request) {
        $payload = $request->get_json_params();
        if (!is_array($payload)) {
            $payload = $request->get_params();
        }

        $description = sanitize_textarea_field((string) ($payload['description'] ?? ''));
        $title_offer = sanitize_text_field((string) ($payload['title_offer'] ?? ''));
        $category_scope = sanitize_text_field((string) ($payload['category_scope'] ?? 'job'));
        $category_direction = sanitize_text_field((string) ($payload['category_direction'] ?? 'offer'));

        if (strlen($description) < 12) {
            return new WP_Error('short_description', 'Inserisci una descrizione piu dettagliata per il suggerimento AI', ['status' => 400]);
        }

        if (!taxonomy_exists(WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX)) {
            WeCoop_Offerte_Lavoro_CPT::register_taxonomies();
        }

        $terms = get_terms([
            'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return new WP_Error('categories_unavailable', 'Categorie non disponibili per il suggerimento', ['status' => 500]);
        }

        $category_catalog = array_map(static function ($term) {
            return [
                'slug' => (string) $term->slug,
                'name' => (string) $term->name,
            ];
        }, $terms);

        $api_key = self::openai_api_key();
        if ($api_key === '') {
            $fallback = self::heuristic_category_suggestion($title_offer, $description, $terms);
            return new WP_REST_Response([
                'success' => true,
                'data' => array_merge($fallback, [
                    'source' => 'heuristic',
                    'note' => 'OPENAI_API_KEY non configurata: usato suggerimento locale',
                ]),
            ], 200);
        }

        $ai_result = self::openai_category_suggestion([
            'api_key' => $api_key,
            'title_offer' => $title_offer,
            'description' => $description,
            'category_scope' => $category_scope,
            'category_direction' => $category_direction,
            'categories' => $category_catalog,
        ]);

        if (is_wp_error($ai_result)) {
            $fallback = self::heuristic_category_suggestion($title_offer, $description, $terms);
            return new WP_REST_Response([
                'success' => true,
                'data' => array_merge($fallback, [
                    'source' => 'heuristic_fallback',
                    'note' => 'Fallback usato per errore AI: ' . $ai_result->get_error_message(),
                ]),
            ], 200);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => array_merge($ai_result, ['source' => 'openai']),
        ], 200);
    }

    public static function get_offers(WP_REST_Request $request) {
        $paged = max(1, (int) $request->get_param('page'));
        $per_page = min(50, max(1, (int) $request->get_param('per_page') ?: 10));

        $args = [
            'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'post_status' => 'publish',
            'paged' => $paged,
            'posts_per_page' => $per_page,
            'orderby' => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
            'meta_key' => 'is_featured',
            'meta_query' => [
                [
                    'relation' => 'OR',
                    [
                        'key' => 'is_active',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'is_active',
                        'value' => '1',
                        'compare' => '=',
                    ],
                ],
            ],
        ];

        $search = sanitize_text_field((string) $request->get_param('search'));
        if ($search !== '') {
            $args['s'] = $search;
        }

        $meta_query = $args['meta_query'];

        $filters = [
            'city' => 'city',
            'region' => 'region',
            'contract_type' => 'contract_type',
            'work_mode' => 'work_mode',
            'language' => 'language_requirement',
        ];

        foreach ($filters as $param => $meta_key) {
            $value = sanitize_text_field((string) $request->get_param($param));
            if ($value !== '') {
                $meta_query[] = [
                    'key' => $meta_key,
                    'value' => $value,
                    'compare' => 'LIKE',
                ];
            }
        }

        $category_scope = sanitize_text_field((string) $request->get_param('category_scope'));
        if ($category_scope !== '') {
            if ($category_scope === 'job') {
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key' => 'category_scope',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'category_scope',
                        'value' => 'job',
                        'compare' => '=',
                    ],
                ];
            } else {
                $meta_query[] = [
                    'key' => 'category_scope',
                    'value' => $category_scope,
                    'compare' => '=',
                ];
            }
        }

        $category_direction = sanitize_text_field((string) $request->get_param('category_direction'));
        if ($category_direction !== '') {
            if ($category_direction === 'offer') {
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key' => 'category_direction',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'category_direction',
                        'value' => 'offer',
                        'compare' => '=',
                    ],
                ];
            } else {
                $meta_query[] = [
                    'key' => 'category_direction',
                    'value' => $category_direction,
                    'compare' => '=',
                ];
            }
        }

        $args['meta_query'] = $meta_query;

        $category = sanitize_text_field((string) $request->get_param('categoria'));
        if ($category !== '') {
            $args['tax_query'] = [[
                'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
                'field' => is_numeric($category) ? 'term_id' : 'slug',
                'terms' => $category,
            ]];
        }

        $query = new WP_Query(array_merge($args, ['posts_per_page' => -1, 'paged' => 1]));

        $fallback_meta_query = [
            [
                'key' => 'submitted_from_app',
                'value' => '1',
                'compare' => '=',
            ],
        ];

        foreach ($filters as $param => $meta_key) {
            $value = sanitize_text_field((string) $request->get_param($param));
            if ($value !== '') {
                $fallback_meta_query[] = [
                    'key' => $meta_key,
                    'value' => $value,
                    'compare' => 'LIKE',
                ];
            }
        }

        if ($category_scope !== '') {
            if ($category_scope === 'job') {
                $fallback_meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key' => 'category_scope',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'category_scope',
                        'value' => 'job',
                        'compare' => '=',
                    ],
                ];
            } else {
                $fallback_meta_query[] = [
                    'key' => 'category_scope',
                    'value' => $category_scope,
                    'compare' => '=',
                ];
            }
        }

        if ($category_direction !== '') {
            if ($category_direction === 'offer') {
                $fallback_meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key' => 'category_direction',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'category_direction',
                        'value' => 'offer',
                        'compare' => '=',
                    ],
                ];
            } else {
                $fallback_meta_query[] = [
                    'key' => 'category_direction',
                    'value' => $category_direction,
                    'compare' => '=',
                ];
            }
        }

        if ($category !== '') {
            $fallback_meta_query[] = [
                'key' => 'category_slug',
                'value' => $category,
                'compare' => '=',
            ];
        }

        $fallback_args = [
            'post_type' => 'post',
            'post_status' => ['publish', 'pending'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => $fallback_meta_query,
        ];

        if ($search !== '') {
            $fallback_args['s'] = $search;
        }

        $fallback_query = new WP_Query($fallback_args);

        $all_posts = [];
        foreach (array_merge($query->posts, $fallback_query->posts) as $post) {
            $all_posts[$post->ID] = $post;
        }

        $all_posts = array_values($all_posts);
        usort($all_posts, static function ($a, $b) {
            $a_featured = (int) get_post_meta((int) $a->ID, 'is_featured', true);
            $b_featured = (int) get_post_meta((int) $b->ID, 'is_featured', true);
            if ($a_featured !== $b_featured) {
                return $b_featured <=> $a_featured;
            }

            return strtotime((string) $b->post_date_gmt) <=> strtotime((string) $a->post_date_gmt);
        });

        $total_items = count($all_posts);
        $total_pages = max(1, (int) ceil($total_items / $per_page));
        $paged = min($paged, $total_pages);
        $offset = ($paged - 1) * $per_page;
        $page_posts = array_slice($all_posts, $offset, $per_page);

        $items = [];
        foreach ($page_posts as $post) {
            $items[] = self::serialize_offer($post);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page' => $paged,
                'per_page' => $per_page,
                'total_items' => $total_items,
                'total_pages' => $total_pages,
            ],
        ], 200);
    }

    public static function get_offer(WP_REST_Request $request) {
        $id = (int) $request['id'];
        $post = get_post($id);

        if (!$post) {
            return new WP_Error('not_found', 'Offerta non trovata', ['status' => 404]);
        }

        $is_offer_type = in_array($post->post_type, [
            WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT,
        ], true);
        $is_fallback_post = $post->post_type === 'post' && (string) get_post_meta($id, 'submitted_from_app', true) === '1';
        $is_allowed_status = in_array($post->post_status, ['publish', 'pending'], true);

        if ((!$is_offer_type && !$is_fallback_post) || !$is_allowed_status) {
            return new WP_Error('not_found', 'Offerta non trovata', ['status' => 404]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => self::serialize_offer($post, true),
        ], 200);
    }

    public static function get_categories() {
        $terms = get_terms([
            'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return new WP_Error('terms_error', 'Errore nel recupero categorie', ['status' => 500]);
        }

        $data = array_map(function ($term) {
            return [
                'id' => (int) $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => (int) $term->count,
            ];
        }, $terms);

        return new WP_REST_Response([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    public static function create_application(WP_REST_Request $request) {
        $payload = $request->get_json_params();
        if (!is_array($payload)) {
            $payload = $request->get_params();
        }

        $offer_id = isset($payload['offer_id']) ? (int) $payload['offer_id'] : 0;
        $name = sanitize_text_field((string) ($payload['name'] ?? ''));
        $phone = sanitize_text_field((string) ($payload['phone'] ?? ''));
        $email = sanitize_email((string) ($payload['email'] ?? ''));
        $city = sanitize_text_field((string) ($payload['city'] ?? ''));
        $note = sanitize_textarea_field((string) ($payload['note'] ?? ''));
        $origin = sanitize_text_field((string) ($payload['origin'] ?? 'Latinoamerica'));
        $consent_privacy = !empty($payload['consent_privacy']);

        $offer_post = $offer_id > 0 ? get_post($offer_id) : null;
        $offer_type = $offer_post ? $offer_post->post_type : '';
        $is_offer_type = in_array($offer_type, [
            WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT,
        ], true);
        $is_fallback_post = $offer_type === 'post' && (string) get_post_meta($offer_id, 'submitted_from_app', true) === '1';

        if ($offer_id <= 0 || (!$is_offer_type && !$is_fallback_post)) {
            return new WP_Error('invalid_offer', 'Offerta non valida', ['status' => 400]);
        }

        if ($name === '' || $phone === '') {
            return new WP_Error('missing_fields', 'Nome e telefono sono obbligatori', ['status' => 400]);
        }

        if (!$consent_privacy) {
            return new WP_Error('privacy_required', 'Consenso privacy obbligatorio', ['status' => 400]);
        }

        $rate_key = 'wecoop_job_apply_' . md5((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        if ((int) get_transient($rate_key) >= 6) {
            return new WP_Error('rate_limited', 'Troppi tentativi. Riprova piu tardi.', ['status' => 429]);
        }

        $application_id = wp_insert_post([
            'post_type' => WeCoop_Offerte_Lavoro_CPT::APPLICATION_CPT,
            'post_status' => 'private',
            'post_title' => sprintf('Candidatura %s - Offerta #%d', $name, $offer_id),
        ], true);

        if (is_wp_error($application_id)) {
            return new WP_Error('save_error', 'Impossibile salvare la candidatura', ['status' => 500]);
        }

        update_post_meta($application_id, 'offer_id', $offer_id);
        update_post_meta($application_id, 'applicant_name', $name);
        update_post_meta($application_id, 'applicant_phone', $phone);
        update_post_meta($application_id, 'applicant_email', $email);
        update_post_meta($application_id, 'applicant_city', $city);
        update_post_meta($application_id, 'applicant_note', $note);
        update_post_meta($application_id, 'applicant_origin', $origin);
        update_post_meta($application_id, 'consent_privacy', $consent_privacy ? 1 : 0);
        update_post_meta($application_id, 'status', 'new');

        set_transient($rate_key, ((int) get_transient($rate_key)) + 1, HOUR_IN_SECONDS);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Candidatura inviata con successo',
            'data' => [
                'application_id' => (int) $application_id,
                'offer_id' => $offer_id,
                'status' => 'new',
            ],
        ], 201);
    }

    public static function create_job_submission(WP_REST_Request $request) {
        $payload = $request->get_json_params();
        if (!is_array($payload)) {
            $payload = $request->get_params();
        }

        // Ensure CPT registration exists also in edge bootstrapping cases.
        if (!post_type_exists(WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT)) {
            WeCoop_Offerte_Lavoro_CPT::register_post_types();
        }

        $submission_type = sanitize_text_field((string) ($payload['submission_type'] ?? ''));
        $title_offer = sanitize_text_field((string) ($payload['title_offer'] ?? ''));
        $city = sanitize_text_field((string) ($payload['city'] ?? ''));
        $contact_phone = sanitize_text_field((string) ($payload['contact_phone'] ?? ''));
        $contact_email = sanitize_email((string) ($payload['contact_email'] ?? ''));
        $description = sanitize_textarea_field((string) ($payload['description'] ?? ''));
        $category_scope = sanitize_text_field((string) ($payload['category_scope'] ?? ''));
        $category_direction = sanitize_text_field((string) ($payload['category_direction'] ?? ''));
        $category_macro = sanitize_text_field((string) ($payload['category_macro'] ?? ''));
        $category_slug = sanitize_text_field((string) ($payload['category_slug'] ?? ''));
        $cv_id = sanitize_text_field((string) ($payload['cv_id'] ?? ''));
        $cv_label = sanitize_text_field((string) ($payload['cv_label'] ?? ''));
        $cv_pdf_url = esc_url_raw((string) ($payload['cv_pdf_url'] ?? ''));
        $cv_docx_url = esc_url_raw((string) ($payload['cv_docx_url'] ?? ''));
        $image_base64 = isset($payload['image_base64']) ? (string) $payload['image_base64'] : '';
        $consent_privacy = !empty($payload['consent_privacy']);

        if (!in_array($category_scope, ['job', 'service'], true)) {
            $category_scope = strtolower($submission_type) === 'servizio' ? 'service' : 'job';
        }

        if (!in_array($category_direction, ['seek', 'offer'], true)) {
            $category_direction = 'offer';
        }

        // Validazione campi obbligatori
        if (empty($submission_type) || empty($title_offer) || empty($city) || empty($contact_phone) || empty($description)) {
            return new WP_Error('missing_fields', 'Tutti i campi sono obbligatori', ['status' => 400]);
        }

        if (strlen($description) < 20) {
            return new WP_Error('short_description', 'La descrizione deve avere almeno 20 caratteri', ['status' => 400]);
        }

        if (!$consent_privacy) {
            return new WP_Error('privacy_required', 'Consenso privacy obbligatorio', ['status' => 400]);
        }

        // Rate limiting
        $rate_key = 'wecoop_job_submission_' . md5((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        if ((int) get_transient($rate_key) >= 3) {
            return new WP_Error('rate_limited', 'Troppi annunci inviati. Riprova piu tardi.', ['status' => 429]);
        }

        $user_id = (int) get_current_user_id();

        $post_args = [
            'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'post_status' => 'publish',
            'post_title' => $title_offer,
            'post_content' => $description,
            'post_excerpt' => wp_trim_words($description, 28),
            'post_author' => $user_id > 0 ? $user_id : 0,
        ];

        // Create the offer directly so it is immediately visible in app listings.
        $submission_id = wp_insert_post($post_args, true);

        // Fallback for restrictive environments where the custom type cannot be inserted.
        if (is_wp_error($submission_id)) {
            $fallback_args = $post_args;
            $fallback_args['post_type'] = 'post';
            $submission_id = wp_insert_post($fallback_args, true);
        }

        if (is_wp_error($submission_id)) {
            $error_message = $submission_id->get_error_message();
            if (!empty($error_message)) {
                error_log('wecoop_offerte_lavoro save_error: ' . $error_message);
            }

            return new WP_Error(
                'save_error',
                'Impossibile salvare l\'annuncio. Riprova tra qualche minuto.',
                [
                    'status' => 500,
                    'details' => $error_message,
                ]
            );
        }

        // Gestisci l'immagine se presente
        $image_url = '';
        if (!empty($image_base64)) {
            $image_url = self::handle_base64_image($submission_id, $image_base64);
        }

        // Salva i metadati
        update_post_meta($submission_id, 'submission_type', $submission_type);
        update_post_meta($submission_id, 'category_scope', $category_scope);
        update_post_meta($submission_id, 'category_direction', $category_direction);
        update_post_meta($submission_id, 'company_name', 'Annuncio dalla community');
        update_post_meta($submission_id, 'title_offer', $title_offer);
        update_post_meta($submission_id, 'city', $city);
        update_post_meta($submission_id, 'phone_whatsapp', $contact_phone);
        update_post_meta($submission_id, 'email_contact', $contact_email);
        update_post_meta($submission_id, 'requirements', $description);
        update_post_meta($submission_id, 'contact_phone', $contact_phone);
        update_post_meta($submission_id, 'contact_email', $contact_email);
        update_post_meta($submission_id, 'description', $description);
        update_post_meta($submission_id, 'category_macro', $category_macro);
        update_post_meta($submission_id, 'category_slug', $category_slug);
        update_post_meta($submission_id, 'attached_cv_id', $cv_id);
        update_post_meta($submission_id, 'attached_cv_label', $cv_label);
        update_post_meta($submission_id, 'attached_cv_pdf_url', $cv_pdf_url);
        update_post_meta($submission_id, 'attached_cv_docx_url', $cv_docx_url);
        update_post_meta($submission_id, 'has_attached_cv', (!empty($cv_pdf_url) || !empty($cv_docx_url)) ? 1 : 0);
        update_post_meta($submission_id, 'is_active', 1);
        update_post_meta($submission_id, 'is_featured', 0);
        update_post_meta($submission_id, 'consent_privacy', $consent_privacy ? 1 : 0);
        update_post_meta($submission_id, 'status', 'published');
        update_post_meta($submission_id, 'submitted_from_app', 1);
        update_post_meta($submission_id, 'submitted_user_id', $user_id > 0 ? $user_id : 0);
        if (!empty($image_url)) {
            update_post_meta($submission_id, 'image_url', $image_url);
        }

        if (!empty($category_slug)) {
            $term = get_term_by('slug', $category_slug, WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX);
            if ($term && !is_wp_error($term)) {
                wp_set_post_terms($submission_id, [(int) $term->term_id], WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX, false);
            }
        }

        // Incrementa il limite rate
        set_transient($rate_key, ((int) get_transient($rate_key)) + 1, DAY_IN_SECONDS);

        // Notifica admin
        do_action('wecoop_job_submission_created', $submission_id, [
            'submission_type' => $submission_type,
            'category_scope' => $category_scope,
            'category_direction' => $category_direction,
            'title_offer' => $title_offer,
            'city' => $city,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'description' => $description,
            'category_macro' => $category_macro,
            'category_slug' => $category_slug,
            'cv_id' => $cv_id,
        ]);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Annuncio pubblicato con successo!',
            'data' => [
                'submission_id' => (int) $submission_id,
                'status' => 'published',
            ],
        ], 201);
    }

    private static function handle_base64_image($post_id, $image_base64) {
        try {
            // Decodifica il base64
            $image_data = base64_decode($image_base64, true);
            if ($image_data === false) {
                error_log('wecoop_offerte_lavoro: Fallita decodifica base64');
                return '';
            }

            // Rileva il tipo di immagine
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $image_data);
            finfo_close($finfo);

            // Valida il tipo MIME
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime_type, $allowed_types, true)) {
                error_log('wecoop_offerte_lavoro: Tipo MIME non supportato: ' . $mime_type);
                return '';
            }

            // Ricava l'estensione
            $ext_map = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $file_ext = $ext_map[$mime_type] ?? 'jpg';

            // Crea un nome file unico
            $filename = 'wecoop-offer-' . $post_id . '-' . time() . '.' . $file_ext;

            // Ottieni la directory uploads
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . $filename;

            // Salva il file
            if (file_put_contents($file_path, $image_data) === false) {
                error_log('wecoop_offerte_lavoro: Fallita scrittura file immagine');
                return '';
            }

            // Crea un attachment post
            $attachment = [
                'post_mime_type' => $mime_type,
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit',
                'post_parent' => (int) $post_id,
            ];

            $attachment_id = wp_insert_attachment($attachment, $file_path, (int) $post_id);

            if ($attachment_id && !is_wp_error($attachment_id)) {
                // Genera metadati e set come featured image
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                wp_update_attachment_metadata($attachment_id, $attach_data);
                set_post_thumbnail((int) $post_id, $attachment_id);

                // Ritorna l'URL dell'immagine
                $image_url = wp_get_attachment_image_url($attachment_id, 'large');
                if ($image_url) {
                    return $image_url;
                }
            }

            // Se l'attachment non funziona, ritorna l'URL diretto
            return $upload_dir['url'] . '/' . $filename;
        } catch (Exception $e) {
            error_log('wecoop_offerte_lavoro: Errore gestione immagine: ' . $e->getMessage());
            return '';
        }
    }

    private static function openai_api_key() {
        if (defined('OPENAI_API_KEY')) {
            $key = (string) constant('OPENAI_API_KEY');
            if ($key !== '') {
                return $key;
            }
        }

        $env_key = getenv('OPENAI_API_KEY');
        return is_string($env_key) ? trim($env_key) : '';
    }

    private static function openai_category_suggestion(array $args) {
        $categories_json = wp_json_encode($args['categories'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($categories_json) || $categories_json === '') {
            return new WP_Error('invalid_categories', 'Catalogo categorie non valido');
        }

        $model = apply_filters('wecoop_offerte_lavoro_openai_model', 'gpt-4o-mini');

        $messages = [
            [
                'role' => 'system',
                'content' => 'Sei un classificatore per annunci lavoro/servizi WECOOP. Rispondi solo con JSON valido.',
            ],
            [
                'role' => 'user',
                'content' => "Classifica il seguente annuncio scegliendo UNA categoria tra quelle permesse.\n"
                    . "Titolo: " . (string) $args['title_offer'] . "\n"
                    . "Descrizione: " . (string) $args['description'] . "\n"
                    . "Scope: " . (string) $args['category_scope'] . "\n"
                    . "Direction: " . (string) $args['category_direction'] . "\n"
                    . "Categorie disponibili (JSON): " . $categories_json . "\n"
                    . "Rispondi solo con questo JSON: "
                    . '{"category_slug":"slug_valido","confidence":0.0,"reason":"motivo breve"}',
            ],
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'timeout' => 25,
            'headers' => [
                'Authorization' => 'Bearer ' . (string) $args['api_key'],
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $model,
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
                'messages' => $messages,
            ]),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return new WP_Error('openai_http_error', 'Errore chiamata OpenAI');
        }

        $content = (string) ($decoded['choices'][0]['message']['content'] ?? '');
        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            return new WP_Error('openai_parse_error', 'Risposta AI non leggibile');
        }

        $slug = sanitize_title((string) ($parsed['category_slug'] ?? ''));
        if ($slug === '') {
            return new WP_Error('openai_invalid_slug', 'Categoria suggerita non valida');
        }

        $allowed = [];
        foreach ((array) $args['categories'] as $item) {
            if (is_array($item) && !empty($item['slug'])) {
                $allowed[] = (string) $item['slug'];
            }
        }

        if (!in_array($slug, $allowed, true)) {
            return new WP_Error('openai_slug_not_allowed', 'Categoria suggerita fuori catalogo');
        }

        $confidence = (float) ($parsed['confidence'] ?? 0.6);
        if ($confidence < 0) {
            $confidence = 0;
        }
        if ($confidence > 1) {
            $confidence = 1;
        }

        return [
            'category_slug' => $slug,
            'category_macro' => self::infer_macro_from_slug($slug),
            'confidence' => $confidence,
            'reason' => sanitize_text_field((string) ($parsed['reason'] ?? 'Suggerimento AI')), 
        ];
    }

    private static function openai_improve_description(array $args) {
        $model = apply_filters('wecoop_offerte_lavoro_openai_model', 'gpt-4o-mini');

        $messages = [
            [
                'role' => 'system',
                'content' => 'Sei un assistente editoriale per annunci lavoro/servizi. Scrivi testi chiari, inclusivi e concreti. Restituisci solo JSON valido.',
            ],
            [
                'role' => 'user',
                'content' => "Migliora questa bozza annuncio mantenendo il significato e i dati reali.\n"
                    . "Titolo: " . (string) $args['title_offer'] . "\n"
                    . "Citta: " . (string) $args['city'] . "\n"
                    . "Contatto: " . (string) $args['contact_phone'] . "\n"
                    . "Scope: " . (string) $args['category_scope'] . "\n"
                    . "Direzione: " . (string) $args['category_direction'] . "\n"
                    . "Testo originale: " . (string) $args['description'] . "\n"
                    . "Regole: massimo 1300 caratteri, frasi brevi, include mansione/servizio, attivita principali, disponibilita/orari se presenti, zona (citta), modalita di contatto. Non inventare dati mancanti.\n"
                    . "Rispondi solo con JSON: {\"ai_description\":\"testo migliorato\"}",
            ],
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'timeout' => 25,
            'headers' => [
                'Authorization' => 'Bearer ' . (string) $args['api_key'],
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode([
                'model' => $model,
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => $messages,
            ]),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($status < 200 || $status >= 300 || !is_array($decoded)) {
            return new WP_Error('openai_http_error', 'Errore chiamata OpenAI');
        }

        $content = (string) ($decoded['choices'][0]['message']['content'] ?? '');
        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            return new WP_Error('openai_parse_error', 'Risposta AI non leggibile');
        }

        $ai_description = trim((string) ($parsed['ai_description'] ?? ''));
        if ($ai_description === '') {
            return new WP_Error('openai_empty_description', 'Descrizione AI vuota');
        }

        $ai_description = preg_replace('/\s+/', ' ', $ai_description);
        if (!is_string($ai_description)) {
            return new WP_Error('openai_invalid_description', 'Descrizione AI non valida');
        }

        return trim($ai_description);
    }

    private static function fallback_improved_description(array $args) {
        $direction_label = ((string) ($args['category_direction'] ?? 'offer')) === 'seek'
            ? 'Cerco'
            : 'Offro';
        $title = trim((string) ($args['title_offer'] ?? ''));
        $city = trim((string) ($args['city'] ?? ''));
        $phone = trim((string) ($args['contact_phone'] ?? ''));
        $raw = trim((string) ($args['description'] ?? ''));

        $intro = $direction_label . ' ' . ($title !== '' ? $title : 'supporto professionale') . '.';
        $location = $city !== '' ? ' Zona: ' . $city . '.' : '';
        $contact = $phone !== '' ? ' Contatti: ' . $phone . '.' : '';

        $clean_raw = preg_replace('/\s+/', ' ', $raw);
        if (!is_string($clean_raw)) {
            $clean_raw = $raw;
        }

        $text = $intro
            . ' Descrizione: '
            . trim($clean_raw)
            . $location
            . ' Disponibilita e dettagli da concordare.'
            . $contact;

        return trim($text);
    }

    private static function heuristic_category_suggestion($title_offer, $description, array $terms) {
        $text = strtolower(trim((string) $title_offer . ' ' . (string) $description));

        $keywords_by_slug = [
            'baby-sitter' => ['baby', 'bambin', 'babysitter', 'nanny'],
            'badante' => ['badante', 'anzian', 'caregiver', 'assistenza'],
            'colf' => ['colf', 'domestic', 'pulizie casa', 'stiro'],
            'oss-osa' => ['oss', 'osa', 'sanitari', 'infermier'],
            'aso' => ['aso', 'dentale', 'studio odontoiatrico'],
            'pulizie-limpieza' => ['pulizie', 'limpieza', 'cleaning'],
            'cameriere' => ['camerier', 'sala', 'ristorante'],
            'aiuto-cucina' => ['aiuto cucina', 'cucina', 'prep'],
            'magazziniere' => ['magazzino', 'logistica', 'scaffale'],
            'autista' => ['autista', 'patente', 'guida'],
            'call-center' => ['call center', 'telefono', 'customer care'],
            'commesso-cassa' => ['commesso', 'cassa', 'negozio', 'vendita'],
        ];

        $best_slug = '';
        $best_score = -1;
        foreach ($keywords_by_slug as $slug => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            if ($score > $best_score) {
                $best_score = $score;
                $best_slug = $slug;
            }
        }

        $allowed_slugs = array_map(static function ($term) {
            return (string) $term->slug;
        }, $terms);

        if ($best_slug === '' || !in_array($best_slug, $allowed_slugs, true)) {
            $best_slug = isset($terms[0]) ? (string) $terms[0]->slug : '';
        }

        return [
            'category_slug' => $best_slug,
            'category_macro' => self::infer_macro_from_slug($best_slug),
            'confidence' => $best_score > 0 ? 0.62 : 0.35,
            'reason' => 'Suggerimento basato su parole chiave principali della descrizione',
        ];
    }

    private static function infer_macro_from_slug($slug) {
        $map = [
            'baby-sitter' => 'personal-care',
            'badante' => 'personal-care',
            'colf' => 'personal-care',
            'oss-osa' => 'health-wellbeing',
            'aso' => 'health-wellbeing',
            'dentista' => 'health-wellbeing',
            'massaggi' => 'health-wellbeing',
            'lavapiatti' => 'food-hospitality',
            'aiuto-cucina' => 'food-hospitality',
            'cameriere' => 'food-hospitality',
            'pizzaiolo' => 'food-hospitality',
            'fotografo' => 'events-creativity',
            'dj' => 'events-creativity',
            'animatori' => 'events-creativity',
            'elettricista' => 'construction-logistics',
            'idraulico' => 'construction-logistics',
            'imbianchino' => 'construction-logistics',
            'magazziniere' => 'construction-logistics',
            'rider-consegne' => 'construction-logistics',
            'autista' => 'construction-logistics',
            'operaio-generico' => 'industry-manufacturing',
            'confezionamento' => 'industry-manufacturing',
            'saldatore' => 'industry-manufacturing',
            'agricoltura-bracciante' => 'agriculture-green',
            'raccolta-frutta' => 'agriculture-green',
            'giardiniere' => 'agriculture-green',
            'call-center' => 'retail-customer-service',
            'commesso-cassa' => 'retail-customer-service',
            'scaffalista' => 'retail-customer-service',
        ];

        $normalized_slug = sanitize_title((string) $slug);
        return $map[$normalized_slug] ?? 'other';
    }

    private static function serialize_offer(WP_Post $post, $full = false) {
        $id = (int) $post->ID;
        $submitted_user_id = (int) get_post_meta($id, 'submitted_user_id', true);
        $author_user_id = $submitted_user_id > 0 ? $submitted_user_id : (int) $post->post_author;
        $author_data = self::resolve_offer_author_data($author_user_id);

        $data = [
            'id' => $id,
            'title' => get_the_title($post),
            'excerpt' => wp_strip_all_tags((string) get_the_excerpt($post)),
            'company_name' => (string) get_post_meta($id, 'company_name', true),
            'city' => (string) get_post_meta($id, 'city', true),
            'province' => (string) get_post_meta($id, 'province', true),
            'region' => (string) get_post_meta($id, 'region', true),
            'contract_type' => (string) get_post_meta($id, 'contract_type', true),
            'work_mode' => (string) get_post_meta($id, 'work_mode', true),
            'salary_range' => (string) get_post_meta($id, 'salary_range', true),
            'language_requirement' => (string) get_post_meta($id, 'language_requirement', true),
            'phone_whatsapp' => (string) get_post_meta($id, 'phone_whatsapp', true),
            'email_contact' => (string) get_post_meta($id, 'email_contact', true),
            'image_url' => (string) (get_post_meta($id, 'image_url', true) ?: get_the_post_thumbnail_url($id, 'large') ?: ''),
            'source_url' => (string) get_post_meta($id, 'source_url', true),
            'requirements' => (string) get_post_meta($id, 'requirements', true),
            'schedule' => (string) get_post_meta($id, 'schedule', true),
            'target_community' => (string) get_post_meta($id, 'target_community', true),
            'expires_at' => (string) get_post_meta($id, 'expires_at', true),
            'is_featured' => (bool) get_post_meta($id, 'is_featured', true),
            'is_active' => (bool) get_post_meta($id, 'is_active', true),
            'category_scope' => (string) (get_post_meta($id, 'category_scope', true) ?: 'job'),
            'category_direction' => (string) (get_post_meta($id, 'category_direction', true) ?: 'offer'),
            'category_macro' => (string) get_post_meta($id, 'category_macro', true),
            'category_sub' => (string) get_post_meta($id, 'category_sub', true),
            'published_at' => get_post_time('c', true, $post),
            'categories' => self::get_offer_categories($id),
            'author_user_id' => $author_data['user_id'],
            'author_name' => $author_data['name'],
            'author_avatar_url' => $author_data['avatar_url'],
            'attached_cv_id' => (string) get_post_meta($id, 'attached_cv_id', true),
            'attached_cv_label' => (string) get_post_meta($id, 'attached_cv_label', true),
            'attached_cv_pdf_url' => (string) get_post_meta($id, 'attached_cv_pdf_url', true),
            'attached_cv_docx_url' => (string) get_post_meta($id, 'attached_cv_docx_url', true),
            'has_attached_cv' => (bool) get_post_meta($id, 'has_attached_cv', true),
        ];

        if ($full) {
            $data['content'] = apply_filters('the_content', $post->post_content);
        }

        return $data;
    }

    private static function resolve_offer_author_data($user_id) {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return [
                'user_id' => 0,
                'name' => '',
                'avatar_url' => '',
            ];
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return [
                'user_id' => 0,
                'name' => '',
                'avatar_url' => '',
            ];
        }

        $name_parts = array_filter([
            (string) get_user_meta($user_id, 'nome', true),
            (string) get_user_meta($user_id, 'cognome', true),
        ]);
        $name = !empty($name_parts)
            ? implode(' ', $name_parts)
            : (string) $user->display_name;

        $avatar_url = (string) get_user_meta($user_id, 'avatar_url', true);
        if ($avatar_url === '') {
            $avatar_url = (string) wp_get_avatar_url($user_id, ['size' => 256]);
        }

        return [
            'user_id' => $user_id,
            'name' => $name,
            'avatar_url' => $avatar_url,
        ];
    }

    private static function get_offer_categories($post_id) {
        $terms = wp_get_post_terms($post_id, WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX);
        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        return array_map(function ($term) {
            return [
                'id' => (int) $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            ];
        }, $terms);
    }
}
