<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Opportunita_REST {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('wecoop/v1', '/opportunita', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_opportunita'],
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => ['type' => 'integer', 'default' => 20],
                'page' => ['type' => 'integer', 'default' => 1],
                'category' => ['type' => 'string'],
                'tag' => ['type' => 'string'],
                'content_type' => ['type' => 'string'],
                'language' => ['type' => 'string'],
                'featured' => ['type' => 'boolean'],
                'publish_in_app' => ['type' => 'boolean', 'default' => true],
            ],
        ]);

        register_rest_route('wecoop/v1', '/opportunita/(?P<id>\\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_opportunita_item'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => ['type' => 'integer', 'required' => true],
            ],
        ]);
    }

    public static function get_opportunita($request) {
        $per_page = min(max((int) $request->get_param('per_page'), 1), 50);
        $page = max((int) $request->get_param('page'), 1);

        $args = [
            'post_type' => WeCoop_Opportunita_CPT::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'ignore_sticky_posts' => true,
            'wecoop_opportunita_order' => 1,
        ];

        $tax_query = [];

        $category = self::csv_to_terms($request->get_param('category'));
        if (!empty($category)) {
            $tax_query[] = [
                'taxonomy' => WeCoop_Opportunita_CPT::TAX_CATEGORIA,
                'field' => 'slug',
                'terms' => $category,
            ];
        }

        $tags = self::csv_to_terms($request->get_param('tag'));
        if (!empty($tags)) {
            $tax_query[] = [
                'taxonomy' => WeCoop_Opportunita_CPT::TAX_TAG,
                'field' => 'slug',
                'terms' => $tags,
            ];
        }

        $content_type = self::csv_to_terms($request->get_param('content_type'));
        if (!empty($content_type)) {
            $tax_query[] = [
                'taxonomy' => WeCoop_Opportunita_CPT::TAX_TIPO,
                'field' => 'slug',
                'terms' => $content_type,
            ];
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        $meta_query = [];

        $language = $request->get_param('language');
        if (is_string($language) && $language !== '') {
            $meta_query[] = [
                'key' => 'language',
                'value' => sanitize_text_field($language),
                'compare' => '=',
            ];
        }

        $featured = $request->get_param('featured');
        if ($featured !== null) {
            $meta_query[] = [
                'key' => 'is_featured',
                'value' => $featured ? '1' : '0',
                'compare' => '=',
            ];
        }

        $publish_in_app = $request->get_param('publish_in_app');
        if ($publish_in_app !== null) {
            $meta_query[] = [
                'key' => 'publish_in_app',
                'value' => $publish_in_app ? '1' : '0',
                'compare' => '=',
            ];
        }

        if (!empty($meta_query)) {
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            $args['meta_query'] = $meta_query;
        }

        add_filter('posts_clauses', [__CLASS__, 'ordering_clauses'], 10, 2);
        $query = new WP_Query($args);
        remove_filter('posts_clauses', [__CLASS__, 'ordering_clauses'], 10);

        $items = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = self::format_item((int) get_the_ID());
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response([
            'success' => true,
            'items' => $items,
            'pagination' => [
                'total' => (int) $query->found_posts,
                'pages' => (int) $query->max_num_pages,
                'current_page' => $page,
                'per_page' => $per_page,
            ],
        ], 200);
    }

    public static function ordering_clauses($clauses, $query) {
        if ((int) $query->get('wecoop_opportunita_order') !== 1) {
            return $clauses;
        }

        global $wpdb;

        $featured_alias = 'wecoop_pm_featured';
        $priority_alias = 'wecoop_pm_priority';

        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} {$featured_alias} ON ({$wpdb->posts}.ID = {$featured_alias}.post_id AND {$featured_alias}.meta_key = 'is_featured')";
        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} {$priority_alias} ON ({$wpdb->posts}.ID = {$priority_alias}.post_id AND {$priority_alias}.meta_key = 'priority_order')";

        $clauses['orderby'] = "CAST(COALESCE({$featured_alias}.meta_value, '0') AS UNSIGNED) DESC, "
            . "CASE WHEN {$priority_alias}.meta_value REGEXP '^[0-9]+$' THEN CAST({$priority_alias}.meta_value AS UNSIGNED) ELSE 999999 END ASC, "
            . "{$wpdb->posts}.post_date DESC";

        return $clauses;
    }

    public static function get_opportunita_item($request) {
        $post_id = (int) $request->get_param('id');
        $post = get_post($post_id);

        if (!$post || $post->post_type !== WeCoop_Opportunita_CPT::POST_TYPE || $post->post_status !== 'publish') {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Opportunita non trovata',
            ], 404);
        }

        return new WP_REST_Response([
            'success' => true,
            'item' => self::format_item($post_id),
        ], 200);
    }

    private static function format_item($post_id) {
        $post = get_post($post_id);

        $categories = self::terms_as_names($post_id, WeCoop_Opportunita_CPT::TAX_CATEGORIA);
        $tags = self::terms_as_names($post_id, WeCoop_Opportunita_CPT::TAX_TAG);
        $content_types = self::terms_as_names($post_id, WeCoop_Opportunita_CPT::TAX_TIPO);

        $card_title = get_post_meta($post_id, 'card_title', true);
        $card_excerpt = get_post_meta($post_id, 'card_excerpt', true);
        $cta_label = get_post_meta($post_id, 'cta_label', true);
        $cta_url = get_post_meta($post_id, 'cta_url', true);

        $title = $card_title !== '' ? $card_title : get_the_title($post_id);
        $excerpt = $card_excerpt !== '' ? $card_excerpt : (has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words((string) $post->post_content, 35));

        $image = get_post_meta($post_id, 'cover_image', true);
        if ($image === '') {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) {
                $image = (string) wp_get_attachment_image_url($thumbnail_id, 'large');
            }
        }

        return [
            'id' => $post_id,
            'title' => $title,
            'excerpt' => $excerpt,
            'category' => !empty($categories) ? $categories[0] : null,
            'tags' => $tags,
            'content_type' => !empty($content_types) ? $content_types[0] : null,
            'cta_label' => $cta_label,
            'cta_url' => $cta_url,
            'cta_type' => get_post_meta($post_id, 'cta_type', true),
            'image' => $image !== '' ? $image : null,
            'icon_name' => get_post_meta($post_id, 'icon_name', true),
            'language' => get_post_meta($post_id, 'language', true) ?: 'it',
            'is_featured' => get_post_meta($post_id, 'is_featured', true) === '1',
            'publish_in_app' => get_post_meta($post_id, 'publish_in_app', true) === '1',
            'priority_order' => (int) get_post_meta($post_id, 'priority_order', true),
            'published_at' => get_the_date('c', $post_id),
        ];
    }

    private static function terms_as_names($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (!$terms || is_wp_error($terms)) {
            return [];
        }

        return array_values(array_map(static function($term) {
            return $term->name;
        }, $terms));
    }

    private static function csv_to_terms($raw) {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, static function($item) {
            return $item !== '';
        });

        return array_values(array_map('sanitize_title', $parts));
    }
}
