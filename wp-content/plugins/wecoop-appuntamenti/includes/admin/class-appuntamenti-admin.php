<?php
/**
 * Back-office operatore: metabox "Appuntamento" dentro l'edit della richiesta_servizio.
 * L'operatore propone slot (data/ora/durata/sede/indirizzo/note), vede gli slot proposti
 * e l'eventuale appuntamento confermato dall'utente.
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Appuntamenti_Admin {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_metabox']);
        add_action('admin_post_wecoop_add_slot', [__CLASS__, 'handle_add_slot']);
        add_action('admin_post_wecoop_delete_slot', [__CLASS__, 'handle_delete_slot']);
    }

    private static function can_manage() {
        return current_user_can('wecoop_appuntamenti_manage') || current_user_can('manage_options');
    }

    public static function add_metabox() {
        if (!self::can_manage()) {
            return;
        }
        add_meta_box(
            'wecoop_appuntamento_box',
            'Appuntamento fisico',
            [__CLASS__, 'render_metabox'],
            'richiesta_servizio',
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        $richiesta_id = (int) $post->ID;
        $slots = WeCoop_Appuntamenti_Repository::get_all_slots($richiesta_id);
        $appuntamento = WeCoop_Appuntamenti_Repository::get_active_appuntamento_by_richiesta($richiesta_id);
        $action_url = admin_url('admin-post.php');

        echo '<div style="padding:8px 0;">';

        // Appuntamento confermato
        if ($appuntamento) {
            echo '<div style="background:#e7f6ec;border:1px solid #59B575;border-radius:8px;padding:12px;margin-bottom:14px;">';
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

        // Lista slot proposti
        echo '<h4 style="margin:8px 0;">Slot proposti</h4>';
        if (empty($slots)) {
            echo '<p style="color:#666;">Nessuno slot proposto. Aggiungine uno qui sotto.</p>';
        } else {
            echo '<table class="widefat striped" style="margin-bottom:14px;"><thead><tr>';
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
                            'action'  => 'wecoop_delete_slot',
                            'slot_id' => (int) $s['id'],
                            'post_id' => $richiesta_id,
                        ], $action_url),
                        'wecoop_delete_slot_' . $s['id']
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

        // Form nuovo slot
        echo '<h4 style="margin:8px 0;">Aggiungi slot</h4>';
        echo '<form method="post" action="' . esc_url($action_url) . '">';
        wp_nonce_field('wecoop_add_slot_' . $richiesta_id, 'wecoop_slot_nonce');
        echo '<input type="hidden" name="action" value="wecoop_add_slot">';
        echo '<input type="hidden" name="post_id" value="' . $richiesta_id . '">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label>Data e ora</label></th><td><input type="datetime-local" name="data_ora" required></td></tr>';
        echo '<tr><th><label>Durata (min)</label></th><td><input type="number" name="durata_min" value="30" min="5" step="5"></td></tr>';
        echo '<tr><th><label>Sede</label></th><td><input type="text" name="sede" class="regular-text" placeholder="Es. Sportello WECOOP Milano"></td></tr>';
        echo '<tr><th><label>Indirizzo</label></th><td><input type="text" name="indirizzo" class="regular-text" placeholder="Via ..., Citta"></td></tr>';
        echo '<tr><th><label>Note</label></th><td><textarea name="note" class="large-text" rows="2"></textarea></td></tr>';
        echo '</tbody></table>';
        submit_button('Proponi slot', 'primary', 'submit', false);
        echo '</form>';

        echo '</div>';
    }

    public static function handle_add_slot() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (!$post_id || !isset($_POST['wecoop_slot_nonce'])
            || !wp_verify_nonce($_POST['wecoop_slot_nonce'], 'wecoop_add_slot_' . $post_id)) {
            wp_die('Nonce non valido');
        }

        $data_ora_raw = isset($_POST['data_ora']) ? sanitize_text_field($_POST['data_ora']) : '';
        $ts = $data_ora_raw ? strtotime($data_ora_raw) : false;
        if ($ts) {
            WeCoop_Appuntamenti_Repository::create_slot([
                'richiesta_id' => $post_id,
                'data_ora'     => date('Y-m-d H:i:s', $ts),
                'durata_min'   => isset($_POST['durata_min']) ? (int) $_POST['durata_min'] : 30,
                'sede'         => isset($_POST['sede']) ? sanitize_text_field($_POST['sede']) : null,
                'indirizzo'    => isset($_POST['indirizzo']) ? sanitize_text_field($_POST['indirizzo']) : null,
                'note'         => isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : null,
                'created_by'   => get_current_user_id(),
            ]);

            // Porta la richiesta in attesa di scelta appuntamento e notifica.
            $stato_corrente = get_post_meta($post_id, 'stato', true);
            if ($stato_corrente !== WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED) {
                $old = $stato_corrente;
                update_post_meta($post_id, 'stato', WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
                do_action('wecoop_richiesta_servizio_status_changed', $post_id, $old, WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
            }
            do_action('wecoop_appuntamento_slot_proposti', $post_id, [], get_current_user_id());
        }

        wp_safe_redirect(get_edit_post_link($post_id, 'redirect'));
        exit;
    }

    public static function handle_delete_slot() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        $slot_id = isset($_GET['slot_id']) ? (int) $_GET['slot_id'] : 0;
        $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
        if (!$slot_id || !check_admin_referer('wecoop_delete_slot_' . $slot_id)) {
            wp_die('Nonce non valido');
        }
        $slot = WeCoop_Appuntamenti_Repository::get_slot($slot_id);
        if ($slot && $slot['stato'] !== 'booked') {
            WeCoop_Appuntamenti_Repository::delete_slot($slot_id);
        }
        wp_safe_redirect(get_edit_post_link($post_id, 'redirect'));
        exit;
    }
}
