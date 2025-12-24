<?php
/**
 * Pipeline Manager
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Pipeline_Manager {
    
    public static function init() {
        add_action('wp_ajax_wecoop_change_lead_stage', [__CLASS__, 'ajax_change_stage']);
    }
    
    public static function ajax_change_stage() {
        check_ajax_referer('wecoop_crm_nonce', 'nonce');
        
        $lead_id = intval($_POST['lead_id']);
        $new_stage = sanitize_text_field($_POST['stage']);
        
        wp_set_object_terms($lead_id, $new_stage, 'pipeline_stage');
        
        wp_send_json_success(['message' => 'Stato aggiornato']);
    }
    
    public static function get_pipeline_stats() {
        $stages = get_terms(['taxonomy' => 'pipeline_stage', 'hide_empty' => false]);
        $stats = [];
        
        foreach ($stages as $stage) {
            $count = wp_count_posts('lead');
            $stats[$stage->name] = [
                'count' => $count->publish ?? 0,
                'value' => 0
            ];
        }
        
        return $stats;
    }
}

WECOOP_Pipeline_Manager::init();
