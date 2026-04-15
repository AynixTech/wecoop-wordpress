<?php
/**
 * Endpoint pubblico: GET /wecoop/v1/offerte-formative
 * Restituisce i percorsi formativi attivi.
 *
 * @package WECOOP_Offerta_Formativa
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Offerte_Formative_Endpoint {

    public static function register_routes() {
        register_rest_route('wecoop/v1', '/offerte-formative', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_offerte'],
            'permission_callback' => '__return_true',
            'args' => [
                'per_page' => [
                    'default'           => 20,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    public static function get_offerte($request) {
        $per_page = (int) $request->get_param('per_page');
        $per_page = max(1, min(50, $per_page));

        $query = new WP_Query([
            'post_type'      => 'offerta_formativa',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'meta_query'     => [
                [
                    'key'     => 'attiva',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'meta_key'  => 'ordine',
            'orderby'   => 'meta_value_num',
            'order'     => 'ASC',
        ]);

        $offerte = [];
        foreach ($query->posts as $post) {
            $image_url = get_the_post_thumbnail_url($post->ID, 'medium') ?: '';
            $offerte[] = [
                'id'           => $post->ID,
                'titolo'       => get_the_title($post),
                'descrizione'  => get_the_excerpt($post),
                'partner_nome' => get_post_meta($post->ID, 'partner_nome', true),
                'categoria'    => get_post_meta($post->ID, 'categoria', true),
                'durata'       => get_post_meta($post->ID, 'durata', true),
                'link_info'    => get_post_meta($post->ID, 'link_info', true),
                'image_url'    => $image_url,
                'ordine'       => (int) get_post_meta($post->ID, 'ordine', true),
            ];
        }

        return rest_ensure_response($offerte);
    }
}
