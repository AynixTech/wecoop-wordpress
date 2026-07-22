<?php
/**
 * Pagina admin dedicata "Appuntamenti".
 * Fornisce una schermata a se' (voce di menu top-level) per gestire gli
 * appuntamenti/slot di una richiesta_servizio, con selezione delle sedi salvate.
 *
 * URL: wp-admin/admin.php?page=wecoop-appuntamenti[&richiesta_id=ID]
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Appuntamenti_Admin_Page {

    const MENU_SLUG = 'wecoop-appuntamenti';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_post_wecoop_page_add_slot', [__CLASS__, 'handle_add_slot']);
        add_action('admin_post_wecoop_page_delete_slot', [__CLASS__, 'handle_delete_slot']);
    }

    private static function can_manage() {
        return current_user_can('wecoop_appuntamenti_manage') || current_user_can('manage_options');
    }

    public static function register_menu() {
        add_menu_page(
            'Appuntamenti',
            'Appuntamenti',
            self::capability(),
            self::MENU_SLUG,
            [__CLASS__, 'render_page'],
            'dashicons-calendar-alt',
            26
        );

        // Sottomenu: prima voce = stessa pagina (rinomina "Gestione").
        add_submenu_page(
            self::MENU_SLUG,
            'Gestione appuntamenti',
            'Gestione',
            self::capability(),
            self::MENU_SLUG,
            [__CLASS__, 'render_page']
        );
        // La voce "Sedi" viene agganciata automaticamente perche' il CPT
        // wecoop_sede usa show_in_menu = self::MENU_SLUG.
    }

    private static function capability() {
        // manage_options passa sempre; gli operatori hanno wecoop_appuntamenti_manage.
        return current_user_can('manage_options') ? 'manage_options' : 'wecoop_appuntamenti_manage';
    }

    public static function render_page() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }

        $richiesta_id = isset($_GET['richiesta_id']) ? (int) $_GET['richiesta_id'] : 0;

        echo '<div class="wrap">';
        echo '<h1 style="display:flex;align-items:center;gap:10px;">';
        echo '<span class="dashicons dashicons-calendar-alt"></span> Appuntamenti';
        echo '</h1>';

        if (!empty($_GET['wecoop_msg'])) {
            $msg = sanitize_text_field(wp_unslash($_GET['wecoop_msg']));
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        }

        if ($richiesta_id) {
            self::render_single($richiesta_id);
        } else {
            self::render_list();
        }

        echo '</div>';
    }

    /**
     * Elenco richieste con stato appuntamento.
     */
    private static function render_list() {
        $awaiting = WECOOP_Appuntamenti_Plugin::STATO_AWAITING;
        $confirmed = WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED;

        $requests = get_posts([
            'post_type'      => 'richiesta_servizio',
            'post_status'    => 'any',
            'numberposts'    => 100,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'meta_query'     => [
                'relation' => 'OR',
                ['key' => 'stato', 'value' => $awaiting],
                ['key' => 'stato', 'value' => $confirmed],
            ],
        ]);

        echo '<p>Seleziona una richiesta per proporre/gestire gli slot di appuntamento. ';
        echo '<a href="' . esc_url(admin_url('edit.php?post_type=' . WeCoop_Appuntamenti_Sedi::POST_TYPE)) . '" class="button">Gestisci sedi</a></p>';

        if (empty($requests)) {
            echo '<p style="color:#666;">Nessuna richiesta in attesa o con appuntamento confermato. ';
            echo 'Puoi comunque aprire una richiesta specifica passando <code>?richiesta_id=ID</code> nell\'URL.</p>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>#</th><th>Richiesta</th><th>Stato</th><th>Slot proposti</th><th>Appuntamento confermato</th><th></th>';
        echo '</tr></thead><tbody>';

        foreach ($requests as $req) {
            $rid   = (int) $req->ID;
            $stato = get_post_meta($rid, 'stato', true);
            $slots = WeCoop_Appuntamenti_Repository::get_all_slots($rid);
            $appt  = WeCoop_Appuntamenti_Repository::get_active_appuntamento_by_richiesta($rid);
            $manage_url = add_query_arg([
                'page'         => self::MENU_SLUG,
                'richiesta_id' => $rid,
            ], admin_url('admin.php'));

            $proposti = count(array_filter($slots, function ($s) {
                return $s['stato'] === 'proposed';
            }));

            echo '<tr>';
            echo '<td>' . $rid . '</td>';
            echo '<td><strong>' . esc_html(get_the_title($rid) ?: ('Richiesta #' . $rid)) . '</strong></td>';
            echo '<td><code>' . esc_html((string) $stato) . '</code></td>';
            echo '<td>' . (int) $proposti . '</td>';
            echo '<td>' . ($appt ? esc_html(date_i18n('d/m/Y H:i', strtotime($appt['data_ora']))) : '&mdash;') . '</td>';
            echo '<td><a class="button button-primary" href="' . esc_url($manage_url) . '">Gestisci</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Gestione appuntamento di una singola richiesta.
     */
    private static function render_single($richiesta_id) {
        $post = get_post($richiesta_id);
        if (!$post || $post->post_type !== 'richiesta_servizio') {
            echo '<div class="notice notice-error"><p>Richiesta non valida (ID ' . (int) $richiesta_id . ').</p></div>';
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=' . self::MENU_SLUG)) . '">&larr; Torna all\'elenco</a></p>';
            return;
        }

        $slots = WeCoop_Appuntamenti_Repository::get_all_slots($richiesta_id);
        $appuntamento = WeCoop_Appuntamenti_Repository::get_active_appuntamento_by_richiesta($richiesta_id);
        $action_url = admin_url('admin-post.php');

        echo '<p><a href="' . esc_url(admin_url('admin.php?page=' . self::MENU_SLUG)) . '">&larr; Torna all\'elenco</a></p>';
        echo '<h2>' . esc_html(get_the_title($richiesta_id) ?: ('Richiesta #' . $richiesta_id)) . ' <span style="color:#888;">(#' . (int) $richiesta_id . ')</span></h2>';
        echo '<p><a href="' . esc_url(get_edit_post_link($richiesta_id)) . '">Apri la richiesta completa</a></p>';

        // Appuntamento confermato
        if ($appuntamento) {
            echo '<div style="background:#e7f6ec;border:1px solid #59B575;border-radius:8px;padding:12px;margin-bottom:14px;max-width:600px;">';
            echo '<strong>Appuntamento confermato dall\'utente</strong><br>';
            echo 'Data/ora: <strong>' . esc_html(date_i18n('d/m/Y H:i', strtotime($appuntamento['data_ora']))) . '</strong><br>';
            echo 'Durata: ' . intval($appuntamento['durata_min']) . ' min<br>';
            if (!empty($appuntamento['sede'])) {
                echo 'Sede: ' . esc_html($appuntamento['sede']) . '<br>';
            }
            if (!empty($appuntamento['indirizzo'])) {
                echo 'Indirizzo: ' . esc_html($appuntamento['indirizzo']) . '<br>';
            }
            if (!empty($appuntamento['note'])) {
                echo 'Note: ' . esc_html($appuntamento['note']) . '<br>';
            }
            echo '</div>';
        }

        // Slot proposti
        echo '<h3>Slot proposti</h3>';
        if (empty($slots)) {
            echo '<p style="color:#666;">Nessuno slot proposto. Aggiungine uno qui sotto.</p>';
        } else {
            echo '<table class="widefat striped" style="margin-bottom:14px;max-width:900px;"><thead><tr>';
            echo '<th>Data/ora</th><th>Durata</th><th>Sede</th><th>Indirizzo</th><th>Note</th><th>Stato</th><th></th>';
            echo '</tr></thead><tbody>';
            foreach ($slots as $s) {
                echo '<tr>';
                echo '<td>' . esc_html(date_i18n('d/m/Y H:i', strtotime($s['data_ora']))) . '</td>';
                echo '<td>' . intval($s['durata_min']) . ' min</td>';
                echo '<td>' . esc_html((string) $s['sede']) . '</td>';
                echo '<td>' . esc_html((string) $s['indirizzo']) . '</td>';
                echo '<td>' . esc_html((string) $s['note']) . '</td>';
                echo '<td><code>' . esc_html($s['stato']) . '</code></td>';
                echo '<td>';
                if ($s['stato'] !== 'booked') {
                    $del_url = wp_nonce_url(
                        add_query_arg([
                            'action'       => 'wecoop_page_delete_slot',
                            'slot_id'      => (int) $s['id'],
                            'richiesta_id' => $richiesta_id,
                        ], $action_url),
                        'wecoop_page_delete_slot_' . $s['id']
                    );
                    echo '<a href="' . esc_url($del_url) . '" onclick="return confirm(\'Eliminare questo slot?\');" style="color:#b32d2e;">Elimina</a>';
                } else {
                    echo '&mdash;';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }

        // Form nuovo slot con selezione sede
        echo '<h3>Aggiungi slot</h3>';
        echo '<form method="post" action="' . esc_url($action_url) . '" style="max-width:600px;">';
        wp_nonce_field('wecoop_page_add_slot_' . $richiesta_id, 'wecoop_slot_nonce');
        echo '<input type="hidden" name="action" value="wecoop_page_add_slot">';
        echo '<input type="hidden" name="richiesta_id" value="' . (int) $richiesta_id . '">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label>Data e ora</label></th><td><input type="datetime-local" name="data_ora" required></td></tr>';
        echo '<tr><th><label>Durata (min)</label></th><td><input type="number" name="durata_min" value="30" min="5" step="5"></td></tr>';
        echo '<tr><th><label for="wecoop_sede_id">Sede salvata</label></th><td>';
        WeCoop_Appuntamenti_Sedi::render_select('sede_id', 'wecoop_page_sede', 'wecoop_page_indirizzo');
        echo '<p class="description">Seleziona una sede per compilare automaticamente i campi sottostanti, oppure inseriscili a mano.</p>';
        echo '</td></tr>';
        echo '<tr><th><label for="wecoop_page_sede">Sede</label></th><td><input type="text" id="wecoop_page_sede" name="sede" class="regular-text" placeholder="Es. Sportello WECOOP Milano"></td></tr>';
        echo '<tr><th><label for="wecoop_page_indirizzo">Indirizzo</label></th><td><input type="text" id="wecoop_page_indirizzo" name="indirizzo" class="regular-text" placeholder="Via ..., Citta"></td></tr>';
        echo '<tr><th><label>Note</label></th><td><textarea name="note" class="large-text" rows="2"></textarea></td></tr>';
        echo '</tbody></table>';
        submit_button('Proponi slot', 'primary', 'submit', false);
        echo '</form>';
    }

    /* ---------------------------------------------------------------------
     * Handlers
     * ------------------------------------------------------------------- */

    public static function handle_add_slot() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        $richiesta_id = isset($_POST['richiesta_id']) ? (int) $_POST['richiesta_id'] : 0;
        if (!$richiesta_id || !isset($_POST['wecoop_slot_nonce'])
            || !wp_verify_nonce($_POST['wecoop_slot_nonce'], 'wecoop_page_add_slot_' . $richiesta_id)) {
            wp_die('Nonce non valido');
        }

        $data_ora_raw = isset($_POST['data_ora']) ? sanitize_text_field($_POST['data_ora']) : '';
        $ts = $data_ora_raw ? strtotime($data_ora_raw) : false;

        if ($ts) {
            // Se e' stata scelta una sede salvata e i campi testo sono vuoti,
            // popola sede/indirizzo dal record sede (fallback lato server).
            $sede      = isset($_POST['sede']) ? sanitize_text_field($_POST['sede']) : '';
            $indirizzo = isset($_POST['indirizzo']) ? sanitize_text_field($_POST['indirizzo']) : '';
            $sede_id   = isset($_POST['sede_id']) ? (int) $_POST['sede_id'] : 0;

            if ($sede_id && ($sede === '' || $indirizzo === '')) {
                $sede_post = get_post($sede_id);
                if ($sede_post && $sede_post->post_type === WeCoop_Appuntamenti_Sedi::POST_TYPE) {
                    if ($sede === '') {
                        $sede = $sede_post->post_title;
                    }
                    if ($indirizzo === '') {
                        $indirizzo = WeCoop_Appuntamenti_Sedi::full_address([
                            'indirizzo' => get_post_meta($sede_id, 'indirizzo', true),
                            'citta'     => get_post_meta($sede_id, 'citta', true),
                        ]);
                    }
                }
            }

            WeCoop_Appuntamenti_Repository::create_slot([
                'richiesta_id' => $richiesta_id,
                'data_ora'     => date('Y-m-d H:i:s', $ts),
                'durata_min'   => isset($_POST['durata_min']) ? (int) $_POST['durata_min'] : 30,
                'sede'         => $sede !== '' ? $sede : null,
                'indirizzo'    => $indirizzo !== '' ? $indirizzo : null,
                'note'         => isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : null,
                'created_by'   => get_current_user_id(),
            ]);

            $stato_corrente = get_post_meta($richiesta_id, 'stato', true);
            if ($stato_corrente !== WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED) {
                $old = $stato_corrente;
                update_post_meta($richiesta_id, 'stato', WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
                do_action('wecoop_richiesta_servizio_status_changed', $richiesta_id, $old, WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
            }
            do_action('wecoop_appuntamento_slot_proposti', $richiesta_id, [], get_current_user_id());
        }

        self::redirect_single($richiesta_id, 'Slot proposto correttamente.');
    }

    public static function handle_delete_slot() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        $slot_id = isset($_GET['slot_id']) ? (int) $_GET['slot_id'] : 0;
        $richiesta_id = isset($_GET['richiesta_id']) ? (int) $_GET['richiesta_id'] : 0;
        if (!$slot_id || !check_admin_referer('wecoop_page_delete_slot_' . $slot_id)) {
            wp_die('Nonce non valido');
        }
        $slot = WeCoop_Appuntamenti_Repository::get_slot($slot_id);
        if ($slot && $slot['stato'] !== 'booked') {
            WeCoop_Appuntamenti_Repository::delete_slot($slot_id);
        }
        self::redirect_single($richiesta_id, 'Slot eliminato.');
    }

    private static function redirect_single($richiesta_id, $msg = '') {
        $url = add_query_arg(array_filter([
            'page'         => self::MENU_SLUG,
            'richiesta_id' => (int) $richiesta_id,
            'wecoop_msg'   => $msg !== '' ? rawurlencode($msg) : null,
        ]), admin_url('admin.php'));
        wp_safe_redirect($url);
        exit;
    }
}
