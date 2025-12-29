<?php
/**
 * Plugin Name: WeCoop Users
 * Plugin URI: https://www.wecoop.org
 * Description: Gestione utenti registrati - Lista, approvazione soci, contatto WhatsApp
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-users
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_USERS_VERSION', '1.0.0');
define('WECOOP_USERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_USERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_USERS_INCLUDES_DIR', WECOOP_USERS_PLUGIN_DIR . 'includes/');

/**
 * Classe principale WeCoop Users
 */
class WeCoop_Users {
    
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
        // Admin
        require_once WECOOP_USERS_INCLUDES_DIR . 'admin/class-users-list-page.php';
        require_once WECOOP_USERS_INCLUDES_DIR . 'admin/class-user-detail-page.php';
    }
    
    /**
     * Inizializza hooks
     */
    private function init_hooks() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wecoop-users') === false) {
            return;
        }
        
        wp_enqueue_style('wecoop-users-admin', WECOOP_USERS_PLUGIN_URL . 'assets/css/admin.css', [], WECOOP_USERS_VERSION);
    }
}

// Inizializza plugin
function wecoop_users() {
    return WeCoop_Users::get_instance();
}

add_action('plugins_loaded', 'wecoop_users');
