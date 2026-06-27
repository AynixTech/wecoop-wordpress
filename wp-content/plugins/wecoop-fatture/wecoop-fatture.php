<?php
/**
 * Plugin Name: WeCoop Fatture
 * Plugin URI: https://www.wecoop.org
 * Description: Backoffice per consultare e scaricare tutte le fatture/ricevute generate dai pagamenti WECOOP.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-fatture
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-servizi
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_FATTURE_VERSION', '1.0.0');
define('WECOOP_FATTURE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_FATTURE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_FATTURE_INCLUDES_DIR', WECOOP_FATTURE_PLUGIN_DIR . 'includes/');
define('WECOOP_FATTURE_FILE', __FILE__);

/**
 * Classe principale WeCoop Fatture
 */
class WeCoop_Fatture {

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

    /**
     * Il plugin dipende da wecoop-servizi (tabella pagamenti + generatore ricevute).
     */
    private function check_dependencies() {
        return class_exists('WeCoop_Ricevuta_PDF');
    }

    public function dependency_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>WeCoop Fatture</strong> richiede il plugin <strong>WeCoop Servizi</strong> attivo.</p>
        </div>
        <?php
    }

    private function load_dependencies() {
        require_once WECOOP_FATTURE_INCLUDES_DIR . 'admin/class-fatture-admin.php';
    }

    private function init_hooks() {
        if (class_exists('WECOOP_Fatture_Admin')) {
            WECOOP_Fatture_Admin::init();
        }
    }
}

// Inizializza
WeCoop_Fatture::get_instance();
