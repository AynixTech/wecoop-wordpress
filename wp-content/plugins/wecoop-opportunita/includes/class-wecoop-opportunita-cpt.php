<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Opportunita_CPT {

    const POST_TYPE = 'opportunita_wecoop';
    const TAX_CATEGORIA = 'categoria_opportunita';
    const TAX_TAG = 'tag_opportunita';
    const TAX_TIPO = 'tipo_contenuto_opportunita';

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('init', [__CLASS__, 'register_meta_fields']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [__CLASS__, 'save_meta_boxes']);
    }

    public static function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name' => 'Progetti e Opportunita',
                'singular_name' => 'Opportunita',
                'add_new' => 'Nuova Opportunita',
                'add_new_item' => 'Aggiungi Nuova Opportunita',
                'edit_item' => 'Modifica Opportunita',
                'view_item' => 'Visualizza Opportunita',
                'search_items' => 'Cerca Opportunita',
                'not_found' => 'Nessuna opportunita trovata',
                'menu_name' => 'Progetti e Opportunita',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'opportunita',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'progetti-opportunita'],
            'menu_icon' => 'dashicons-megaphone',
            'taxonomies' => [
                self::TAX_CATEGORIA,
                self::TAX_TAG,
                self::TAX_TIPO,
            ],
        ]);
    }

    public static function register_taxonomies() {
        register_taxonomy(self::TAX_CATEGORIA, self::POST_TYPE, [
            'labels' => [
                'name' => 'Categorie Opportunita',
                'singular_name' => 'Categoria Opportunita',
                'menu_name' => 'Categoria Principale',
            ],
            'public' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'categoria-opportunita'],
        ]);

        register_taxonomy(self::TAX_TAG, self::POST_TYPE, [
            'labels' => [
                'name' => 'Tag Opportunita',
                'singular_name' => 'Tag Opportunita',
                'menu_name' => 'Tag/Filtro',
            ],
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'tag-opportunita'],
        ]);

        register_taxonomy(self::TAX_TIPO, self::POST_TYPE, [
            'labels' => [
                'name' => 'Tipi Contenuto Opportunita',
                'singular_name' => 'Tipo Contenuto Opportunita',
                'menu_name' => 'Tipo Contenuto',
            ],
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'tipo-contenuto-opportunita'],
        ]);
    }

    public static function register_meta_fields() {
        $string_fields = [
            'card_title',
            'card_excerpt',
            'cta_label',
            'cta_type',
            'language',
            'icon_name',
        ];

        foreach ($string_fields as $field) {
            register_post_meta(self::POST_TYPE, $field, [
                'single' => true,
                'type' => 'string',
                'show_in_rest' => true,
                'sanitize_callback' => $field === 'card_excerpt' ? 'sanitize_textarea_field' : 'sanitize_text_field',
                'auth_callback' => [__CLASS__, 'meta_auth_callback'],
            ]);
        }

        register_post_meta(self::POST_TYPE, 'cta_url', [
            'single' => true,
            'type' => 'string',
            'show_in_rest' => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback' => [__CLASS__, 'meta_auth_callback'],
        ]);

        register_post_meta(self::POST_TYPE, 'cover_image', [
            'single' => true,
            'type' => 'string',
            'show_in_rest' => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback' => [__CLASS__, 'meta_auth_callback'],
        ]);

        register_post_meta(self::POST_TYPE, 'priority_order', [
            'single' => true,
            'type' => 'integer',
            'show_in_rest' => true,
            'sanitize_callback' => 'absint',
            'auth_callback' => [__CLASS__, 'meta_auth_callback'],
        ]);

        register_post_meta(self::POST_TYPE, 'is_featured', [
            'single' => true,
            'type' => 'boolean',
            'show_in_rest' => true,
            'sanitize_callback' => [__CLASS__, 'sanitize_checkbox_value'],
            'auth_callback' => [__CLASS__, 'meta_auth_callback'],
        ]);

        register_post_meta(self::POST_TYPE, 'publish_in_app', [
            'single' => true,
            'type' => 'boolean',
            'show_in_rest' => true,
            'sanitize_callback' => [__CLASS__, 'sanitize_checkbox_value'],
            'auth_callback' => [__CLASS__, 'meta_auth_callback'],
        ]);
    }

    public static function meta_auth_callback(...$args) {
        return current_user_can('edit_posts');
    }

    public static function sanitize_checkbox_value($value) {
        return !empty($value);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'wecoop_opportunita_card',
            'Card Azionabile',
            [__CLASS__, 'render_card_metabox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function render_card_metabox($post) {
        wp_nonce_field('wecoop_opportunita_save', 'wecoop_opportunita_nonce');

        $card_title = get_post_meta($post->ID, 'card_title', true);
        $card_excerpt = get_post_meta($post->ID, 'card_excerpt', true);
        $cta_label = get_post_meta($post->ID, 'cta_label', true);
        $cta_url = get_post_meta($post->ID, 'cta_url', true);
        $cta_type = get_post_meta($post->ID, 'cta_type', true);
        $priority_order = get_post_meta($post->ID, 'priority_order', true);
        $is_featured = get_post_meta($post->ID, 'is_featured', true);
        $publish_in_app = get_post_meta($post->ID, 'publish_in_app', true);
        $language = get_post_meta($post->ID, 'language', true);
        $cover_image = get_post_meta($post->ID, 'cover_image', true);
        $icon_name = get_post_meta($post->ID, 'icon_name', true);

        if ($language === '') {
            $language = 'it';
        }

        if ($cta_type === '') {
            $cta_type = 'internal_page';
        }

        if ($publish_in_app === '') {
            $publish_in_app = '1';
        }

        ?>
        <style>
            .wecoop-op-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
                margin-top: 10px;
            }
            .wecoop-op-full {
                grid-column: 1 / -1;
            }
            .wecoop-op-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .wecoop-op-field input,
            .wecoop-op-field textarea,
            .wecoop-op-field select {
                width: 100%;
            }
            .wecoop-op-help {
                color: #646970;
                font-size: 12px;
                margin-top: 4px;
            }
        </style>

        <div class="wecoop-op-grid">
            <div class="wecoop-op-field wecoop-op-full">
                <label for="card_title">Card Title</label>
                <input id="card_title" type="text" name="card_title" value="<?php echo esc_attr($card_title); ?>" maxlength="120" placeholder="Titolo breve per card">
            </div>

            <div class="wecoop-op-field wecoop-op-full">
                <label for="card_excerpt">Card Excerpt</label>
                <textarea id="card_excerpt" name="card_excerpt" rows="4" maxlength="320" placeholder="Descrizione breve da 3-4 righe"><?php echo esc_textarea($card_excerpt); ?></textarea>
                <p class="wecoop-op-help">Ideale: 160-220 caratteri.</p>
            </div>

            <div class="wecoop-op-field">
                <label for="cta_label">CTA Label</label>
                <input id="cta_label" type="text" name="cta_label" value="<?php echo esc_attr($cta_label); ?>" maxlength="80" placeholder="Es. Partecipa">
            </div>

            <div class="wecoop-op-field">
                <label for="cta_type">CTA Type</label>
                <select id="cta_type" name="cta_type">
                    <option value="internal_page" <?php selected($cta_type, 'internal_page'); ?>>internal_page</option>
                    <option value="external_url" <?php selected($cta_type, 'external_url'); ?>>external_url</option>
                    <option value="form" <?php selected($cta_type, 'form'); ?>>form</option>
                    <option value="app_deeplink" <?php selected($cta_type, 'app_deeplink'); ?>>app_deeplink</option>
                </select>
            </div>

            <div class="wecoop-op-field wecoop-op-full">
                <label for="cta_url">CTA URL</label>
                <input id="cta_url" type="url" name="cta_url" value="<?php echo esc_attr($cta_url); ?>" placeholder="https://... oppure deep link app">
            </div>

            <div class="wecoop-op-field">
                <label for="priority_order">Priority Order</label>
                <input id="priority_order" type="number" min="0" name="priority_order" value="<?php echo esc_attr($priority_order === '' ? 0 : (int) $priority_order); ?>">
            </div>

            <div class="wecoop-op-field">
                <label for="language">Language</label>
                <select id="language" name="language">
                    <option value="it" <?php selected($language, 'it'); ?>>it</option>
                    <option value="en" <?php selected($language, 'en'); ?>>en</option>
                    <option value="es" <?php selected($language, 'es'); ?>>es</option>
                </select>
            </div>

            <div class="wecoop-op-field">
                <label for="cover_image">Cover Image URL (opzionale)</label>
                <input id="cover_image" type="url" name="cover_image" value="<?php echo esc_attr($cover_image); ?>" placeholder="https://...">
            </div>

            <div class="wecoop-op-field">
                <label for="icon_name">Icon Name (opzionale)</label>
                <input id="icon_name" type="text" name="icon_name" value="<?php echo esc_attr($icon_name); ?>" maxlength="80" placeholder="Es. briefcase">
            </div>

            <div class="wecoop-op-field">
                <label><input type="checkbox" name="is_featured" value="1" <?php checked($is_featured, '1'); ?>> Evidenzia in alto (featured)</label>
            </div>

            <div class="wecoop-op-field">
                <label><input type="checkbox" name="publish_in_app" value="1" <?php checked($publish_in_app, '1'); ?>> Pubblica in app</label>
            </div>
        </div>
        <?php
    }

    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['wecoop_opportunita_nonce']) || !wp_verify_nonce($_POST['wecoop_opportunita_nonce'], 'wecoop_opportunita_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $text_fields = [
            'card_title',
            'cta_label',
            'cta_type',
            'language',
            'icon_name',
        ];

        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
            }
        }

        if (isset($_POST['card_excerpt'])) {
            update_post_meta($post_id, 'card_excerpt', sanitize_textarea_field(wp_unslash($_POST['card_excerpt'])));
        }

        if (isset($_POST['cta_url'])) {
            update_post_meta($post_id, 'cta_url', esc_url_raw(wp_unslash($_POST['cta_url'])));
        }

        if (isset($_POST['cover_image'])) {
            update_post_meta($post_id, 'cover_image', esc_url_raw(wp_unslash($_POST['cover_image'])));
        }

        $priority = isset($_POST['priority_order']) ? absint($_POST['priority_order']) : 0;
        update_post_meta($post_id, 'priority_order', $priority);

        update_post_meta($post_id, 'is_featured', isset($_POST['is_featured']) ? '1' : '0');
        update_post_meta($post_id, 'publish_in_app', isset($_POST['publish_in_app']) ? '1' : '0');
    }

    public static function seed_default_terms() {
        $categorie = [
            'Formazione' => 'formazione',
            'Lavoro' => 'lavoro',
            'Imprenditorialita' => 'imprenditorialita',
            'Inclusione sociale' => 'inclusione-sociale',
        ];

        foreach ($categorie as $name => $slug) {
            if (!term_exists($slug, self::TAX_CATEGORIA)) {
                wp_insert_term($name, self::TAX_CATEGORIA, ['slug' => $slug]);
            }
        }

        $tags = [
            'Giovani' => 'giovani',
            'Donne' => 'donne',
            'Migranti' => 'migranti',
            'Famiglie' => 'famiglie',
            'Studenti' => 'studenti',
            'Imprenditori' => 'imprenditori',
        ];

        foreach ($tags as $name => $slug) {
            if (!term_exists($slug, self::TAX_TAG)) {
                wp_insert_term($name, self::TAX_TAG, ['slug' => $slug]);
            }
        }

        $tipi = [
            'Operativo' => 'operativo',
            'Opportunita' => 'opportunita',
            'Educativo breve' => 'educativo-breve',
        ];

        foreach ($tipi as $name => $slug) {
            if (!term_exists($slug, self::TAX_TIPO)) {
                wp_insert_term($name, self::TAX_TIPO, ['slug' => $slug]);
            }
        }
    }
}
