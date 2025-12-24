<?php
/**
 * Plugin Name: WeCoop Core
 * Plugin URI: https://www.stage.wecoop.org
 * Description: Funzionalità core di WeCoop CRM - Gestione base soci, ruoli e autenticazione
 * Version: 1.0.0
 * Author: WeCoop
 * Author URI: https://www.stage.wecoop.org
 * Text Domain: wecoop-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Plugin Priority: 1
 */

if (!defined('ABSPATH')) exit;

// Forza caricamento prioritario
add_filter('pre_update_option_active_plugins', function($plugins) {
    if (is_array($plugins)) {
        $core = 'wecoop-core/wecoop-core.php';
        $key = array_search($core, $plugins);
        if ($key !== false) {
            unset($plugins[$key]);
            array_unshift($plugins, $core);
        }
    }
    return $plugins;
});

// Costanti plugin
define('WECOOP_CORE_VERSION', '1.0.0');
define('WECOOP_CORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_CORE_INCLUDES_DIR', WECOOP_CORE_PLUGIN_DIR . 'includes/');

/**
 * Classe principale WeCoop Core
 */
class WeCoop_Core {
    
    private static $instance = null;
    
    /**
     * Singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Costruttore
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Carica dipendenze
     */
    private function load_dependencies() {
        // Core
        require_once WECOOP_CORE_INCLUDES_DIR . 'class-socio-role.php';
        require_once WECOOP_CORE_INCLUDES_DIR . 'class-auth-handler.php';
        require_once WECOOP_CORE_INCLUDES_DIR . 'class-user-meta.php';
        require_once WECOOP_CORE_INCLUDES_DIR . 'class-email-template-unified.php';
        require_once WECOOP_CORE_INCLUDES_DIR . 'class-multilingual-email.php';
    }
    
    /**
     * Inizializza hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        add_action('init', [$this, 'load_textdomain']);
        
        // Inizializza componenti
        WeCoop_Socio_Role::init();
        WeCoop_Auth_Handler::init();
        WeCoop_User_Meta::init();
    }
    
    /**
     * Attivazione plugin
     */
    public function on_activation() {
        // Crea ruolo "Socio"
        add_role('socio', __('Socio WECOOP', 'wecoop-core'), [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ]);
        
        flush_rewrite_rules();
    }
    
    /**
     * Disattivazione plugin
     */
    public function on_deactivation() {
        flush_rewrite_rules();
    }
    
    /**
     * Carica traduzioni
     */
    public function load_textdomain() {
        load_plugin_textdomain('wecoop-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Inizializza plugin
WeCoop_Core::get_instance();
