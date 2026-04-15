<?php
/**
 * CPT: partner — Aziende/enti formativi partner di WeCoop
 *
 * @package WECOOP_Offerta_Formativa
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Partner_CPT {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_partner', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_partner_posts_columns', [__CLASS__, 'set_columns']);
        add_action('manage_partner_posts_custom_column', [__CLASS__, 'render_column'], 10, 2);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
    }

    public static function register_post_type() {
        register_post_type('partner', [
            'labels' => [
                'name'               => 'Partner',
                'singular_name'      => 'Partner',
                'add_new'            => 'Nuovo Partner',
                'add_new_item'       => 'Aggiungi Partner',
                'edit_item'          => 'Modifica Partner',
                'view_item'          => 'Visualizza Partner',
                'search_items'       => 'Cerca Partner',
                'not_found'          => 'Nessun partner trovato',
                'not_found_in_trash' => 'Nessun partner nel cestino',
                'menu_name'          => 'Partner',
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'edit.php?post_type=offerta_formativa',
            'show_in_rest'    => false,
            'supports'        => ['title', 'thumbnail'],
            'capability_type' => 'post',
            'has_archive'     => false,
            'rewrite'         => false,
            'menu_icon'       => 'dashicons-building',
        ]);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'partner_details',
            'Dettagli Partner',
            [__CLASS__, 'render_metabox'],
            'partner',
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        wp_nonce_field('partner_save', 'partner_nonce');

        $sito_web    = get_post_meta($post->ID, 'sito_web', true);
        $descrizione = get_post_meta($post->ID, 'descrizione', true);
        $logo_id     = get_post_thumbnail_id($post->ID);
        $logo_url    = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
        ?>
        <style>
            .partner-field { margin-bottom: 16px; }
            .partner-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
            .partner-field input[type="text"], .partner-field input[type="url"],
            .partner-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; }
            .partner-field textarea { height: 80px; resize: vertical; }
            .partner-logo-preview { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
            .partner-logo-preview img { max-width: 120px; max-height: 80px; border-radius: 6px;
                border: 1px solid #dde; object-fit: contain; background: #f6f7f7; padding: 4px; }
            .partner-hint { font-size: 12px; color: #646970; margin-top: 4px; }
        </style>

        <p class="partner-hint" style="margin-bottom:16px;padding:10px 14px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:2px;font-size:13px;">
            <strong>Logo:</strong> usa il campo <em>Immagine in evidenza</em> (pannello laterale destro).
            L'immagine sarà mostrata come avatar del partner nelle offerte formative dell'app.
        </p>

        <?php if ($logo_url): ?>
        <div class="partner-logo-preview">
            <img src="<?php echo esc_url($logo_url); ?>" alt="Logo partner" />
            <span class="partner-hint">Immagine in evidenza attuale</span>
        </div>
        <?php endif; ?>

        <div class="partner-field">
            <label for="partner_sito_web">Sito web</label>
            <input type="url" id="partner_sito_web" name="partner_sito_web"
                   value="<?php echo esc_attr($sito_web); ?>" placeholder="https://www.example.com" />
        </div>
        <div class="partner-field">
            <label for="partner_descrizione">Descrizione breve</label>
            <textarea id="partner_descrizione" name="partner_descrizione"
                      placeholder="es. Fondazione specializzata in corsi di lingua italiana per stranieri."><?php echo esc_textarea($descrizione); ?></textarea>
        </div>
        <?php
    }

    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['partner_nonce']) ||
            !wp_verify_nonce($_POST['partner_nonce'], 'partner_save')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['partner_sito_web'])) {
            update_post_meta($post_id, 'sito_web', esc_url_raw($_POST['partner_sito_web']));
        }
        if (isset($_POST['partner_descrizione'])) {
            update_post_meta($post_id, 'descrizione', sanitize_textarea_field($_POST['partner_descrizione']));
        }
    }

    public static function set_columns($columns) {
        return [
            'cb'       => $columns['cb'],
            'logo'     => 'Logo',
            'title'    => 'Nome Partner',
            'sito_web' => 'Sito Web',
            'offerte'  => 'Offerte associate',
            'date'     => $columns['date'] ?? 'Data',
        ];
    }

    public static function render_column($column, $post_id) {
        switch ($column) {
            case 'logo':
                $thumb = get_the_post_thumbnail($post_id, [48, 48]);
                if ($thumb) {
                    echo '<div style="width:48px;height:48px;overflow:hidden;border-radius:4px;border:1px solid #dde;background:#f6f7f7;display:flex;align-items:center;justify-content:center;">' . $thumb . '</div>';
                } else {
                    echo '<span style="color:#aaa;">—</span>';
                }
                break;
            case 'sito_web':
                $url = get_post_meta($post_id, 'sito_web', true);
                echo $url ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(parse_url($url, PHP_URL_HOST) ?: $url) . '</a>' : '—';
                break;
            case 'offerte':
                $q = new WP_Query([
                    'post_type'      => 'offerta_formativa',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => [['key' => 'partner_id', 'value' => $post_id, 'compare' => '=']],
                ]);
                echo intval($q->found_posts);
                break;
        }
    }

    public static function enqueue_scripts($hook) {
        // no custom JS needed — media handled by featured image
    }
}
