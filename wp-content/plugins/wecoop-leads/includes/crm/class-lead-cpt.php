<?php
/**
 * Custom Post Type: Lead CRM
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Lead_CPT {
    
    public static function register_post_type() {
        register_post_type('lead', [
            'labels' => [
                'name' => 'Lead',
                'singular_name' => 'Lead',
                'add_new' => 'Aggiungi Lead',
                'add_new_item' => 'Aggiungi Nuovo Lead',
                'edit_item' => 'Modifica Lead',
                'view_item' => 'Visualizza Lead'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'wecoop-crm',
            'capability_type' => 'post',
            'supports' => ['title', 'editor'],
            'has_archive' => false,
            'menu_icon' => 'dashicons-businessman'
        ]);
    }
    
    public static function register_taxonomies() {
        register_taxonomy('pipeline_stage', 'lead', [
            'labels' => [
                'name' => 'Stati Pipeline',
                'singular_name' => 'Stato Pipeline'
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_admin_column' => true
        ]);
        
        // Crea stati default
        $default_stages = ['Nuovo', 'Contattato', 'Qualificato', 'Proposta', 'Vinto', 'Perso'];
        foreach ($default_stages as $stage) {
            if (!term_exists($stage, 'pipeline_stage')) {
                wp_insert_term($stage, 'pipeline_stage');
            }
        }
    }
}
