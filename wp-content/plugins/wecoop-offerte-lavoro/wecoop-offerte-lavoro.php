<?php
/**
 * Plugin Name: WeCoop Offerte Lavoro
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione offerte e annunci di lavoro per community latina in Italia con API REST per app WECOOP.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-offerte-lavoro
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

 

define('WECOOP_OFFERTE_LAVORO_VERSION', '1.0.0');
define('WECOOP_OFFERTE_LAVORO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_OFFERTE_LAVORO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_OFFERTE_LAVORO_INCLUDES_DIR', WECOOP_OFFERTE_LAVORO_PLUGIN_DIR . 'includes/');

class WeCoop_Offerte_Lavoro {

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

    private function load_dependencies() {
        require_once WECOOP_OFFERTE_LAVORO_INCLUDES_DIR . 'class-wecoop-offerte-lavoro-cpt.php';
        require_once WECOOP_OFFERTE_LAVORO_INCLUDES_DIR . 'class-wecoop-offerte-lavoro-admin.php';
        require_once WECOOP_OFFERTE_LAVORO_INCLUDES_DIR . 'class-wecoop-offerte-lavoro-rest.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);

        WeCoop_Offerte_Lavoro_CPT::init();
        WeCoop_Offerte_Lavoro_Admin::init();
        WeCoop_Offerte_Lavoro_REST::init();
    }

    public function on_activation() {
        WeCoop_Offerte_Lavoro_CPT::register_post_types();
        WeCoop_Offerte_Lavoro_CPT::register_taxonomies();
        WeCoop_Offerte_Lavoro_CPT::register_meta_fields();
        WeCoop_Offerte_Lavoro_CPT::seed_default_terms();

        flush_rewrite_rules();
    }

    public function on_deactivation() {
        flush_rewrite_rules();
    }
}

WeCoop_Offerte_Lavoro::get_instance();
