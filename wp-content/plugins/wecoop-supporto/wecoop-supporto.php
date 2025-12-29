<?php
/**
 * Plugin Name: WeCoop Supporto
 * Plugin URI: https://www.wecoop.org
 * Description: Sistema di richieste supporto con chat WhatsApp integrata
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-supporto
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_SUPPORTO_VERSION', '1.0.0');
define('WECOOP_SUPPORTO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_SUPPORTO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_SUPPORTO_INCLUDES_DIR', WECOOP_SUPPORTO_PLUGIN_DIR . 'includes/');

/**
 * Classe principale WeCoop Supporto
 */
class WeCoop_Supporto {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Carica dipendenze
     */
    private function load_dependencies() {
        // Post Type
        require_once WECOOP_SUPPORTO_INCLUDES_DIR . 'post-types/class-richiesta-supporto.php';
        
        // API
        require_once WECOOP_SUPPORTO_INCLUDES_DIR . 'api/class-supporto-endpoint.php';
        
        // Admin
        require_once WECOOP_SUPPORTO_INCLUDES_DIR . 'admin/class-supporto-list.php';
        require_once WECOOP_SUPPORTO_INCLUDES_DIR . 'admin/class-supporto-detail.php';
    }
    
    /**
     * Inizializza hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        add_action('init', [$this, 'register_post_types']);
        
        // Inizializza API
        if (class_exists('WeCoop_Supporto_Endpoint')) {
            WeCoop_Supporto_Endpoint::init();
        }
    }
    
    /**
     * Registra Post Types
     */
    public function register_post_types() {
        if (class_exists('WeCoop_Richiesta_Supporto_CPT')) {
            $cpt = new WeCoop_Richiesta_Supporto_CPT();
            $cpt->register();
        }
    }
    
    /**
     * Attivazione plugin
     */
    public function on_activation() {
        $this->register_post_types();
        flush_rewrite_rules();
    }
}

// Inizializza plugin
function wecoop_supporto() {
    return WeCoop_Supporto::get_instance();
}

add_action('plugins_loaded', 'wecoop_supporto');
