<?php
/**
 * Import/Export Leads
 * 
 * @package WeCoop_Leads
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Import_Export {
    
    public static function import_csv($file_path) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'message' => 'File non trovato'];
        }
        
        $handle = fopen($file_path, 'r');
        $headers = fgetcsv($handle);
        $imported = 0;
        
        while (($data = fgetcsv($handle)) !== false) {
            $lead_data = array_combine($headers, $data);
            
            $post_id = wp_insert_post([
                'post_type' => 'lead',
                'post_title' => $lead_data['nome'] . ' ' . $lead_data['cognome'],
                'post_status' => 'publish'
            ]);
            
            if (!is_wp_error($post_id)) {
                foreach (['nome', 'cognome', 'email', 'telefono', 'fonte'] as $field) {
                    if (isset($lead_data[$field])) {
                        update_post_meta($post_id, $field, sanitize_text_field($lead_data[$field]));
                    }
                }
                $imported++;
            }
        }
        
        fclose($handle);
        
        return ['success' => true, 'imported' => $imported];
    }
    
    public static function export_csv() {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads-export.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nome', 'Cognome', 'Email', 'Telefono', 'Stato', 'Fonte']);
        
        $leads = get_posts(['post_type' => 'lead', 'posts_per_page' => -1]);
        
        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead->ID,
                get_post_meta($lead->ID, 'nome', true),
                get_post_meta($lead->ID, 'cognome', true),
                get_post_meta($lead->ID, 'email', true),
                get_post_meta($lead->ID, 'telefono', true),
                wp_get_post_terms($lead->ID, 'pipeline_stage', ['fields' => 'names'])[0] ?? '',
                get_post_meta($lead->ID, 'fonte', true)
            ]);
        }
        
        fclose($output);
        exit;
    }
}
