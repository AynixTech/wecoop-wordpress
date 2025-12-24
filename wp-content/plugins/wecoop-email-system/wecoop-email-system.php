<?php
/**
 * Plugin Name: WeCoop Email System
 * Plugin URI: https://www.wecoop.org
 * Description: Sistema Email WECOOP - Template, Manager, Tracking, i18n
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-email-system
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_EMAIL_SYSTEM_VERSION', '1.0.0');
define('WECOOP_EMAIL_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_EMAIL_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_EMAIL_SYSTEM_INCLUDES_DIR', WECOOP_EMAIL_SYSTEM_PLUGIN_DIR . 'includes/');
define('WECOOP_EMAIL_SYSTEM_FILE', __FILE__);

/**
 * Classe principale WeCoop Email System
 */
class WeCoop_Email_System {
    
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
            <p><strong>WeCoop Email System</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        // Email Components
        require_once WECOOP_EMAIL_SYSTEM_INCLUDES_DIR . 'emails/class-email-i18n.php';
        require_once WECOOP_EMAIL_SYSTEM_INCLUDES_DIR . 'emails/class-email-template.php';
        require_once WECOOP_EMAIL_SYSTEM_INCLUDES_DIR . 'emails/class-email-manager.php';
        require_once WECOOP_EMAIL_SYSTEM_INCLUDES_DIR . 'emails/class-email-tracker.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        
        // Inizializza componenti
        if (class_exists('WECOOP_Email_Manager')) {
            WECOOP_Email_Manager::init();
        }
        if (class_exists('WECOOP_Email_Tracker')) {
            WECOOP_Email_Tracker::init();
        }
    }
    
    public function on_activation() {
        global $wpdb;
        
        // Tabella email tracking
        $table_name = $wpdb->prefix . 'wecoop_email_tracking';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            recipient_email varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            template varchar(100),
            status varchar(50) DEFAULT 'sent',
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            opened_at datetime DEFAULT NULL,
            clicked_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY recipient_email (recipient_email),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Opzioni default
        if (!get_option('wecoop_email_options')) {
            update_option('wecoop_email_options', [
                'enable_tracking' => true,
                'enable_templates' => true,
            ]);
        }
    }
}

// Inizializza
WeCoop_Email_System::get_instance();
