<?php
/**
 * Custom Post Type: Partner
 *
 * Gestisce i partner/aziende di WeCoop
 *
 * @package WECOOP_Partners
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Partner_CPT {

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_partner', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_partner_posts_columns', [__CLASS__, 'set_custom_columns']);
        add_action('manage_partner_posts_custom_column', [__CLASS__, 'custom_column_content'], 10, 2);
    }

    /**
     * Registra CPT
     */
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
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'show_in_rest'  => true,
            'rest_base'     => 'partners-cpt',
            'supports'      => ['title', 'thumbnail'],
            'capability_type' => 'post',
            'has_archive'   => false,
            'rewrite'       => false,
            'menu_icon'     => 'dashicons-groups',
            'menu_position' => 25,
        ]);
    }

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
        $new['date']        = $columns['date'];
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
            'partner',
            'normal',
            'high'
        );
    }

    /**
     * Render metabox dettagli
     */
    public static function render_metabox_details($post) {
        wp_nonce_field('partner_save', 'partner_nonce');

        $website_url   = get_post_meta($post->ID, 'website_url', true);
        $descrizione   = get_post_meta($post->ID, 'descrizione', true);
        $ordine        = get_post_meta($post->ID, 'ordine', true);
        ?>
        <style>
            .partner-field { margin-bottom: 16px; }
            .partner-field label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
            .partner-field input[type="text"],
            .partner-field input[type="url"],
            .partner-field input[type="number"],
            .partner-field textarea { width: 100%; padding: 8px 10px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; }
            .partner-field textarea { resize: vertical; min-height: 80px; }
            .partner-hint { font-size: 12px; color: #646970; margin-top: 4px; }
            .partner-section-title { font-size: 13px; font-weight: 700; color: #50575e; text-transform: uppercase; letter-spacing: .5px; margin: 20px 0 10px; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px; }
        </style>

        <div class="partner-field">
            <label for="partner_website_url">Sito Web</label>
            <input type="url" id="partner_website_url" name="partner_website_url"
                   value="<?php echo esc_attr($website_url); ?>"
                   placeholder="https://www.esempio.it" />
            <p class="partner-hint">URL completo del sito ufficiale del partner (opzionale).</p>
        </div>

        <div class="partner-field">
            <label for="partner_descrizione">Descrizione breve</label>
            <textarea id="partner_descrizione" name="partner_descrizione"><?php echo esc_textarea($descrizione); ?></textarea>
            <p class="partner-hint">Breve descrizione del partner (opzionale, visibile nell'app).</p>
        </div>

        <div class="partner-field" style="max-width:120px;">
            <label for="partner_ordine">Ordine di visualizzazione</label>
            <input type="number" id="partner_ordine" name="partner_ordine"
                   value="<?php echo esc_attr($ordine !== '' ? $ordine : '0'); ?>"
                   min="0" step="1" />
            <p class="partner-hint">Numero più basso = mostrato prima.</p>
        </div>

        <p style="margin-top:16px;padding:10px 14px;background:#f0f6fc;border-left:4px solid #2271b1;border-radius:2px;font-size:13px;">
            <strong>Logo:</strong> usa il riquadro <em>Immagine in evidenza</em> (in alto a destra) per caricare il logo del partner.<br>
            <strong>Nome azienda:</strong> scrivi il nome nel campo <em>Titolo</em> in alto.
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

        $fields = [
            'website_url' => 'partner_website_url',
            'descrizione' => 'partner_descrizione',
            'ordine'      => 'partner_ordine',
        ];

        foreach ($fields as $meta_key => $post_key) {
            if (isset($_POST[$post_key])) {
                $value = sanitize_text_field($_POST[$post_key]);
                if ($meta_key === 'website_url') {
                    $value = esc_url_raw($_POST[$post_key]);
                }
                if ($meta_key === 'descrizione') {
                    $value = sanitize_textarea_field($_POST[$post_key]);
                }
                if ($meta_key === 'ordine') {
                    $value = absint($_POST[$post_key]);
                }
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
}
