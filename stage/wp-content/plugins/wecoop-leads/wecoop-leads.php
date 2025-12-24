<?php
/**
 * Plugin Name: WeCoop Leads
 * Plugin URI: https://www.stage.wecoop.org
 * Description: CRM Pipeline stile NoCRM.io - Lead Management, Goals, Reports, Import/Export
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.stage.wecoop.org
 * Text Domain: wecoop-leads
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) exit;

// Costanti
define('WECOOP_LEADS_VERSION', '1.0.0');
define('WECOOP_LEADS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOOP_LEADS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOOP_LEADS_INCLUDES_DIR', WECOOP_LEADS_PLUGIN_DIR . 'includes/');
define('WECOOP_LEADS_FILE', __FILE__);

/**
 * Classe principale WeCoop Leads
 */
class WeCoop_Leads {
    
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
            <p><strong>WeCoop Leads</strong> richiede il plugin <strong>WeCoop Core</strong> attivo.</p>
        </div>
        <?php
    }
    
    private function load_dependencies() {
        // CRM Components
        require_once WECOOP_LEADS_INCLUDES_DIR . 'crm/class-lead-cpt.php';
        require_once WECOOP_LEADS_INCLUDES_DIR . 'crm/class-pipeline-manager.php';
        require_once WECOOP_LEADS_INCLUDES_DIR . 'crm/class-goals-reports.php';
        require_once WECOOP_LEADS_INCLUDES_DIR . 'crm/class-import-export.php';
        
        // API
        require_once WECOOP_LEADS_INCLUDES_DIR . 'api/class-lead-endpoint.php';
        
        // Admin
        require_once WECOOP_LEADS_INCLUDES_DIR . 'admin/class-crm-menu.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'on_activation']);
        register_deactivation_hook(__FILE__, [$this, 'on_deactivation']);
        
        add_action('init', [$this, 'register_cpts'], 5);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Inizializza componenti
        if (class_exists('WECOOP_Lead_API')) {
            WECOOP_Lead_API::init();
        }
    }
    
    public function register_cpts() {
        if (class_exists('WECOOP_Lead_CPT')) {
            WECOOP_Lead_CPT::register_post_type();
            WECOOP_Lead_CPT::register_taxonomies();
        }
    }
    
    public function on_activation() {
        $this->register_cpts();
        flush_rewrite_rules();
        
        // Crea opzioni default
        if (!get_option('wecoop_leads_options')) {
            update_option('wecoop_leads_options', [
                'enable_lead_pipeline' => true,
                'enable_goals' => true,
                'enable_reports' => true,
            ]);
        }
    }
    
    public function on_deactivation() {
        flush_rewrite_rules();
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wecoop-crm') === false && get_post_type() !== 'lead') {
            return;
        }
        
        wp_enqueue_style(
            'wecoop-leads-admin',
            WECOOP_LEADS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WECOOP_LEADS_VERSION
        );
        
        wp_enqueue_script(
            'wecoop-leads-admin',
            WECOOP_LEADS_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WECOOP_LEADS_VERSION,
            true
        );
    }
}

// Inizializza
WeCoop_Leads::get_instance();
