<?php
/**
 * CRM Menu
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_CRM_Menu {
    
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
    }
    
    public static function add_menu() {
        add_menu_page(
            'CRM Dashboard',
            'CRM',
            'manage_options',
            'wecoop-crm',
            [__CLASS__, 'render_dashboard'],
            'dashicons-chart-line',
            25
        );
        
        add_submenu_page(
            'wecoop-crm',
            'Pipeline',
            'Pipeline',
            'manage_options',
            'wecoop-pipeline',
            [__CLASS__, 'render_pipeline']
        );
        
        add_submenu_page(
            'wecoop-crm',
            'Goals & Reports',
            'Goals & Reports',
            'manage_options',
            'wecoop-goals',
            [__CLASS__, 'render_goals']
        );
    }
    
    public static function render_dashboard() {
        $stats = WECOOP_Goals_Reports::get_dashboard_stats();
        ?>
        <div class="wrap">
            <h1>CRM Dashboard</h1>
            <div class="wecoop-stats-grid">
                <div class="stat-box">
                    <h3>Totale Lead</h3>
                    <p class="stat-number"><?php echo $stats['total_leads']; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Lead Vinti</h3>
                    <p class="stat-number"><?php echo $stats['won_leads']; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Tasso Conversione</h3>
                    <p class="stat-number"><?php echo $stats['conversion_rate']; ?>%</p>
                </div>
                <div class="stat-box">
                    <h3>Valore Pipeline</h3>
                    <p class="stat-number">€<?php echo number_format($stats['pipeline_value'], 2); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    public static function render_pipeline() {
        echo '<div class="wrap"><h1>Pipeline</h1><p>Vista Pipeline in sviluppo</p></div>';
    }
    
    public static function render_goals() {
        echo '<div class="wrap"><h1>Goals & Reports</h1><p>Report in sviluppo</p></div>';
    }
}

WECOOP_CRM_Menu::init();
