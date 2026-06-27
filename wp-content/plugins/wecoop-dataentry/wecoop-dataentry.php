<?php
/**
 * Plugin Name: WeCoop Data Entry
 * Plugin URI: https://www.wecoop.org
 * Description: Inserimento utenti con modello dati WECOOP compatibile con i moduli CRM esistenti.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-dataentry
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: wecoop-core
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WeCoop_User_Meta')) {
    require_once WP_PLUGIN_DIR . '/wecoop-core/includes/class-user-meta.php';
}

require_once __DIR__ . '/includes/class-xlsx-reader.php';

class WeCoop_DataEntry {
    private const MENU_SLUG = 'wecoop-dataentry';

    /** Nome del foglio Excel da importare. */
    private const IMPORT_SHEET = 'Registro Giornaliero';

    /** Riga (1-based) che contiene le intestazioni nel registro. */
    private const IMPORT_HEADER_ROW = 8;

    /** Dominio usato per generare email placeholder quando assente. */
    private const IMPORT_PLACEHOLDER_DOMAIN = 'wecoop.org';

    public static function get_instance() {
        static $instance = null;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_post_wecoop_dataentry_create_user', [$this, 'handle_create_user']);
        add_action('admin_post_wecoop_dataentry_update_user', [$this, 'handle_update_user']);
        add_action('admin_post_wecoop_dataentry_delete_user', [$this, 'handle_delete_user']);
        add_action('admin_post_wecoop_dataentry_import', [$this, 'handle_import']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('init', [$this, 'ensure_capability']);
    }

    private function current_user_has_role($role) {
        $user = wp_get_current_user();
        return $user && in_array($role, (array) $user->roles, true);
    }

    private function can_access() {
        return current_user_can('wecoop_dataentry_access') || current_user_can('manage_options') || current_user_can('create_users') || $this->current_user_has_role('operator');
    }

    public function ensure_capability() {
        foreach (['administrator', 'operator'] as $role_name) {
            $role = get_role($role_name);
            if ($role && !$role->has_cap('wecoop_dataentry_access')) {
                $role->add_cap('wecoop_dataentry_access');
            }
        }
    }

    public function register_menu() {
        if (!$this->can_access()) {
            return;
        }

        add_menu_page(
            'WeCoop Data Entry',
            'WeCoop Data Entry',
            'wecoop_dataentry_access',
            self::MENU_SLUG,
            [$this, 'render_page']
            ,
            'dashicons-database-add',
            26
        );
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, self::MENU_SLUG) === false) {
            return;
        }

        wp_register_style('wecoop-dataentry-admin', false, [], '1.0.0');
        wp_enqueue_style('wecoop-dataentry-admin');
        wp_add_inline_style('wecoop-dataentry-admin', $this->get_inline_css());

        wp_register_script('wecoop-dataentry-admin', false, [], '1.0.0', true);
        wp_enqueue_script('wecoop-dataentry-admin');
        wp_add_inline_script('wecoop-dataentry-admin', $this->get_inline_js());
    }

    private function get_page_url(array $args = []) {
        return add_query_arg(array_merge([
            'page' => self::MENU_SLUG,
        ], $args), admin_url('admin.php'));
    }

    private function get_view() {
        $view = sanitize_key($_GET['view'] ?? 'list');
        return in_array($view, ['list', 'new', 'detail', 'edit', 'import'], true) ? $view : 'list';
    }

    /**
     * Recupera gli utenti data-entry applicando ricerca e filtri avanzati.
     *
     * @param array $args {
     *     @type int    $per_page
     *     @type int    $paged
     *     @type string $search        Ricerca su login/email/nome/cognome/telefono/CF.
     *     @type string $status        '', 'complete', 'incomplete'
     *     @type string $role          Ruolo WP.
     *     @type string $categoria     categoria_profilazione.
     *     @type string $source        '', 'import', 'manual'.
     *     @type string $orderby       'created', 'name', 'email', 'completion'.
     *     @type string $order         'ASC'|'DESC'.
     * }
     */
    private function get_inserted_users(array $args = []) {
        $defaults = [
            'per_page' => 20,
            'paged'    => 1,
            'search'   => '',
            'status'   => '',
            'role'     => '',
            'categoria' => '',
            'source'   => '',
            'orderby'  => 'created',
            'order'    => 'DESC',
        ];
        $args = array_merge($defaults, $args);

        $meta_query = [
            'relation' => 'AND',
            [
                'key'     => 'wecoop_dataentry_created_at',
                'compare' => 'EXISTS',
            ],
        ];

        if ($args['status'] === 'complete') {
            $meta_query[] = [
                'key'     => 'profilo_completo',
                'value'   => '1',
                'compare' => '=',
            ];
        } elseif ($args['status'] === 'incomplete') {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => 'profilo_completo', 'value' => '1', 'compare' => '!='],
                ['key' => 'profilo_completo', 'compare' => 'NOT EXISTS'],
            ];
        }

        if ($args['categoria'] !== '') {
            $meta_query[] = [
                'key'     => 'categoria_profilazione',
                'value'   => $args['categoria'],
                'compare' => '=',
            ];
        }

        if ($args['source'] === 'import') {
            $meta_query[] = [
                'key'     => 'wecoop_dataentry_source',
                'value'   => 'excel_registro_giornaliero',
                'compare' => '=',
            ];
        } elseif ($args['source'] === 'manual') {
            $meta_query[] = [
                'key'     => 'wecoop_dataentry_source',
                'compare' => 'NOT EXISTS',
            ];
        }

        $query_args = [
            'number'     => (int) $args['per_page'],
            'paged'      => max(1, (int) $args['paged']),
            'meta_query' => $meta_query,
        ];

        if ($args['role'] !== '') {
            $query_args['role'] = $args['role'];
        }

        // Ordinamento
        switch ($args['orderby']) {
            case 'name':
                $query_args['orderby'] = 'display_name';
                break;
            case 'email':
                $query_args['orderby'] = 'user_email';
                break;
            case 'completion':
                $query_args['meta_key'] = 'profilo_percentuale';
                $query_args['orderby']  = 'meta_value_num';
                break;
            case 'created':
            default:
                $query_args['meta_key'] = 'wecoop_dataentry_created_at';
                $query_args['orderby']  = 'meta_value';
                break;
        }
        $query_args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        if ($args['search'] !== '') {
            // Ricerca in OR tra colonne utente e meta del profilo
            // (gestita unendo gli ID corrispondenti).
            $query_args = $this->merge_search_queries($query_args, $args['search']);
        }

        $query = new WP_User_Query($query_args);

        return [
            'users' => $query->get_results(),
            'total' => (int) $query->get_total(),
        ];
    }

    /**
     * Esegue la ricerca testuale come OR tra colonne utente e meta profilo.
     * WP_User_Query non supporta nativamente OR tra 'search' e 'meta_query',
     * quindi raccogliamo gli ID corrispondenti ai meta e li uniamo via 'include'.
     */
    private function merge_search_queries(array $query_args, $search) {
        // Mantiene le clausole meta dei filtri (escludendo la chiave 'relation').
        $base_meta = ['relation' => 'AND'];
        foreach ($query_args['meta_query'] as $key => $clause) {
            if ($key === 'relation') {
                continue;
            }
            if (is_array($clause) && isset($clause['relation']) && $clause['relation'] === 'OR') {
                continue; // eventuale blocco ricerca OR: gestito separatamente
            }
            $base_meta[] = $clause;
        }

        // ID che matchano i meta del profilo.
        $meta_match = new WP_User_Query([
            'number'     => -1,
            'fields'     => 'ID',
            'meta_query' => [
                'relation' => 'OR',
                ['key' => 'nome', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'cognome', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'telefono', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'telefono_completo', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'codice_fiscale', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'citta', 'value' => $search, 'compare' => 'LIKE'],
            ],
        ]);
        $meta_ids = array_map('intval', (array) $meta_match->get_results());

        // ID che matchano le colonne utente.
        $col_match = new WP_User_Query([
            'number'         => -1,
            'fields'         => 'ID',
            'search'         => '*' . $search . '*',
            'search_columns' => ['user_login', 'user_email', 'display_name', 'user_nicename'],
        ]);
        $col_ids = array_map('intval', (array) $col_match->get_results());

        $include = array_values(array_unique(array_merge($meta_ids, $col_ids)));
        if (empty($include)) {
            $include = [0]; // forza nessun risultato
        }

        unset($query_args['search'], $query_args['search_columns']);
        $query_args['meta_query'] = $base_meta;
        $query_args['include'] = $include;

        return $query_args;
    }

    private function get_inserted_user_count($status = '') {
        $meta_query = [
            [
                'key'     => 'wecoop_dataentry_created_at',
                'compare' => 'EXISTS',
            ],
        ];

        if ($status === 'complete') {
            $meta_query[] = ['key' => 'profilo_completo', 'value' => '1', 'compare' => '='];
        } elseif ($status === 'incomplete') {
            $meta_query[] = [
                'relation' => 'OR',
                ['key' => 'profilo_completo', 'value' => '1', 'compare' => '!='],
                ['key' => 'profilo_completo', 'compare' => 'NOT EXISTS'],
            ];
        } elseif ($status === 'import') {
            $meta_query[] = ['key' => 'wecoop_dataentry_source', 'value' => 'excel_registro_giornaliero', 'compare' => '='];
        }

        $query = new WP_User_Query([
            'number'     => 1,
            'fields'     => 'ids',
            'meta_query' => $meta_query,
        ]);

        return (int) $query->get_total();
    }

    private function is_dataentry_user($user_id) {
        return $user_id > 0 && get_user_meta($user_id, 'wecoop_dataentry_created_at', true) !== '';
    }

    private function get_user_defaults($user_id = 0) {
        $defaults = [
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'user_email' => '',
            'user_pass' => '',
            'user_role' => 'subscriber',
            'send_notification' => 1,
            'nome' => '',
            'cognome' => '',
            'sesso' => '',
            'data_nascita' => '',
            'luogo_nascita' => '',
            'codice_fiscale' => '',
            'nazionalita' => '',
            'stato_civile' => '',
            'telefono' => '',
            'prefix' => '',
            'indirizzo' => '',
            'civico' => '',
            'citta' => '',
            'cap' => '',
            'provincia' => '',
            'nazione' => '',
            'numero_figli' => '',
            'figli_minori' => '',
            'figli_minori_numero' => '',
            'persone_a_carico' => '',
            'tipo_lavoro' => '',
            'contratto' => '',
            'settore' => '',
            'anni_lavoro' => '',
            'reddito_annuo' => '',
            'reddito_mensile' => '',
            'altri_redditi' => '',
            'prestiti_attivi' => '',
            'rate_mensili' => '',
            'ritardi_pagamenti' => '',
            'doc_carta_identita' => '',
            'doc_codice_fiscale' => '',
            'doc_cu' => '',
            'doc_dichiarazione_redditi' => '',
            'categoria_profilazione' => '',
            'capacita_economica' => '',
            'interesse' => '',
            'professione' => '',
            'paese_provenienza' => '',
            'note_dataentry' => '',
        ];

        if ($user_id <= 0) {
            return $defaults;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return $defaults;
        }

        $profile = WeCoop_User_Meta::get_user_profile_data($user_id);
        $defaults['first_name'] = $profile['first_name'] ?? '';
        $defaults['last_name'] = $profile['last_name'] ?? '';
        $defaults['display_name'] = $profile['display_name'] ?? '';
        $defaults['user_email'] = $profile['user_email'] ?? '';
        $defaults['user_role'] = $user->roles[0] ?? 'subscriber';

        foreach ($defaults as $key => $value) {
            if ($key === 'first_name' || $key === 'last_name' || $key === 'display_name' || $key === 'user_email' || $key === 'user_role') {
                continue;
            }

            if (array_key_exists($key, $profile)) {
                $defaults[$key] = (string) $profile[$key];
            }
        }

        return $defaults;
    }

    private function get_user_detail_data($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return null;
        }

        $profile = WeCoop_User_Meta::get_user_profile_data($user_id);

        return [
            'user' => $user,
            'profile' => $profile,
            'created_at' => (string) get_user_meta($user_id, 'wecoop_dataentry_created_at', true),
            'created_by' => (int) get_user_meta($user_id, 'wecoop_dataentry_created_by', true),
            'updated_at' => (string) get_user_meta($user_id, 'wecoop_dataentry_updated_at', true),
        ];
    }

    private function get_inline_js() {
        return '
            document.addEventListener("DOMContentLoaded", function () {
                const modal = document.getElementById("wecoop-dataentry-delete-modal");
                if (!modal) {
                    return;
                }

                const form = modal.querySelector("form");
                const userIdInput = modal.querySelector("input[name=\"user_id\"]");
                const userNameNode = modal.querySelector("[data-role=\"user-name\"]");
                const openButtons = document.querySelectorAll("[data-wecoop-delete-user]");
                const closeModal = function () {
                    modal.classList.remove("is-open");
                };

                openButtons.forEach(function (button) {
                    button.addEventListener("click", function (event) {
                        event.preventDefault();
                        userIdInput.value = button.getAttribute("data-user-id") || "";
                        userNameNode.textContent = button.getAttribute("data-user-name") || "questo utente";
                        form.action = button.getAttribute("data-delete-action") || form.action;
                        modal.classList.add("is-open");
                    });
                });

                modal.querySelectorAll("[data-wecoop-close]").forEach(function (button) {
                    button.addEventListener("click", function (event) {
                        event.preventDefault();
                        closeModal();
                    });
                });

                modal.addEventListener("click", function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                document.addEventListener("keydown", function (event) {
                    if (event.key === "Escape") {
                        closeModal();
                    }
                });
            });
        ';
    }

    private function get_inline_css() {
        return '
            .wecoop-dataentry-wrap .wecoop-toolbar { display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; margin:20px 0; }
            .wecoop-dataentry-wrap .wecoop-summary-grid { display:grid; gap:16px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top:20px; }
            .wecoop-dataentry-wrap .wecoop-summary-card { background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:16px 18px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
            .wecoop-dataentry-wrap .wecoop-summary-label { color:#646970; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
            .wecoop-dataentry-wrap .wecoop-summary-value { font-size:32px; font-weight:700; margin-top:8px; color:#2271b1; }
            .wecoop-dataentry-wrap .wecoop-table-wrap { background:#fff; border:1px solid #dcdcde; border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,.04); overflow:hidden; }
            .wecoop-dataentry-wrap .wecoop-table { width:100%; border-collapse:collapse; }
            .wecoop-dataentry-wrap .wecoop-table th,
            .wecoop-dataentry-wrap .wecoop-table td { padding:12px 14px; border-bottom:1px solid #eef0f2; text-align:left; vertical-align:top; }
            .wecoop-dataentry-wrap .wecoop-table th { background:#f9fafb; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#646970; }
            .wecoop-dataentry-wrap .wecoop-table tr:hover td { background:#fcfcfd; }
            .wecoop-dataentry-wrap .wecoop-actions-inline { display:flex; gap:8px; flex-wrap:wrap; }
            .wecoop-dataentry-wrap .wecoop-status { display:inline-flex; align-items:center; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600; }
            .wecoop-dataentry-wrap .wecoop-status.is-complete { background:#e7f7ee; color:#137333; }
            .wecoop-dataentry-wrap .wecoop-status.is-incomplete { background:#fdecec; color:#b42318; }
            .wecoop-dataentry-wrap .wecoop-progress { display:flex; flex-direction:column; gap:4px; min-width:140px; }
            .wecoop-dataentry-wrap .wecoop-progress__track { position:relative; height:8px; border-radius:999px; background:#e9ebee; overflow:hidden; }
            .wecoop-dataentry-wrap .wecoop-progress__bar { position:absolute; inset:0 auto 0 0; height:100%; border-radius:999px; transition:width .2s ease; }
            .wecoop-dataentry-wrap .wecoop-progress__bar.is-low { background:#d63638; }
            .wecoop-dataentry-wrap .wecoop-progress__bar.is-mid { background:#dba617; }
            .wecoop-dataentry-wrap .wecoop-progress__bar.is-high { background:#68a020; }
            .wecoop-dataentry-wrap .wecoop-progress__bar.is-full { background:#1a8a3e; }
            .wecoop-dataentry-wrap .wecoop-progress__label { font-size:12px; font-weight:600; color:#50575e; }
            .wecoop-dataentry-wrap .wecoop-progress__label.is-full { color:#137333; }
            .wecoop-dataentry-wrap .wecoop-modal { display:none; position:fixed; inset:0; z-index:100000; background:rgba(12,18,28,.55); padding:20px; }
            .wecoop-dataentry-wrap .wecoop-modal.is-open { display:flex; align-items:center; justify-content:center; }
            .wecoop-dataentry-wrap .wecoop-modal__panel { width:min(100%, 560px); background:#fff; border-radius:14px; padding:24px; box-shadow:0 20px 80px rgba(0,0,0,.25); }
            .wecoop-dataentry-wrap .wecoop-modal__actions { display:flex; gap:12px; justify-content:flex-end; margin-top:24px; flex-wrap:wrap; }
            .wecoop-dataentry-wrap .wecoop-card { background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); margin-top:20px; }
            .wecoop-dataentry-wrap .wecoop-grid { display:grid; gap:16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .wecoop-dataentry-wrap .wecoop-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .wecoop-dataentry-wrap .wecoop-field { display:flex; flex-direction:column; gap:6px; }
            .wecoop-dataentry-wrap .wecoop-field label { font-weight:600; }
            .wecoop-dataentry-wrap .wecoop-field input[type="text"],
            .wecoop-dataentry-wrap .wecoop-field input[type="email"],
            .wecoop-dataentry-wrap .wecoop-field input[type="number"],
            .wecoop-dataentry-wrap .wecoop-field input[type="date"],
            .wecoop-dataentry-wrap .wecoop-field select,
            .wecoop-dataentry-wrap .wecoop-field textarea { width:100%; max-width:100%; }
            .wecoop-dataentry-wrap .wecoop-field textarea { min-height:110px; }
            .wecoop-dataentry-wrap .wecoop-section { margin-top:24px; padding-top:24px; border-top:1px solid #eef0f2; }
            .wecoop-dataentry-wrap .wecoop-section h2 { margin:0 0 8px; }
            .wecoop-dataentry-wrap .wecoop-help { color:#646970; margin:0; }
            .wecoop-dataentry-wrap .wecoop-actions { display:flex; align-items:center; gap:12px; margin-top:20px; flex-wrap:wrap; }
            .wecoop-dataentry-wrap .wecoop-notice { margin:16px 0 0; }
            .wecoop-dataentry-wrap .wecoop-badge-link { font-weight:600; }
            .wecoop-dataentry-wrap .wecoop-summary-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .wecoop-dataentry-wrap .wecoop-summary-card--link { display:block; text-decoration:none; color:inherit; transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
            .wecoop-dataentry-wrap .wecoop-summary-card--link:hover { border-color:#2271b1; box-shadow:0 2px 10px rgba(34,113,177,.12); transform:translateY(-1px); }
            .wecoop-dataentry-wrap .wecoop-summary-card--link.is-active { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1; }
            .wecoop-dataentry-wrap .wecoop-filters { background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:16px 18px; margin-top:20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
            .wecoop-dataentry-wrap .wecoop-filters__search { display:flex; gap:8px; margin-bottom:14px; }
            .wecoop-dataentry-wrap .wecoop-filters__search input[type="search"] { flex:1; height:38px; padding:0 12px; border:1px solid #c3c4c7; border-radius:8px; font-size:14px; }
            .wecoop-dataentry-wrap .wecoop-filters__row { display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end; }
            .wecoop-dataentry-wrap .wecoop-filters__field { display:flex; flex-direction:column; gap:4px; font-size:12px; font-weight:600; color:#646970; }
            .wecoop-dataentry-wrap .wecoop-filters__field select { min-width:150px; height:34px; border-radius:8px; }
            .wecoop-dataentry-wrap .wecoop-filters__actions { display:flex; gap:8px; align-items:center; margin-left:auto; }
            .wecoop-dataentry-wrap .wecoop-results-bar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin:18px 2px 8px; color:#646970; font-size:13px; }
            .wecoop-dataentry-wrap .wecoop-sort { text-decoration:none; color:#646970; display:inline-flex; align-items:center; gap:2px; }
            .wecoop-dataentry-wrap .wecoop-sort:hover { color:#2271b1; }
            .wecoop-dataentry-wrap .wecoop-sort.is-active { color:#2271b1; font-weight:700; }
            .wecoop-dataentry-wrap .wecoop-user-name { text-decoration:none; color:#1d2327; }
            .wecoop-dataentry-wrap .wecoop-user-name:hover { color:#2271b1; }
            .wecoop-dataentry-wrap .wecoop-muted { color:#646970; font-size:12px; }
            .wecoop-dataentry-wrap .wecoop-muted--warn { color:#b26a00; }
            .wecoop-dataentry-wrap .wecoop-empty { text-align:center; padding:32px 14px; color:#646970; }
            .wecoop-dataentry-wrap .wecoop-chip { display:inline-flex; align-items:center; padding:3px 9px; border-radius:999px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.03em; }
            .wecoop-dataentry-wrap .wecoop-chip--import { background:#e6f0fb; color:#1c5d99; }
            .wecoop-dataentry-wrap .wecoop-chip--manual { background:#eef0f2; color:#50575e; }
            .wecoop-dataentry-wrap .wecoop-pagination { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; margin-top:16px; }
            .wecoop-dataentry-wrap .wecoop-page { min-width:34px; height:34px; padding:0 10px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #dcdcde; border-radius:8px; background:#fff; color:#2271b1; text-decoration:none; font-size:13px; font-weight:600; }
            .wecoop-dataentry-wrap .wecoop-page:hover { border-color:#2271b1; background:#f3f8fd; }
            .wecoop-dataentry-wrap .wecoop-page.is-current { background:#2271b1; border-color:#2271b1; color:#fff; }
            .wecoop-dataentry-wrap .wecoop-page.is-disabled { color:#a7aaad; background:#f6f7f7; cursor:default; }
            .wecoop-dataentry-wrap .wecoop-page.is-gap { border:none; background:transparent; color:#646970; }
            @media (max-width: 960px) {
                .wecoop-dataentry-wrap .wecoop-summary-grid { grid-template-columns: 1fr; }
                .wecoop-dataentry-wrap .wecoop-summary-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .wecoop-dataentry-wrap .wecoop-grid,
                .wecoop-dataentry-wrap .wecoop-grid--3 { grid-template-columns: 1fr; }
                .wecoop-dataentry-wrap .wecoop-filters__actions { margin-left:0; }
            }
        ';
    }

    public function handle_create_user() {
        if (!$this->can_access()) {
            wp_die('Accesso negato');
        }

        check_admin_referer('wecoop_dataentry_create_user');

        $payload = $this->collect_payload();
        $validation_errors = $this->validate_payload($payload);
        if (!empty($validation_errors)) {
            $this->redirect_with_error('validation_error', implode(' ', $validation_errors));
        }

        $user_login = $this->build_unique_login($payload);
        $user_pass = !empty($payload['user_pass']) ? $payload['user_pass'] : wp_generate_password(20, true, true);
        $user_role = $this->normalize_user_role($payload['user_role'] ?? 'subscriber');

        $user_id = wp_insert_user([
            'user_login' => $user_login,
            'user_pass' => $user_pass,
            'user_email' => $payload['user_email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'display_name' => $payload['display_name'],
            'role' => $user_role,
        ]);

        if (is_wp_error($user_id)) {
            $this->redirect_with_error('create_failed', $user_id->get_error_message());
        }

        $result = WeCoop_User_Meta::save_user_profile($user_id, $payload);
        if (is_wp_error($result)) {
            wp_delete_user($user_id);
            $this->redirect_with_error('save_failed', $result->get_error_message());
        }

        update_user_meta($user_id, 'wecoop_dataentry_created_at', current_time('mysql'));
        update_user_meta($user_id, 'wecoop_dataentry_created_by', get_current_user_id());
        update_user_meta($user_id, 'wecoop_dataentry_updated_at', current_time('mysql'));

        if (!empty($payload['send_notification'])) {
            wp_new_user_notification($user_id, null, 'user');
        }

        wp_redirect(add_query_arg([
            'page' => self::MENU_SLUG,
            'view' => 'detail',
            'message' => 'created',
            'user_id' => $user_id,
        ], admin_url('users.php')));
        exit;
    }

    public function handle_update_user() {
        if (!$this->can_access()) {
            wp_die('Accesso negato');
        }

        check_admin_referer('wecoop_dataentry_update_user');

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if (!$this->is_dataentry_user($user_id)) {
            wp_die('Utente non trovato');
        }

        $payload = $this->collect_payload();
        $validation_errors = $this->validate_payload($payload, $user_id);
        if (!empty($validation_errors)) {
            $this->redirect_with_error('validation_error', implode(' ', $validation_errors));
        }

        $payload['user_id'] = $user_id;

        $user = get_userdata($user_id);
        if (!$user) {
            wp_die('Utente non trovato');
        }

        $update_data = [
            'ID' => $user_id,
            'user_email' => $payload['user_email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'display_name' => $payload['display_name'],
            'role' => $this->normalize_user_role($payload['user_role'] ?? ($user->roles[0] ?? 'subscriber')),
        ];

        if (trim((string) $payload['user_pass']) !== '') {
            $update_data['user_pass'] = $payload['user_pass'];
        }

        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            $this->redirect_with_error('save_failed', $result->get_error_message());
        }

        $save_result = WeCoop_User_Meta::save_user_profile($user_id, $payload);
        if (is_wp_error($save_result)) {
            $this->redirect_with_error('save_failed', $save_result->get_error_message());
        }

        update_user_meta($user_id, 'wecoop_dataentry_updated_at', current_time('mysql'));

        wp_redirect(add_query_arg([
            'page' => self::MENU_SLUG,
            'view' => 'detail',
            'message' => 'updated',
            'user_id' => $user_id,
        ], admin_url('users.php')));
        exit;
    }

    public function handle_delete_user() {
        if (!$this->can_access()) {
            wp_die('Accesso negato');
        }

        check_admin_referer('wecoop_dataentry_delete_user');

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if (!$this->is_dataentry_user($user_id)) {
            wp_die('Utente non trovato');
        }

        if (wp_delete_user($user_id)) {
            wp_redirect(add_query_arg([
                'page' => self::MENU_SLUG,
                'view' => 'list',
                'message' => 'deleted',
            ], admin_url('users.php')));
            exit;
        }

        $this->redirect_with_error('delete_failed', 'Impossibile eliminare l\'utente.');
    }

    private function collect_payload() {
        $payload = [
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'display_name' => sanitize_text_field($_POST['display_name'] ?? ''),
            'user_email' => sanitize_email($_POST['user_email'] ?? ''),
            'user_pass' => isset($_POST['user_pass']) ? (string) $_POST['user_pass'] : '',
            'user_role' => sanitize_text_field($_POST['user_role'] ?? 'subscriber'),
            'send_notification' => !empty($_POST['send_notification']),
        ];

        $text_fields = [
            'nome', 'cognome', 'sesso', 'data_nascita', 'luogo_nascita', 'codice_fiscale',
            'nazionalita', 'stato_civile', 'telefono', 'prefix', 'indirizzo', 'civico',
            'citta', 'cap', 'provincia', 'nazione', 'numero_figli', 'figli_minori_numero',
            'persone_a_carico', 'tipo_lavoro', 'contratto', 'settore', 'anni_lavoro',
            'reddito_annuo', 'reddito_mensile', 'rate_mensili', 'categoria_profilazione',
            'capacita_economica', 'interesse', 'professione', 'paese_provenienza',
        ];

        $boolean_fields = [
            'figli_minori', 'altri_redditi', 'prestiti_attivi', 'doc_carta_identita',
            'doc_codice_fiscale', 'doc_cu', 'doc_dichiarazione_redditi',
        ];

        foreach ($text_fields as $field) {
            $payload[$field] = isset($_POST[$field]) ? sanitize_text_field(wp_unslash($_POST[$field])) : '';
        }

        foreach ($boolean_fields as $field) {
            $payload[$field] = !empty($_POST[$field]) ? '1' : '0';
        }

        $payload['ritardi_pagamenti'] = sanitize_text_field($_POST['ritardi_pagamenti'] ?? '');
        $payload['note_dataentry'] = isset($_POST['note_dataentry']) ? sanitize_textarea_field(wp_unslash($_POST['note_dataentry'])) : '';

        $payload['telefono_completo'] = WeCoop_User_Meta::build_phone_complete($payload);

        return $payload;
    }

    private function validate_payload(array $payload, $user_id = 0) {
        $errors = [];

        if (!is_email($payload['user_email'])) {
            $errors[] = 'L\'email e\' obbligatoria e deve essere valida.';
        }

        foreach (['first_name', 'last_name', 'telefono', 'citta', 'indirizzo', 'codice_fiscale', 'data_nascita', 'nazionalita'] as $field) {
            if (trim((string) ($payload[$field] ?? '')) === '') {
                $errors[] = sprintf('Il campo %s e\' obbligatorio.', $field);
            }
        }

        $existing_user_id = email_exists($payload['user_email']);
        if ($existing_user_id && (int) $existing_user_id !== (int) $user_id) {
            $errors[] = 'Esiste gia\' un utente con questa email.';
        }

        return $errors;
    }

    private function build_unique_login(array $payload) {
        $base = sanitize_user((string) ($payload['user_email'] ?: $payload['first_name'] . '.' . $payload['last_name']), true);
        if ($base === '') {
            $base = 'wecoop-user';
        }

        $login = $base;
        $counter = 1;

        while (username_exists($login)) {
            $login = $base . '-' . $counter;
            $counter++;
        }

        return $login;
    }

    private function normalize_user_role($role) {
        $allowed_roles = ['subscriber', 'socio', 'operator'];
        $role = sanitize_key($role);

        return in_array($role, $allowed_roles, true) ? $role : 'subscriber';
    }

    private function redirect_with_error($code, $message, $view = '') {
        if ($view !== '') {
            wp_redirect(add_query_arg([
                'page' => self::MENU_SLUG,
                'view' => $view,
                'message' => $code,
                'error_msg' => rawurlencode($message),
            ], admin_url('admin.php')));
            exit;
        }

        wp_redirect(add_query_arg([
            'page' => self::MENU_SLUG,
            'message' => $code,
            'error_msg' => rawurlencode($message),
        ], admin_url('users.php')));
        exit;
    }

    private function render_input($name, $label, $value = '', $type = 'text', $extra = '') {
        echo '<div class="wecoop-field">';
        echo '<label for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
        echo '<input type="' . esc_attr($type) . '" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" ' . $extra . '>';
        echo '</div>';
    }

    private function render_select($name, $label, array $options, $value = '') {
        echo '<div class="wecoop-field">';
        echo '<label for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
        echo '<select id="' . esc_attr($name) . '" name="' . esc_attr($name) . '">';
        foreach ($options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
        echo '</div>';
    }

    private function render_textarea($name, $label, $value = '') {
        echo '<div class="wecoop-field">';
        echo '<label for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
        echo '<textarea id="' . esc_attr($name) . '" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea>';
        echo '</div>';
    }

    private function render_yes_no_select($name, $label, $value = '') {
        $options = [
            '' => 'Seleziona',
            '1' => 'SI',
            '0' => 'NO',
        ];

        $this->render_select($name, $label, $options, (string) $value);
    }

    private function render_page_navigation($current_view, $user_id = 0) {
        $list_url = $this->get_page_url(['view' => 'list']);
        $new_url = $this->get_page_url(['view' => 'new']);
        $import_url = $this->get_page_url(['view' => 'import']);
        $detail_url = $user_id > 0 ? $this->get_page_url(['view' => 'detail', 'user_id' => $user_id]) : '';
        $edit_url = $user_id > 0 ? $this->get_page_url(['view' => 'edit', 'user_id' => $user_id]) : '';

        echo '<div class="nav-tab-wrapper" style="margin-top:16px;">';
        echo '<a href="' . esc_url($list_url) . '" class="nav-tab ' . esc_attr($current_view === 'list' ? 'nav-tab-active' : '') . '">Lista utenti</a>';
        echo '<a href="' . esc_url($new_url) . '" class="nav-tab ' . esc_attr($current_view === 'new' ? 'nav-tab-active' : '') . '">Nuovo utente</a>';
        echo '<a href="' . esc_url($import_url) . '" class="nav-tab ' . esc_attr($current_view === 'import' ? 'nav-tab-active' : '') . '">Importa Excel</a>';

        if ($detail_url !== '') {
            echo '<a href="' . esc_url($detail_url) . '" class="nav-tab ' . esc_attr($current_view === 'detail' ? 'nav-tab-active' : '') . '">Dettagli</a>';
            echo '<a href="' . esc_url($edit_url) . '" class="nav-tab ' . esc_attr($current_view === 'edit' ? 'nav-tab-active' : '') . '">Modifica</a>';
        }

        echo '</div>';
    }

    private function render_user_actions($user_id, $user_name) {
        $detail_url = $this->get_page_url(['view' => 'detail', 'user_id' => $user_id]);
        $edit_url = $this->get_page_url(['view' => 'edit', 'user_id' => $user_id]);
        $delete_action = admin_url('admin-post.php');

        echo '<div class="wecoop-actions-inline">';
        echo '<a class="button button-small" href="' . esc_url($detail_url) . '">Dettagli</a>';
        echo '<a class="button button-small button-primary" href="' . esc_url($edit_url) . '">Modifica</a>';
        echo '<button type="button" class="button button-small button-link-delete" data-wecoop-delete-user="1" data-user-id="' . esc_attr((string) $user_id) . '" data-user-name="' . esc_attr($user_name) . '" data-delete-action="' . esc_url($delete_action) . '">Elimina</button>';
        echo '</div>';
    }

    private function render_list_page($message, $error_msg) {
        $allowed_per_page = [20, 50, 100];
        $allowed_orderby  = ['created', 'name', 'email', 'completion'];

        $search    = sanitize_text_field(wp_unslash($_GET['s'] ?? ''));
        $paged     = max(1, (int) ($_GET['paged'] ?? 1));
        $per_page  = (int) ($_GET['per_page'] ?? 20);
        if (!in_array($per_page, $allowed_per_page, true)) {
            $per_page = 20;
        }
        $status    = sanitize_key($_GET['status'] ?? '');
        if (!in_array($status, ['complete', 'incomplete'], true)) {
            $status = '';
        }
        $role      = sanitize_key($_GET['role_filter'] ?? '');
        $categoria = sanitize_key($_GET['categoria'] ?? '');
        $source    = sanitize_key($_GET['source'] ?? '');
        if (!in_array($source, ['import', 'manual'], true)) {
            $source = '';
        }
        $orderby   = sanitize_key($_GET['orderby'] ?? 'created');
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'created';
        }
        $order = strtoupper(sanitize_key($_GET['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $results = $this->get_inserted_users([
            'per_page'  => $per_page,
            'paged'     => $paged,
            'search'    => $search,
            'status'    => $status,
            'role'      => $role,
            'categoria' => $categoria,
            'source'    => $source,
            'orderby'   => $orderby,
            'order'     => $order,
        ]);
        $users = $results['users'];
        $total = $results['total'];

        $count_all        = $this->get_inserted_user_count();
        $count_complete   = $this->get_inserted_user_count('complete');
        $count_incomplete = $this->get_inserted_user_count('incomplete');
        $count_import     = $this->get_inserted_user_count('import');

        $total_pages = $per_page > 0 ? (int) ceil($total / $per_page) : 1;
        $range_start = $total > 0 ? (($paged - 1) * $per_page) + 1 : 0;
        $range_end   = min($paged * $per_page, $total);

        $has_filters = ($search !== '' || $status !== '' || $role !== '' || $categoria !== '' || $source !== '');

        $base_filter_args = [
            'view'      => 'list',
            's'         => $search,
            'status'    => $status,
            'role_filter' => $role,
            'categoria' => $categoria,
            'source'    => $source,
            'orderby'   => $orderby,
            'order'     => $order,
            'per_page'  => $per_page,
        ];

        $created_notice = $message === 'created' ? 'Utente creato con successo.' : '';
        $updated_notice = $message === 'updated' ? 'Utente aggiornato con successo.' : '';
        $deleted_notice = $message === 'deleted' ? 'Utente eliminato con successo.' : '';
        ?>
        <div class="wrap wecoop-dataentry-wrap">
            <h1 class="wp-heading-inline">WeCoop Data Entry</h1>
            <a class="page-title-action" href="<?php echo esc_url($this->get_page_url(['view' => 'new'])); ?>">Nuovo utente</a>
            <a class="page-title-action" href="<?php echo esc_url($this->get_page_url(['view' => 'import'])); ?>">Importa Excel</a>
            <p class="wecoop-help">Lista degli utenti creati con questo modulo e compatibili con il modello dati WECOOP.</p>
            <?php $this->render_page_navigation('list'); ?>

            <?php if ($created_notice !== ''): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p><?php echo esc_html($created_notice); ?></p></div>
            <?php elseif ($updated_notice !== ''): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p><?php echo esc_html($updated_notice); ?></p></div>
            <?php elseif ($deleted_notice !== ''): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p><?php echo esc_html($deleted_notice); ?></p></div>
            <?php elseif ($message === 'validation_error' || $message === 'create_failed' || $message === 'save_failed' || $message === 'delete_failed'): ?>
                <div class="notice notice-error is-dismissible wecoop-notice"><p><strong>Operazione non riuscita.</strong> <?php echo esc_html($error_msg); ?></p></div>
            <?php endif; ?>

            <div class="wecoop-summary-grid wecoop-summary-grid--4">
                <a class="wecoop-summary-card wecoop-summary-card--link <?php echo $status === '' && $source === '' ? 'is-active' : ''; ?>" href="<?php echo esc_url($this->get_page_url(['view' => 'list'])); ?>">
                    <div class="wecoop-summary-label">Totale inseriti</div>
                    <div class="wecoop-summary-value"><?php echo esc_html(number_format_i18n($count_all)); ?></div>
                </a>
                <a class="wecoop-summary-card wecoop-summary-card--link <?php echo $status === 'complete' ? 'is-active' : ''; ?>" href="<?php echo esc_url($this->get_page_url(['view' => 'list', 'status' => 'complete'])); ?>">
                    <div class="wecoop-summary-label">Profili completi</div>
                    <div class="wecoop-summary-value" style="color:#1a8a3e;"><?php echo esc_html(number_format_i18n($count_complete)); ?></div>
                </a>
                <a class="wecoop-summary-card wecoop-summary-card--link <?php echo $status === 'incomplete' ? 'is-active' : ''; ?>" href="<?php echo esc_url($this->get_page_url(['view' => 'list', 'status' => 'incomplete'])); ?>">
                    <div class="wecoop-summary-label">Profili incompleti</div>
                    <div class="wecoop-summary-value" style="color:#b42318;"><?php echo esc_html(number_format_i18n($count_incomplete)); ?></div>
                </a>
                <a class="wecoop-summary-card wecoop-summary-card--link <?php echo $source === 'import' ? 'is-active' : ''; ?>" href="<?php echo esc_url($this->get_page_url(['view' => 'list', 'source' => 'import'])); ?>">
                    <div class="wecoop-summary-label">Importati da Excel</div>
                    <div class="wecoop-summary-value" style="color:#2271b1;"><?php echo esc_html(number_format_i18n($count_import)); ?></div>
                </a>
            </div>

            <form class="wecoop-filters" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::MENU_SLUG); ?>">
                <input type="hidden" name="view" value="list">
                <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
                <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">

                <div class="wecoop-filters__search">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Cerca per nome, cognome, email, telefono, codice fiscale, citta'...">
                    <button type="submit" class="button button-primary">Cerca</button>
                </div>

                <div class="wecoop-filters__row">
                    <label class="wecoop-filters__field">
                        <span>Stato profilo</span>
                        <select name="status">
                            <option value="">Tutti</option>
                            <option value="complete" <?php selected($status, 'complete'); ?>>Solo completi</option>
                            <option value="incomplete" <?php selected($status, 'incomplete'); ?>>Solo incompleti</option>
                        </select>
                    </label>
                    <label class="wecoop-filters__field">
                        <span>Ruolo</span>
                        <select name="role_filter">
                            <option value="">Tutti</option>
                            <option value="subscriber" <?php selected($role, 'subscriber'); ?>>Subscriber</option>
                            <option value="socio" <?php selected($role, 'socio'); ?>>Socio</option>
                            <option value="operator" <?php selected($role, 'operator'); ?>>Operator</option>
                        </select>
                    </label>
                    <label class="wecoop-filters__field">
                        <span>Categoria</span>
                        <select name="categoria">
                            <option value="">Tutte</option>
                            <option value="giovane" <?php selected($categoria, 'giovane'); ?>>Giovane</option>
                            <option value="lavoratore" <?php selected($categoria, 'lavoratore'); ?>>Lavoratore</option>
                            <option value="famiglia" <?php selected($categoria, 'famiglia'); ?>>Famiglia</option>
                        </select>
                    </label>
                    <label class="wecoop-filters__field">
                        <span>Origine</span>
                        <select name="source">
                            <option value="">Tutte</option>
                            <option value="manual" <?php selected($source, 'manual'); ?>>Inseriti manualmente</option>
                            <option value="import" <?php selected($source, 'import'); ?>>Importati da Excel</option>
                        </select>
                    </label>
                    <label class="wecoop-filters__field">
                        <span>Per pagina</span>
                        <select name="per_page">
                            <?php foreach ($allowed_per_page as $pp): ?>
                                <option value="<?php echo esc_attr((string) $pp); ?>" <?php selected($per_page, $pp); ?>><?php echo esc_html((string) $pp); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="wecoop-filters__actions">
                        <button type="submit" class="button">Applica filtri</button>
                        <?php if ($has_filters): ?>
                            <a class="button button-link" href="<?php echo esc_url($this->get_page_url(['view' => 'list'])); ?>">Azzera</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <div class="wecoop-results-bar">
                <span>
                    <?php if ($total > 0): ?>
                        <?php echo esc_html(sprintf('%s-%s di %s risultati', number_format_i18n($range_start), number_format_i18n($range_end), number_format_i18n($total))); ?>
                    <?php else: ?>
                        Nessun risultato
                    <?php endif; ?>
                </span>
                <?php if ($total_pages > 1): ?>
                    <span><?php echo esc_html(sprintf('Pagina %s di %s', number_format_i18n($paged), number_format_i18n($total_pages))); ?></span>
                <?php endif; ?>
            </div>

            <div class="wecoop-card" style="padding:0; overflow:hidden;">
                <div class="wecoop-table-wrap">
                    <table class="wecoop-table">
                        <thead>
                            <tr>
                                <th><?php $this->render_sortable_header('Utente', 'name', $orderby, $order, $base_filter_args); ?></th>
                                <th>Contatti</th>
                                <th><?php $this->render_sortable_header('Completamento', 'completion', $orderby, $order, $base_filter_args); ?></th>
                                <th>Stato</th>
                                <th>Origine</th>
                                <th><?php $this->render_sortable_header('Creato', 'created', $orderby, $order, $base_filter_args); ?></th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="wecoop-empty">
                                        <?php echo $has_filters ? 'Nessun utente corrisponde ai filtri selezionati.' : 'Nessun utente inserito trovato.'; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $profile = WeCoop_User_Meta::get_user_profile_data($user->ID);
                                    $full_name = trim(($profile['nome'] ?: $profile['first_name']) . ' ' . ($profile['cognome'] ?: $profile['last_name']));
                                    $display_name = $full_name !== '' ? $full_name : $user->display_name;
                                    $created_at = (string) get_user_meta($user->ID, 'wecoop_dataentry_created_at', true);
                                    $created_at_label = $created_at !== '' ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $created_at) : '-';
                                    $status_complete = get_user_meta($user->ID, 'profilo_completo', true);
                                    $completion_percent = $this->get_completion_percent($user->ID, $profile);
                                    $is_import = get_user_meta($user->ID, 'wecoop_dataentry_source', true) === 'excel_registro_giornaliero';
                                    $is_placeholder = get_user_meta($user->ID, 'wecoop_email_placeholder', true) === '1';
                                    $detail_url = $this->get_page_url(['view' => 'detail', 'user_id' => $user->ID]);
                                    ?>
                                    <tr>
                                        <td>
                                            <a class="wecoop-user-name" href="<?php echo esc_url($detail_url); ?>"><strong><?php echo esc_html($display_name); ?></strong></a><br>
                                            <span class="wecoop-muted<?php echo $is_placeholder ? ' wecoop-muted--warn' : ''; ?>"><?php echo esc_html($user->user_email); ?><?php echo $is_placeholder ? ' (segnaposto)' : ''; ?></span><br>
                                            <span class="wecoop-muted"><?php echo esc_html($user->user_login); ?></span>
                                        </td>
                                        <td>
                                            <?php echo esc_html(($profile['telefono_completo'] ?: $profile['telefono']) ?: '-'); ?><br>
                                            <span class="wecoop-muted"><?php echo esc_html(trim(($profile['citta'] ?: '') . ' ' . ($profile['provincia'] ? '(' . $profile['provincia'] . ')' : '')) ?: '-'); ?></span>
                                        </td>
                                        <td><?php $this->render_progress($completion_percent); ?></td>
                                        <td>
                                            <span class="wecoop-status <?php echo $status_complete ? 'is-complete' : 'is-incomplete'; ?>"><?php echo $status_complete ? 'Completo' : 'Incompleto'; ?></span>
                                        </td>
                                        <td>
                                            <span class="wecoop-chip <?php echo $is_import ? 'wecoop-chip--import' : 'wecoop-chip--manual'; ?>"><?php echo $is_import ? 'Excel' : 'Manuale'; ?></span>
                                        </td>
                                        <td><?php echo esc_html($created_at_label); ?></td>
                                        <td><?php $this->render_user_actions($user->ID, $display_name !== '' ? $display_name : $user->user_login); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php $this->render_pagination($paged, $total_pages, $base_filter_args); ?>

            <?php $this->render_delete_modal(); ?>
        </div>
        <?php
    }

    private function render_sortable_header($label, $column, $current_orderby, $current_order, array $base_args) {
        $is_active = ($current_orderby === $column);
        $next_order = ($is_active && $current_order === 'ASC') ? 'DESC' : 'ASC';
        $args = array_merge($base_args, [
            'orderby' => $column,
            'order'   => $next_order,
            'paged'   => 1,
        ]);
        $url = $this->get_page_url($args);

        $arrow = '';
        if ($is_active) {
            $arrow = $current_order === 'ASC' ? ' &uarr;' : ' &darr;';
        }

        echo '<a class="wecoop-sort ' . ($is_active ? 'is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . $arrow . '</a>';
    }

    private function render_pagination($paged, $total_pages, array $base_args) {
        if ($total_pages < 2) {
            return;
        }

        $make_url = function ($page) use ($base_args) {
            return $this->get_page_url(array_merge($base_args, ['paged' => $page]));
        };

        echo '<div class="wecoop-pagination">';

        // Prev
        if ($paged > 1) {
            echo '<a class="wecoop-page" href="' . esc_url($make_url($paged - 1)) . '">&laquo; Prec</a>';
        } else {
            echo '<span class="wecoop-page is-disabled">&laquo; Prec</span>';
        }

        // Numeri con finestra attorno alla pagina corrente
        $window = 2;
        $pages = [];
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === 1 || $i === $total_pages || ($i >= $paged - $window && $i <= $paged + $window)) {
                $pages[] = $i;
            }
        }
        $pages = array_values(array_unique($pages));

        $prev = 0;
        foreach ($pages as $p) {
            if ($prev && $p - $prev > 1) {
                echo '<span class="wecoop-page is-gap">&hellip;</span>';
            }
            if ($p === $paged) {
                echo '<span class="wecoop-page is-current">' . esc_html((string) $p) . '</span>';
            } else {
                echo '<a class="wecoop-page" href="' . esc_url($make_url($p)) . '">' . esc_html((string) $p) . '</a>';
            }
            $prev = $p;
        }

        // Next
        if ($paged < $total_pages) {
            echo '<a class="wecoop-page" href="' . esc_url($make_url($paged + 1)) . '">Succ &raquo;</a>';
        } else {
            echo '<span class="wecoop-page is-disabled">Succ &raquo;</span>';
        }

        echo '</div>';
    }

    private function render_detail_page($user_id, $message, $error_msg) {
        $detail = $this->get_user_detail_data($user_id);
        if (!$detail) {
            wp_die('Utente non trovato');
        }

        $user = $detail['user'];
        $profile = $detail['profile'];
        $full_name = trim(($profile['nome'] ?: $profile['first_name']) . ' ' . ($profile['cognome'] ?: $profile['last_name']));
        $created_at = $detail['created_at'] !== '' ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $detail['created_at']) : '-';
        $updated_at = $detail['updated_at'] !== '' ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $detail['updated_at']) : '-';
        $complete = get_user_meta($user_id, 'profilo_completo', true);
        $completion_percent = $this->get_completion_percent($user_id, $profile);

        $sections = [
            'Account' => [
                'Nome visualizzato' => $full_name !== '' ? $full_name : $user->display_name,
                'Email' => $user->user_email,
                'Username' => $user->user_login,
                'Ruolo' => implode(', ', $user->roles),
                'Creato il' => $created_at,
                'Aggiornato il' => $updated_at,
            ],
            'Anagrafica' => [
                'Nome' => $profile['nome'] ?? '',
                'Cognome' => $profile['cognome'] ?? '',
                'Sesso' => $profile['sesso'] ?? '',
                'Data nascita' => $profile['data_nascita'] ?? '',
                'Luogo nascita' => $profile['luogo_nascita'] ?? '',
                'Codice fiscale' => $profile['codice_fiscale'] ?? '',
                'Nazionalita' => $profile['nazionalita'] ?? '',
                'Stato civile' => $profile['stato_civile'] ?? '',
            ],
            'Contatti' => [
                'Telefono' => $profile['telefono_completo'] ?: ($profile['telefono'] ?? ''),
                'Indirizzo' => trim(($profile['indirizzo'] ?? '') . ' ' . ($profile['civico'] ?? '')),
                'Citta' => $profile['citta'] ?? '',
                'CAP' => $profile['cap'] ?? '',
                'Provincia' => $profile['provincia'] ?? '',
                'Nazione' => $profile['nazione'] ?? '',
                'Paese di provenienza' => $profile['paese_provenienza'] ?? '',
            ],
            'Lavoro e reddito' => [
                'Tipo lavoro' => $profile['tipo_lavoro'] ?? '',
                'Contratto' => $profile['contratto'] ?? '',
                'Settore' => $profile['settore'] ?? '',
                'Professione' => $profile['professione'] ?? '',
                'Anni lavoro' => $profile['anni_lavoro'] ?? '',
                'Reddito annuo' => $profile['reddito_annuo'] ?? '',
                'Reddito mensile' => $profile['reddito_mensile'] ?? '',
                'Altri redditi' => $profile['altri_redditi'] ?? '',
                'Prestiti attivi' => $profile['prestiti_attivi'] ?? '',
                'Rate mensili' => $profile['rate_mensili'] ?? '',
                'Ritardi pagamenti' => $profile['ritardi_pagamenti'] ?? '',
            ],
            'Documenti e profiling' => [
                'Carta identita' => $profile['doc_carta_identita'] ?? '',
                'Codice fiscale' => $profile['doc_codice_fiscale'] ?? '',
                'CU' => $profile['doc_cu'] ?? '',
                'Dichiarazione redditi' => $profile['doc_dichiarazione_redditi'] ?? '',
                'Categoria profilazione' => $profile['categoria_profilazione'] ?? '',
                'Capacita economica' => $profile['capacita_economica'] ?? '',
                'Interesse' => $profile['interesse'] ?? '',
                'Note' => $profile['note_dataentry'] ?? '',
            ],
        ];
        ?>
        <div class="wrap wecoop-dataentry-wrap">
            <h1 class="wp-heading-inline">Dettagli utente</h1>
            <a class="page-title-action" href="<?php echo esc_url($this->get_page_url(['view' => 'edit', 'user_id' => $user_id])); ?>">Modifica</a>
            <?php $this->render_page_navigation('detail', $user_id); ?>

            <?php if ($message === 'created'): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p>Utente creato con successo.</p></div>
            <?php elseif ($message === 'updated'): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p>Utente aggiornato con successo.</p></div>
            <?php elseif ($message === 'validation_error' || $message === 'save_failed' || $message === 'delete_failed' || $message === 'create_failed'): ?>
                <div class="notice notice-error is-dismissible wecoop-notice"><p><strong>Operazione non riuscita.</strong> <?php echo esc_html($error_msg); ?></p></div>
            <?php endif; ?>

            <div class="wecoop-summary-grid">
                <div class="wecoop-summary-card">
                    <div class="wecoop-summary-label">Profilo</div>
                    <div class="wecoop-summary-value" style="font-size:20px;"><?php echo esc_html($complete ? 'Completo' : 'Incompleto'); ?></div>
                    <div style="margin-top:10px;"><?php $this->render_progress($completion_percent); ?></div>
                </div>
                <div class="wecoop-summary-card">
                    <div class="wecoop-summary-label">Email</div>
                    <div class="wecoop-summary-value" style="font-size:20px; word-break:break-word;"><?php echo esc_html($user->user_email); ?></div>
                </div>
                <div class="wecoop-summary-card">
                    <div class="wecoop-summary-label">Telefono</div>
                    <div class="wecoop-summary-value" style="font-size:20px; word-break:break-word;"><?php echo esc_html($profile['telefono_completo'] ?: ($profile['telefono'] ?? '-')); ?></div>
                </div>
            </div>

            <div class="wecoop-toolbar">
                <div class="wecoop-actions-inline">
                    <a class="button button-primary" href="<?php echo esc_url($this->get_page_url(['view' => 'edit', 'user_id' => $user_id])); ?>">Modifica</a>
                    <button type="button" class="button button-link-delete" data-wecoop-delete-user="1" data-user-id="<?php echo esc_attr((string) $user_id); ?>" data-user-name="<?php echo esc_attr($full_name !== '' ? $full_name : $user->display_name); ?>" data-delete-action="<?php echo esc_url(admin_url('admin-post.php')); ?>">Elimina</button>
                </div>
            </div>

            <?php foreach ($sections as $section_title => $items): ?>
                <div class="wecoop-card">
                    <h2><?php echo esc_html($section_title); ?></h2>
                    <table class="widefat striped" style="margin-top:12px;">
                        <tbody>
                            <?php foreach ($items as $label => $value): ?>
                                <tr>
                                    <th style="width:260px;"><?php echo esc_html($label); ?></th>
                                    <td>
                                        <?php if ($label === 'Note'): ?>
                                            <?php echo wp_kses_post(nl2br(esc_html((string) $value))); ?>
                                        <?php else: ?>
                                            <?php echo esc_html($this->format_detail_value($label, $value)); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>

            <?php $this->render_delete_modal(); ?>
        </div>
        <?php
    }

    private function render_delete_modal() {
        ?>
        <div class="wecoop-modal" id="wecoop-dataentry-delete-modal" aria-hidden="true">
            <div class="wecoop-modal__panel" role="dialog" aria-modal="true" aria-labelledby="wecoop-delete-title">
                <h2 id="wecoop-delete-title">Conferma eliminazione</h2>
                <p>Stai per eliminare <strong data-role="user-name">questo utente</strong>. L'operazione non puo' essere annullata.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('wecoop_dataentry_delete_user'); ?>
                    <input type="hidden" name="action" value="wecoop_dataentry_delete_user">
                    <input type="hidden" name="user_id" value="">
                    <div class="wecoop-modal__actions">
                        <button type="button" class="button" data-wecoop-close="1">Annulla</button>
                        <button type="submit" class="button button-primary button-link-delete">Conferma eliminazione</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    private function format_detail_value($label, $value) {
        $value = trim((string) $value);

        if ($value === '') {
            return '-';
        }

        if (in_array($label, ['Altri redditi', 'Prestiti attivi', 'Carta identita', 'Codice fiscale', 'CU', 'Dichiarazione redditi'], true)) {
            return $value === '1' ? 'SI' : ($value === '0' ? 'NO' : $value);
        }

        return $value;
    }

    /**
     * Ritorna la percentuale di completamento del profilo (0-100).
     * Usa il meta salvato; se assente, lo calcola al volo dal profilo.
     */
    private function get_completion_percent($user_id, $profile = null) {
        $stored = get_user_meta($user_id, 'profilo_percentuale', true);
        if ($stored !== '' && is_numeric($stored)) {
            return (int) $stored;
        }

        if ($profile === null) {
            $profile = WeCoop_User_Meta::get_user_profile_data($user_id);
        }

        $values = [];
        foreach (WeCoop_User_Meta::get_completion_fields() as $field) {            if ($field === 'email') {
                $values['email'] = $profile['user_email'] ?? '';
                continue;
            }
            $values[$field] = $profile[$field] ?? '';
        }

        return WeCoop_User_Meta::calculate_completion_percent($values);
    }

    private function render_progress($percent) {
        $percent = max(0, min(100, (int) $percent));

        if ($percent >= 100) {
            $bar_class = 'is-full';
        } elseif ($percent >= 70) {
            $bar_class = 'is-high';
        } elseif ($percent >= 40) {
            $bar_class = 'is-mid';
        } else {
            $bar_class = 'is-low';
        }

        $label_class = $percent >= 100 ? 'is-full' : '';

        echo '<div class="wecoop-progress">';
        echo '<div class="wecoop-progress__track">';
        echo '<span class="wecoop-progress__bar ' . esc_attr($bar_class) . '" style="width:' . esc_attr((string) $percent) . '%"></span>';
        echo '</div>';
        echo '<span class="wecoop-progress__label ' . esc_attr($label_class) . '">' . esc_html($percent . '% completato') . '</span>';
        echo '</div>';
    }

    /* ------------------------------------------------------------------ *
     *  IMPORT EXCEL (Registro Giornaliero)
     * ------------------------------------------------------------------ */

    /**
     * Indici colonna (0-based) nel foglio "Registro Giornaliero".
     */
    private function import_column_map() {
        return [
            'operatore'      => 1,   // B
            'data'           => 2,   // C
            'servizio1'      => 3,   // D
            'imp_dovuto1'    => 4,   // E
            'servizio2'      => 5,   // F
            'imp_dovuto2'    => 6,   // G
            'servizio3'      => 7,   // H
            'imp_dovuto3'    => 8,   // I
            'importo_pagato' => 9,   // J
            'saldo'          => 10,  // K
            'metodo'         => 11,  // L
            'cognome'        => 12,  // M
            'nome'           => 13,  // N
            'cittadinanza'   => 14,  // O
            'email'          => 15,  // P
            'cellulare'      => 16,  // Q
            'stato_pratica'  => 17,  // R
            'osservazione'   => 18,  // S
            'cod_pratica'    => 19,  // T
            'cod_tessera'    => 20,  // U
        ];
    }

    private function cell($row, $idx) {
        return isset($row[$idx]) ? trim((string) $row[$idx]) : '';
    }

    /**
     * Estrae i clienti aggregati (uno per cognome+nome) da un file XLSX.
     * Ritorna ['clients' => [...], 'rows' => int, 'error' => string].
     */
    private function extract_clients_from_file($file_path) {
        $result = WeCoop_Xlsx_Reader::read_sheet($file_path, self::IMPORT_SHEET);
        if ($result['error'] !== '') {
            return ['clients' => [], 'rows' => 0, 'error' => $result['error']];
        }

        $map = $this->import_column_map();
        $clients = [];
        $data_rows = 0;

        foreach ($result['rows'] as $row_index => $row) {
            if ($row_index <= self::IMPORT_HEADER_ROW) {
                continue;
            }

            $cognome = $this->cell($row, $map['cognome']);
            $nome = $this->cell($row, $map['nome']);

            if ($cognome === '' && $nome === '') {
                continue;
            }

            $data_rows++;

            $key = strtoupper($cognome) . '|' . strtoupper($nome);

            if (!isset($clients[$key])) {
                $clients[$key] = [
                    'cognome'      => $this->clean_name($cognome),
                    'nome'         => $this->clean_name($nome),
                    'email'        => '',
                    'cellulare'    => '',
                    'cittadinanza' => '',
                    'operatore'    => '',
                    'pratiche'     => [],
                ];
            }

            $c =& $clients[$key];

            $email = $this->clean_email($this->cell($row, $map['email']));
            if ($email !== '' && $c['email'] === '') {
                $c['email'] = $email;
            }

            $tel = $this->clean_phone($this->cell($row, $map['cellulare']));
            if ($tel !== '' && $c['cellulare'] === '') {
                $c['cellulare'] = $tel;
            }

            $cit = $this->cell($row, $map['cittadinanza']);
            if ($cit !== '' && $c['cittadinanza'] === '') {
                $c['cittadinanza'] = $cit;
            }

            $op = $this->cell($row, $map['operatore']);
            if ($op !== '' && $c['operatore'] === '') {
                $c['operatore'] = $op;
            }

            // Storico pratica per le note
            $data = WeCoop_Xlsx_Reader::excel_serial_to_date($this->cell($row, $map['data']));
            if ($data === '') {
                $data = $this->cell($row, $map['data']);
            }

            $servizio = $this->cell($row, $map['servizio1']);
            if ($servizio === '' || strpos($servizio, '---') === 0) {
                $servizio = $this->cell($row, $map['servizio2']);
            }
            if ($servizio === '' || strpos($servizio, '---') === 0) {
                $servizio = $this->cell($row, $map['servizio3']);
            }

            $pratica = [
                'data'     => $data,
                'servizio' => $servizio,
                'pagato'   => $this->cell($row, $map['importo_pagato']),
                'metodo'   => $this->cell($row, $map['metodo']),
                'stato'    => $this->cell($row, $map['stato_pratica']),
                'oss'      => $this->cell($row, $map['osservazione']),
            ];

            if ($pratica['data'] !== '' || $pratica['servizio'] !== '' || $pratica['stato'] !== '') {
                $c['pratiche'][] = $pratica;
            }

            unset($c);
        }

        return ['clients' => $clients, 'rows' => $data_rows, 'error' => ''];
    }

    private function clean_name($value) {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        return $value;
    }

    private function clean_email($value) {
        $value = trim(str_replace(["\t", "\r", "\n", ' '], '', (string) $value));
        $value = sanitize_email($value);
        return is_email($value) ? $value : '';
    }

    private function clean_phone($value) {
        $value = trim((string) $value);
        // Mantiene cifre e + iniziale.
        $value = preg_replace('/[^0-9+]/', '', $value);
        return $value;
    }

    /**
     * Costruisce una email placeholder univoca tipo nome.cognome@wecoop.org
     */
    private function build_placeholder_email($nome, $cognome) {
        $slug = sanitize_title($nome . ' ' . $cognome);
        $slug = str_replace('-', '.', $slug);
        if ($slug === '') {
            $slug = 'utente.' . wp_generate_password(6, false, false);
        }

        $domain = self::IMPORT_PLACEHOLDER_DOMAIN;
        $base = $slug;
        $email = $base . '@' . $domain;
        $counter = 1;

        while (email_exists($email)) {
            $email = $base . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }

    /**
     * Costruisce il testo note con lo storico pratiche del cliente.
     */
    private function build_notes_from_client(array $client) {
        $lines = [];
        $lines[] = 'Importato da Registro Giornaliero (Excel CONCAF).';

        if ($client['operatore'] !== '') {
            $lines[] = 'Operatore: ' . $client['operatore'];
        }

        if (!empty($client['pratiche'])) {
            $lines[] = '';
            $lines[] = 'Storico pratiche (' . count($client['pratiche']) . '):';
            foreach ($client['pratiche'] as $p) {
                $parts = [];
                if ($p['data'] !== '') { $parts[] = $p['data']; }
                if ($p['servizio'] !== '') { $parts[] = $p['servizio']; }
                if ($p['pagato'] !== '') { $parts[] = 'pagato ' . $p['pagato']; }
                if ($p['metodo'] !== '') { $parts[] = $p['metodo']; }
                if ($p['stato'] !== '') { $parts[] = $p['stato']; }
                if ($p['oss'] !== '') { $parts[] = $p['oss']; }
                $lines[] = '- ' . implode(' | ', $parts);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Mappa un cliente aggregato nel payload del modello WECOOP.
     */
    private function map_client_to_payload(array $client) {
        $email = $client['email'] !== ''
            ? $client['email']
            : $this->build_placeholder_email($client['nome'], $client['cognome']);

        $nazionalita = $client['cittadinanza'];

        $payload = [
            'first_name'   => $client['nome'],
            'last_name'    => $client['cognome'],
            'display_name' => trim($client['nome'] . ' ' . $client['cognome']),
            'user_email'   => $email,
            'user_pass'    => '',
            'user_role'    => 'subscriber',
            'send_notification' => false,
            'nome'         => $client['nome'],
            'cognome'      => $client['cognome'],
            'telefono'     => $client['cellulare'],
            'nazionalita'  => $nazionalita,
            'paese_provenienza' => $nazionalita,
            'note_dataentry' => $this->build_notes_from_client($client),
            '_email_was_placeholder' => $client['email'] === '',
        ];

        return $payload;
    }

    public function handle_import() {
        if (!$this->can_access()) {
            wp_die('Accesso negato');
        }

        check_admin_referer('wecoop_dataentry_import');

        if (empty($_FILES['import_files']) || empty($_FILES['import_files']['name'][0])) {
            $this->redirect_with_error('import_failed', 'Nessun file selezionato.', 'import');
        }

        $update_existing = !empty($_POST['update_existing']);

        // Aggrega i clienti di tutti i file caricati.
        $all_clients = [];
        $total_rows = 0;
        $file_errors = [];
        $files = $_FILES['import_files'];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if ((int) $files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp = $files['tmp_name'][$i];
            $name = $files['name'][$i];

            if (!is_uploaded_file($tmp)) {
                $file_errors[] = $name . ': upload non valido.';
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                $file_errors[] = $name . ': formato non supportato (serve .xlsx).';
                continue;
            }

            $extracted = $this->extract_clients_from_file($tmp);
            if ($extracted['error'] !== '') {
                $file_errors[] = $name . ': ' . $extracted['error'];
                continue;
            }

            $total_rows += $extracted['rows'];

            foreach ($extracted['clients'] as $key => $client) {
                if (!isset($all_clients[$key])) {
                    $all_clients[$key] = $client;
                    continue;
                }

                // Merge tra file diversi.
                $existing =& $all_clients[$key];
                if ($existing['email'] === '' && $client['email'] !== '') { $existing['email'] = $client['email']; }
                if ($existing['cellulare'] === '' && $client['cellulare'] !== '') { $existing['cellulare'] = $client['cellulare']; }
                if ($existing['cittadinanza'] === '' && $client['cittadinanza'] !== '') { $existing['cittadinanza'] = $client['cittadinanza']; }
                if ($existing['operatore'] === '' && $client['operatore'] !== '') { $existing['operatore'] = $client['operatore']; }
                $existing['pratiche'] = array_merge($existing['pratiche'], $client['pratiche']);
                unset($existing);
            }
        }

        if (empty($all_clients)) {
            $msg = 'Nessun cliente valido trovato.';
            if (!empty($file_errors)) {
                $msg .= ' ' . implode(' ', $file_errors);
            }
            $this->redirect_with_error('import_failed', $msg, 'import');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($all_clients as $client) {
            $payload = $this->map_client_to_payload($client);
            $was_placeholder = !empty($payload['_email_was_placeholder']);
            unset($payload['_email_was_placeholder']);

            $existing_user_id = email_exists($payload['user_email']);

            if ($existing_user_id) {
                if (!$update_existing) {
                    $skipped++;
                    continue;
                }

                $result = WeCoop_User_Meta::save_user_profile((int) $existing_user_id, $payload);
                if (is_wp_error($result)) {
                    $errors++;
                    continue;
                }
                update_user_meta((int) $existing_user_id, 'wecoop_dataentry_updated_at', current_time('mysql'));
                if (get_user_meta((int) $existing_user_id, 'wecoop_dataentry_created_at', true) === '') {
                    update_user_meta((int) $existing_user_id, 'wecoop_dataentry_created_at', current_time('mysql'));
                    update_user_meta((int) $existing_user_id, 'wecoop_dataentry_created_by', get_current_user_id());
                }
                $updated++;
                continue;
            }

            $user_login = $this->build_unique_login($payload);
            $user_pass = wp_generate_password(20, true, true);

            $user_id = wp_insert_user([
                'user_login'   => $user_login,
                'user_pass'    => $user_pass,
                'user_email'   => $payload['user_email'],
                'first_name'   => $payload['first_name'],
                'last_name'    => $payload['last_name'],
                'display_name' => $payload['display_name'],
                'role'         => 'subscriber',
            ]);

            if (is_wp_error($user_id)) {
                $errors++;
                continue;
            }

            $result = WeCoop_User_Meta::save_user_profile($user_id, $payload);
            if (is_wp_error($result)) {
                wp_delete_user($user_id);
                $errors++;
                continue;
            }

            update_user_meta($user_id, 'wecoop_dataentry_created_at', current_time('mysql'));
            update_user_meta($user_id, 'wecoop_dataentry_created_by', get_current_user_id());
            update_user_meta($user_id, 'wecoop_dataentry_updated_at', current_time('mysql'));
            update_user_meta($user_id, 'wecoop_dataentry_source', 'excel_registro_giornaliero');
            if ($was_placeholder) {
                update_user_meta($user_id, 'wecoop_email_placeholder', '1');
            }

            $created++;
        }

        $summary = sprintf(
            'clienti=%d, creati=%d, aggiornati=%d, saltati=%d, errori=%d, righe=%d',
            count($all_clients), $created, $updated, $skipped, $errors, $total_rows
        );
        if (!empty($file_errors)) {
            $summary .= ' | avvisi: ' . implode(' ', $file_errors);
        }

        wp_redirect(add_query_arg([
            'page'    => self::MENU_SLUG,
            'view'    => 'import',
            'message' => 'imported',
            'summary' => rawurlencode($summary),
        ], admin_url('admin.php')));
        exit;
    }

    private function render_import_page($message, $error_msg) {
        $summary = isset($_GET['summary']) ? rawurldecode((string) $_GET['summary']) : '';
        ?>
        <div class="wrap wecoop-dataentry-wrap">
            <h1 class="wp-heading-inline">Importa Excel</h1>
            <p class="wecoop-help">Carica uno o piu' file <code>.xlsx</code> del Registro Giornaliero (CONCAF). I clienti vengono creati come utenti WECOOP, deduplicati per Cognome + Nome. Lo storico delle pratiche viene salvato nelle Note.</p>
            <?php $this->render_page_navigation('import'); ?>

            <?php if ($message === 'imported'): ?>
                <div class="notice notice-success is-dismissible wecoop-notice"><p><strong>Importazione completata.</strong> <?php echo esc_html($summary); ?></p></div>
            <?php elseif ($message === 'import_failed'): ?>
                <div class="notice notice-error is-dismissible wecoop-notice"><p><strong>Importazione non riuscita.</strong> <?php echo esc_html($error_msg); ?></p></div>
            <?php endif; ?>

            <div class="wecoop-card">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('wecoop_dataentry_import'); ?>
                    <input type="hidden" name="action" value="wecoop_dataentry_import">

                    <div class="wecoop-field">
                        <label for="import_files">File Excel (.xlsx) &mdash; foglio "<?php echo esc_html(self::IMPORT_SHEET); ?>"</label>
                        <input type="file" id="import_files" name="import_files[]" accept=".xlsx" multiple required>
                    </div>

                    <p style="margin-top:12px;">
                        <label><input type="checkbox" name="update_existing" value="1"> Aggiorna gli utenti gia' esistenti (stessa email). Se non selezionato vengono saltati.</label>
                    </p>

                    <div class="wecoop-actions">
                        <button type="submit" class="button button-primary button-large">Avvia importazione</button>
                        <span class="wecoop-help">I clienti senza email ricevono una email segnaposto <code>nome.cognome@<?php echo esc_html(self::IMPORT_PLACEHOLDER_DOMAIN); ?></code> da completare manualmente.</span>
                    </div>
                </form>
            </div>

            <div class="wecoop-card">
                <h2>Come vengono mappati i dati</h2>
                <table class="widefat striped" style="margin-top:12px;">
                    <thead><tr><th>Colonna Excel</th><th>Campo WECOOP</th></tr></thead>
                    <tbody>
                        <tr><td>COGNOME Cliente</td><td>Cognome / last_name</td></tr>
                        <tr><td>NOME Cliente</td><td>Nome / first_name</td></tr>
                        <tr><td>e-mail</td><td>Email (segnaposto se mancante)</td></tr>
                        <tr><td>cellulare</td><td>Telefono</td></tr>
                        <tr><td>Cittadinanza</td><td>Nazionalita + Paese di provenienza</td></tr>
                        <tr><td>Operatore, Data, Servizio, Importo, Stato, Osservazione</td><td>Note (storico pratiche)</td></tr>
                    </tbody>
                </table>
                <p class="wecoop-help" style="margin-top:12px;">Nota: codice fiscale, data di nascita, indirizzo e citta' non sono presenti nel registro, quindi il profilo importato risultera' incompleto finche' non verranno integrati manualmente.</p>
            </div>
        </div>
        <?php
    }

    public function render_page() {
        if (!$this->can_access()) {
            wp_die('Accesso negato');
        }

        $view = $this->get_view();
        $message = sanitize_text_field($_GET['message'] ?? '');
        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        $error_msg = isset($_GET['error_msg']) ? rawurldecode((string) $_GET['error_msg']) : '';

        if ($view === 'list') {
            $this->render_list_page($message, $error_msg);
            return;
        }

        if ($view === 'import') {
            $this->render_import_page($message, $error_msg);
            return;
        }

        if ($view === 'detail' && $user_id > 0) {
            $this->render_detail_page($user_id, $message, $error_msg);
            return;
        }

        $form_mode = $view === 'edit' ? 'edit' : 'new';
        $editing_user_id = $form_mode === 'edit' ? $user_id : 0;
        $defaults = $this->get_user_defaults($editing_user_id);
        $form_title = $form_mode === 'edit' ? 'Modifica utente' : 'Nuovo utente';
        $form_help = $form_mode === 'edit'
            ? 'Aggiorna i dati gia\' inseriti e salva le modifiche nel modello WECOOP.'
            : 'Crea nuovi utenti mantenendo la compatibilita\' con i meta usati dagli altri moduli WECOOP.';
        $form_action = $form_mode === 'edit' ? 'wecoop_dataentry_update_user' : 'wecoop_dataentry_create_user';
        $submit_label = $form_mode === 'edit' ? 'Salva modifiche' : 'Crea utente';

        ?>
        <div class="wrap wecoop-dataentry-wrap">
            <h1><?php echo esc_html($form_title); ?></h1>
            <p class="wecoop-help"><?php echo esc_html($form_help); ?></p>
            <?php $this->render_page_navigation($form_mode, $editing_user_id); ?>

            <?php if ($message === 'created' && $editing_user_id > 0): ?>
                <?php $detail_url = $this->get_page_url(['view' => 'detail', 'user_id' => $editing_user_id]); ?>
                <div class="notice notice-success is-dismissible wecoop-notice">
                    <p><strong>Utente creato con successo.</strong> <a class="wecoop-badge-link" href="<?php echo esc_url($detail_url); ?>">Apri dettaglio utente</a></p>
                </div>
            <?php elseif ($message === 'updated' && $editing_user_id > 0): ?>
                <div class="notice notice-success is-dismissible wecoop-notice">
                    <p><strong>Utente aggiornato con successo.</strong> <a class="wecoop-badge-link" href="<?php echo esc_url($this->get_page_url(['view' => 'detail', 'user_id' => $editing_user_id])); ?>">Apri dettaglio utente</a></p>
                </div>
            <?php elseif ($message === 'validation_error' || $message === 'create_failed' || $message === 'save_failed'): ?>
                <div class="notice notice-error is-dismissible wecoop-notice">
                    <p><strong>Operazione non riuscita.</strong> <?php echo esc_html($error_msg); ?></p>
                </div>
            <?php endif; ?>

            <div class="wecoop-card">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field($form_mode === 'edit' ? 'wecoop_dataentry_update_user' : 'wecoop_dataentry_create_user'); ?>
                    <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
                    <?php if ($form_mode === 'edit' && $editing_user_id > 0): ?>
                        <input type="hidden" name="user_id" value="<?php echo esc_attr((string) $editing_user_id); ?>">
                    <?php endif; ?>

                    <div class="wecoop-section" style="border-top:none;padding-top:0;margin-top:0;">
                        <h2>Dati account</h2>
                        <p class="wecoop-help">Il username viene generato automaticamente se non presente.</p>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_input('first_name', 'Nome *', $defaults['first_name']); ?>
                            <?php $this->render_input('last_name', 'Cognome *', $defaults['last_name']); ?>
                            <?php $this->render_input('display_name', 'Nome visualizzato', $defaults['display_name']); ?>
                            <?php $this->render_input('user_email', 'Email *', $defaults['user_email'], 'email'); ?>
                            <?php $this->render_input('user_pass', $form_mode === 'edit' ? 'Nuova password' : 'Password iniziale', '', 'text', $form_mode === 'edit' ? 'placeholder="Lascia vuoto per non cambiarla"' : 'placeholder="Lascia vuoto per generarla automaticamente"'); ?>
                            <?php $this->render_select('user_role', 'Ruolo WordPress', [
                                'subscriber' => 'Subscriber',
                                'socio' => 'Socio',
                                'operator' => 'Operator',
                            ], $defaults['user_role']); ?>
                        </div>
                        <p style="margin-top:12px;">
                            <label><input type="checkbox" name="send_notification" value="1" <?php checked($defaults['send_notification'], 1); ?>> Invia notifica email al nuovo utente</label>
                        </p>
                    </div>

                    <div class="wecoop-section">
                        <h2>1. Dati anagrafici</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_input('nome', 'Nome', $defaults['first_name']); ?>
                            <?php $this->render_input('cognome', 'Cognome', $defaults['last_name']); ?>
                            <?php $this->render_select('sesso', 'Sesso', ['' => 'Seleziona', 'M' => 'M', 'F' => 'F'], $defaults['sesso']); ?>
                            <?php $this->render_input('data_nascita', 'Data nascita', $defaults['data_nascita'], 'date'); ?>
                            <?php $this->render_input('codice_fiscale', 'Codice fiscale *', $defaults['codice_fiscale'], 'text', 'style="text-transform:uppercase" maxlength="16"'); ?>
                            <?php $this->render_input('nazionalita', 'Nazionalita *', $defaults['nazionalita']); ?>
                            <?php $this->render_input('stato_civile', 'Stato civile', $defaults['stato_civile']); ?>
                            <?php $this->render_input('luogo_nascita', 'Luogo di nascita', $defaults['luogo_nascita']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>2. Contatti</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_input('telefono', 'Telefono *', $defaults['telefono']); ?>
                            <?php $this->render_input('prefix', 'Prefisso', $defaults['prefix'], 'text', 'placeholder="39"'); ?>
                            <?php $this->render_input('citta', 'Citta *', $defaults['citta']); ?>
                            <?php $this->render_input('indirizzo', 'Indirizzo *', $defaults['indirizzo']); ?>
                            <?php $this->render_input('civico', 'Civico', $defaults['civico']); ?>
                            <?php $this->render_input('cap', 'CAP', $defaults['cap']); ?>
                            <?php $this->render_input('provincia', 'Provincia', $defaults['provincia'], 'text', 'maxlength="2" style="text-transform:uppercase"'); ?>
                            <?php $this->render_input('nazione', 'Nazione', $defaults['nazione'] ?: 'Italia'); ?>
                            <?php $this->render_input('paese_provenienza', 'Paese di provenienza', $defaults['paese_provenienza']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>3. Nucleo familiare</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_input('numero_figli', 'Numero figli', $defaults['numero_figli'], 'number', 'min="0" step="1"'); ?>
                            <?php $this->render_yes_no_select('figli_minori', 'Figli minori', $defaults['figli_minori']); ?>
                            <?php $this->render_input('figli_minori_numero', 'Numero figli minori', $defaults['figli_minori_numero'], 'number', 'min="0" step="1"'); ?>
                            <?php $this->render_input('persone_a_carico', 'Persone a carico', $defaults['persone_a_carico'], 'number', 'min="0" step="1"'); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>4. Situazione lavorativa</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_select('tipo_lavoro', 'Tipo lavoro', [
                                '' => 'Seleziona',
                                'dipendente' => 'Dipendente',
                                'autonomo' => 'Autonomo',
                                'disoccupato' => 'Disoccupato',
                                'studente' => 'Studente',
                            ], $defaults['tipo_lavoro']); ?>
                            <?php $this->render_select('contratto', 'Contratto', [
                                '' => 'Seleziona',
                                'indeterminato' => 'Indeterminato',
                                'determinato' => 'Determinato',
                                'altro' => 'Altro',
                            ], $defaults['contratto']); ?>
                            <?php $this->render_input('settore', 'Settore', $defaults['settore']); ?>
                            <?php $this->render_input('anni_lavoro', 'Anni lavoro', $defaults['anni_lavoro'], 'number', 'min="0" step="1"'); ?>
                            <?php $this->render_input('professione', 'Professione attuale', $defaults['professione']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>5. Reddito</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_input('reddito_annuo', 'Reddito annuo', $defaults['reddito_annuo']); ?>
                            <?php $this->render_input('reddito_mensile', 'Reddito mensile', $defaults['reddito_mensile']); ?>
                            <?php $this->render_yes_no_select('altri_redditi', 'Altri redditi', $defaults['altri_redditi']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>6. Situazione finanziaria</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_yes_no_select('prestiti_attivi', 'Prestiti attivi', $defaults['prestiti_attivi']); ?>
                            <?php $this->render_input('rate_mensili', 'Rate mensili', $defaults['rate_mensili']); ?>
                            <?php $this->render_select('ritardi_pagamenti', 'Ritardi pagamenti', [
                                '' => 'Seleziona',
                                'si' => 'SI',
                                'no' => 'NO',
                                'non_noto' => 'NON NOTO',
                            ], $defaults['ritardi_pagamenti']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>7. Documenti</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_select('doc_carta_identita', 'Carta identita', ['' => 'Seleziona', '1' => 'Presente', '0' => 'Mancante'], $defaults['doc_carta_identita']); ?>
                            <?php $this->render_select('doc_codice_fiscale', 'Codice fiscale', ['' => 'Seleziona', '1' => 'Presente', '0' => 'Mancante'], $defaults['doc_codice_fiscale']); ?>
                            <?php $this->render_select('doc_cu', 'CU', ['' => 'Seleziona', '1' => 'Presente', '0' => 'Mancante'], $defaults['doc_cu']); ?>
                            <?php $this->render_select('doc_dichiarazione_redditi', 'Dichiarazione redditi', ['' => 'Seleziona', '1' => 'Presente', '0' => 'Mancante'], $defaults['doc_dichiarazione_redditi']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>8. Profilazione (operatore)</h2>
                        <div class="wecoop-grid wecoop-grid--3">
                            <?php $this->render_select('categoria_profilazione', 'Categoria', [
                                '' => 'Seleziona',
                                'giovane' => 'Giovane',
                                'lavoratore' => 'Lavoratore',
                                'famiglia' => 'Famiglia',
                            ], $defaults['categoria_profilazione']); ?>
                            <?php $this->render_select('capacita_economica', 'Capacita economica', [
                                '' => 'Seleziona',
                                'bassa' => 'Bassa',
                                'media' => 'Media',
                                'alta' => 'Alta',
                            ], $defaults['capacita_economica']); ?>
                            <?php $this->render_select('interesse', 'Interesse', [
                                '' => 'Seleziona',
                                'prestiti' => 'Prestiti',
                                'formazione' => 'Formazione',
                                'lavoro' => 'Lavoro',
                            ], $defaults['interesse']); ?>
                        </div>
                    </div>

                    <div class="wecoop-section">
                        <h2>9. Note</h2>
                        <div class="wecoop-grid">
                            <?php $this->render_textarea('note_dataentry', 'Note', $defaults['note_dataentry']); ?>
                        </div>
                    </div>

                    <div class="wecoop-actions">
                        <button type="submit" class="button button-primary button-large"><?php echo esc_html($submit_label); ?></button>
                        <span class="wecoop-help">I campi con * sono quelli minimi compatibili con i moduli WECOOP gia\' esistenti.</span>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

add_action('plugins_loaded', static function () {
    WeCoop_DataEntry::get_instance();
});
