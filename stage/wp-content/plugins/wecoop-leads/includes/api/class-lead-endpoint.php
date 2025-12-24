<?php
/**
 * Lead API Endpoint
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Lead_API {
    
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    public static function register_routes() {
        register_rest_route('wecoop/v1', '/leads', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'create_lead'],
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
        
        register_rest_route('wecoop/v1', '/leads', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_leads'],
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    }
    
    public static function create_lead($request) {
        $params = $request->get_json_params();
        
        $post_id = wp_insert_post([
            'post_type' => 'lead',
            'post_title' => $params['nome'] . ' ' . $params['cognome'],
            'post_status' => 'publish'
        ]);
        
        if (is_wp_error($post_id)) {
            return rest_ensure_response(['success' => false, 'message' => $post_id->get_error_message()]);
        }
        
        // Salva meta
        $meta_fields = ['nome', 'cognome', 'email', 'telefono', 'fonte', 'valore_stimato', 'note'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($params[$field]));
            }
        }
        
        // Imposta stato
        wp_set_object_terms($post_id, 'Nuovo', 'pipeline_stage');
        
        return rest_ensure_response(['success' => true, 'lead_id' => $post_id]);
    }
    
    public static function get_leads($request) {
        $args = [
            'post_type' => 'lead',
            'posts_per_page' => $request->get_param('per_page') ?: 20,
            'paged' => $request->get_param('page') ?: 1
        ];
        
        $query = new WP_Query($args);
        $leads = [];
        
        foreach ($query->posts as $post) {
            $leads[] = [
                'id' => $post->ID,
                'nome' => get_post_meta($post->ID, 'nome', true),
                'cognome' => get_post_meta($post->ID, 'cognome', true),
                'email' => get_post_meta($post->ID, 'email', true),
                'telefono' => get_post_meta($post->ID, 'telefono', true),
                'stato' => wp_get_post_terms($post->ID, 'pipeline_stage', ['fields' => 'names'])[0] ?? 'Nuovo'
            ];
        }
        
        return rest_ensure_response(['success' => true, 'leads' => $leads, 'total' => $query->found_posts]);
    }
}
