<?php
/**
 * Plugin Name: WeCoop WhatsApp
 * Plugin URI: https://www.stage.wecoop.org
 * Description: Integrazione WhatsApp Business API per WECOOP
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.stage.wecoop.org
 * Text Domain: wecoop-whatsapp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_WHATSAPP_VERSION', '1.0.0');
define('WECOOP_WHATSAPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_WHATSAPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_WHATSAPP_INCLUDES_DIR', WECOOP_WHATSAPP_PLUGIN_DIR . 'includes/');
define('WECOOP_WHATSAPP_FILE', __FILE__);

/**
 * Classe principale WeCoop WhatsApp
 */
class WeCoop_WhatsApp_Plugin {
    
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
            <p><strong>WeCoop WhatsApp</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        require_once WECOOP_WHATSAPP_INCLUDES_DIR . 'whatsapp/class-whatsapp.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        
        // Inizializza componenti
        if (class_exists('WECOOP_WhatsApp')) {
            WECOOP_WhatsApp::init();
        }
    }
    
    public function on_activation() {
        // Opzioni default
        if (!get_option('wecoop_whatsapp_options')) {
            update_option('wecoop_whatsapp_options', [
                'enable_whatsapp' => true,
                'api_key' => '',
                'phone_number' => '',
            ]);
        }
    }
}

// Inizializza
WeCoop_WhatsApp_Plugin::get_instance();
