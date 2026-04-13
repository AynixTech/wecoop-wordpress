<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Offerte_Lavoro_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_pages']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT, [__CLASS__, 'save_offer_meta']);
        add_action('manage_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT . '_posts_columns', [__CLASS__, 'offer_columns']);
        add_action('manage_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT . '_posts_custom_column', [__CLASS__, 'render_offer_columns'], 10, 2);
    }

    public static function register_admin_pages() {
        add_submenu_page(
            'edit.php?post_type=' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            __('Annunci Inseriti', 'wecoop-offerte-lavoro'),
            __('Annunci Inseriti', 'wecoop-offerte-lavoro'),
            'edit_posts',
            'wecoop-annunci-inseriti',
            [__CLASS__, 'render_annunci_inseriti_page']
        );
    }

    public static function render_annunci_inseriti_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('Non autorizzato', 'wecoop-offerte-lavoro'));
        }

        self::handle_migrate_fallback_action();
        self::handle_delete_action();

        $offers = get_posts([
            'post_type' => [
                WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT,
            ],
            'post_status' => ['publish', 'pending', 'draft', 'private'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $fallback_posts = get_posts([
            'post_type' => 'post',
            'post_status' => ['publish', 'pending', 'draft', 'private'],
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'submitted_from_app',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $all_posts = [];
        foreach (array_merge($offers, $fallback_posts) as $post) {
            $all_posts[$post->ID] = $post;
        }

        usort($all_posts, static function ($a, $b) {
            return strtotime((string) $b->post_date_gmt) <=> strtotime((string) $a->post_date_gmt);
        });

        $notice = isset($_GET['wecoop_notice']) ? sanitize_text_field(wp_unslash($_GET['wecoop_notice'])) : '';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Annunci Inseriti', 'wecoop-offerte-lavoro') . '</h1>';
        echo '<p>' . esc_html__('Qui puoi vedere tutti gli annunci inseriti (offerte, proposte e annunci da app) ed eliminarli.', 'wecoop-offerte-lavoro') . '</p>';

        if ($notice === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Annuncio eliminato con successo.', 'wecoop-offerte-lavoro') . '</p></div>';
        }

        if ($notice === 'migrated') {
            $migrated = isset($_GET['migrated']) ? max(0, (int) $_GET['migrated']) : 0;
            $failed = isset($_GET['failed']) ? max(0, (int) $_GET['failed']) : 0;
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf('Migrazione completata. Migrati: %d, errori: %d.', $migrated, $failed)) . '</p></div>';
        }

        $migrate_url = wp_nonce_url(
            add_query_arg([
                'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                'page' => 'wecoop-annunci-inseriti',
                'wecoop_action' => 'migrate_fallback',
            ], admin_url('edit.php')),
            'wecoop_migrate_fallback_posts'
        );

        echo '<p style="margin: 14px 0 18px;">';
        echo '<a class="button button-primary" href="' . esc_url($migrate_url) . '" onclick="return confirm(\'Vuoi migrare i post fallback in Offerte Lavoro CPT?\');">';
        echo esc_html__('Migra fallback in CPT', 'wecoop-offerte-lavoro');
        echo '</a>';
        echo '</p>';

        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>' . esc_html__('Titolo', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Tipo', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Ambito', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Direzione', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Citta', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Stato', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Data', 'wecoop-offerte-lavoro') . '</th>';
        echo '<th>' . esc_html__('Azioni', 'wecoop-offerte-lavoro') . '</th>';
        echo '</tr></thead><tbody>';

        if (empty($all_posts)) {
            echo '<tr><td colspan="9">' . esc_html__('Nessun annuncio trovato.', 'wecoop-offerte-lavoro') . '</td></tr>';
        } else {
            foreach ($all_posts as $post) {
                $post_id = (int) $post->ID;
                $city = (string) get_post_meta($post_id, 'city', true);
                $scope = (string) get_post_meta($post_id, 'category_scope', true);
                $direction = (string) get_post_meta($post_id, 'category_direction', true);

                if ($scope === '') {
                    $scope = 'job';
                }
                if ($direction === '') {
                    $direction = 'offer';
                }

                $type_label = $post->post_type;
                if ($post->post_type === WeCoop_Offerte_Lavoro_CPT::OFFER_CPT) {
                    $type_label = 'Offerta';
                } elseif ($post->post_type === WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT) {
                    $type_label = 'Proposta';
                } elseif ($post->post_type === 'post') {
                    $type_label = 'Post (app)';
                }

                $delete_url = wp_nonce_url(
                    add_query_arg([
                        'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                        'page' => 'wecoop-annunci-inseriti',
                        'wecoop_action' => 'delete',
                        'post_id' => $post_id,
                    ], admin_url('edit.php')),
                    'wecoop_delete_annuncio_' . $post_id
                );

                echo '<tr>';
                echo '<td>' . esc_html((string) $post_id) . '</td>';
                echo '<td><strong>' . esc_html((string) $post->post_title) . '</strong></td>';
                echo '<td>' . esc_html($type_label) . '</td>';
                echo '<td>' . esc_html($scope) . '</td>';
                echo '<td>' . esc_html($direction) . '</td>';
                echo '<td>' . esc_html($city) . '</td>';
                echo '<td>' . esc_html((string) $post->post_status) . '</td>';
                echo '<td>' . esc_html(get_date_from_gmt((string) $post->post_date_gmt, 'd/m/Y H:i')) . '</td>';
                echo '<td>';
                echo '<a class="button button-small" href="' . esc_url(get_edit_post_link($post_id)) . '">Modifica</a> ';
                echo '<a class="button button-small button-link-delete" href="' . esc_url($delete_url) . '" onclick="return confirm(\'Eliminare questo annuncio?\');">Elimina</a>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    private static function handle_delete_action() {
        $action = isset($_GET['wecoop_action']) ? sanitize_text_field(wp_unslash($_GET['wecoop_action'])) : '';
        $post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

        if ($action !== 'delete' || $post_id <= 0) {
            return;
        }

        check_admin_referer('wecoop_delete_annuncio_' . $post_id);

        if (!current_user_can('delete_post', $post_id)) {
            wp_die(esc_html__('Non autorizzato a eliminare questo annuncio.', 'wecoop-offerte-lavoro'));
        }

        wp_trash_post($post_id);

        wp_safe_redirect(add_query_arg([
            'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'page' => 'wecoop-annunci-inseriti',
            'wecoop_notice' => 'deleted',
        ], admin_url('edit.php')));
        exit;
    }

    private static function handle_migrate_fallback_action() {
        $action = isset($_GET['wecoop_action']) ? sanitize_text_field(wp_unslash($_GET['wecoop_action'])) : '';
        if ($action !== 'migrate_fallback') {
            return;
        }

        check_admin_referer('wecoop_migrate_fallback_posts');

        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('Non autorizzato a migrare gli annunci fallback.', 'wecoop-offerte-lavoro'));
        }

        $fallback_posts = get_posts([
            'post_type' => 'post',
            'post_status' => ['publish', 'pending', 'draft', 'private'],
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'submitted_from_app',
                    'value' => '1',
                    'compare' => '=',
                ],
            ],
            'orderby' => 'date',
            'order' => 'ASC',
        ]);

        $migrated = 0;
        $failed = 0;

        foreach ($fallback_posts as $old_post) {
            $new_post_id = wp_insert_post([
                'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                'post_status' => 'publish',
                'post_title' => (string) $old_post->post_title,
                'post_content' => (string) $old_post->post_content,
                'post_excerpt' => (string) $old_post->post_excerpt,
            ], true);

            if (is_wp_error($new_post_id)) {
                $failed++;
                continue;
            }

            $all_meta = get_post_meta((int) $old_post->ID);
            foreach ($all_meta as $meta_key => $meta_values) {
                if (strpos((string) $meta_key, '_') === 0) {
                    continue;
                }

                foreach ((array) $meta_values as $meta_value) {
                    add_post_meta((int) $new_post_id, (string) $meta_key, maybe_unserialize($meta_value));
                }
            }

            // Normalize key fields for CPT listing.
            update_post_meta((int) $new_post_id, 'is_active', 1);
            if ((string) get_post_meta((int) $new_post_id, 'category_scope', true) === '') {
                update_post_meta((int) $new_post_id, 'category_scope', 'job');
            }
            if ((string) get_post_meta((int) $new_post_id, 'category_direction', true) === '') {
                update_post_meta((int) $new_post_id, 'category_direction', 'offer');
            }

            $category_slug = (string) get_post_meta((int) $new_post_id, 'category_slug', true);
            if ($category_slug !== '') {
                $term = get_term_by('slug', $category_slug, WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX);
                if ($term && !is_wp_error($term)) {
                    wp_set_post_terms((int) $new_post_id, [(int) $term->term_id], WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX, false);
                }
            }

            wp_trash_post((int) $old_post->ID);
            $migrated++;
        }

        wp_safe_redirect(add_query_arg([
            'post_type' => WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'page' => 'wecoop-annunci-inseriti',
            'wecoop_notice' => 'migrated',
            'migrated' => $migrated,
            'failed' => $failed,
        ], admin_url('edit.php')));
        exit;
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'wecoop_offer_meta',
            __('Dettagli Offerta', 'wecoop-offerte-lavoro'),
            [__CLASS__, 'render_offer_meta_box'],
            WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
            'normal',
            'high'
        );
    }

    public static function render_offer_meta_box($post) {
        wp_nonce_field('wecoop_offer_meta_nonce', 'wecoop_offer_meta_nonce_field');

        $fields = self::fields();

        echo '<table class="form-table">';

        foreach ($fields as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            $type = in_array($key, ['is_featured', 'is_active'], true) ? 'checkbox' : 'text';

            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
            echo '<td>';

            if ($type === 'checkbox') {
                echo '<input type="checkbox" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="1" ' . checked((bool) $value, true, false) . ' />';
            } else {
                echo '<input type="text" class="regular-text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr((string) $value) . '" />';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    public static function save_offer_meta($post_id) {
        if (!isset($_POST['wecoop_offer_meta_nonce_field'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wecoop_offer_meta_nonce_field'])), 'wecoop_offer_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        foreach (self::fields() as $key => $_label) {
            if (in_array($key, ['is_featured', 'is_active'], true)) {
                update_post_meta($post_id, $key, isset($_POST[$key]) ? 1 : 0);
                continue;
            }

            if (!isset($_POST[$key])) {
                continue;
            }

            $raw = wp_unslash($_POST[$key]);
            if ($key === 'email_contact') {
                $value = sanitize_email((string) $raw);
            } elseif (in_array($key, ['source_url', 'image_url'], true)) {
                $value = esc_url_raw((string) $raw);
            } else {
                $value = sanitize_text_field((string) $raw);
            }
            update_post_meta($post_id, $key, $value);
        }
    }

    public static function offer_columns($columns) {
        $columns['company_name'] = __('Azienda', 'wecoop-offerte-lavoro');
        $columns['city'] = __('Citta', 'wecoop-offerte-lavoro');
        $columns['contract_type'] = __('Contratto', 'wecoop-offerte-lavoro');
        $columns['is_active'] = __('Attiva', 'wecoop-offerte-lavoro');

        return $columns;
    }

    public static function render_offer_columns($column, $post_id) {
        if ($column === 'company_name') {
            echo esc_html((string) get_post_meta($post_id, 'company_name', true));
        }

        if ($column === 'city') {
            echo esc_html((string) get_post_meta($post_id, 'city', true));
        }

        if ($column === 'contract_type') {
            echo esc_html((string) get_post_meta($post_id, 'contract_type', true));
        }

        if ($column === 'is_active') {
            echo (bool) get_post_meta($post_id, 'is_active', true) ? 'Si' : 'No';
        }
    }

    private static function fields() {
        return [
            'category_macro' => 'Macrocategoria',
            'category_sub' => 'Sottocategoria',
            'company_name' => 'Azienda',
            'city' => 'Citta',
            'province' => 'Provincia',
            'region' => 'Regione',
            'contract_type' => 'Tipo contratto',
            'work_mode' => 'Modalita lavoro (presenza/remoto/ibrido)',
            'salary_range' => 'Range retribuzione',
            'language_requirement' => 'Lingue richieste',
            'phone_whatsapp' => 'Telefono / WhatsApp',
            'email_contact' => 'Email contatto',
            'image_url' => 'Immagine (URL opzionale)',
            'source_url' => 'URL esterno annuncio',
            'requirements' => 'Requisiti principali',
            'schedule' => 'Orari / Disponibilita',
            'target_community' => 'Target comunita',
            'expires_at' => 'Scadenza (YYYY-MM-DD)',
            'is_featured' => 'In evidenza',
            'is_active' => 'Annuncio attivo',
        ];
    }
}
