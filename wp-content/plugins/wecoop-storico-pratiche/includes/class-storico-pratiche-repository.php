<?php
/**
 * Repository: documenti dello storico pratiche.
 * Gestisce la tabella custom e le operazioni CRUD.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Storico_Pratiche_Repository {

    const DB_VERSION_OPTION = 'wecoop_storico_pratiche_db_version';
    const DB_VERSION = '1.0.0';

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'wecoop_pratiche_documenti';
    }

    /**
     * Crea/aggiorna lo schema della tabella.
     */
    public static function install() {
        global $wpdb;

        $table = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            tipo varchar(40) NOT NULL,
            anno smallint(6) DEFAULT NULL,
            titolo varchar(255) DEFAULT NULL,
            file_name varchar(255) NOT NULL,
            file_path varchar(255) NOT NULL,
            file_size bigint(20) unsigned DEFAULT 0,
            mime_type varchar(100) DEFAULT NULL,
            uploaded_by bigint(20) unsigned DEFAULT NULL,
            data_caricamento datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY tipo (tipo),
            KEY user_tipo (user_id, tipo)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

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

    /**
     * Inserisce un nuovo documento.
     *
     * @return int|false ID inserito o false in errore.
     */
    public static function insert(array $data) {
        global $wpdb;

        $defaults = [
            'user_id'          => 0,
            'tipo'             => '',
            'anno'             => null,
            'titolo'           => null,
            'file_name'        => '',
            'file_path'        => '',
            'file_size'        => 0,
            'mime_type'        => null,
            'uploaded_by'      => get_current_user_id(),
            'data_caricamento' => current_time('mysql'),
        ];
        $data = array_merge($defaults, $data);

        $result = $wpdb->insert(
            self::table_name(),
            [
                'user_id'          => (int) $data['user_id'],
                'tipo'             => (string) $data['tipo'],
                'anno'             => $data['anno'] !== null ? (int) $data['anno'] : null,
                'titolo'           => $data['titolo'] !== null ? (string) $data['titolo'] : null,
                'file_name'        => (string) $data['file_name'],
                'file_path'        => (string) $data['file_path'],
                'file_size'        => (int) $data['file_size'],
                'mime_type'        => $data['mime_type'],
                'uploaded_by'      => (int) $data['uploaded_by'],
                'data_caricamento' => (string) $data['data_caricamento'],
            ],
            ['%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s']
        );

        if ($result === false) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Recupera un documento per ID.
     */
    public static function get($id) {
        global $wpdb;
        $table = self::table_name();
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $id),
            ARRAY_A
        );
    }

    /**
     * Documenti di un utente, opzionalmente filtrati per tipo, ordinati per anno/data DESC.
     */
    public static function get_by_user($user_id, $tipo = '') {
        global $wpdb;
        $table = self::table_name();

        if ($tipo !== '') {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE user_id = %d AND tipo = %s ORDER BY anno DESC, data_caricamento DESC",
                    (int) $user_id,
                    $tipo
                ),
                ARRAY_A
            );
        } else {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE user_id = %d ORDER BY anno DESC, data_caricamento DESC",
                    (int) $user_id
                ),
                ARRAY_A
            );
        }

        return is_array($rows) ? $rows : [];
    }

    /**
     * Conteggio documenti per un utente.
     */
    public static function count_by_user($user_id) {
        global $wpdb;
        $table = self::table_name();
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id = %d", (int) $user_id)
        );
    }

    /**
     * Conteggio totale documenti.
     */
    public static function count_all() {
        global $wpdb;
        $table = self::table_name();
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    /**
     * Elimina un documento (solo record DB; il file va rimosso a parte).
     */
    public static function delete($id) {
        global $wpdb;
        return (bool) $wpdb->delete(self::table_name(), ['id' => (int) $id], ['%d']);
    }
}
