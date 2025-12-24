<?php
/**
 * Plugin Name: WeCoop Soci
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione Soci WECOOP - Richieste adesione, tessere, profili utente
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-soci
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_SOCI_VERSION', '1.0.0');
define('WECOOP_SOCI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_SOCI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_SOCI_INCLUDES_DIR', WECOOP_SOCI_PLUGIN_DIR . 'includes/');
define('WECOOP_SOCI_FILE', __FILE__);

/**
 * Classe principale WeCoop Soci
 */
class WeCoop_Soci {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Verifica dipendenza wecoop-core
        if (!$this->check_dependencies()) {
            add_action('admin_notices', [$this, 'dependency_notice']);
            return;
        }
        
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Verifica dipendenze
     */
    private function check_dependencies() {
        return class_exists('WeCoop_Core');
    }
    
    /**
     * Notice dipendenza mancante
     */
    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>WeCoop Soci</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    /**
     * Carica dipendenze
     */
    private function load_dependencies() {
        // Post Types
        require_once WECOOP_SOCI_INCLUDES_DIR . 'post-types/class-richiesta-socio.php';
        
        // API
        require_once WECOOP_SOCI_INCLUDES_DIR . 'api/class-soci-endpoint.php';
        
        // Admin
        require_once WECOOP_SOCI_INCLUDES_DIR . 'admin/class-soci-management.php';
        
        // Tessera Handler
        require_once WECOOP_SOCI_INCLUDES_DIR . 'class-tessera-handler.php';
    }
    
    /**
     * Inizializza hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        add_action('init', [$this, 'register_cpts'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Inizializza componenti
        if (class_exists('WECOOP_Soci_Endpoint')) {
            WECOOP_Soci_Endpoint::init();
        }
        if (class_exists('WECOOP_Tessera_Handler')) {
            WECOOP_Tessera_Handler::init();
        }
        if (class_exists('WECOOP_Soci_Management')) {
            WECOOP_Soci_Management::init();
        }
        if (class_exists('WECOOP_Richiesta_Socio_CPT')) {
            WECOOP_Richiesta_Socio_CPT::init();
        }
        
        // AJAX handlers
        add_action('wp_ajax_get_socio_details', [$this, 'ajax_get_socio_details']);
        add_action('wp_ajax_update_socio_details', [$this, 'ajax_update_socio_details']);
    }
    
    /**
     * Registra CPT
     */
    public function register_cpts() {
        // Già gestito da WECOOP_Richiesta_Socio_CPT::init()
    }
    
    /**
     * Attivazione
     */
    public function on_activation() {
        $this->register_cpts();
        flush_rewrite_rules();
    }
    
    /**
     * Disattivazione
     */
    public function on_deactivation() {
        flush_rewrite_rules();
    }
    
    /**
     * Enqueue assets admin
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wecoop-soci') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wecoop-soci-admin',
            WECOOP_SOCI_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_SOCI_VERSION
        );
        
        wp_enqueue_script(
            'wecoop-soci-admin',
            WECOOP_SOCI_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WECOOP_SOCI_VERSION,
            true
        );
        
        wp_localize_script('wecoop-soci-admin', 'wecoopSoci', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('socio_actions_nonce')
        ]);
    }
    
    /**
     * AJAX: Get socio details
     */
    public function ajax_get_socio_details() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'socio_actions_nonce')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => 'Utente non trovato']);
        }
        
        $fields = [
            'numero_tessera', 'nome', 'cognome', 'prefix', 'telefono', 'telefono_completo',
            'citta', 'indirizzo', 'cap', 'provincia', 'codice_fiscale',
            'data_nascita', 'luogo_nascita', 'professione', 
            'paese_provenienza', 'nazionalita',
            'status_socio', 'data_adesione', 'quota_pagata',
            'profilo_completo', 'campi_mancanti', 'percentuale_completamento'
        ];
        
        $data = [
            'id' => $user_id,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name
        ];
        
        foreach ($fields as $field) {
            $value = get_user_meta($user_id, $field, true);
            $data[$field] = $value ?: null;
        }
        
        if (!$data['percentuale_completamento'] && !empty($data['campi_mancanti']) && is_array($data['campi_mancanti'])) {
            $total_fields = 9;
            $missing = count($data['campi_mancanti']);
            $data['percentuale_completamento'] = round((1 - ($missing / $total_fields)) * 100);
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX: Update socio details
     */
    public function ajax_update_socio_details() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'socio_actions_nonce')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => 'Utente non trovato']);
        }
        
        $fields = [
            'nome', 'cognome', 'prefix', 'telefono', 'citta', 'indirizzo',
            'cap', 'provincia', 'codice_fiscale', 'data_nascita', 'luogo_nascita',
            'professione', 'paese_provenienza', 'nazionalita'
        ];
        
        $updated_fields = [];
        
        // Aggiorna email
        if (!empty($_POST['email']) && is_email($_POST['email'])) {
            $email = sanitize_email($_POST['email']);
            
            $existing_user = get_user_by('email', $email);
            if ($existing_user && $existing_user->ID !== $user_id) {
                wp_send_json_error(['message' => 'Email già utilizzata da un altro utente']);
            }
            
            wp_update_user([
                'ID' => $user_id,
                'user_email' => $email
            ]);
            $updated_fields[] = 'email';
        }
        
        // Aggiorna nome e cognome
        if (!empty($_POST['nome']) && !empty($_POST['cognome'])) {
            $nome = sanitize_text_field($_POST['nome']);
            $cognome = sanitize_text_field($_POST['cognome']);
            
            wp_update_user([
                'ID' => $user_id,
                'first_name' => $nome,
                'last_name' => $cognome,
                'display_name' => $nome . ' ' . $cognome
            ]);
            
            update_user_meta($user_id, 'nome', $nome);
            update_user_meta($user_id, 'cognome', $cognome);
            $updated_fields[] = 'nome';
            $updated_fields[] = 'cognome';
        }
        
        // Aggiorna user meta
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                if (!in_array($field, ['nome', 'cognome'])) {
                    $updated_fields[] = $field;
                }
            }
        }
        
        // Telefono completo
        if (!empty($_POST['prefix']) && !empty($_POST['telefono'])) {
            $telefono_completo = '+' . sanitize_text_field($_POST['prefix']) . sanitize_text_field($_POST['telefono']);
            update_user_meta($user_id, 'telefono_completo', $telefono_completo);
        }
        
        // Verifica completamento profilo
        $required_for_complete = [
            'nome' => get_user_meta($user_id, 'nome', true),
            'cognome' => get_user_meta($user_id, 'cognome', true),
            'email' => $user->user_email,
            'telefono' => get_user_meta($user_id, 'telefono', true),
            'citta' => get_user_meta($user_id, 'citta', true),
            'indirizzo' => get_user_meta($user_id, 'indirizzo', true),
            'codice_fiscale' => get_user_meta($user_id, 'codice_fiscale', true),
            'data_nascita' => get_user_meta($user_id, 'data_nascita', true),
            'nazionalita' => get_user_meta($user_id, 'nazionalita', true)
        ];
        
        $profilo_completo = true;
        $campi_mancanti = [];
        
        foreach ($required_for_complete as $field => $value) {
            if (empty($value)) {
                $profilo_completo = false;
                $campi_mancanti[] = $field;
            }
        }
        
        update_user_meta($user_id, 'profilo_completo', $profilo_completo);
        update_user_meta($user_id, 'campi_mancanti', $campi_mancanti);
        
        wp_send_json_success([
            'message' => 'Dati socio aggiornati con successo',
            'updated_fields' => $updated_fields,
            'profilo_completo' => $profilo_completo
        ]);
    }
}

// Inizializza
WeCoop_Soci::get_instance();
