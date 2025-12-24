<?php
/**
 * Push Notifications Helper Functions
 * 
 * Funzioni helper per inviare notifiche push automatiche
 * su eventi specifici di WordPress
 * 
 * @package WECOOP_Notifications
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Invia notifica push a uno o più utenti
 * 
 * @param int|array $user_ids ID utente singolo o array di ID
 * @param string $title Titolo notifica
 * @param string $body Corpo notifica
 * @param string|null $screen Nome schermata Flutter (EventDetail, ServiceDetail, Profile, etc.)
 * @param string|null $id ID risorsa (evento_id, servizio_id, etc.)
 * @param array $extra_data Dati extra da includere nel payload
 * @return array Risultato invio ['success' => bool, 'sent' => int, 'failed' => int]
 */
function wecoop_send_push_notification($user_ids, $title, $body, $screen = null, $id = null, $extra_data = []) {
    if (!class_exists('WECOOP_Push_Integrations')) {
        return ['success' => false, 'message' => 'Plugin notifiche non attivo'];
    }
    
    // Converti singolo ID in array
    if (!is_array($user_ids)) {
        $user_ids = [$user_ids];
    }
    
    // Prepara dati payload
    $data = array_merge([
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
    ], $extra_data);
    
    if ($screen) {
        $data['screen'] = $screen;
    }
    
    if ($id) {
        $data['id'] = (string) $id;
    }
    
    // Invia notifica
    return WECOOP_Push_Integrations::send_push_notification($user_ids, $title, $body, $data);
}

/**
 * Invia notifica a tutti gli utenti con un ruolo specifico
 * 
 * @param string $role Ruolo WordPress (socio, administrator, etc.)
 * @param string $title Titolo notifica
 * @param string $body Corpo notifica
 * @param string|null $screen Nome schermata Flutter
 * @param string|null $id ID risorsa
 * @return array Risultato invio
 */
function wecoop_send_push_to_role($role, $title, $body, $screen = null, $id = null) {
    $users = get_users([
        'role' => $role,
        'fields' => 'ID'
    ]);
    
    if (empty($users)) {
        return ['success' => false, 'message' => 'Nessun utente trovato con ruolo ' . $role];
    }
    
    return wecoop_send_push_notification($users, $title, $body, $screen, $id);
}

/**
 * Invia notifica a tutti gli utenti (broadcast)
 * 
 * @param string $title Titolo notifica
 * @param string $body Corpo notifica
 * @param string|null $screen Nome schermata Flutter
 * @param string|null $id ID risorsa
 * @return array Risultato invio
 */
function wecoop_send_push_to_all($title, $body, $screen = null, $id = null) {
    $users = get_users(['fields' => 'ID']);
    
    if (empty($users)) {
        return ['success' => false, 'message' => 'Nessun utente registrato'];
    }
    
    return wecoop_send_push_notification($users, $title, $body, $screen, $id);
}

/**
 * Ottieni FCM token di un utente
 * 
 * @param int $user_id ID utente
 * @return string|null Token FCM o null se non trovato
 */
function wecoop_get_user_fcm_token($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wecoop_push_tokens';
    
    return $wpdb->get_var($wpdb->prepare(
        "SELECT token FROM {$table_name} WHERE user_id = %d",
        $user_id
    ));
}

/**
 * Verifica se un utente ha un FCM token registrato
 * 
 * @param int $user_id ID utente
 * @return bool
 */
function wecoop_user_has_fcm_token($user_id) {
    return !empty(wecoop_get_user_fcm_token($user_id));
}

/**
 * Conta quanti utenti hanno FCM token registrato
 * 
 * @return int Numero utenti con token
 */
function wecoop_count_users_with_tokens() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'wecoop_push_tokens';
    
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
}

// ===== HOOKS AUTOMATICI =====

/**
 * Hook: Notifica quando viene pubblicato un nuovo evento
 */
add_action('publish_evento', function($post_id, $post) {
    // Evita notifiche per revisioni o aggiornamenti
    if (wp_is_post_revision($post_id) || $post->post_date !== $post->post_modified) {
        return;
    }
    
    $evento_title = get_the_title($post_id);
    $evento_date = get_post_meta($post_id, 'data_evento', true);
    
    $body = 'Nuovo evento disponibile';
    if ($evento_date) {
        $body .= ' - ' . date_i18n('d/m/Y', strtotime($evento_date));
    }
    
    // Invia a tutti i soci
    wecoop_send_push_to_role('socio', 'Nuovo Evento: ' . $evento_title, $body, 'EventDetail', (string) $post_id);
    
}, 10, 2);

/**
 * Hook: Notifica quando socio viene approvato
 */
add_action('wecoop_socio_approved', function($user_id, $socio_data) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    wecoop_send_push_notification(
        $user_id,
        'Iscrizione Approvata',
        'Benvenuto in WeCoop! La tua richiesta di adesione è stata approvata.',
        'Profile'
    );
}, 10, 2);

/**
 * Hook: Notifica quando richiesta servizio cambia stato
 */
add_action('wecoop_richiesta_servizio_status_changed', function($richiesta_id, $old_status, $new_status) {
    global $wpdb;
    
    // Ottieni user_id dalla richiesta
    $user_id = get_post_field('post_author', $richiesta_id);
    if (!$user_id) return;
    
    $servizio_title = get_the_title(get_post_meta($richiesta_id, 'servizio_id', true));
    
    $messages = [
        'approvata' => 'La tua richiesta per "' . $servizio_title . '" è stata approvata!',
        'rifiutata' => 'La tua richiesta per "' . $servizio_title . '" è stata rifiutata.',
        'completata' => 'Il servizio "' . $servizio_title . '" è stato completato!',
        'in_lavorazione' => 'La tua richiesta per "' . $servizio_title . '" è in lavorazione.'
    ];
    
    $body = $messages[$new_status] ?? 'Stato richiesta aggiornato';
    
    wecoop_send_push_notification(
        $user_id,
        'Richiesta Servizio Aggiornata',
        $body,
        'ServiceDetail',
        (string) $richiesta_id
    );
}, 10, 3);

/**
 * Hook: Notifica reminder evento (24h prima)
 */
add_action('wecoop_evento_reminder_24h', function($evento_id) {
    // Ottieni partecipanti iscritti all'evento
    $partecipanti = get_post_meta($evento_id, 'partecipanti', true);
    
    if (empty($partecipanti) || !is_array($partecipanti)) {
        return;
    }
    
    $evento_title = get_the_title($evento_id);
    $evento_date = get_post_meta($evento_id, 'data_evento', true);
    
    $body = 'Ti ricordiamo che domani alle ' . date_i18n('H:i', strtotime($evento_date)) . ' si terrà: ' . $evento_title;
    
    wecoop_send_push_notification(
        $partecipanti,
        'Promemoria Evento',
        $body,
        'EventDetail',
        (string) $evento_id
    );
}, 10, 1);

/**
 * Hook: Notifica conferma iscrizione evento
 */
add_action('wecoop_evento_iscrizione_confermata', function($user_id, $evento_id) {
    $evento_title = get_the_title($evento_id);
    $evento_date = get_post_meta($evento_id, 'data_evento', true);
    
    $body = 'Iscrizione confermata per ' . date_i18n('d/m/Y H:i', strtotime($evento_date));
    
    wecoop_send_push_notification(
        $user_id,
        'Iscrizione Evento Confermata',
        $body,
        'EventDetail',
        (string) $evento_id
    );
}, 10, 2);
