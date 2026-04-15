<?php
/**
 * Endpoint pubblico: POST /wecoop/v1/studiare-italia
 * Riceve la richiesta dall'app, salva come CPT e invia notifica email.
 *
 * @package WECOOP_Offerta_Formativa
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Studiare_Italia_Endpoint {

    public static function register_routes() {
        register_rest_route('wecoop/v1', '/studiare-italia', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_request'],
            'permission_callback' => '__return_true',
            'args'                => self::get_args(),
        ]);
    }

    private static function get_args() {
        return [
            'nome_cognome' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($v) { return !empty(trim($v)) && strlen($v) <= 200; },
            ],
            'email' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_email',
                'validate_callback' => 'is_email',
            ],
            'paese_origine' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'whatsapp' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'eta' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'titolo_studio' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'livello_italiano' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'livello_inglese' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'cosa_studiare' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'quando_iniziare' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'gia_studiato' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'ha_documenti' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'aiuto_richiesto' => [
                'required'          => false,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }

    public static function handle_request($request) {
        $nome_cognome = $request->get_param('nome_cognome');
        $email        = $request->get_param('email');

        // Crea il post CPT
        $post_id = wp_insert_post([
            'post_type'   => 'richiesta_studio',
            'post_title'  => sanitize_text_field($nome_cognome),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Errore nel salvataggio della richiesta.',
            ], 500);
        }

        // Salva tutti i campi come meta
        $meta_fields = [
            'nome_cognome', 'paese_origine', 'email', 'whatsapp',
            'eta', 'titolo_studio', 'livello_italiano', 'livello_inglese',
            'cosa_studiare', 'quando_iniziare', 'gia_studiato',
            'ha_documenti', 'aiuto_richiesto',
        ];
        foreach ($meta_fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null && $value !== '') {
                update_post_meta($post_id, $field, $value);
            }
        }
        update_post_meta($post_id, 'stato', 'Nuova');
        update_post_meta($post_id, 'data_invio', current_time('d/m/Y H:i'));

        // Crea lead nel plugin wecoop-leads se disponibile
        self::create_lead($request, $post_id);

        // Invia email di notifica agli operatori
        self::send_notification_email($post_id, $nome_cognome, $email, $request);

        return new WP_REST_Response([
            'success'    => true,
            'richiesta_id' => $post_id,
            'message'    => 'Richiesta inviata con successo.',
        ], 201);
    }

    private static function create_lead($request, $richiesta_id) {
        if (!post_type_exists('lead')) return;

        $nome_cognome = $request->get_param('nome_cognome');
        $parts = explode(' ', trim($nome_cognome), 2);
        $nome    = $parts[0] ?? $nome_cognome;
        $cognome = $parts[1] ?? '';

        $lead_id = wp_insert_post([
            'post_type'   => 'lead',
            'post_title'  => $nome_cognome,
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($lead_id)) {
            update_post_meta($lead_id, 'nome',     $nome);
            update_post_meta($lead_id, 'cognome',  $cognome);
            update_post_meta($lead_id, 'email',    $request->get_param('email'));
            update_post_meta($lead_id, 'telefono', $request->get_param('whatsapp') ?: '');
            update_post_meta($lead_id, 'fonte',    'App – Studiare in Italia');
            update_post_meta($lead_id, 'note',     'Richiesta studio #' . $richiesta_id);
            if (taxonomy_exists('pipeline_stage')) {
                wp_set_object_terms($lead_id, 'Nuovo', 'pipeline_stage');
            }
        }
    }

    private static function send_notification_email($post_id, $nome, $email, $request) {
        $admin_email  = get_option('admin_email');
        $notify_email = get_option('wecoop_notification_email', $admin_email);
        $site_name    = get_bloginfo('name');
        $edit_url     = admin_url('post.php?post=' . $post_id . '&action=edit');

        $admin_message = "Nuova richiesta 'Studiare in Italia' ricevuta dall'app WeCoop.\n\n";
        $admin_message .= "=== DATI RICHIEDENTE ===\n";

        $labels = [
            'nome_cognome'     => 'Nome e cognome',
            'paese_origine'    => 'Paese di origine',
            'email'            => 'Email',
            'whatsapp'         => 'WhatsApp',
            'eta'              => 'Età',
            'titolo_studio'    => 'Titolo di studio',
            'livello_italiano' => 'Livello italiano',
            'livello_inglese'  => 'Livello inglese',
            'cosa_studiare'    => 'Cosa vuole studiare',
            'quando_iniziare'  => 'Quando vuole iniziare',
            'gia_studiato'     => 'Ha già studiato in Italia',
            'ha_documenti'     => 'Ha documenti per Italia',
            'aiuto_richiesto'  => 'Aiuto richiesto per',
        ];

        foreach ($labels as $field => $label) {
            $val = $request->get_param($field);
            if ($val) $admin_message .= "$label: $val\n";
        }

        $admin_message .= "\nVisualizza nel CRM: $edit_url\n";

        wp_mail(
            $notify_email,
            "[{$site_name}] Nuova richiesta: Studiare in Italia – {$nome}",
            $admin_message,
            ['Content-Type: text/plain; charset=UTF-8']
        );

        // Email di conferma all'utente
        $user_message  = "Ciao {$nome},\n\n";
        $user_message .= "Abbiamo ricevuto la tua richiesta per studiare in Italia.\n";
        $user_message .= "Un operatore WeCoop ti contatterà presto per guidarti nel tuo percorso.\n\n";
        $user_message .= "Grazie per aver scelto WeCoop!\n";
        $user_message .= "Il team WeCoop\n";
        $user_message .= "https://www.wecoop.org\n";

        wp_mail(
            $email,
            "[WeCoop] Richiesta 'Studiare in Italia' ricevuta",
            $user_message,
            ['Content-Type: text/plain; charset=UTF-8']
        );
    }
}
