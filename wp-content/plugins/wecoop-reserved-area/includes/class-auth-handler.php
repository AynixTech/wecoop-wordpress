<?php
/**
 * Auth Handler
 * 
 * @package WeCoop_Reserved_Area
 */

if (!defined('ABSPATH')) exit;

class WeCoop_RA_Auth_Handler {
    
    /**
     * Autenticazione custom (non usa wp_signon)
     */
    public static function authenticate($username, $password) {
        // Pulisci username
        $username = trim($username);
        
        // Cerca utente per email
        $user = get_user_by('email', $username);
        
        // Se non trovato, cerca per username
        if (!$user) {
            $user = get_user_by('login', $username);
        }
        
        // Se non trovato, cerca per telefono
        if (!$user) {
            $user = self::get_user_by_phone($username);
        }
        
        // Utente non trovato
        if (!$user) {
            return new WP_Error('invalid_username', 'Username o password non corretti');
        }
        
        // Verifica password
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            return new WP_Error('incorrect_password', 'Username o password non corretti');
        }
        
        // Verifica che l'utente sia attivo
        if (!self::is_user_active($user->ID)) {
            return new WP_Error('inactive_account', 'Account non attivo. Contatta l\'amministratore.');
        }
        
        return $user;
    }
    
    /**
     * Cerca utente per telefono
     */
    private static function get_user_by_phone($phone) {
        global $wpdb;
        
        // Pulisci numero
        $phone_clean = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($phone_clean)) {
            return false;
        }
        
        // Cerca in user_meta
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = 'telefono_completo' 
            AND meta_value LIKE %s 
            LIMIT 1",
            '%' . $phone_clean
        ));
        
        if ($user_id) {
            return get_user_by('id', $user_id);
        }
        
        return false;
    }
    
    /**
     * Verifica se l'utente è attivo
     */
    private static function is_user_active($user_id) {
        $status = get_user_meta($user_id, 'status_socio', true);
        
        // Se non ha status, considera attivo
        if (empty($status)) {
            return true;
        }
        
        return in_array($status, ['attivo', 'approved']);
    }
    
    /**
     * Genera token di reset password
     */
    public static function generate_reset_token($user_id) {
        $token = wp_generate_password(32, false);
        $expiry = time() + 3600; // 1 ora
        
        update_user_meta($user_id, 'password_reset_token', $token);
        update_user_meta($user_id, 'password_reset_expiry', $expiry);
        
        return $token;
    }
    
    /**
     * Verifica token reset password
     */
    public static function verify_reset_token($user_id, $token) {
        $saved_token = get_user_meta($user_id, 'password_reset_token', true);
        $expiry = get_user_meta($user_id, 'password_reset_expiry', true);
        
        if (empty($saved_token) || empty($expiry)) {
            return false;
        }
        
        if (time() > $expiry) {
            return false;
        }
        
        return hash_equals($saved_token, $token);
    }
}
