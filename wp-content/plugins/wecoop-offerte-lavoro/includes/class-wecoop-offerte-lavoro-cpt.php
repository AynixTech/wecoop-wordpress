<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Offerte_Lavoro_CPT {

    public const OFFER_CPT = 'wecoop_job_offer';
    public const APPLICATION_CPT = 'wecoop_job_application';
    public const CATEGORY_TAX = 'wecoop_job_category';

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('init', [__CLASS__, 'register_meta_fields']);
    }

    public static function register_post_types() {
        register_post_type(self::OFFER_CPT, [
            'labels' => [
                'name' => __('Offerte Lavoro', 'wecoop-offerte-lavoro'),
                'singular_name' => __('Offerta Lavoro', 'wecoop-offerte-lavoro'),
                'add_new_item' => __('Aggiungi Offerta Lavoro', 'wecoop-offerte-lavoro'),
                'edit_item' => __('Modifica Offerta Lavoro', 'wecoop-offerte-lavoro'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businesswoman',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'offerte-lavoro'],
        ]);

        register_post_type(self::APPLICATION_CPT, [
            'labels' => [
                'name' => __('Candidature Lavoro', 'wecoop-offerte-lavoro'),
                'singular_name' => __('Candidatura Lavoro', 'wecoop-offerte-lavoro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'menu_icon' => 'dashicons-id',
            'supports' => ['title'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);
    }

    public static function register_taxonomies() {
        register_taxonomy(self::CATEGORY_TAX, [self::OFFER_CPT], [
            'labels' => [
                'name' => __('Categorie Lavoro', 'wecoop-offerte-lavoro'),
                'singular_name' => __('Categoria Lavoro', 'wecoop-offerte-lavoro'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => 'categoria-lavoro'],
        ]);
    }

    public static function register_meta_fields() {
        $offer_fields = [
            'company_name' => 'string',
            'city' => 'string',
            'province' => 'string',
            'region' => 'string',
            'contract_type' => 'string',
            'work_mode' => 'string',
            'salary_range' => 'string',
            'language_requirement' => 'string',
            'phone_whatsapp' => 'string',
            'email_contact' => 'string',
            'source_url' => 'string',
            'requirements' => 'string',
            'schedule' => 'string',
            'target_community' => 'string',
            'expires_at' => 'string',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];

        foreach ($offer_fields as $field => $type) {
            register_post_meta(self::OFFER_CPT, $field, [
                'type' => $type,
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => [__CLASS__, 'sanitize_meta_value'],
                'auth_callback' => '__return_true',
            ]);
        }

        $application_fields = [
            'offer_id' => 'integer',
            'applicant_name' => 'string',
            'applicant_phone' => 'string',
            'applicant_email' => 'string',
            'applicant_city' => 'string',
            'applicant_note' => 'string',
            'applicant_origin' => 'string',
            'consent_privacy' => 'boolean',
            'status' => 'string',
        ];

        foreach ($application_fields as $field => $type) {
            register_post_meta(self::APPLICATION_CPT, $field, [
                'type' => $type,
                'single' => true,
                'show_in_rest' => false,
                'sanitize_callback' => [__CLASS__, 'sanitize_meta_value'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    public static function sanitize_meta_value($value) {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value) && (string) ((int) $value) === (string) $value) {
            return (int) $value;
        }

        if (is_string($value)) {
            return sanitize_text_field($value);
        }

        return $value;
    }

    public static function seed_default_terms() {
        $terms = [
            'Baby sitter' => 'baby-sitter',
            'Badante' => 'badante',
            'Colf' => 'colf',
            'OSS / OSA' => 'oss-osa',
            'ASO (Assistente Studio Odontoiatrico)' => 'aso',
            'Segreteria' => 'segreteria',
            'Manicure / Onicotecnica' => 'manicure',
            'Dentista / Odontoiatria' => 'dentista',
            'Massaggi / Benessere' => 'massaggi',
            'Fotografo / Fotografa' => 'fotografo',
            'DJ / Musica Eventi' => 'dj',
            'Animatori / Animatrici' => 'animatori',
            'Pulizie / Limpieza' => 'pulizie-limpieza',
        ];

        foreach ($terms as $name => $slug) {
            if (!term_exists($slug, self::CATEGORY_TAX)) {
                wp_insert_term($name, self::CATEGORY_TAX, ['slug' => $slug]);
            }
        }
    }
}
