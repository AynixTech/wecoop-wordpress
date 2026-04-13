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

        $post_args = [
            'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'post_status' => 'publish',
            'post_title' => $title_offer,
            'post_content' => $description,
            'post_excerpt' => wp_trim_words($description, 28),
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
        update_post_meta($submission_id, 'is_active', 1);
        update_post_meta($submission_id, 'is_featured', 0);
        update_post_meta($submission_id, 'consent_privacy', $consent_privacy ? 1 : 0);
        update_post_meta($submission_id, 'status', 'published');
        update_post_meta($submission_id, 'submitted_from_app', 1);

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

    private static function serialize_offer(WP_Post $post, $full = false) {
        $id = (int) $post->ID;

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
            'published_at' => get_post_time('c', true, $post),
            'categories' => self::get_offer_categories($id),
        ];

        if ($full) {
            $data['content'] = apply_filters('the_content', $post->post_content);
        }

        return $data;
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
