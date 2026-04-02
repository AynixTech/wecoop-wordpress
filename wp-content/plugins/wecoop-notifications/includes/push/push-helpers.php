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
    $richiesta_id = intval($richiesta_id);
    if ($richiesta_id <= 0 || $old_status === $new_status) {
        return;
    }

    // Owner richiesta: usa meta user_id (fallback post_author).
    $user_id = intval(get_post_meta($richiesta_id, 'user_id', true));
    if ($user_id <= 0) {
        $user_id = intval(get_post_field('post_author', $richiesta_id));
    }
    if ($user_id <= 0) {
        return;
    }

    $lang = 'it';
    if (class_exists('WeCoop_Multilingual_Email')) {
        $lang = WeCoop_Multilingual_Email::get_user_language($user_id);
    } else {
        $saved_lang = get_user_meta($user_id, 'preferred_language', true);
        if (in_array($saved_lang, ['it', 'en', 'es', 'fr'], true)) {
            $lang = $saved_lang;
        }
    }

    $servizio = trim((string) get_post_meta($richiesta_id, 'servizio', true));
    if ($servizio === '') {
        $servizio = 'servizio';
    }
    $numero_pratica = trim((string) get_post_meta($richiesta_id, 'numero_pratica', true));

    $copy = [
        'it' => [
            'title' => 'Aggiornamento richiesta',
            'fallback' => 'La tua richiesta è stata aggiornata.',
            'practice_label' => 'Pratica',
            'titles' => [
                'awaiting_payment' => 'Pagamento richiesto',
                'awaiting_signature' => 'Firma richiesta',
                'processing' => 'Richiesta in lavorazione',
                'completed' => 'Richiesta completata',
                'rejected' => 'Richiesta non approvata',
                'cancelled' => 'Richiesta annullata',
            ],
            'status' => [
                'pending' => 'In attesa',
                'awaiting_payment' => 'In attesa di pagamento',
                'paid' => 'Pagata',
                'awaiting_signature' => 'In attesa di firma',
                'processing' => 'In lavorazione',
                'completed' => 'Completata',
                'rejected' => 'Rifiutata',
                'cancelled' => 'Annullata',
            ],
            'status_messages' => [
                'pending' => 'Abbiamo ricevuto la tua richiesta per "{servizio}". E\' in attesa di presa in carico.',
                'awaiting_payment' => 'La tua richiesta per "{servizio}" e\' in attesa di pagamento. Apri l\'app e completa il pagamento per continuare.',
                'paid' => 'Pagamento ricevuto per la richiesta "{servizio}". Procediamo con i prossimi passaggi.',
                'awaiting_signature' => 'La tua richiesta per "{servizio}" richiede la firma del Documento Unico. Apri l\'app e firma per proseguire.',
                'processing' => 'La tua richiesta per "{servizio}" e\' in lavorazione. Ti aggiorneremo appena ci sono novita\'.',
                'completed' => 'La tua richiesta per "{servizio}" e\' stata completata con successo.',
                'rejected' => 'La tua richiesta per "{servizio}" non puo\' essere approvata al momento. Contattaci per dettagli.',
                'cancelled' => 'La tua richiesta per "{servizio}" e\' stata annullata.',
            ],
            'message' => 'La tua richiesta per "{servizio}" ora è: {status}.',
        ],
        'en' => [
            'title' => 'Request updated',
            'fallback' => 'Your request has been updated.',
            'practice_label' => 'Case',
            'titles' => [
                'awaiting_payment' => 'Payment required',
                'awaiting_signature' => 'Signature required',
                'processing' => 'Request in progress',
                'completed' => 'Request completed',
                'rejected' => 'Request not approved',
                'cancelled' => 'Request cancelled',
            ],
            'status' => [
                'pending' => 'Pending',
                'awaiting_payment' => 'Awaiting payment',
                'paid' => 'Paid',
                'awaiting_signature' => 'Awaiting signature',
                'processing' => 'In progress',
                'completed' => 'Completed',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled',
            ],
            'status_messages' => [
                'pending' => 'We received your request for "{servizio}". It is pending review.',
                'awaiting_payment' => 'Your request for "{servizio}" is awaiting payment. Open the app and complete payment to continue.',
                'paid' => 'Payment received for your request "{servizio}". We are moving to the next step.',
                'awaiting_signature' => 'Your request for "{servizio}" requires your signature on the Documento Unico. Open the app and sign to continue.',
                'processing' => 'Your request for "{servizio}" is now in progress. We will keep you updated.',
                'completed' => 'Your request for "{servizio}" has been completed successfully.',
                'rejected' => 'Your request for "{servizio}" cannot be approved at this time. Contact us for details.',
                'cancelled' => 'Your request for "{servizio}" has been cancelled.',
            ],
            'message' => 'Your request for "{servizio}" is now: {status}.',
        ],
        'es' => [
            'title' => 'Solicitud actualizada',
            'fallback' => 'Tu solicitud ha sido actualizada.',
            'practice_label' => 'Expediente',
            'titles' => [
                'awaiting_payment' => 'Pago requerido',
                'awaiting_signature' => 'Firma requerida',
                'processing' => 'Solicitud en curso',
                'completed' => 'Solicitud completada',
                'rejected' => 'Solicitud no aprobada',
                'cancelled' => 'Solicitud cancelada',
            ],
            'status' => [
                'pending' => 'Pendiente',
                'awaiting_payment' => 'Pendiente de pago',
                'paid' => 'Pagada',
                'awaiting_signature' => 'Pendiente de firma',
                'processing' => 'En curso',
                'completed' => 'Completada',
                'rejected' => 'Rechazada',
                'cancelled' => 'Cancelada',
            ],
            'status_messages' => [
                'pending' => 'Hemos recibido tu solicitud para "{servizio}". Está pendiente de revisión.',
                'awaiting_payment' => 'Tu solicitud para "{servizio}" está pendiente de pago. Abre la app y completa el pago para continuar.',
                'paid' => 'Pago recibido para tu solicitud "{servizio}". Pasamos al siguiente paso.',
                'awaiting_signature' => 'Tu solicitud para "{servizio}" requiere la firma del Documento Unico. Abre la app y firma para continuar.',
                'processing' => 'Tu solicitud para "{servizio}" está en curso. Te mantendremos informado.',
                'completed' => 'Tu solicitud para "{servizio}" se ha completado correctamente.',
                'rejected' => 'Tu solicitud para "{servizio}" no puede ser aprobada por ahora. Contáctanos para más detalles.',
                'cancelled' => 'Tu solicitud para "{servizio}" ha sido cancelada.',
            ],
            'message' => 'Tu solicitud para "{servizio}" ahora está: {status}.',
        ],
        'fr' => [
            'title' => 'Demande mise à jour',
            'fallback' => 'Votre demande a été mise à jour.',
            'practice_label' => 'Dossier',
            'titles' => [
                'awaiting_payment' => 'Paiement requis',
                'awaiting_signature' => 'Signature requise',
                'processing' => 'Demande en cours',
                'completed' => 'Demande terminée',
                'rejected' => 'Demande non approuvée',
                'cancelled' => 'Demande annulée',
            ],
            'status' => [
                'pending' => 'En attente',
                'awaiting_payment' => 'En attente de paiement',
                'paid' => 'Payée',
                'awaiting_signature' => 'En attente de signature',
                'processing' => 'En cours',
                'completed' => 'Terminée',
                'rejected' => 'Refusée',
                'cancelled' => 'Annulée',
            ],
            'status_messages' => [
                'pending' => 'Nous avons reçu votre demande pour "{servizio}". Elle est en attente de traitement.',
                'awaiting_payment' => 'Votre demande pour "{servizio}" est en attente de paiement. Ouvrez l\'application et finalisez le paiement pour continuer.',
                'paid' => 'Paiement reçu pour votre demande "{servizio}". Nous passons à l\'étape suivante.',
                'awaiting_signature' => 'Votre demande pour "{servizio}" nécessite la signature du Documento Unico. Ouvrez l\'application et signez pour continuer.',
                'processing' => 'Votre demande pour "{servizio}" est en cours. Nous vous tiendrons informé.',
                'completed' => 'Votre demande pour "{servizio}" a été terminée avec succès.',
                'rejected' => 'Votre demande pour "{servizio}" ne peut pas être approuvée pour le moment. Contactez-nous pour plus de détails.',
                'cancelled' => 'Votre demande pour "{servizio}" a été annulée.',
            ],
            'message' => 'Votre demande pour "{servizio}" est maintenant: {status}.',
        ],
    ];

    $dictionary = $copy[$lang] ?? $copy['it'];
    $status_label = $dictionary['status'][$new_status] ?? ucfirst(str_replace('_', ' ', (string) $new_status));
    $title = $dictionary['titles'][$new_status] ?? $dictionary['title'];

    $template = $dictionary['status_messages'][$new_status] ?? $dictionary['message'];
    $body = str_replace(
        ['{servizio}', '{status}'],
        [$servizio, $status_label],
        $template
    );
    if ($body === '') {
        $body = $dictionary['fallback'];
    }

    if ($numero_pratica !== '') {
        $body .= ' ' . $dictionary['practice_label'] . ': ' . $numero_pratica . '.';
    }

    wecoop_send_push_notification(
        $user_id,
        $title,
        $body,
        'ServiceDetail',
        (string) $richiesta_id,
        [
            'richiesta_id' => (string) $richiesta_id,
            'old_status' => (string) $old_status,
            'new_status' => (string) $new_status,
            'numero_pratica' => (string) $numero_pratica,
            'lang' => (string) $lang,
        ]
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
