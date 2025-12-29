<?php
/**
 * Pagina Dettaglio Utente
 * Include form completamento profilo, approvazione socio e WhatsApp
 */

if (!defined('ABSPATH')) exit;

class WeCoop_User_Detail_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_wecoop_users_completa_profilo', [$this, 'handle_completa_profilo']);
        add_action('admin_post_wecoop_users_approva_socio', [$this, 'handle_approva_socio']);
        add_action('admin_post_wecoop_users_revoca_socio', [$this, 'handle_revoca_socio']);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            null, // Hidden submenu
            'Dettaglio Utente',
            'Dettaglio Utente',
            'manage_options',
            'wecoop-user-detail',
            [$this, 'render_page']
        );
    }
    
    public function handle_completa_profilo() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_completa_profilo');
        
        $user_id = intval($_POST['user_id']);
        
        // Aggiorna profilo utente
        $update_data = [
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'display_name' => sanitize_text_field($_POST['display_name'])
        ];
        
        $result = wp_update_user($update_data);
        
        if (!is_wp_error($result)) {
            // Aggiorna user meta
            update_user_meta($user_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
            update_user_meta($user_id, 'citta', sanitize_text_field($_POST['citta']));
            update_user_meta($user_id, 'cap', sanitize_text_field($_POST['cap']));
            update_user_meta($user_id, 'provincia', strtoupper(sanitize_text_field($_POST['provincia'])));
            update_user_meta($user_id, 'codice_fiscale', strtoupper(sanitize_text_field($_POST['codice_fiscale'])));
            update_user_meta($user_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
            update_user_meta($user_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
            update_user_meta($user_id, 'profilo_completo', true);
            
            // Aggiorna anche i meta nel post richiesta_socio
            $richiesta = get_posts([
                'post_type' => 'richiesta_socio',
                'meta_key' => 'user_id_socio',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ]);
            
            if (!empty($richiesta)) {
                $post_id = $richiesta[0]->ID;
                update_post_meta($post_id, 'email', sanitize_email($_POST['email']));
                update_post_meta($post_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
                update_post_meta($post_id, 'citta', sanitize_text_field($_POST['citta']));
                update_post_meta($post_id, 'cap', sanitize_text_field($_POST['cap']));
                update_post_meta($post_id, 'provincia', strtoupper(sanitize_text_field($_POST['provincia'])));
                update_post_meta($post_id, 'codice_fiscale', strtoupper(sanitize_text_field($_POST['codice_fiscale'])));
                update_post_meta($post_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
                update_post_meta($post_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
                update_post_meta($post_id, 'profilo_completo', true);
            }
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'profilo_salvato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode($result->get_error_message())
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function handle_approva_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_approva_socio');
        
        $user_id = intval($_POST['user_id']);
        
        // Trova richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            $post_id = $richiesta[0]->ID;
            
            // Aggiorna status
            update_post_meta($post_id, 'is_socio', true);
            update_post_meta($post_id, 'data_approvazione', current_time('mysql'));
            update_user_meta($user_id, 'is_socio', true);
            
            // Cambia ruolo a 'socio'
            $user = new WP_User($user_id);
            if (!get_role('socio')) {
                add_role('socio', 'Socio', get_role('subscriber')->capabilities);
            }
            $user->set_role('socio');
            
            // Cambia status post
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish'
            ]);
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'socio_approvato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore_richiesta'
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function handle_revoca_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_revoca_socio');
        
        $user_id = intval($_POST['user_id']);
        
        update_user_meta($user_id, 'is_socio', false);
        
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            update_post_meta($richiesta[0]->ID, 'is_socio', false);
            update_post_meta($richiesta[0]->ID, 'data_revoca', current_time('mysql'));
        }
        
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-user-detail',
            'user_id' => $user_id,
            'message' => 'socio_revocato'
        ], admin_url('admin.php')));
        exit;
    }
    
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        if (!$user_id) {
            echo '<div class="wrap">';
            echo '<h1>Dettaglio Utente</h1>';
            echo '<div class="notice notice-error"><p>ID utente non specificato.</p></div>';
            echo '</div>';
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            echo '<div class="wrap">';
            echo '<h1>Dettaglio Utente</h1>';
            echo '<div class="notice notice-error"><p>Utente non trovato.</p></div>';
            echo '</div>';
            return;
        }
        
        // Recupera dati utente
        $is_socio = get_user_meta($user_id, 'is_socio', true);
        $profilo_completo = get_user_meta($user_id, 'profilo_completo', true);
        $telefono_completo = get_user_meta($user_id, 'telefono_completo', true) ?: $user->user_login;
        $indirizzo = get_user_meta($user_id, 'indirizzo', true);
        $citta = get_user_meta($user_id, 'citta', true);
        $cap = get_user_meta($user_id, 'cap', true);
        $provincia = get_user_meta($user_id, 'provincia', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        $data_nascita = get_user_meta($user_id, 'data_nascita', true);
        $luogo_nascita = get_user_meta($user_id, 'luogo_nascita', true);
        
        // Recupera richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        $numero_pratica = '';
        $data_registrazione = $user->user_registered;
        if (!empty($richiesta)) {
            $numero_pratica = get_post_meta($richiesta[0]->ID, 'numero_pratica', true);
            $data_registrazione = $richiesta[0]->post_date;
        }
        
        // Messaggio WhatsApp
        $whatsapp_number = preg_replace('/[^0-9]/', '', $telefono_completo);
        $whatsapp_message = "Ciao {$user->display_name}, ti contattiamo da WeCoop per completare il tuo profilo socio.";
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($whatsapp_message);
        
        // Messaggi
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        
        require WECOOP_USERS_PLUGIN_DIR . 'templates/user-detail.php';
    }
}

new WeCoop_User_Detail_Page();
