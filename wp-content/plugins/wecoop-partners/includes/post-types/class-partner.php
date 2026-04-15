<?php
/**
 * Meta boxes e colonne per il CPT wecoop_partner (registrato dal tema)
 *
 * Non registriamo un nuovo CPT: estendiamo quello già presente nel tema.
 *
 * @package WECOOP_Partners
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Partner_CPT {

    public static function init() {
        add_action('add_meta_boxes',            [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_wecoop_partner',  [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_wecoop_partner_posts_columns',        [__CLASS__, 'set_custom_columns']);
        add_action('manage_wecoop_partner_posts_custom_column',  [__CLASS__, 'custom_column_content'], 10, 2);
    }

    /** Non registriamo nulla – il tema lo fa già */
    public static function register_post_type() {}

    /**
     * Colonne lista admin
     */
    public static function set_custom_columns($columns) {
        $new = [];
        $new['cb']          = $columns['cb'];
        $new['logo']        = 'Logo';
        $new['title']       = 'Nome Azienda';
        $new['website_url'] = 'Sito Web';
        $new['ordine']      = 'Ordine';
        $new['date']        = $columns['date'] ?? 'Data';
        return $new;
    }

    /**
     * Contenuto colonne custom
     */
    public static function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'logo':
                $logo_url = get_the_post_thumbnail_url($post_id, [50, 50]);
                if ($logo_url) {
                    echo '<img src="' . esc_url($logo_url) . '" style="width:50px;height:50px;object-fit:contain;border-radius:4px;background:#f5f5f5;padding:2px;" />';
                } else {
                    echo '<span style="color:#aaa;">—</span>';
                }
                break;
            case 'website_url':
                $url = get_post_meta($post_id, 'website_url', true);
                if ($url) {
                    echo '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a>';
                } else {
                    echo '<span style="color:#aaa;">—</span>';
                }
                break;
            case 'ordine':
                $ordine = get_post_meta($post_id, 'ordine', true);
                echo esc_html($ordine !== '' ? $ordine : '0');
                break;
        }
    }

    /**
     * Metaboxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'partner_details',
            'Dettagli Partner',
            [__CLASS__, 'render_metabox_details'],
            'wecoop_partner',
            'normal',
            'high'
        );
    }

    /**
     * Render metabox dettagli
     */
    public static function render_metabox_details($post) {
        wp_nonce_field('partner_save', 'partner_nonce');

        $website_url = get_post_meta($post->ID, 'website_url', true);
        $ordine      = get_post_meta($post->ID, 'ordine', true);
        ?>
        <style>
            .partner-field { margin-bottom: 16px; }
            .partner-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
            .partner-field input[type="text"],
            .partner-field input[type="url"],
            .partner-field input[type="number"] { width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; }
            .partner-hint { font-size: 12px; color: #646970; margin-top: 4px; }
        </style>

        <div class="partner-field">
            <label for="partner_website_url">Sito Web</label>
            <input type="url" id="partner_website_url" name="partner_website_url"
                   value="<?php echo esc_attr($website_url); ?>"
                   placeholder="https://www.esempio.it" />
            <p class="partner-hint">URL completo del sito ufficiale del partner (opzionale).</p>
        </div>

        <div class="partner-field" style="max-width:120px;">
            <label for="partner_ordine">Ordine di visualizzazione</label>
            <input type="number" id="partner_ordine" name="partner_ordine"
                   value="<?php echo esc_attr($ordine !== '' ? $ordine : '0'); ?>"
                   min="0" step="1" />
            <p class="partner-hint">Numero più basso = mostrato prima.</p>
        </div>

        <p style="margin-top:16px;padding:10px 14px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:2px;font-size:13px;">
            <strong>Logo:</strong> usa il riquadro <em>Immagine in evidenza</em> per caricare il logo.<br>
            <strong>Nome azienda:</strong> scrivi nel campo <em>Titolo</em> in alto.<br>
            <strong>Descrizione:</strong> usa il campo <em>Contenuto / Estratto</em> già presente nel post.
        </p>
        <?php
    }

    /**
     * Salva metaboxes
     */
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['partner_nonce']) || !wp_verify_nonce($_POST['partner_nonce'], 'partner_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['partner_website_url'])) {
            update_post_meta($post_id, 'website_url', esc_url_raw($_POST['partner_website_url']));
        }
        if (isset($_POST['partner_ordine'])) {
            update_post_meta($post_id, 'ordine', absint($_POST['partner_ordine']));
        }
    }
}
