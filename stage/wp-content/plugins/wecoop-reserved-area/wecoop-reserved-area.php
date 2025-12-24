<?php
/**
 * Plugin Name: WeCoop Reserved Area
 * Plugin URI: https://www.stage.wecoop.org
 * Description: Area riservata custom con login, registrazione e gestione profilo
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.stage.wecoop.org
 * Text Domain: wecoop-reserved-area
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_RESERVED_AREA_VERSION', '1.0.0');
define('WECOOP_RESERVED_AREA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_RESERVED_AREA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_RESERVED_AREA_INCLUDES_DIR', WECOOP_RESERVED_AREA_PLUGIN_DIR . 'includes/');

/**
 * Classe principale WeCoop Reserved Area
 */
class WeCoop_Reserved_Area {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        if (!$this->check_dependencies()) {
            add_action('admin_notices', [$this, 'dependency_notice']);
            return;
        }
        
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function check_dependencies() {
        return class_exists('WeCoop_Core');
    }
    
    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>WeCoop Reserved Area</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        require_once WECOOP_RESERVED_AREA_INCLUDES_DIR . 'class-auth-handler.php';
        require_once WECOOP_RESERVED_AREA_INCLUDES_DIR . 'class-shortcodes.php';
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'register_shortcodes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_nopriv_wecoop_login', [$this, 'ajax_login']);
        add_action('wp_ajax_nopriv_wecoop_register', [$this, 'ajax_register']);
        add_action('wp_ajax_wecoop_logout', [$this, 'ajax_logout']);
        add_action('wp_ajax_wecoop_update_profile', [$this, 'ajax_update_profile']);
        
        // Inizializza componenti
        WeCoop_RA_Shortcodes::init();
    }
    
    public function register_shortcodes() {
        add_shortcode('wecoop_login', [WeCoop_RA_Shortcodes::class, 'login_form']);
        add_shortcode('wecoop_register', [WeCoop_RA_Shortcodes::class, 'register_form']);
        add_shortcode('wecoop_profile', [WeCoop_RA_Shortcodes::class, 'profile_page']);
        add_shortcode('wecoop_dashboard', [WeCoop_RA_Shortcodes::class, 'dashboard']);
    }
    
    public function enqueue_assets() {
        if ($this->is_reserved_area_page()) {
            wp_enqueue_style(
                'wecoop-reserved-area',
                WECOOP_RESERVED_AREA_PLUGIN_URL . 'assets/css/reserved-area.css',
                [],
                WECOOP_RESERVED_AREA_VERSION
            );
            
            wp_enqueue_script(
                'wecoop-reserved-area',
                WECOOP_RESERVED_AREA_PLUGIN_URL . 'assets/js/reserved-area.js',
                ['jquery'],
                WECOOP_RESERVED_AREA_VERSION,
                true
            );
            
            wp_localize_script('wecoop-reserved-area', 'wecoopRA', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wecoop_ra_nonce'),
                'homeurl' => home_url()
            ]);
        }
    }
    
    private function is_reserved_area_page() {
        global $post;
        if (!$post) return false;
        
        return has_shortcode($post->post_content, 'wecoop_login') ||
               has_shortcode($post->post_content, 'wecoop_register') ||
               has_shortcode($post->post_content, 'wecoop_profile') ||
               has_shortcode($post->post_content, 'wecoop_dashboard');
    }
    
    /**
     * AJAX: Login
     */
    public function ajax_login() {
        check_ajax_referer('wecoop_ra_nonce', 'nonce');
        
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(['message' => 'Username e password sono obbligatori']);
        }
        
        // Tenta login
        $user = WeCoop_RA_Auth_Handler::authenticate($username, $password);
        
        if (is_wp_error($user)) {
            wp_send_json_error(['message' => $user->get_error_message()]);
        }
        
        // Login riuscito
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        wp_send_json_success([
            'message' => 'Login effettuato con successo',
            'user' => [
                'id' => $user->ID,
                'nome' => get_user_meta($user->ID, 'nome', true),
                'cognome' => get_user_meta($user->ID, 'cognome', true),
                'email' => $user->user_email
            ],
            'redirect' => home_url('/area-riservata/dashboard/')
        ]);
    }
    
    /**
     * AJAX: Register
     */
    public function ajax_register() {
        check_ajax_referer('wecoop_ra_nonce', 'nonce');
        
        $nome = sanitize_text_field($_POST['nome']);
        $cognome = sanitize_text_field($_POST['cognome']);
        $email = sanitize_email($_POST['email']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $password = $_POST['password'];
        
        // Validazione
        if (empty($nome) || empty($cognome) || empty($email) || empty($password)) {
            wp_send_json_error(['message' => 'Tutti i campi sono obbligatori']);
        }
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Email non valida']);
        }
        
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'Questa email è già registrata']);
        }
        
        if (strlen($password) < 8) {
            wp_send_json_error(['message' => 'La password deve essere di almeno 8 caratteri']);
        }
        
        // Crea utente
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }
        
        // Aggiorna dati utente
        wp_update_user([
            'ID' => $user_id,
            'first_name' => $nome,
            'last_name' => $cognome,
            'display_name' => $nome . ' ' . $cognome
        ]);
        
        // Salva meta
        update_user_meta($user_id, 'nome', $nome);
        update_user_meta($user_id, 'cognome', $cognome);
        if (!empty($telefono)) {
            update_user_meta($user_id, 'telefono', $telefono);
        }
        
        // Assegna ruolo socio
        $user = new WP_User($user_id);
        $user->set_role('socio');
        
        // Login automatico
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success([
            'message' => 'Registrazione completata con successo',
            'redirect' => home_url('/area-riservata/dashboard/')
        ]);
    }
    
    /**
     * AJAX: Logout
     */
    public function ajax_logout() {
        check_ajax_referer('wecoop_ra_nonce', 'nonce');
        
        wp_logout();
        wp_send_json_success([
            'message' => 'Logout effettuato',
            'redirect' => home_url('/area-riservata/login/')
        ]);
    }
    
    /**
     * AJAX: Update Profile
     */
    public function ajax_update_profile() {
        check_ajax_referer('wecoop_ra_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Devi essere autenticato']);
        }
        
        $user_id = get_current_user_id();
        
        // Aggiorna dati
        $fields = ['nome', 'cognome', 'telefono', 'citta', 'indirizzo', 'cap', 'provincia'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Aggiorna email se fornita
        if (!empty($_POST['email']) && is_email($_POST['email'])) {
            $email = sanitize_email($_POST['email']);
            $existing = get_user_by('email', $email);
            
            if ($existing && $existing->ID !== $user_id) {
                wp_send_json_error(['message' => 'Email già utilizzata da un altro utente']);
            }
            
            wp_update_user([
                'ID' => $user_id,
                'user_email' => $email
            ]);
        }
        
        // Cambia password se fornita
        if (!empty($_POST['new_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            
            $user = get_userdata($user_id);
            if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
                wp_send_json_error(['message' => 'Password attuale non corretta']);
            }
            
            wp_set_password($new_password, $user_id);
        }
        
        wp_send_json_success(['message' => 'Profilo aggiornato con successo']);
    }
}

// Inizializza
WeCoop_Reserved_Area::get_instance();
