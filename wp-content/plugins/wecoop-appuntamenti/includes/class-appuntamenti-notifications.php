<?php
/**
 * Notifiche appuntamenti: push FCM (utente + operatore) ed email multilingua.
 *
 * Si aggancia agli hook lanciati dall'endpoint:
 *  - wecoop_appuntamento_slot_proposti
 *  - wecoop_appuntamento_confermato
 *  - wecoop_appuntamento_annullato
 *  - wecoop_appuntamento_riprogrammato
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Appuntamenti_Notifications {

    public static function init() {
        add_action('wecoop_appuntamento_slot_proposti', [__CLASS__, 'on_slot_proposti'], 10, 3);
        add_action('wecoop_appuntamento_confermato', [__CLASS__, 'on_confermato'], 10, 3);
        add_action('wecoop_appuntamento_annullato', [__CLASS__, 'on_annullato'], 10, 3);
        add_action('wecoop_appuntamento_riprogrammato', [__CLASS__, 'on_riprogrammato'], 10, 3);
    }

    /* -----------------------------------------------------------------
     * Copy multilingua (it/en/es/fr)
     * --------------------------------------------------------------- */

    private static function copy($lang) {
        $c = [
            'it' => [
                'slot_title'   => 'Scegli data appuntamento',
                'slot_body'    => 'Sono disponibili nuove date per il tuo appuntamento relativo a "%s". Apri l\'app e scegli quella che preferisci.',
                'conf_title'   => 'Appuntamento confermato',
                'conf_body'    => 'Il tuo appuntamento per "%s" e\' confermato per il %s presso %s.',
                'op_conf_title' => 'Nuovo appuntamento prenotato',
                'op_conf_body' => '%s ha prenotato un appuntamento per "%s" il %s.',
                'cancel_title' => 'Appuntamento annullato',
                'op_cancel_body' => '%s ha annullato l\'appuntamento per "%s" del %s.',
                'resc_title'   => 'Appuntamento riprogrammato',
                'resc_body'    => 'Il tuo appuntamento per "%s" e\' stato spostato al %s presso %s.',
                'op_resc_body' => '%s ha riprogrammato l\'appuntamento per "%s" al %s.',
                'no_place'     => 'la sede indicata',
            ],
            'en' => [
                'slot_title'   => 'Choose appointment date',
                'slot_body'    => 'New dates are available for your appointment about "%s". Open the app and pick your preferred slot.',
                'conf_title'   => 'Appointment confirmed',
                'conf_body'    => 'Your appointment for "%s" is confirmed on %s at %s.',
                'op_conf_title' => 'New appointment booked',
                'op_conf_body' => '%s booked an appointment for "%s" on %s.',
                'cancel_title' => 'Appointment cancelled',
                'op_cancel_body' => '%s cancelled the appointment for "%s" on %s.',
                'resc_title'   => 'Appointment rescheduled',
                'resc_body'    => 'Your appointment for "%s" has been moved to %s at %s.',
                'op_resc_body' => '%s rescheduled the appointment for "%s" to %s.',
                'no_place'     => 'the indicated location',
            ],
            'es' => [
                'slot_title'   => 'Elige la fecha de la cita',
                'slot_body'    => 'Hay nuevas fechas disponibles para tu cita sobre "%s". Abre la app y elige la que prefieras.',
                'conf_title'   => 'Cita confirmada',
                'conf_body'    => 'Tu cita para "%s" esta confirmada el %s en %s.',
                'op_conf_title' => 'Nueva cita reservada',
                'op_conf_body' => '%s reservo una cita para "%s" el %s.',
                'cancel_title' => 'Cita cancelada',
                'op_cancel_body' => '%s cancelo la cita para "%s" del %s.',
                'resc_title'   => 'Cita reprogramada',
                'resc_body'    => 'Tu cita para "%s" se ha movido al %s en %s.',
                'op_resc_body' => '%s reprogramo la cita para "%s" al %s.',
                'no_place'     => 'el lugar indicado',
            ],
            'fr' => [
                'slot_title'   => 'Choisissez la date du rendez-vous',
                'slot_body'    => 'De nouvelles dates sont disponibles pour votre rendez-vous concernant "%s". Ouvrez l\'app et choisissez.',
                'conf_title'   => 'Rendez-vous confirme',
                'conf_body'    => 'Votre rendez-vous pour "%s" est confirme le %s a %s.',
                'op_conf_title' => 'Nouveau rendez-vous reserve',
                'op_conf_body' => '%s a reserve un rendez-vous pour "%s" le %s.',
                'cancel_title' => 'Rendez-vous annule',
                'op_cancel_body' => '%s a annule le rendez-vous pour "%s" du %s.',
                'resc_title'   => 'Rendez-vous reprogramme',
                'resc_body'    => 'Votre rendez-vous pour "%s" a ete deplace au %s a %s.',
                'op_resc_body' => '%s a reprogramme le rendez-vous pour "%s" au %s.',
                'no_place'     => 'le lieu indique',
            ],
        ];
        return isset($c[$lang]) ? $c[$lang] : $c['it'];
    }

    private static function user_lang($user_id) {
        if (class_exists('WeCoop_Multilingual_Email')) {
            return WeCoop_Multilingual_Email::get_user_language($user_id);
        }
        $l = get_user_meta($user_id, 'preferred_language', true);
        return in_array($l, ['it', 'en', 'es', 'fr'], true) ? $l : 'it';
    }

    private static function servizio_label($richiesta_id) {
        $s = trim((string) get_post_meta($richiesta_id, 'servizio', true));
        return $s !== '' ? $s : 'servizio';
    }

    private static function fmt_data($data_ora, $lang) {
        $ts = strtotime($data_ora);
        if (!$ts) {
            return $data_ora;
        }
        // Formato leggibile, timezone WordPress.
        return date_i18n('d/m/Y H:i', $ts);
    }

    private static function push($user_id, $title, $body, $richiesta_id, $extra = []) {
        if (!function_exists('wecoop_send_push_notification')) {
            return;
        }
        wecoop_send_push_notification(
            $user_id,
            $title,
            $body,
            'AppointmentDetail',
            $richiesta_id,
            array_merge(['richiesta_id' => (string) $richiesta_id], $extra)
        );
    }

    private static function email($user_id, $subject, $body) {
        $user = get_userdata($user_id);
        if (!$user || empty($user->user_email)) {
            return;
        }
        wp_mail($user->user_email, $subject, $body);
    }

    /* -----------------------------------------------------------------
     * Handlers
     * --------------------------------------------------------------- */

    public static function on_slot_proposti($richiesta_id, $slot_ids, $operator_id) {
        $user_id = self::owner($richiesta_id);
        if ($user_id <= 0) {
            return;
        }
        $lang = self::user_lang($user_id);
        $c = self::copy($lang);
        $servizio = self::servizio_label($richiesta_id);

        $body = sprintf($c['slot_body'], $servizio);
        self::push($user_id, $c['slot_title'], $body, $richiesta_id, ['event' => 'slot_proposti']);
        self::email($user_id, $c['slot_title'], $body);
    }

    public static function on_confermato($appuntamento_id, $richiesta_id, $user_id) {
        $appt = WeCoop_Appuntamenti_Repository::get_appuntamento($appuntamento_id);
        if (!$appt) {
            return;
        }
        $lang = self::user_lang($user_id);
        $c = self::copy($lang);
        $servizio = self::servizio_label($richiesta_id);
        $data = self::fmt_data($appt['data_ora'], $lang);
        $sede = $appt['sede'] ? $appt['sede'] : $c['no_place'];

        // Utente
        $body = sprintf($c['conf_body'], $servizio, $data, $sede);
        self::push($user_id, $c['conf_title'], $body, $richiesta_id, ['event' => 'confermato']);
        self::email($user_id, $c['conf_title'], $body);

        // Operatore
        $operator_id = (int) $appt['operator_id'];
        if ($operator_id > 0) {
            $op_lang = self::user_lang($operator_id);
            $oc = self::copy($op_lang);
            $user = get_userdata($user_id);
            $user_name = $user ? $user->display_name : ('#' . $user_id);
            $op_body = sprintf($oc['op_conf_body'], $user_name, $servizio, $data);
            self::push($operator_id, $oc['op_conf_title'], $op_body, $richiesta_id, ['event' => 'confermato_operatore']);
            self::email($operator_id, $oc['op_conf_title'], $op_body);
        }
    }

    public static function on_annullato($appuntamento_id, $richiesta_id, $user_id) {
        $appt = WeCoop_Appuntamenti_Repository::get_appuntamento($appuntamento_id);
        if (!$appt) {
            return;
        }
        $operator_id = (int) $appt['operator_id'];
        if ($operator_id <= 0) {
            return;
        }
        $op_lang = self::user_lang($operator_id);
        $oc = self::copy($op_lang);
        $servizio = self::servizio_label($richiesta_id);
        $data = self::fmt_data($appt['data_ora'], $op_lang);
        $user = get_userdata($user_id);
        $user_name = $user ? $user->display_name : ('#' . $user_id);

        $op_body = sprintf($oc['op_cancel_body'], $user_name, $servizio, $data);
        self::push($operator_id, $oc['cancel_title'], $op_body, $richiesta_id, ['event' => 'annullato']);
        self::email($operator_id, $oc['cancel_title'], $op_body);
    }

    public static function on_riprogrammato($appuntamento_id, $richiesta_id, $user_id) {
        $appt = WeCoop_Appuntamenti_Repository::get_appuntamento($appuntamento_id);
        if (!$appt) {
            return;
        }
        $lang = self::user_lang($user_id);
        $c = self::copy($lang);
        $servizio = self::servizio_label($richiesta_id);
        $data = self::fmt_data($appt['data_ora'], $lang);
        $sede = $appt['sede'] ? $appt['sede'] : $c['no_place'];

        // Utente
        $body = sprintf($c['resc_body'], $servizio, $data, $sede);
        self::push($user_id, $c['resc_title'], $body, $richiesta_id, ['event' => 'riprogrammato']);
        self::email($user_id, $c['resc_title'], $body);

        // Operatore
        $operator_id = (int) $appt['operator_id'];
        if ($operator_id > 0) {
            $op_lang = self::user_lang($operator_id);
            $oc = self::copy($op_lang);
            $user = get_userdata($user_id);
            $user_name = $user ? $user->display_name : ('#' . $user_id);
            $op_body = sprintf($oc['op_resc_body'], $user_name, $servizio, $data);
            self::push($operator_id, $oc['resc_title'], $op_body, $richiesta_id, ['event' => 'riprogrammato_operatore']);
            self::email($operator_id, $oc['resc_title'], $op_body);
        }
    }

    private static function owner($richiesta_id) {
        $uid = (int) get_post_meta($richiesta_id, 'user_id', true);
        if ($uid <= 0) {
            $uid = (int) get_post_field('post_author', $richiesta_id);
        }
        return $uid;
    }
}
