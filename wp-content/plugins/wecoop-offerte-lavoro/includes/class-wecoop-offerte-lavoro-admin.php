<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Offerte_Lavoro_Admin {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT, [__CLASS__, 'save_offer_meta']);
        add_action('manage_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT . '_posts_columns', [__CLASS__, 'offer_columns']);
        add_action('manage_' . WeCoop_Offerte_Lavoro_CPT::OFFER_CPT . '_posts_custom_column', [__CLASS__, 'render_offer_columns'], 10, 2);
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
            $value = $key === 'email_contact' ? sanitize_email((string) $raw) : sanitize_text_field((string) $raw);
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
