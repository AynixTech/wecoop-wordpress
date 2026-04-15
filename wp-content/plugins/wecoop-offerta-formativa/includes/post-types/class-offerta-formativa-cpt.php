<?php
/**
 * CPT: offerta_formativa — Gestione percorsi formativi (editoriale)
 *
 * @package WECOOP_Offerta_Formativa
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Offerta_Formativa_CPT {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_offerta_formativa', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_offerta_formativa_posts_columns', [__CLASS__, 'set_columns']);
        add_action('manage_offerta_formativa_posts_custom_column', [__CLASS__, 'render_column'], 10, 2);
    }

    public static function register_post_type() {
        register_post_type('offerta_formativa', [
            'labels' => [
                'name'               => 'Offerte Formative',
                'singular_name'      => 'Offerta Formativa',
                'add_new'            => 'Nuova Offerta',
                'add_new_item'       => 'Aggiungi Offerta Formativa',
                'edit_item'          => 'Modifica Offerta Formativa',
                'view_item'          => 'Visualizza Offerta Formativa',
                'search_items'       => 'Cerca Offerta Formativa',
                'not_found'          => 'Nessuna offerta trovata',
                'not_found_in_trash' => 'Nessuna offerta nel cestino',
                'menu_name'          => 'WeCoop Formazione',
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'show_in_rest'  => true,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
            'capability_type' => 'post',
            'has_archive'   => false,
            'rewrite'       => false,
            'menu_icon'     => 'dashicons-welcome-learn-more',
            'menu_position' => 26,
        ]);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'offerta_formativa_details',
            'Dettagli Offerta Formativa',
            [__CLASS__, 'render_metabox'],
            'offerta_formativa',
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        wp_nonce_field('offerta_formativa_save', 'offerta_formativa_nonce');

        $partner_id     = (int) get_post_meta($post->ID, 'partner_id', true);
        $partner_nome   = get_post_meta($post->ID, 'partner_nome', true);
        $categoria      = get_post_meta($post->ID, 'categoria', true);
        $durata         = get_post_meta($post->ID, 'durata', true);
        $link_info      = get_post_meta($post->ID, 'link_info', true);
        $ordine         = get_post_meta($post->ID, 'ordine', true);
        $attiva         = get_post_meta($post->ID, 'attiva', true);

        $categorie = ['Lingua italiana', 'Lingua inglese', 'Formazione professionale', 'Università', 'Corsi online', 'Altro'];

        $partners = get_posts([
            'post_type'      => 'wecoop_partner',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
        ?>
        <style>
            .of-field { margin-bottom: 16px; }
            .of-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
            .of-field input[type="text"], .of-field input[type="url"], .of-field input[type="number"],
            .of-field select { width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; }
            .of-hint { font-size: 12px; color: #646970; margin-top: 4px; }
            .of-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            #of-partner-preview { display:flex; align-items:center; gap:10px; margin-top:8px; min-height:40px; }
            #of-partner-preview img { max-width:80px; max-height:50px; border-radius:4px; border:1px solid #dde; object-fit:contain; background:#f6f7f7; padding:3px; }
        </style>
        <div class="of-row">
            <div class="of-field">
                <label for="of_partner_id">Partner / Ente formativo</label>
                <select id="of_partner_id" name="of_partner_id">
                    <option value="">— Nessun partner —</option>
                    <?php foreach ($partners as $p):
                        $logo = get_the_post_thumbnail_url($p->ID, 'thumbnail') ?: '';
                    ?>
                        <option value="<?php echo esc_attr($p->ID); ?>"
                                data-logo="<?php echo esc_attr($logo); ?>"
                                <?php selected($partner_id, $p->ID); ?>>
                            <?php echo esc_html(get_the_title($p)); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (!$partners && $partner_nome): ?>
                        <option value="" disabled><?php echo esc_html('(legacy) ' . $partner_nome); ?></option>
                    <?php endif; ?>
                </select>
                <div id="of-partner-preview">
                    <?php
                    if ($partner_id) {
                        $logo = get_the_post_thumbnail_url($partner_id, 'thumbnail');
                        $name = get_the_title($partner_id);
                        if ($logo) echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($name) . '" />';
                        echo '<span style="font-size:13px;color:#646970;">' . esc_html($name) . '</span>';
                    }
                    ?>
                </div>
                <script>
                document.getElementById('of_partner_id').addEventListener('change', function() {
                    var opt = this.options[this.selectedIndex];
                    var logo = opt.getAttribute('data-logo') || '';
                    var name = opt.text;
                    var preview = document.getElementById('of-partner-preview');
                    preview.innerHTML = logo
                        ? '<img src="' + logo + '" alt="' + name + '" /><span style="font-size:13px;color:#646970;">' + name + '</span>'
                        : '';
                });
                </script>
            </div>
            <div class="of-field">
                <label for="of_categoria">Categoria</label>
                <select id="of_categoria" name="of_categoria">
                    <option value="">— Seleziona —</option>
                    <?php foreach ($categorie as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($categoria, $cat); ?>>
                            <?php echo esc_html($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="of-row">
            <div class="of-field">
                <label for="of_durata">Durata (es. 3 mesi, annuale)</label>
                <input type="text" id="of_durata" name="of_durata" value="<?php echo esc_attr($durata); ?>" placeholder="es. 6 mesi" />
            </div>
            <div class="of-field">
                <label for="of_link_info">Link informazioni</label>
                <input type="url" id="of_link_info" name="of_link_info" value="<?php echo esc_attr($link_info); ?>" placeholder="https://..." />
            </div>
        </div>
        <div class="of-row">
            <div class="of-field" style="max-width:100px;">
                <label for="of_ordine">Ordine di visualizzazione</label>
                <input type="number" id="of_ordine" name="of_ordine" value="<?php echo esc_attr($ordine !== '' ? $ordine : '0'); ?>" min="0" step="1" />
            </div>
            <div class="of-field" style="display:flex;align-items:center;gap:8px;margin-top:28px;">
                <input type="checkbox" id="of_attiva" name="of_attiva" value="1" <?php checked($attiva, '1'); ?> style="width:18px;height:18px;" />
                <label for="of_attiva" style="font-weight:600;cursor:pointer;">Visibile sull'app</label>
                <p class="of-hint" style="margin:0;">Se deselezionato, non appare nell'app</p>
            </div>
        </div>
        <p style="margin-top:12px;padding:10px 14px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:2px;font-size:13px;">
            <strong>Titolo:</strong> nome del corso/percorso (campo <em>Titolo</em> in alto).<br>
            <strong>Descrizione:</strong> descrizione breve nel campo <em>Estratto</em>.<br>
            <strong>Logo/Immagine:</strong> usa il campo <em>Immagine in evidenza</em>.
        </p>
        <?php
    }

    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['offerta_formativa_nonce']) ||
            !wp_verify_nonce($_POST['offerta_formativa_nonce'], 'offerta_formativa_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = [
            'categoria' => 'of_categoria',
            'durata'    => 'of_durata',
            'ordine'    => 'of_ordine',
        ];
        foreach ($fields as $meta_key => $post_key) {
            if (isset($_POST[$post_key])) {
                $value = $meta_key === 'ordine'
                    ? absint($_POST[$post_key])
                    : sanitize_text_field($_POST[$post_key]);
                update_post_meta($post_id, $meta_key, $value);
            }
        }
        // Partner
        if (isset($_POST['of_partner_id'])) {
            $pid = absint($_POST['of_partner_id']);
            update_post_meta($post_id, 'partner_id', $pid);
            // Keep partner_nome in sync for legacy compatibility
            $pname = $pid ? get_the_title($pid) : '';
            update_post_meta($post_id, 'partner_nome', sanitize_text_field($pname));
        }
        if (isset($_POST['of_link_info'])) {
            update_post_meta($post_id, 'link_info', esc_url_raw($_POST['of_link_info']));
        }
        update_post_meta($post_id, 'attiva', isset($_POST['of_attiva']) ? '1' : '0');
    }

    public static function set_columns($columns) {
        return [
            'cb'           => $columns['cb'],
            'title'        => 'Titolo',
            'partner_logo' => 'Partner',
            'categoria'    => 'Categoria',
            'ordine'       => 'Ordine',
            'attiva'       => 'Visibile App',
            'date'         => $columns['date'] ?? 'Data',
        ];
    }

    public static function render_column($column, $post_id) {
        switch ($column) {
            case 'partner_logo':
                $pid  = (int) get_post_meta($post_id, 'partner_id', true);
                $name = $pid ? get_the_title($pid) : get_post_meta($post_id, 'partner_nome', true);
                if ($pid) {
                    $thumb = get_the_post_thumbnail($pid, [32, 32]);
                    echo '<div style="display:flex;align-items:center;gap:6px;">';
                    if ($thumb) echo '<span style="width:32px;height:32px;overflow:hidden;border-radius:3px;border:1px solid #dde;flex-shrink:0;">' . $thumb . '</span>';
                    echo '<span>' . esc_html($name) . '</span></div>';
                } else {
                    echo esc_html($name ?: '—');
                }
                break;
            case 'categoria':
                echo esc_html(get_post_meta($post_id, 'categoria', true) ?: '—');
                break;
            case 'ordine':
                echo esc_html(get_post_meta($post_id, 'ordine', true) ?: '0');
                break;
            case 'attiva':
                $attiva = get_post_meta($post_id, 'attiva', true);
                echo $attiva === '1'
                    ? '<span style="color:#00a32a;font-weight:600;">✓ Sì</span>'
                    : '<span style="color:#d63638;">✗ No</span>';
                break;
        }
    }
}
