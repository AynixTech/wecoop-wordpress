<?php
/**
 * Plugin Name: WeCoop Servizi
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione Richieste Servizi WECOOP
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-servizi
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_SERVIZI_VERSION', '1.0.0');
define('WECOOP_SERVIZI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_SERVIZI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_SERVIZI_INCLUDES_DIR', WECOOP_SERVIZI_PLUGIN_DIR . 'includes/');
define('WECOOP_SERVIZI_FILE', __FILE__);

/**
 * Classe principale WeCoop Servizi
 */
class WeCoop_Servizi {
    
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
            <p><strong>WeCoop Servizi</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        // Normalizer
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'class-servizi-normalizer.php';
        
        // Post Types
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'post-types/class-richiesta-servizio.php';
        
        // API
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'api/class-servizi-endpoint.php';
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'api/class-stripe-payment-intent.php';
        
        // Admin
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'admin/class-servizi-management.php';
        
        // Sistema Pagamenti Custom
        require_once WECOOP_SERVIZI_INCLUDES_DIR . 'class-payment-system.php';
        
        // WooCommerce Integration (deprecato)
        if (class_exists('WooCommerce')) {
            require_once WECOOP_SERVIZI_INCLUDES_DIR . 'class-woocommerce-integration.php';
        }
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        add_action('init', [$this, 'register_cpts'], 10);
        
        // Inizializza componenti
        if (class_exists('WECOOP_Servizi_Endpoint')) {
            WECOOP_Servizi_Endpoint::init();
        }
        if (class_exists('WECOOP_Servizi_Management')) {
            WECOOP_Servizi_Management::init();
        }
        if (class_exists('WECOOP_Servizi_Payment_System')) {
            WECOOP_Servizi_Payment_System::init();
        }
        if (class_exists('WeCoop_Stripe_Payment_Intent')) {
            WeCoop_Stripe_Payment_Intent::init();
        }
        if (class_exists('WECOOP_Servizi_WooCommerce_Integration')) {
            WECOOP_Servizi_WooCommerce_Integration::init();
        }
    }
    
    public function register_cpts() {
        if (class_exists('WECOOP_Richiesta_Servizio_CPT')) {
            WECOOP_Richiesta_Servizio_CPT::register_post_type();
        }
    }
    
    public function on_activation() {
        $this->register_cpts();
        flush_rewrite_rules();
    }
    
    public function on_deactivation() {
        flush_rewrite_rules();
    }
}

// Inizializza
WeCoop_Servizi::get_instance();
