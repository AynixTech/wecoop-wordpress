<?php
/**
 * Goals & Reports
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Goals_Reports {
    
    public static function get_dashboard_stats() {
        $total_leads = wp_count_posts('lead')->publish ?? 0;
        $won_leads = self::count_leads_by_stage('Vinto');
        $conversion_rate = $total_leads > 0 ? round(($won_leads / $total_leads) * 100, 2) : 0;
        
        return [
            'total_leads' => $total_leads,
            'won_leads' => $won_leads,
            'conversion_rate' => $conversion_rate,
            'pipeline_value' => self::get_total_pipeline_value()
        ];
    }
    
    private static function count_leads_by_stage($stage) {
        $query = new WP_Query([
            'post_type' => 'lead',
            'tax_query' => [[
                'taxonomy' => 'pipeline_stage',
                'field' => 'name',
                'terms' => $stage
            ]],
            'fields' => 'ids'
        ]);
        
        return $query->found_posts;
    }
    
    private static function get_total_pipeline_value() {
        global $wpdb;
        
        $total = $wpdb->get_var("
            SELECT SUM(meta_value) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = 'valore_stimato'
        ");
        
        return floatval($total);
    }
}
