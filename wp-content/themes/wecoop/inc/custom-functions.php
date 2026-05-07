<?php
// ─── Gestione richiesta eliminazione account e dati ─────────────────────────
add_action( 'init', function() {
    if ( ! isset( $_POST['wecoop_delete_account_nonce'] ) ) {
        return;
    }

    // Verifica nonce
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wecoop_delete_account_nonce'] ) ), 'wecoop_delete_account_action' ) ) {
        wp_die( esc_html__( 'Errore di sicurezza, riprova.', 'wecoop' ) );
    }

    // Deve essere loggato
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( add_query_arg( 'delete_status', 'not_logged', wp_get_referer() ?: home_url() ) );
        exit;
    }

    $current_user = wp_get_current_user();
    $posted_id    = isset( $_POST['wecoop_user_id'] ) ? intval( $_POST['wecoop_user_id'] ) : 0;

    // ID deve corrispondere all'utente corrente (prevenzione IDOR)
    if ( $posted_id !== $current_user->ID ) {
        wp_safe_redirect( add_query_arg( 'delete_status', 'error', wp_get_referer() ?: home_url() ) );
        exit;
    }

    // Checkbox di conferma obbligatoria
    if ( empty( $_POST['wecoop_confirm_delete'] ) ) {
        wp_safe_redirect( add_query_arg( 'delete_status', 'no_confirm', wp_get_referer() ?: home_url() ) );
        exit;
    }

    $admin_email  = (string) get_option( 'admin_email' );
    $user_email   = $current_user->user_email;
    $user_login   = $current_user->user_login;
    $user_display = $current_user->display_name;
    $requested_at = current_time( 'Y-m-d H:i:s' );

    // Email all'admin
    $admin_subject = 'Richiesta eliminazione account WeCoop';
    $admin_body    = "Hai ricevuto una nuova richiesta di eliminazione account.\n\n"
                   . "Nome visualizzato : {$user_display}\n"
                   . "Username          : {$user_login}\n"
                   . "Email             : {$user_email}\n"
                   . "Data richiesta    : {$requested_at}\n\n"
                   . "Accedi all'area amministrativa per elaborare la richiesta entro 30 giorni come previsto dal GDPR.";
    wp_mail( $admin_email, $admin_subject, $admin_body );

    // Email di conferma all'utente
    $user_subject = 'Conferma richiesta eliminazione account WeCoop';
    $user_body    = "Ciao {$user_display},\n\n"
                   . "Abbiamo ricevuto la tua richiesta di eliminazione dell'account e di tutti i dati associati.\n\n"
                   . "Il nostro team la elaborerà entro 30 giorni come previsto dal Regolamento Generale sulla Protezione dei Dati (GDPR).\n\n"
                   . "Se hai domande, contattaci a: privacy@wecoop.org\n\n"
                   . "-- WeCoop Team";
    wp_mail( $user_email, $user_subject, $user_body );

    // Redirect con stato successo
    $page = get_page_by_path( 'elimina-account' );
    $redirect_url = $page ? get_permalink( $page ) : home_url();
    wp_safe_redirect( add_query_arg( 'delete_status', 'sent', $redirect_url ) );
    exit;
} );

