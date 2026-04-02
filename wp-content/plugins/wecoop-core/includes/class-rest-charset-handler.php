<?php
/**
 * REST API Charset Handler
 * 
 * Forza UTF-8 encoding corretto nelle risposte REST API
 * Risolve problemi di caratteri speciali visualizzati come \uXXXX
 * 
 * @package WECOOP_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WeCoop_REST_Charset_Handler {
    
    /**
     * Inizializza
     */
    public static function init() {
        // Filtra tutte le risposte REST per forzare UTF-8 encoding
        add_filter('rest_post_dispatch', [__CLASS__, 'ensure_utf8_charset'], 10, 4);
        
        // Anche per risposte custom
        add_filter('rest_prepare_post', [__CLASS__, 'ensure_utf8_response'], 10, 2);
        
        // Forza header charset nelle risposte di errore REST
        add_filter('rest_request_after_callbacks', [__CLASS__, 'set_charset_headers'], 10, 3);
    }
    
    /**
     * Ensure UTF-8 charset header in all REST responses
     */
    public static function ensure_utf8_charset($response, $server, $request, $result) {
        // Assicura che il response sia un WP_REST_Response
        if (!$response instanceof WP_REST_Response) {
            return $response;
        }
        
        // Forza charset nei headers
        $response->set_headers([
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff'
        ]);
        
        return $response;
    }
    
    /**
     * Set charset headers for all requests
     */
    public static function set_charset_headers($response, $server, $request) {
        // Se è un errore, assicura charset
        if ($response instanceof WP_Error) {
            return $response;
        }
        
        // Se è una response, assicura charset
        if ($response instanceof WP_REST_Response) {
            $response->set_headers([
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
        
        return $response;
    }
    
    /**
     * Ensure UTF-8 charset in prepare post responses
     */
    public static function ensure_utf8_response($response, $post) {
        if (!$response instanceof WP_REST_Response) {
            return $response;
        }
        
        $response->set_headers([
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
        
        return $response;
    }
    
    /**
     * Encode response data with proper UTF-8 handling
     * 
     * Ensures that json_encode output uses JSON_UNESCAPED_UNICODE
     * for better readability when displaying results
     */
    public static function encode_response($data) {
        if (function_exists('wp_json_encode')) {
            // WordPress function that handles encoding properly
            return wp_json_encode($data);
        }
        
        // Fallback: use json_encode with UTF-8 unescaped
        return json_encode(
            self::ensure_utf8_strings($data),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
    
    /**
     * Recursively ensure all strings in an array/object are proper UTF-8
     */
    private static function ensure_utf8_strings($data) {
        if (is_string($data)) {
            // Ensure string is valid UTF-8
            if (!mb_check_encoding($data, 'UTF-8')) {
                $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8,ISO-8859-1,Windows-1252');
            }
            return $data;
        } elseif (is_array($data) || is_object($data)) {
            $is_object = is_object($data);
            $arr = (array) $data;
            
            foreach ($arr as $key => &$value) {
                $value = self::ensure_utf8_strings($value);
            }
            
            return $is_object ? (object) $arr : $arr;
        }
        
        return $data;
    }
    
    /**
     * Wrapper per rest_ensure_response che forza charset
     */
    public static function rest_ensure_response_with_charset($data) {
        $response = rest_ensure_response($data);
        
        if ($response instanceof WP_REST_Response) {
            $response->set_headers([
                'Content-Type' => 'application/json; charset=utf-8'
            ]);
        }
        
        return $response;
    }
}
