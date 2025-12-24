<?php
/**
 * Plugin Name: WeCoop Notifications
 * Plugin URI: https://www.wecoop.org
 * Description: Sistema Push Notifications con Firebase Cloud Messaging (FCM v1 + Legacy)
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-notifications
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_NOTIFICATIONS_VERSION', '1.0.0');
define('WECOOP_NOTIFICATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_NOTIFICATIONS_INCLUDES_DIR', WECOOP_NOTIFICATIONS_PLUGIN_DIR . 'includes/');
define('WECOOP_NOTIFICATIONS_FILE', __FILE__);

/**
 * Classe principale WeCoop Notifications
 */
class WeCoop_Notifications {
    
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
            <p><strong>WeCoop Notifications</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        // API
        require_once WECOOP_NOTIFICATIONS_INCLUDES_DIR . 'api/class-push-endpoint.php';
        require_once WECOOP_NOTIFICATIONS_INCLUDES_DIR . 'api/class-push-token-endpoint.php';
        
        // Push System
        require_once WECOOP_NOTIFICATIONS_INCLUDES_DIR . 'push/class-push-integrations.php';
        require_once WECOOP_NOTIFICATIONS_INCLUDES_DIR . 'push/push-helpers.php';
        
        // Admin
        require_once WECOOP_NOTIFICATIONS_INCLUDES_DIR . 'admin/class-push-notifications-admin.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        
        add_action('admin_menu', [$this, 'add_admin_menu'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Inizializza componenti
        if (class_exists('WECOOP_Push_Endpoint')) {
            WECOOP_Push_Endpoint::init();
        }
        if (class_exists('WECOOP_Push_Token_Endpoint')) {
            WECOOP_Push_Token_Endpoint::init();
        }
        if (class_exists('WECOOP_Push_Integrations')) {
            WECOOP_Push_Integrations::init();
        }
        if (class_exists('WECOOP_Push_Notifications_Admin')) {
            WECOOP_Push_Notifications_Admin::init();
        }
    }
    
    public function on_activation() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabella push_logs per storico notifiche inviate
        $table_logs = $wpdb->prefix . 'wecoop_push_logs';
        $sql_logs = "CREATE TABLE IF NOT EXISTS $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            recipient_type varchar(50) NOT NULL,
            recipient_value text,
            title varchar(255) NOT NULL,
            body text NOT NULL,
            data longtext,
            status varchar(50) DEFAULT 'pending',
            sent_at datetime DEFAULT NULL,
            scheduled_for datetime DEFAULT NULL,
            response longtext,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY sent_at (sent_at)
        ) $charset_collate;";
        
        // Tabella push_tokens per FCM tokens degli utenti
        $table_tokens = $wpdb->prefix . 'wecoop_push_tokens';
        $sql_tokens = "CREATE TABLE IF NOT EXISTS $table_tokens (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            token text NOT NULL,
            device_info varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id),
            KEY updated_at (updated_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_logs);
        dbDelta($sql_tokens);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Push Notifications',
            'Push Notifications',
            'manage_options',
            'wecoop-push-notifications',
            [__CLASS__, 'render_admin_page'],
            'dashicons-megaphone',
            30
        );
    }
    
    public static function render_admin_page() {
        if (class_exists('WECOOP_Push_Notifications_Admin')) {
            $admin = new WECOOP_Push_Notifications_Admin();
            $admin->render_admin_page();
        }
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wecoop-push-notifications') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wecoop-notifications-admin',
            WECOOP_NOTIFICATIONS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_NOTIFICATIONS_VERSION
        );
        
        wp_enqueue_script(
            'wecoop-notifications-admin',
            WECOOP_NOTIFICATIONS_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WECOOP_NOTIFICATIONS_VERSION,
            true
        );
        
        wp_localize_script('wecoop-notifications-admin', 'wecoopNotifications', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wecoop_push_nonce')
        ]);
    }
}

// Inizializza
WeCoop_Notifications::get_instance();
