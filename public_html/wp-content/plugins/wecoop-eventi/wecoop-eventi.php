<?php
/**
 * Plugin Name: WeCoop Eventi
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione Eventi WECOOP con campi multilingua
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-eventi
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_EVENTI_VERSION', '1.0.0');
define('WECOOP_EVENTI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_EVENTI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_EVENTI_INCLUDES_DIR', WECOOP_EVENTI_PLUGIN_DIR . 'includes/');
define('WECOOP_EVENTI_FILE', __FILE__);

/**
 * Classe principale WeCoop Eventi
 */
class WeCoop_Eventi {
    
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
            <p><strong>WeCoop Eventi</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        // Post Types
        require_once WECOOP_EVENTI_INCLUDES_DIR . 'post-types/class-evento.php';
        
        // API
        require_once WECOOP_EVENTI_INCLUDES_DIR . 'api/class-eventi-endpoint.php';
        
        // Admin
        require_once WECOOP_EVENTI_INCLUDES_DIR . 'admin/class-eventi-admin.php';
        require_once WECOOP_EVENTI_INCLUDES_DIR . 'admin/class-eventi-management.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        add_action('init', [$this, 'register_cpts'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('rest_api_init', [$this, 'init_rest_api'], 20);
        
        // Inizializza componenti
        WECOOP_Evento_CPT::init();
        WECOOP_Eventi_Endpoint::init();
        WECOOP_Eventi_Admin::init();
        WECOOP_Eventi_Management::init();
    }
    
    public function init_rest_api() {
        // Forza re-registrazione endpoint
        WECOOP_Eventi_Endpoint::register_routes();
    }
    
    public function register_cpts() {
        if (class_exists('WECOOP_Evento_CPT')) {
            WECOOP_Evento_CPT::register_post_type();
        }
    }
    
    public function on_activation() {
        $this->register_cpts();
        flush_rewrite_rules();
    }
    
    public function on_deactivation() {
        flush_rewrite_rules();
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ($post_type !== 'evento') {
            return;
        }
        
        wp_enqueue_style(
            'wecoop-eventi-admin',
            WECOOP_EVENTI_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_EVENTI_VERSION
        );
        
        wp_enqueue_script(
            'wecoop-eventi-admin',
            WECOOP_EVENTI_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WECOOP_EVENTI_VERSION,
            true
        );
    }
}

// Inizializza
WeCoop_Eventi::get_instance();
