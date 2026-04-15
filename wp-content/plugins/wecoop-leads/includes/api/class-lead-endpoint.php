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

        // Endpoint pubblico per richieste dall'app (es. Vivere in Italia)
        register_rest_route('wecoop/v1', '/lead-generico', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'create_lead_generico'],
            'permission_callback' => '__return_true',
            'args' => [
                'nome_cognome' => ['required' => true,  'sanitize_callback' => 'sanitize_text_field'],
                'email'        => ['required' => true,  'sanitize_callback' => 'sanitize_email'],
                'service'      => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
            ],
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

    public static function create_lead_generico($request) {
        $nome_cognome = $request->get_param('nome_cognome') ?: '';
        $parts   = explode(' ', trim($nome_cognome), 2);
        $nome    = $parts[0] ?? $nome_cognome;
        $cognome = $parts[1] ?? '';
        $email   = $request->get_param('email') ?: '';
        $service = $request->get_param('service') ?: 'app';

        $post_id = wp_insert_post([
            'post_type'   => 'lead',
            'post_title'  => $nome_cognome,
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['success' => false, 'message' => $post_id->get_error_message()], 500);
        }

        update_post_meta($post_id, 'nome',     $nome);
        update_post_meta($post_id, 'cognome',  $cognome);
        update_post_meta($post_id, 'email',    $email);
        update_post_meta($post_id, 'telefono', $request->get_param('telefono') ?: '');
        update_post_meta($post_id, 'fonte',    'App – ' . ucwords(str_replace('_', ' ', $service)));

        // Salva campi aggiuntivi come note
        $extra = [];
        $extra_keys = ['paese_origine', 'citta', 'ha_permesso', 'ha_casa', 'aiuto_richiesto', 'note'];
        foreach ($extra_keys as $key) {
            $val = $request->get_param($key);
            if ($val !== null && $val !== '') {
                $extra[] = ucwords(str_replace('_', ' ', $key)) . ': ' . sanitize_text_field($val);
            }
        }
        if (!empty($extra)) {
            update_post_meta($post_id, 'note', implode("\n", $extra));
        }

        if (taxonomy_exists('pipeline_stage')) {
            wp_set_object_terms($post_id, 'Nuovo', 'pipeline_stage');
        }

        // Email di notifica admin
        $admin_email  = get_option('admin_email');
        $notify_email = get_option('wecoop_notification_email', $admin_email);
        $site_name    = get_bloginfo('name');
        $edit_url     = admin_url('post.php?post=' . $post_id . '&action=edit');

        $msg  = "Nuova richiesta dall'app WeCoop (" . ucwords(str_replace('_', ' ', $service)) . ").\n\n";
        $msg .= "Nome: $nome_cognome\n";
        $msg .= "Email: $email\n";
        $msg .= ($request->get_param('telefono') ? "Telefono: " . $request->get_param('telefono') . "\n" : '');
        if (!empty($extra)) $msg .= "\n" . implode("\n", $extra) . "\n";
        $msg .= "\nVisualizza nel CRM: $edit_url";

        wp_mail($notify_email, "[$site_name] Nuova richiesta: $nome_cognome", $msg);

        return new WP_REST_Response([
            'success' => true,
            'lead_id' => $post_id,
            'message' => 'Richiesta inviata con successo.',
        ], 201);
    }
}
