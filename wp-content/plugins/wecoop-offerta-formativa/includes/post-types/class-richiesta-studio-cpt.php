<?php
/**
 * CPT: richiesta_studio — Richieste "Studiare in Italia" arrivate dall'app
 *
 * @package WECOOP_Offerta_Formativa
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Richiesta_Studio_CPT {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_filter('manage_richiesta_studio_posts_columns', [__CLASS__, 'set_columns']);
        add_action('manage_richiesta_studio_posts_custom_column', [__CLASS__, 'render_column'], 10, 2);
    }

    public static function register_post_type() {
        register_post_type('richiesta_studio', [
            'labels' => [
                'name'               => 'Richieste Studio',
                'singular_name'      => 'Richiesta Studio',
                'add_new_item'       => 'Aggiungi Richiesta',
                'edit_item'          => 'Visualizza Richiesta',
                'view_item'          => 'Visualizza Richiesta',
                'search_items'       => 'Cerca Richieste',
                'not_found'          => 'Nessuna richiesta ricevuta',
                'not_found_in_trash' => 'Nessuna richiesta nel cestino',
                'menu_name'          => 'WeCoop Richieste Studio',
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'edit.php?post_type=offerta_formativa',
            'show_in_rest'  => false,
            'supports'      => ['title'],
            'capability_type' => 'post',
            'has_archive'   => false,
            'rewrite'       => false,
            'menu_icon'     => 'dashicons-clipboard',
        ]);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'richiesta_studio_details',
            'Dati Richiesta',
            [__CLASS__, 'render_metabox'],
            'richiesta_studio',
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        $fields = [
            'nome_cognome'      => 'Nome e cognome',
            'paese_origine'     => 'Paese di origine',
            'email'             => 'Email',
            'whatsapp'          => 'WhatsApp',
            'eta'               => 'Età',
            'titolo_studio'     => 'Titolo di studio',
            'livello_italiano'  => 'Livello italiano',
            'livello_inglese'   => 'Livello inglese',
            'cosa_studiare'     => 'Cosa vuole studiare',
            'quando_iniziare'   => 'Quando vuole iniziare',
            'gia_studiato'      => 'Ha già studiato in Italia',
            'ha_documenti'      => 'Ha documenti per l\'Italia',
            'aiuto_richiesto'   => 'Aiuto richiesto per',
            'data_invio'        => 'Data invio',
            'stato'             => 'Stato',
        ];
        ?>
        <style>
            .rs-field { display: grid; grid-template-columns: 200px 1fr; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f0f0f1; align-items: start; }
            .rs-field:last-child { border-bottom: none; }
            .rs-label { font-weight: 600; color: #50575e; font-size: 13px; }
            .rs-value { font-size: 13px; color: #1d2327; }
            .rs-status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 12px; font-weight: 600; }
            .rs-status.nuova { background: #d7edff; color: #004e99; }
            .rs-status.contattato { background: #d2f7e0; color: #006d39; }
            .rs-status.chiusa { background: #efefef; color: #50575e; }
        </style>
        <?php foreach ($fields as $meta_key => $label):
            $value = get_post_meta($post->ID, $meta_key, true);
            if ($value === '' || $value === null) continue;
            ?>
            <div class="rs-field">
                <span class="rs-label"><?php echo esc_html($label); ?></span>
                <span class="rs-value">
                    <?php if ($meta_key === 'stato'):
                        $class = in_array($value, ['contattato', 'chiusa']) ? $value : 'nuova';
                        echo '<span class="rs-status ' . esc_attr($class) . '">' . esc_html($value) . '</span>';
                    else:
                        echo esc_html($value);
                    endif; ?>
                </span>
            </div>
        <?php endforeach; ?>

        <?php if (current_user_can('edit_post', $post->ID)): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:2px solid #e0e0e0;">
            <label for="rs_stato_update" style="font-weight:600;">Aggiorna stato richiesta:</label>
            <select id="rs_stato_update" name="rs_stato_update" style="margin-left:10px;padding:6px 10px;border-radius:4px;border:1px solid #8c8f94;">
                <?php
                $stato_corrente = get_post_meta($post->ID, 'stato', true) ?: 'Nuova';
                foreach (['Nuova', 'Contattata', 'In lavorazione', 'Chiusa'] as $s):
                ?>
                    <option value="<?php echo esc_attr($s); ?>" <?php selected($stato_corrente, $s); ?>><?php echo esc_html($s); ?></option>
                <?php endforeach; ?>
            </select>
            <?php wp_nonce_field('rs_stato_save', 'rs_nonce'); ?>
        </div>
        <?php endif; ?>
        <?php
    }

    public static function set_columns($columns) {
        return [
            'cb'             => $columns['cb'],
            'title'          => 'Nome',
            'paese_origine'  => 'Paese',
            'cosa_studiare'  => 'Vuole studiare',
            'stato'          => 'Stato',
            'data_invio'     => 'Data invio',
        ];
    }

    public static function render_column($column, $post_id) {
        switch ($column) {
            case 'paese_origine':
                echo esc_html(get_post_meta($post_id, 'paese_origine', true) ?: '—');
                break;
            case 'cosa_studiare':
                $val = get_post_meta($post_id, 'cosa_studiare', true);
                echo esc_html($val ? wp_trim_words($val, 8) : '—');
                break;
            case 'stato':
                $stato = get_post_meta($post_id, 'stato', true) ?: 'Nuova';
                $colors = ['Nuova' => '#d7edff', 'Contattata' => '#d2f7e0', 'In lavorazione' => '#fff3c4', 'Chiusa' => '#efefef'];
                $textColors = ['Nuova' => '#004e99', 'Contattata' => '#006d39', 'In lavorazione' => '#7c5500', 'Chiusa' => '#50575e'];
                $bg   = $colors[$stato] ?? '#efefef';
                $text = $textColors[$stato] ?? '#50575e';
                echo '<span style="background:' . esc_attr($bg) . ';color:' . esc_attr($text) . ';padding:3px 10px;border-radius:3px;font-size:12px;font-weight:600;">'
                    . esc_html($stato) . '</span>';
                break;
            case 'data_invio':
                echo esc_html(get_post_meta($post_id, 'data_invio', true) ?: '—');
                break;
        }
    }
}

// Salva stato da metabox (hook save_post generico per richiesta_studio)
add_action('save_post_richiesta_studio', function($post_id) {
    if (!isset($_POST['rs_nonce']) || !wp_verify_nonce($_POST['rs_nonce'], 'rs_stato_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['rs_stato_update'])) {
        $allowed = ['Nuova', 'Contattata', 'In lavorazione', 'Chiusa'];
        $stato = sanitize_text_field($_POST['rs_stato_update']);
        if (in_array($stato, $allowed)) {
            update_post_meta($post_id, 'stato', $stato);
        }
    }
});
