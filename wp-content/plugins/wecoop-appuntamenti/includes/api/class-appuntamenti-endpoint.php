<?php
/**
 * REST API Endpoint: Appuntamenti (prenotazione slot stile Calendly).
 *
 * Namespace: wecoop/v1
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Appuntamenti_Endpoint {

    const NS = 'wecoop/v1';

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        $ns = self::NS;

        // --- Utente ---
        register_rest_route($ns, '/appuntamenti/richiesta/(?P<richiesta_id>\d+)/slot', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_slots'],
            'permission_callback' => [__CLASS__, 'check_auth'],
        ]);

        register_rest_route($ns, '/appuntamenti/prenota', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'prenota'],
            'permission_callback' => [__CLASS__, 'check_auth'],
        ]);

        register_rest_route($ns, '/appuntamenti/me', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_miei'],
            'permission_callback' => [__CLASS__, 'check_auth'],
        ]);

        register_rest_route($ns, '/appuntamenti/(?P<id>\d+)/annulla', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'annulla'],
            'permission_callback' => [__CLASS__, 'check_auth'],
        ]);

        register_rest_route($ns, '/appuntamenti/(?P<id>\d+)/riprogramma', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'riprogramma'],
            'permission_callback' => [__CLASS__, 'check_auth'],
        ]);

        // --- Operatore/Admin ---
        register_rest_route($ns, '/appuntamenti/richiesta/(?P<richiesta_id>\d+)/slot', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'crea_slot'],
            'permission_callback' => [__CLASS__, 'check_operator'],
        ]);

        register_rest_route($ns, '/appuntamenti/slot/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [__CLASS__, 'elimina_slot'],
            'permission_callback' => [__CLASS__, 'check_operator'],
        ]);
    }

    /* -----------------------------------------------------------------
     * AUTH
     * --------------------------------------------------------------- */

    public static function check_auth($request) {
        if (self::current_user_id($request) > 0) {
            return true;
        }
        return new WP_Error('unauthorized', 'Autenticazione richiesta', ['status' => 401]);
    }

    public static function check_operator($request) {
        if (self::current_user_id($request) <= 0) {
            return new WP_Error('unauthorized', 'Autenticazione richiesta', ['status' => 401]);
        }
        if (current_user_can('wecoop_appuntamenti_manage') || current_user_can('manage_options')) {
            return true;
        }
        return new WP_Error('forbidden', 'Permessi insufficienti', ['status' => 403]);
    }

    /**
     * Risolve l'utente corrente: sessione WP, oppure decodifica JWT (HS256).
     */
    public static function current_user_id($request) {
        if (is_user_logged_in()) {
            return (int) get_current_user_id();
        }

        $auth_header = $request->get_header('authorization');
        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $m)) {
            return 0;
        }
        return self::user_id_from_jwt(trim($m[1]));
    }

    /**
     * Validazione JWT manuale (HS256), coerente con wecoop-push-tokens.
     */
    private static function user_id_from_jwt($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return 0;
        }
        list($h, $p, $s) = $parts;
        $payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
        if (!$payload || !isset($payload['data']['user']['id'])) {
            return 0;
        }
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return 0;
        }
        if (defined('JWT_AUTH_SECRET_KEY')) {
            $check = hash_hmac('sha256', $h . '.' . $p, JWT_AUTH_SECRET_KEY, true);
            $expected = base64_decode(strtr($s, '-_', '+/'));
            if (!hash_equals($check, $expected)) {
                return 0;
            }
        }
        return (int) $payload['data']['user']['id'];
    }

    /* -----------------------------------------------------------------
     * HELPERS
     * --------------------------------------------------------------- */

    private static function richiesta_owner($richiesta_id) {
        $uid = (int) get_post_meta($richiesta_id, 'user_id', true);
        if ($uid <= 0) {
            $uid = (int) get_post_field('post_author', $richiesta_id);
        }
        return $uid;
    }

    private static function reschedule_limit_hours() {
        return (int) get_option('wecoop_appuntamenti_reschedule_limit_hours', 24);
    }

    /**
     * Serializza uno slot per l'app.
     */
    private static function format_slot($row) {
        return [
            'id'          => (int) $row['id'],
            'richiesta_id' => (int) $row['richiesta_id'],
            'data_ora'    => $row['data_ora'],
            'durata_min'  => (int) $row['durata_min'],
            'sede'        => $row['sede'],
            'indirizzo'   => $row['indirizzo'],
            'note'        => $row['note'],
            'stato'       => $row['stato'],
        ];
    }

    /**
     * Serializza un appuntamento per l'app, includendo se e' modificabile.
     */
    private static function format_appuntamento($row) {
        $can_modify = false;
        if ($row && $row['stato'] === 'confirmed') {
            $ts = strtotime($row['data_ora']);
            $limit = self::reschedule_limit_hours() * 3600;
            $can_modify = ($ts - current_time('timestamp')) > $limit;
        }
        return [
            'id'           => (int) $row['id'],
            'richiesta_id' => (int) $row['richiesta_id'],
            'slot_id'      => (int) $row['slot_id'],
            'data_ora'     => $row['data_ora'],
            'durata_min'   => (int) $row['durata_min'],
            'sede'         => $row['sede'],
            'indirizzo'    => $row['indirizzo'],
            'note'         => $row['note'],
            'stato'        => $row['stato'],
            'can_modify'   => $can_modify,
            'reschedule_limit_hours' => self::reschedule_limit_hours(),
        ];
    }

    /* -----------------------------------------------------------------
     * ENDPOINT UTENTE
     * --------------------------------------------------------------- */

    public static function get_slots($request) {
        $uid = self::current_user_id($request);
        $richiesta_id = (int) $request['richiesta_id'];

        if (self::richiesta_owner($richiesta_id) !== $uid) {
            return new WP_REST_Response(['success' => false, 'message' => 'Richiesta non trovata'], 404);
        }

        $slots = WeCoop_Appuntamenti_Repository::get_proposed_slots($richiesta_id, true);
        $appuntamento = WeCoop_Appuntamenti_Repository::get_active_appuntamento_by_richiesta($richiesta_id);

        return new WP_REST_Response([
            'success'      => true,
            'slots'        => array_map([__CLASS__, 'format_slot'], $slots ?: []),
            'appuntamento' => $appuntamento ? self::format_appuntamento($appuntamento) : null,
        ], 200);
    }

    public static function prenota($request) {
        $uid = self::current_user_id($request);
        $body = $request->get_json_params();
        $richiesta_id = isset($body['richiesta_id']) ? (int) $body['richiesta_id'] : 0;
        $slot_id = isset($body['slot_id']) ? (int) $body['slot_id'] : 0;

        if ($richiesta_id <= 0 || $slot_id <= 0) {
            return new WP_REST_Response(['success' => false, 'message' => 'Parametri mancanti'], 400);
        }
        if (self::richiesta_owner($richiesta_id) !== $uid) {
            return new WP_REST_Response(['success' => false, 'message' => 'Richiesta non trovata'], 404);
        }

        // Un solo appuntamento attivo per richiesta.
        if (WeCoop_Appuntamenti_Repository::get_active_appuntamento_by_richiesta($richiesta_id)) {
            return new WP_REST_Response(['success' => false, 'message' => 'Esiste gia un appuntamento attivo'], 409);
        }

        $slot = WeCoop_Appuntamenti_Repository::get_slot($slot_id);
        if (!$slot || (int) $slot['richiesta_id'] !== $richiesta_id) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot non valido'], 404);
        }
        if (strtotime($slot['data_ora']) < current_time('timestamp')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot scaduto'], 409);
        }

        // Riserva atomica dello slot (anti doppia prenotazione).
        if (!WeCoop_Appuntamenti_Repository::book_slot_atomic($slot_id)) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot non piu disponibile'], 409);
        }

        $appt_id = WeCoop_Appuntamenti_Repository::create_appuntamento([
            'richiesta_id' => $richiesta_id,
            'slot_id'      => $slot_id,
            'user_id'      => $uid,
            'operator_id'  => $slot['created_by'],
            'data_ora'     => $slot['data_ora'],
            'durata_min'   => $slot['durata_min'],
            'sede'         => $slot['sede'],
            'indirizzo'    => $slot['indirizzo'],
            'note'         => $slot['note'],
        ]);

        if (!$appt_id) {
            // Rollback dello slot.
            WeCoop_Appuntamenti_Repository::update_slot_status($slot_id, 'proposed');
            return new WP_REST_Response(['success' => false, 'message' => 'Errore durante la prenotazione'], 500);
        }

        self::set_richiesta_stato($richiesta_id, WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED);

        $appuntamento = WeCoop_Appuntamenti_Repository::get_appuntamento($appt_id);
        do_action('wecoop_appuntamento_confermato', $appt_id, $richiesta_id, $uid);

        return new WP_REST_Response([
            'success'      => true,
            'message'      => 'Appuntamento confermato',
            'appuntamento' => self::format_appuntamento($appuntamento),
        ], 201);
    }

    public static function get_miei($request) {
        $uid = self::current_user_id($request);
        $rows = WeCoop_Appuntamenti_Repository::get_appuntamenti_by_user($uid, false);
        return new WP_REST_Response([
            'success'      => true,
            'appuntamenti' => array_map([__CLASS__, 'format_appuntamento'], $rows ?: []),
        ], 200);
    }

    public static function annulla($request) {
        $uid = self::current_user_id($request);
        $id = (int) $request['id'];

        $appt = WeCoop_Appuntamenti_Repository::get_appuntamento($id);
        if (!$appt || (int) $appt['user_id'] !== $uid) {
            return new WP_REST_Response(['success' => false, 'message' => 'Appuntamento non trovato'], 404);
        }
        if ($appt['stato'] !== 'confirmed') {
            return new WP_REST_Response(['success' => false, 'message' => 'Appuntamento non annullabile'], 409);
        }
        if (!self::within_reschedule_window($appt['data_ora'])) {
            return new WP_REST_Response([
                'success' => false,
                'code'    => 'reschedule_window',
                'message' => 'Non e possibile annullare a meno di ' . self::reschedule_limit_hours() . ' ore dall appuntamento',
            ], 409);
        }

        WeCoop_Appuntamenti_Repository::update_appuntamento(
            $id,
            ['stato' => 'cancelled', 'cancelled_at' => current_time('mysql')],
            ['%s', '%s']
        );
        // Lo slot torna disponibile.
        WeCoop_Appuntamenti_Repository::update_slot_status((int) $appt['slot_id'], 'proposed');
        self::set_richiesta_stato((int) $appt['richiesta_id'], WECOOP_Appuntamenti_Plugin::STATO_AWAITING);

        do_action('wecoop_appuntamento_annullato', $id, (int) $appt['richiesta_id'], $uid);

        return new WP_REST_Response(['success' => true, 'message' => 'Appuntamento annullato'], 200);
    }

    public static function riprogramma($request) {
        $uid = self::current_user_id($request);
        $id = (int) $request['id'];
        $body = $request->get_json_params();
        $nuovo_slot_id = isset($body['nuovo_slot_id']) ? (int) $body['nuovo_slot_id'] : 0;

        $appt = WeCoop_Appuntamenti_Repository::get_appuntamento($id);
        if (!$appt || (int) $appt['user_id'] !== $uid) {
            return new WP_REST_Response(['success' => false, 'message' => 'Appuntamento non trovato'], 404);
        }
        if ($appt['stato'] !== 'confirmed') {
            return new WP_REST_Response(['success' => false, 'message' => 'Appuntamento non modificabile'], 409);
        }
        if (!self::within_reschedule_window($appt['data_ora'])) {
            return new WP_REST_Response([
                'success' => false,
                'code'    => 'reschedule_window',
                'message' => 'Non e possibile riprogrammare a meno di ' . self::reschedule_limit_hours() . ' ore dall appuntamento',
            ], 409);
        }

        $richiesta_id = (int) $appt['richiesta_id'];
        $slot = WeCoop_Appuntamenti_Repository::get_slot($nuovo_slot_id);
        if (!$slot || (int) $slot['richiesta_id'] !== $richiesta_id) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot non valido'], 404);
        }
        if (strtotime($slot['data_ora']) < current_time('timestamp')) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot scaduto'], 409);
        }
        if (!WeCoop_Appuntamenti_Repository::book_slot_atomic($nuovo_slot_id)) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot non piu disponibile'], 409);
        }

        // Libera il vecchio slot.
        WeCoop_Appuntamenti_Repository::update_slot_status((int) $appt['slot_id'], 'proposed');

        WeCoop_Appuntamenti_Repository::update_appuntamento(
            $id,
            [
                'slot_id'    => $nuovo_slot_id,
                'data_ora'   => $slot['data_ora'],
                'durata_min' => (int) $slot['durata_min'],
                'sede'       => $slot['sede'],
                'indirizzo'  => $slot['indirizzo'],
                'note'       => $slot['note'],
            ],
            ['%d', '%s', '%d', '%s', '%s', '%s']
        );

        do_action('wecoop_appuntamento_riprogrammato', $id, $richiesta_id, $uid);

        $appuntamento = WeCoop_Appuntamenti_Repository::get_appuntamento($id);
        return new WP_REST_Response([
            'success'      => true,
            'message'      => 'Appuntamento riprogrammato',
            'appuntamento' => self::format_appuntamento($appuntamento),
        ], 200);
    }

    private static function within_reschedule_window($data_ora) {
        $ts = strtotime($data_ora);
        $limit = self::reschedule_limit_hours() * 3600;
        return ($ts - current_time('timestamp')) > $limit;
    }

    /* -----------------------------------------------------------------
     * ENDPOINT OPERATORE
     * --------------------------------------------------------------- */

    public static function crea_slot($request) {
        $operator_id = self::current_user_id($request);
        $richiesta_id = (int) $request['richiesta_id'];
        $body = $request->get_json_params();

        if (get_post_type($richiesta_id) !== 'richiesta_servizio') {
            return new WP_REST_Response(['success' => false, 'message' => 'Richiesta non trovata'], 404);
        }

        // Accetta un singolo slot o un array 'slots'.
        $slots_in = isset($body['slots']) && is_array($body['slots']) ? $body['slots'] : [$body];
        $created = [];

        foreach ($slots_in as $s) {
            $data_ora = isset($s['data_ora']) ? sanitize_text_field($s['data_ora']) : '';
            if ($data_ora === '' || strtotime($data_ora) === false) {
                continue;
            }
            $slot_id = WeCoop_Appuntamenti_Repository::create_slot([
                'richiesta_id' => $richiesta_id,
                'data_ora'     => date('Y-m-d H:i:s', strtotime($data_ora)),
                'durata_min'   => isset($s['durata_min']) ? (int) $s['durata_min'] : 30,
                'sede'         => isset($s['sede']) ? sanitize_text_field($s['sede']) : null,
                'indirizzo'    => isset($s['indirizzo']) ? sanitize_text_field($s['indirizzo']) : null,
                'note'         => isset($s['note']) ? sanitize_textarea_field($s['note']) : null,
                'created_by'   => $operator_id,
            ]);
            if ($slot_id) {
                $created[] = $slot_id;
            }
        }

        if (empty($created)) {
            return new WP_REST_Response(['success' => false, 'message' => 'Nessuno slot valido'], 400);
        }

        // Porta la richiesta in attesa di scelta appuntamento (se non gia confermato).
        $stato_corrente = get_post_meta($richiesta_id, 'stato', true);
        if ($stato_corrente !== WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED) {
            self::set_richiesta_stato($richiesta_id, WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
        }

        do_action('wecoop_appuntamento_slot_proposti', $richiesta_id, $created, $operator_id);

        return new WP_REST_Response([
            'success'  => true,
            'message'  => 'Slot creati',
            'slot_ids' => $created,
        ], 201);
    }

    public static function elimina_slot($request) {
        $id = (int) $request['id'];
        $slot = WeCoop_Appuntamenti_Repository::get_slot($id);
        if (!$slot) {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot non trovato'], 404);
        }
        if ($slot['stato'] === 'booked') {
            return new WP_REST_Response(['success' => false, 'message' => 'Slot prenotato, non eliminabile'], 409);
        }
        WeCoop_Appuntamenti_Repository::delete_slot($id);
        return new WP_REST_Response(['success' => true, 'message' => 'Slot eliminato'], 200);
    }

    /* -----------------------------------------------------------------
     * STATO RICHIESTA
     * --------------------------------------------------------------- */

    /**
     * Aggiorna lo stato della richiesta_servizio e lancia l'hook standard
     * usato dal sistema di notifiche push.
     */
    private static function set_richiesta_stato($richiesta_id, $nuovo_stato) {
        $old = get_post_meta($richiesta_id, 'stato', true);
        if ($old === $nuovo_stato) {
            return;
        }
        update_post_meta($richiesta_id, 'stato', $nuovo_stato);
        do_action('wecoop_richiesta_servizio_status_changed', $richiesta_id, $old, $nuovo_stato);
    }
}
