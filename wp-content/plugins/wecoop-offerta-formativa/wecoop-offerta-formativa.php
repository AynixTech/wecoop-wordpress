<?php
/**
 * Plugin Name: WeCoop Offerta Formativa
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione offerta formativa e richieste "Studiare in Italia" per l'app WeCoop
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-offerta-formativa
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

define('WECOOP_OF_VERSION', '1.0.0');
define('WECOOP_OF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_OF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_OF_INCLUDES_DIR', WECOOP_OF_PLUGIN_DIR . 'includes/');
define('WECOOP_OF_FILE', __FILE__);

class WeCoop_Offerta_Formativa {

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
            <p><strong>WeCoop Offerta Formativa</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }

    private function load_dependencies() {
        require_once WECOOP_OF_INCLUDES_DIR . 'post-types/class-offerta-formativa-cpt.php';
        require_once WECOOP_OF_INCLUDES_DIR . 'post-types/class-richiesta-studio-cpt.php';
        require_once WECOOP_OF_INCLUDES_DIR . 'api/class-offerte-formative-endpoint.php';
        require_once WECOOP_OF_INCLUDES_DIR . 'api/class-studiare-italia-endpoint.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);

        add_action('init', [$this, 'register_cpts'], 5);
        add_action('rest_api_init', [$this, 'register_routes'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        if (class_exists('WECOOP_Partner_CPT')) WECOOP_Partner_CPT::init();
        WECOOP_Offerta_Formativa_CPT::init();
        WECOOP_Richiesta_Studio_CPT::init();
    }

    public function register_cpts() {
        WECOOP_Offerta_Formativa_CPT::register_post_type();
        WECOOP_Richiesta_Studio_CPT::register_post_type();
    }

    public function register_routes() {
        WECOOP_Offerte_Formative_Endpoint::register_routes();
        WECOOP_Studiare_Italia_Endpoint::register_routes();
    }

    public function on_activation() {
        WECOOP_Offerta_Formativa_CPT::register_post_type();
        WECOOP_Richiesta_Studio_CPT::register_post_type();
        flush_rewrite_rules();
    }

    public function on_deactivation() {
        flush_rewrite_rules();
    }

    public function enqueue_admin_assets($hook) {
        $screen = get_current_screen();
        if (!$screen) return;
        if (strpos($screen->post_type, 'offerta_formativa') === false &&
            strpos($screen->post_type, 'richiesta_studio') === false) {
            return;
        }
        wp_enqueue_style(
            'wecoop-offerta-formativa-admin',
            WECOOP_OF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_OF_VERSION
        );
    }
}

WeCoop_Offerta_Formativa::get_instance();
