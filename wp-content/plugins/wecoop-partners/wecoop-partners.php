<?php
/**
 * Plugin Name: WeCoop Partners
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione Partner/Aziende di WeCoop - Logo, nome e informazioni partner
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-partners
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_PARTNERS_VERSION', '1.0.0');
define('WECOOP_PARTNERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_PARTNERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_PARTNERS_INCLUDES_DIR', WECOOP_PARTNERS_PLUGIN_DIR . 'includes/');
define('WECOOP_PARTNERS_FILE', __FILE__);

/**
 * Classe principale WeCoop Partners
 */
class WeCoop_Partners {

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
            <p><strong>WeCoop Partners</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }

    private function load_dependencies() {
        require_once WECOOP_PARTNERS_INCLUDES_DIR . 'post-types/class-partner.php';
        require_once WECOOP_PARTNERS_INCLUDES_DIR . 'api/class-partners-endpoint.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);

        add_action('init', [$this, 'register_cpts'], 10);
        add_action('rest_api_init', [$this, 'init_rest_api'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        WECOOP_Partner_CPT::init();
        WECOOP_Partners_Endpoint::init();
    }

    public function init_rest_api() {
        WECOOP_Partners_Endpoint::register_routes();
    }

    public function register_cpts() {
        if (class_exists('WECOOP_Partner_CPT')) {
            WECOOP_Partner_CPT::register_post_type();
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
        if ($post_type !== 'partner') {
            return;
        }
        wp_enqueue_style(
            'wecoop-partners-admin',
            WECOOP_PARTNERS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_PARTNERS_VERSION
        );
    }
}

// Bootstrap
add_action('plugins_loaded', function () {
    WeCoop_Partners::get_instance();
}, 20);
