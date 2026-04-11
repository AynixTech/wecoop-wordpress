<?php
/**
 * Plugin Name: WeCoop Progetti e Opportunita
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione contenuti Progetti e Opportunita per app e backoffice operativo.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-opportunita
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WECOOP_OPPORTUNITA_VERSION', '1.0.0');
define('WECOOP_OPPORTUNITA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_OPPORTUNITA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_OPPORTUNITA_INCLUDES_DIR', WECOOP_OPPORTUNITA_PLUGIN_DIR . 'includes/');

class WeCoop_Opportunita {

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
        echo '<div class="notice notice-error"><p><strong>WeCoop Progetti e Opportunita</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p></div>';
    }

    private function load_dependencies() {
        require_once WECOOP_OPPORTUNITA_INCLUDES_DIR . 'class-wecoop-opportunita-cpt.php';
        require_once WECOOP_OPPORTUNITA_INCLUDES_DIR . 'class-wecoop-opportunita-admin.php';
        require_once WECOOP_OPPORTUNITA_INCLUDES_DIR . 'class-wecoop-opportunita-rest.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);

        WeCoop_Opportunita_CPT::init();
        WeCoop_Opportunita_Admin::init();
        WeCoop_Opportunita_REST::init();
    }

    public function on_activation() {
        WeCoop_Opportunita_CPT::register_post_type();
        WeCoop_Opportunita_CPT::register_taxonomies();
        WeCoop_Opportunita_CPT::register_meta_fields();
        WeCoop_Opportunita_CPT::seed_default_terms();

        flush_rewrite_rules();
    }

    public function on_deactivation() {
        flush_rewrite_rules();
    }
}

WeCoop_Opportunita::get_instance();
