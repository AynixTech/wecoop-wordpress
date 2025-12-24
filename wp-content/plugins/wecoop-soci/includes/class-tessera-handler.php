<?php
/**
 * Handler per Tessera Socio Pubblica
 * 
 * Intercetta URL /tessera-socio/ e mostra il template
 * 
 * @package WECOOP_CRM
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Tessera_Handler {
    
    public static function init() {
        add_action('template_redirect', [__CLASS__, 'handle_tessera_request'], 1);
    }
    
    /**
     * Handle tessera request
     */
    public static function handle_tessera_request() {
        // Controlla se l'URL corrente è /tessera-socio/
        $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        if ($current_path === 'tessera-socio' || strpos($current_path, 'tessera-socio') === 0) {
            // Include template
            $template = WECOOP_SOCI_PLUGIN_DIR . 'templates/tessera-pubblica.php';
            
            if (file_exists($template)) {
                include $template;
                exit;
            } else {
                wp_die('Template tessera non trovato: ' . $template);
            }
        }
    }
}
