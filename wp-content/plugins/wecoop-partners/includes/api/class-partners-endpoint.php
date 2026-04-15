<?php
/**
 * REST API Endpoint: Partners
 *
 * GET /wecoop/v1/partners — lista partner pubblici
 *
 * @package WECOOP_Partners
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Partners_Endpoint {

    public static function init() {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            @ini_set('display_errors', 0);
            @error_reporting(E_ERROR | E_PARSE);
        }
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        // GET /wecoop/v1/partners
        register_rest_route('wecoop/v1', '/partners', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_partners'],
            'permission_callback' => '__return_true',
            'args'                => [
                'per_page' => ['type' => 'integer', 'default' => 50, 'minimum' => 1, 'maximum' => 100],
                'page'     => ['type' => 'integer', 'default' => 1],
            ],
        ]);
    }

    /**
     * GET /wecoop/v1/partners
     *
     * Restituisce la lista partner ordinata per campo 'ordine' ASC, poi per titolo.
     */
    public static function get_partners(WP_REST_Request $request) {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            @ini_set('display_errors', 0);
            @error_reporting(0);
        }

        $per_page = (int) $request->get_param('per_page');
        $page     = (int) $request->get_param('page');

        $args = [
            'post_type'      => 'wecoop_partner',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => ['meta_value_num' => 'ASC', 'title' => 'ASC'],
            'meta_key'       => 'ordine',
        ];

        $query   = new WP_Query($args);
        $result  = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $logo_url    = get_the_post_thumbnail_url($post_id, 'full') ?: '';
                $website_url = get_post_meta($post_id, 'website_url', true) ?: '';
                $descrizione = get_the_excerpt() ?: (get_post_meta($post_id, 'descrizione', true) ?: '');
                $ordine      = (int)(get_post_meta($post_id, 'ordine', true) ?: 0);

                $result[] = [
                    'id'          => $post_id,
                    'nome'        => get_the_title(),
                    'logo_url'    => $logo_url,
                    'website_url' => $website_url,
                    'descrizione' => $descrizione,
                    'ordine'      => $ordine,
                ];
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response($result, 200);
    }
}
