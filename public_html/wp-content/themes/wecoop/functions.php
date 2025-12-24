<?php
/**
 * Impostazioni tema Wecoop
 */
function wecoop_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');

    register_nav_menus([
        'main-menu' => __('Menu Principale', 'wecoop')
    ]);
}
add_action('after_setup_theme', 'wecoop_setup');

/**
 * Caricamento script e stili principali
 */
function wecoop_scripts() {
    // Stili principali
    wp_enqueue_style('wecoop-style', get_stylesheet_uri(), [], filemtime(get_stylesheet_directory() . '/style.css'));

    // Script minificato personalizzato
    wp_enqueue_script('wecoop-scripts', get_template_directory_uri() . '/assets/js/scripts.min.js', ['jquery'], filemtime(get_template_directory() . '/assets/js/scripts.min.js'), true);

}

add_action('wp_enqueue_scripts', 'wecoop_scripts');

/**
 * Caricamento jQuery da CDN (solo front-end)
 */
function load_jquery_from_cdn() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', [], '3.6.0', true);
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'load_jquery_from_cdn');

/**
 * Caricamento dinamico di tutti i CSS in /assets/css/
 */
function wecoop_enqueue_styles() {
    $css_dir = get_template_directory() . '/assets/css/';
    $css_files = glob($css_dir . '*.css');

    foreach ($css_files as $css_file) {
        $css_filename = basename($css_file);
        wp_enqueue_style('wecoop-' . sanitize_title($css_filename), get_template_directory_uri() . '/assets/css/' . $css_filename, [], filemtime($css_file), 'all');
    }
}
add_action('wp_enqueue_scripts', 'wecoop_enqueue_styles');

/**
 * Integrazione Swiper.js (caricamento su tutte le pagine, modifica se vuoi limitare)
 */
function enqueue_swiper_scripts() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', ['jquery'], '11', true);
    wp_enqueue_script('wecoop-swiper', get_template_directory_uri() . '/assets/js/wecoop-swiper.js', ['jquery', 'swiper-js'], filemtime(get_template_directory() . '/assets/js/wecoop-swiper.js'), true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_scripts');

/**
 * Traduzioni tramite file JSON
 */
function theme_load_translation() {
    $lang = isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : 'it';
    
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en', 'es'])) {
        $lang = sanitize_text_field($_GET['lang']);
    }
    
    if (!in_array($lang, ['it', 'en', 'es'])) {
        $lang = 'it';
    }
    
    $lang_file = get_template_directory() . "/languages/$lang.json";
    return file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];
}

function theme_translate($key) {
    static $translations;
    if (!$translations) {
        $translations = theme_load_translation();
    }
    return $translations[$key] ?? $key;
}

/**
 * Caricamento font esterni (Nunito, Merriweather, Font Awesome)
 */
function wecoop_enqueue_fonts() {
    wp_enqueue_style('nunito-font', 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap', [], null);
    wp_enqueue_style('merriweather-font', 'https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap', [], null);
    wp_enqueue_style('feeling-passionate-font', 'https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap', [], null);

    wp_enqueue_style('wecoop-fonts', get_template_directory_uri() . '/assets/css/fonts.css', [], filemtime(get_template_directory() . '/assets/css/fonts.css'));
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], '6.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'wecoop_enqueue_fonts');

function create_member_role() {
    // Controlla se il ruolo esiste già
    if (!get_role('member')) {
        add_role(
            'member', // Nome interno del ruolo
            'Member', // Nome visualizzato
            array(
                'read' => true,                // Permette di leggere i contenuti
                'edit_posts' => false,         // Non permette di modificare post
                'delete_posts' => false,       // Non permette di cancellare post
                // Aggiungi altre capability se vuoi
            )
        );
    }
}
add_action('init', 'create_member_role');


add_action('show_user_profile', 'wecoop_extra_fields');
add_action('edit_user_profile', 'wecoop_extra_fields');

// Includi le funzioni custom
require_once get_template_directory() . '/inc/custom-functions.php';

// Enqueue CSS e JS personalizzati del form "request-delete-user"
function wecoop_enqueue_request_delete_user_assets() {
    wp_enqueue_style('wecoop-request-delete-user', get_template_directory_uri() . '/assets/css/request-delete-user.css');
    wp_enqueue_script('wecoop-request-delete-user', get_template_directory_uri() . '/assets/js/request-delete-user.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'wecoop_enqueue_request_delete_user_assets');

/**
 * Salva la lingua dal parametro URL
 */
function save_language_from_url() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en', 'es'])) {
        $lang = sanitize_text_field($_GET['lang']);
        setcookie('site_lang', $lang, time() + (365 * 24 * 60 * 60), '/', '', false, false);
    } elseif (isset($_GET['set_lang']) && in_array($_GET['set_lang'], ['it', 'en', 'es'])) {
        $lang = sanitize_text_field($_GET['set_lang']);
        setcookie('site_lang', $lang, time() + (365 * 24 * 60 * 60), '/', '', false, false);
        wp_redirect(remove_query_arg('set_lang'));
        exit;
    }
}
add_action('init', 'save_language_from_url');

?>
