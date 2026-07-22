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
        echo '<tr><th><label>Orari proposti</label></th><td>';
        echo '<div id="wecoop-mb-orari">';
        echo '<div class="wecoop-orario-row" style="margin-bottom:6px;display:flex;gap:6px;align-items:center;">';
        echo '<input type="datetime-local" name="data_ora[]" required>';
        echo '<button type="button" class="button wecoop-remove-orario" style="color:#b32d2e;" title="Rimuovi">&times;</button>';
        echo '</div>';
        echo '</div>';
        echo '<button type="button" class="button" id="wecoop-mb-add-orario">+ Aggiungi orario</button>';
        echo '<p class="description">Puoi proporre pi&ugrave; orari: verranno creati come slot separati con la stessa durata/sede/note.</p>';
        echo '</td></tr>';
        echo '<tr><th><label>Durata (min)</label></th><td><input type="number" name="durata_min" value="30" min="5" step="5"></td></tr>';
        if (class_exists('WeCoop_Appuntamenti_Sedi')) {
            echo '<tr><th><label for="sede_id">Sede salvata</label></th><td>';
            WeCoop_Appuntamenti_Sedi::render_select('sede_id', 'wecoop_mb_sede', 'wecoop_mb_indirizzo');
            echo '<p class="description">Seleziona una sede per compilare i campi automaticamente.</p>';
            echo '</td></tr>';
        }
        echo '<tr><th><label for="wecoop_mb_sede">Sede</label></th><td><input type="text" id="wecoop_mb_sede" name="sede" class="regular-text" placeholder="Es. Sportello WECOOP Milano"></td></tr>';
        echo '<tr><th><label for="wecoop_mb_indirizzo">Indirizzo</label></th><td><input type="text" id="wecoop_mb_indirizzo" name="indirizzo" class="regular-text" placeholder="Via ..., Citta"></td></tr>';
        echo '<tr><th><label>Note</label></th><td><textarea name="note" class="large-text" rows="2"></textarea></td></tr>';
        echo '</tbody></table>';
        submit_button('Proponi slot', 'primary', 'submit', false);
        echo '</form>';

        self::render_orari_js('wecoop-mb-orari', 'wecoop-mb-add-orario');

        echo '</div>';
    }

    /**
     * JS condiviso per aggiungere/rimuovere righe orario.
     *
     * @param string $container_id id del contenitore delle righe.
     * @param string $add_btn_id   id del bottone "aggiungi orario".
     */
    public static function render_orari_js($container_id, $add_btn_id) {
        $cid = esc_js($container_id);
        $bid = esc_js($add_btn_id);
        echo '<script>(function(){';
        echo 'var box=document.getElementById("' . $cid . '");';
        echo 'var add=document.getElementById("' . $bid . '");';
        echo 'if(!box||!add)return;';
        echo 'add.addEventListener("click",function(){';
        echo 'var row=box.querySelector(".wecoop-orario-row");';
        echo 'if(!row)return;';
        echo 'var clone=row.cloneNode(true);';
        echo 'var inp=clone.querySelector("input");';
        echo 'if(inp)inp.value="";';
        echo 'box.appendChild(clone);';
        echo '});';
        echo 'box.addEventListener("click",function(e){';
        echo 'var btn=e.target.closest(".wecoop-remove-orario");';
        echo 'if(!btn)return;';
        echo 'var rows=box.querySelectorAll(".wecoop-orario-row");';
        echo 'if(rows.length<=1){var i=btn.parentNode.querySelector("input");if(i)i.value="";return;}';
        echo 'btn.closest(".wecoop-orario-row").remove();';
        echo '});';
        echo '})();</script>';
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

        $data_ora_raw = isset($_POST['data_ora']) ? $_POST['data_ora'] : [];
        if (!is_array($data_ora_raw)) {
            $data_ora_raw = [$data_ora_raw];
        }

        // Risolvi sede/indirizzo una sola volta (validi per tutti gli orari).
        $sede      = isset($_POST['sede']) ? sanitize_text_field($_POST['sede']) : '';
        $indirizzo = isset($_POST['indirizzo']) ? sanitize_text_field($_POST['indirizzo']) : '';
        $sede_id   = isset($_POST['sede_id']) ? (int) $_POST['sede_id'] : 0;

        if ($sede_id && class_exists('WeCoop_Appuntamenti_Sedi') && ($sede === '' || $indirizzo === '')) {
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

        $durata = isset($_POST['durata_min']) ? (int) $_POST['durata_min'] : 30;
        $note   = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : null;

        $created = 0;
        foreach ($data_ora_raw as $raw) {
            $raw = sanitize_text_field($raw);
            $ts  = $raw ? strtotime($raw) : false;
            if (!$ts) {
                continue;
            }
            WeCoop_Appuntamenti_Repository::create_slot([
                'richiesta_id' => $post_id,
                'data_ora'     => date('Y-m-d H:i:s', $ts),
                'durata_min'   => $durata,
                'sede'         => $sede !== '' ? $sede : null,
                'indirizzo'    => $indirizzo !== '' ? $indirizzo : null,
                'note'         => $note,
                'created_by'   => get_current_user_id(),
            ]);
            $created++;
        }

        if ($created > 0) {
            // Porta la richiesta in attesa di scelta appuntamento e notifica.
            $stato_corrente = get_post_meta($post_id, 'stato', true);
            if ($stato_corrente !== WECOOP_Appuntamenti_Plugin::STATO_CONFIRMED) {
                $old = $stato_corrente;
                update_post_meta($post_id, 'stato', WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
                do_action('wecoop_richiesta_servizio_status_changed', $post_id, $old, WECOOP_Appuntamenti_Plugin::STATO_AWAITING);
            }
            do_action('wecoop_appuntamento_slot_proposti', $post_id, [], get_current_user_id());
        }

        wp_safe_redirect(admin_url('post.php?action=edit&post=' . $post_id));
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
        wp_safe_redirect(admin_url('post.php?action=edit&post=' . $post_id));
        exit;
    }
}
