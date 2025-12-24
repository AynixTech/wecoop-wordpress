<?php
// Gestione richiesta cancellazione dati utente
add_action( 'init', function() {
    if ( isset($_POST['request_delete_data']) ) {

        // Verifica nonce
        if ( ! isset($_POST['request_delete_data_nonce']) || 
             ! wp_verify_nonce( $_POST['request_delete_data_nonce'], 'request_delete_data_action' ) ) {
            wp_die('Errore di sicurezza, riprova.');
        }

        // Controlla utente loggato
        if ( ! is_user_logged_in() ) {
            wp_die('Devi essere loggato per richiedere la cancellazione dei dati.');
        }

        $user_id = intval($_POST['user_id']);
        $current_user = wp_get_current_user();

        // Controllo che l'utente corrisponda
        if ( $user_id !== $current_user->ID ) {
            wp_die('Errore: ID utente non corrisponde.');
        }

        // Invia email all'admin
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email,
            'Richiesta eliminazione dati utente',
            "L'utente {$current_user->user_login} ({$current_user->user_email}) ha richiesto la cancellazione dei propri dati."
        );

        // Messaggio di conferma all'utente
        add_action('wp_footer', function() {
            echo '<p style="color:green;">Richiesta inviata correttamente. Verrai contattato a breve.</p>';
        });
    }
});

