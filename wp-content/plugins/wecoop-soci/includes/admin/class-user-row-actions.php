<?php
/**
 * Aggiunge link "Gestisci" nella lista utenti
 */

if (!defined('ABSPATH')) {
    exit;
}

// Aggiungi link "Gestisci" nelle azioni utente
add_filter('user_row_actions', function($actions, $user) {
    if (current_user_can('manage_options')) {
        $url = add_query_arg([
            'page' => 'wecoop-gestione-socio',
            'user_id' => $user->ID
        ], admin_url('users.php'));
        
        $actions['wecoop_gestisci'] = sprintf(
            '<a href="%s">Gestisci Socio</a>',
            esc_url($url)
        );
    }
    return $actions;
}, 10, 2);
