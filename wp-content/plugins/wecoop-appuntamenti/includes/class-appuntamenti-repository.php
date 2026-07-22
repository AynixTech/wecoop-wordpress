<?php
/**
 * Repository: slot proposti e appuntamenti confermati.
 * Gestisce due tabelle custom e le operazioni CRUD/transazionali.
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Appuntamenti_Repository {

    const DB_VERSION_OPTION = 'wecoop_appuntamenti_db_version';
    const DB_VERSION = '1.0.0';

    public static function slot_table() {
        global $wpdb;
        return $wpdb->prefix . 'wecoop_appuntamento_slot';
    }

    public static function appuntamenti_table() {
        global $wpdb;
        return $wpdb->prefix . 'wecoop_appuntamenti';
    }

    /**
     * Crea/aggiorna lo schema delle tabelle.
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $slot = self::slot_table();
        $appt = self::appuntamenti_table();

        $sql_slot = "CREATE TABLE $slot (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            richiesta_id bigint(20) unsigned NOT NULL,
            data_ora datetime NOT NULL,
            durata_min smallint(6) NOT NULL DEFAULT 30,
            sede varchar(255) DEFAULT NULL,
            indirizzo varchar(255) DEFAULT NULL,
            note text DEFAULT NULL,
            stato varchar(20) NOT NULL DEFAULT 'proposed',
            created_by bigint(20) unsigned DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY richiesta_id (richiesta_id),
            KEY stato (stato),
            KEY richiesta_stato (richiesta_id, stato)
        ) $charset_collate;";

        $sql_appt = "CREATE TABLE $appt (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            richiesta_id bigint(20) unsigned NOT NULL,
            slot_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            operator_id bigint(20) unsigned DEFAULT NULL,
            data_ora datetime NOT NULL,
            durata_min smallint(6) NOT NULL DEFAULT 30,
            sede varchar(255) DEFAULT NULL,
            indirizzo varchar(255) DEFAULT NULL,
            note text DEFAULT NULL,
            stato varchar(20) NOT NULL DEFAULT 'confirmed',
            confirmed_at datetime DEFAULT NULL,
            cancelled_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY richiesta_id (richiesta_id),
            KEY user_id (user_id),
            KEY slot_id (slot_id),
            KEY stato (stato)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_slot);
        dbDelta($sql_appt);

        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Esegue l'install se la versione DB e' cambiata.
     */
    public static function maybe_upgrade() {
        if (get_option(self::DB_VERSION_OPTION) !== self::DB_VERSION) {
            self::install();
        }
    }

    /* ---------------------------------------------------------------------
     * SLOT
     * ------------------------------------------------------------------- */

    /**
     * Inserisce uno slot proposto.
     *
     * @return int|false ID inserito o false.
     */
    public static function create_slot(array $data) {
        global $wpdb;

        $inserted = $wpdb->insert(
            self::slot_table(),
            [
                'richiesta_id' => (int) $data['richiesta_id'],
                'data_ora'     => $data['data_ora'],
                'durata_min'   => isset($data['durata_min']) ? (int) $data['durata_min'] : 30,
                'sede'         => isset($data['sede']) ? $data['sede'] : null,
                'indirizzo'    => isset($data['indirizzo']) ? $data['indirizzo'] : null,
                'note'         => isset($data['note']) ? $data['note'] : null,
                'stato'        => 'proposed',
                'created_by'   => isset($data['created_by']) ? (int) $data['created_by'] : null,
                'created_at'   => current_time('mysql'),
            ],
            ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );

        return $inserted ? (int) $wpdb->insert_id : false;
    }

    public static function get_slot($slot_id) {
        global $wpdb;
        $slot_id = (int) $slot_id;
        return $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . self::slot_table() . ' WHERE id = %d', $slot_id),
            ARRAY_A
        );
    }

    /**
     * Slot proposti (disponibili) per una richiesta.
     *
     * @param bool $only_future filtra runtime gli slot passati.
     */
    public static function get_proposed_slots($richiesta_id, $only_future = true) {
        global $wpdb;
        $richiesta_id = (int) $richiesta_id;

        $sql = 'SELECT * FROM ' . self::slot_table()
            . " WHERE richiesta_id = %d AND stato = 'proposed'";
        $params = [$richiesta_id];

        if ($only_future) {
            $sql .= ' AND data_ora >= %s';
            $params[] = current_time('mysql');
        }
        $sql .= ' ORDER BY data_ora ASC';

        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }

    /**
     * Tutti gli slot di una richiesta (per il back-office).
     */
    public static function get_all_slots($richiesta_id) {
        global $wpdb;
        $richiesta_id = (int) $richiesta_id;
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . self::slot_table() . ' WHERE richiesta_id = %d ORDER BY data_ora ASC',
                $richiesta_id
            ),
            ARRAY_A
        );
    }

    public static function update_slot_status($slot_id, $stato) {
        global $wpdb;
        return $wpdb->update(
            self::slot_table(),
            ['stato' => $stato],
            ['id' => (int) $slot_id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Marca uno slot come 'booked' solo se attualmente 'proposed' (check atomico
     * anti doppia prenotazione).
     *
     * @return bool true se lo slot e' stato effettivamente riservato.
     */
    public static function book_slot_atomic($slot_id) {
        global $wpdb;
        $affected = $wpdb->query(
            $wpdb->prepare(
                'UPDATE ' . self::slot_table()
                . " SET stato = 'booked' WHERE id = %d AND stato = 'proposed'",
                (int) $slot_id
            )
        );
        return $affected === 1;
    }

    public static function delete_slot($slot_id) {
        global $wpdb;
        return $wpdb->delete(self::slot_table(), ['id' => (int) $slot_id], ['%d']);
    }

    /* ---------------------------------------------------------------------
     * APPUNTAMENTI
     * ------------------------------------------------------------------- */

    /**
     * @return int|false ID appuntamento creato.
     */
    public static function create_appuntamento(array $data) {
        global $wpdb;
        $now = current_time('mysql');

        $inserted = $wpdb->insert(
            self::appuntamenti_table(),
            [
                'richiesta_id' => (int) $data['richiesta_id'],
                'slot_id'      => (int) $data['slot_id'],
                'user_id'      => (int) $data['user_id'],
                'operator_id'  => isset($data['operator_id']) ? (int) $data['operator_id'] : null,
                'data_ora'     => $data['data_ora'],
                'durata_min'   => isset($data['durata_min']) ? (int) $data['durata_min'] : 30,
                'sede'         => isset($data['sede']) ? $data['sede'] : null,
                'indirizzo'    => isset($data['indirizzo']) ? $data['indirizzo'] : null,
                'note'         => isset($data['note']) ? $data['note'] : null,
                'stato'        => 'confirmed',
                'confirmed_at' => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            ['%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? (int) $wpdb->insert_id : false;
    }

    public static function get_appuntamento($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . self::appuntamenti_table() . ' WHERE id = %d', (int) $id),
            ARRAY_A
        );
    }

    /**
     * Appuntamento confermato attivo per una richiesta (se esiste).
     */
    public static function get_active_appuntamento_by_richiesta($richiesta_id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::appuntamenti_table()
                . " WHERE richiesta_id = %d AND stato = 'confirmed' ORDER BY id DESC LIMIT 1",
                (int) $richiesta_id
            ),
            ARRAY_A
        );
    }

    /**
     * Appuntamenti di un utente.
     */
    public static function get_appuntamenti_by_user($user_id, $only_active = false) {
        global $wpdb;
        $sql = 'SELECT * FROM ' . self::appuntamenti_table() . ' WHERE user_id = %d';
        $params = [(int) $user_id];
        if ($only_active) {
            $sql .= " AND stato = 'confirmed'";
        }
        $sql .= ' ORDER BY data_ora ASC';
        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }

    public static function update_appuntamento($id, array $fields, array $formats) {
        global $wpdb;
        $fields['updated_at'] = current_time('mysql');
        $formats[] = '%s';
        return $wpdb->update(
            self::appuntamenti_table(),
            $fields,
            ['id' => (int) $id],
            $formats,
            ['%d']
        );
    }
}
