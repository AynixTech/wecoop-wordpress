<?php
/**
 * WECOOP Theme - Refactor istituzionale e modulare.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wecoop_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('align-wide');

    register_nav_menus([
        'main-menu' => __('Menu Principale', 'wecoop'),
        'footer-menu' => __('Menu Footer', 'wecoop'),
    ]);
}
add_action('after_setup_theme', 'wecoop_setup');

function wecoop_enqueue_assets() {
    wp_enqueue_style('wecoop-style', get_stylesheet_uri(), [], filemtime(get_stylesheet_directory() . '/style.css'));
    wp_enqueue_style('wecoop-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lora:wght@500;700&family=Source+Sans+3:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('wecoop-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', [], '6.5.2');

    wp_enqueue_style(
        'wecoop-site-refactor',
        get_template_directory_uri() . '/assets/css/site-refactor.css',
        ['wecoop-style', 'wecoop-fontawesome'],
        file_exists(get_template_directory() . '/assets/css/site-refactor.css') ? filemtime(get_template_directory() . '/assets/css/site-refactor.css') : null
    );

    wp_enqueue_script(
        'wecoop-theme-js',
        get_template_directory_uri() . '/assets/js/theme.js',
        [],
        file_exists(get_template_directory() . '/assets/js/theme.js') ? filemtime(get_template_directory() . '/assets/js/theme.js') : null,
        true
    );
}
add_action('wp_enqueue_scripts', 'wecoop_enqueue_assets');

function wecoop_register_partner_cpt() {
    register_post_type('wecoop_partner', [
        'labels' => [
            'name' => __('Partners', 'wecoop'),
            'singular_name' => __('Partner', 'wecoop'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon' => 'dashicons-groups',
        'has_archive' => false,
        'rewrite' => ['slug' => 'partners-list'],
    ]);
}
add_action('init', 'wecoop_register_partner_cpt');

function wecoop_register_block_patterns() {
    if (!function_exists('register_block_pattern_category')) {
        return;
    }

    register_block_pattern_category('wecoop-sections', ['label' => __('WECOOP Sections', 'wecoop')]);

    if (function_exists('register_block_pattern')) {
        register_block_pattern('wecoop/cta-collabora', [
            'title' => __('CTA Collaborate', 'wecoop'),
            'categories' => ['wecoop-sections'],
            'content' => "<!-- wp:group {\"className\":\"wecoop-cta\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group wecoop-cta\"><!-- wp:heading {\"level\":3} --><h3>Build local impact together</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Contact WECOOP to activate new collaborations with institutions, organizations, and businesses.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class=\"wp-block-buttons\"><!-- wp:button {\"className\":\"is-style-fill\"} --><div class=\"wp-block-button is-style-fill\"><a class=\"wp-block-button__link wp-element-button\" href=\"/contact\">Contact us</a></div><!-- /wp:button --><!-- wp:button {\"className\":\"is-style-outline\"} --><div class=\"wp-block-button is-style-outline\"><a class=\"wp-block-button__link wp-element-button\" href=\"/collaborate-with-us\">Collaborate</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div>\n<!-- /wp:group -->",
        ]);
    }
}
add_action('init', 'wecoop_register_block_patterns');

function wecoop_language() {
    $lang = isset($_GET['lang']) ? sanitize_key($_GET['lang']) : (isset($_COOKIE['site_lang']) ? sanitize_key($_COOKIE['site_lang']) : 'it');
    if (!in_array($lang, ['it', 'es', 'en'], true)) {
        $lang = 'it';
    }
    return $lang;
}

function translate_string($key, $default = '') {
    static $cache = [];

    $lang = wecoop_language();
    $lang_file = get_template_directory() . '/languages/' . $lang . '.json';
    $fallback_file = get_template_directory() . '/languages/it.json';

    if (!isset($cache[$lang])) {
        $cache[$lang] = [];
        if (file_exists($lang_file)) {
            $json = file_get_contents($lang_file);
            $decoded = json_decode((string) $json, true);
            if (is_array($decoded)) {
                $cache[$lang] = $decoded;
            }
        }
    }

    if (!isset($cache['it'])) {
        $cache['it'] = [];
        if (file_exists($fallback_file)) {
            $json = file_get_contents($fallback_file);
            $decoded = json_decode((string) $json, true);
            if (is_array($decoded)) {
                $cache['it'] = $decoded;
            }
        }
    }

    if (isset($cache[$lang][$key]) && is_string($cache[$lang][$key])) {
        return $cache[$lang][$key];
    }

    if (isset($cache['it'][$key]) && is_string($cache['it'][$key])) {
        return $cache['it'][$key];
    }

    return $default !== '' ? $default : $key;
}

function wecoop_t($es, $it = '', $en = '') {
    $lang = wecoop_language();

    if ($lang === 'it' && $it !== '') {
        return $it;
    }

    if ($lang === 'en' && $en !== '') {
        return $en;
    }

    return $es;
}

function wecoop_save_language_cookie() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'es', 'en'], true)) {
        setcookie('site_lang', sanitize_key($_GET['lang']), time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    }
}
add_action('init', 'wecoop_save_language_cookie');

function wecoop_whatsapp_shortcode() {
    $phone = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393341390175'));
    $message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Hello WECOOP, I would like to receive more information.'));
    $url = 'https://wa.me/' . $phone . '?text=' . $message;

    return '<a class="wecoop-whatsapp-button" href="' . esc_url($url) . '" target="_blank" rel="noopener">WhatsApp</a>';
}
add_shortcode('wecoop_whatsapp', 'wecoop_whatsapp_shortcode');

function wecoop_newsletter_shortcode() {
    $action = esc_url(admin_url('admin-post.php'));
    $nonce = wp_create_nonce('wecoop_newsletter_nonce');

    return '<form class="wecoop-newsletter-form" action="' . $action . '" method="post">'
        . '<input type="hidden" name="action" value="wecoop_newsletter_submit">'
        . '<input type="hidden" name="wecoop_newsletter_nonce" value="' . esc_attr($nonce) . '">'
        . '<label for="wecoop_newsletter_email">Email</label>'
        . '<input id="wecoop_newsletter_email" type="email" name="email" required placeholder="email@dominio.com">'
        . '<button type="submit">Subscribe</button>'
        . '</form>';
}
add_shortcode('wecoop_newsletter', 'wecoop_newsletter_shortcode');

function wecoop_handle_newsletter() {
    if (!isset($_POST['wecoop_newsletter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wecoop_newsletter_nonce'])), 'wecoop_newsletter_nonce')) {
        wp_die('Nonce non valido');
    }

    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (empty($email) || !is_email($email)) {
        wp_safe_redirect(add_query_arg('newsletter', 'invalid', wp_get_referer()));
        exit;
    }

    wp_mail(get_option('admin_email'), 'Nuova iscrizione newsletter WECOOP', 'Email iscritta: ' . $email);
    wp_safe_redirect(add_query_arg('newsletter', 'ok', wp_get_referer()));
    exit;
}
add_action('admin_post_nopriv_wecoop_newsletter_submit', 'wecoop_handle_newsletter');
add_action('admin_post_wecoop_newsletter_submit', 'wecoop_handle_newsletter');

function wecoop_contact_form_shortcode() {
    $action = esc_url(admin_url('admin-post.php'));
    $nonce = wp_create_nonce('wecoop_contact_nonce');

    return '<form class="wecoop-contact-form" action="' . $action . '" method="post">'
        . '<input type="hidden" name="action" value="wecoop_contact_submit">'
        . '<input type="hidden" name="wecoop_contact_nonce" value="' . esc_attr($nonce) . '">'
        . '<label for="wc_name">Full name</label><input id="wc_name" type="text" name="name" required>'
        . '<label for="wc_email">Email</label><input id="wc_email" type="email" name="email" required>'
        . '<label for="wc_message">Message</label><textarea id="wc_message" name="message" rows="5" required></textarea>'
        . '<button type="submit">Send</button>'
        . '</form>';
}
add_shortcode('wecoop_contact_form', 'wecoop_contact_form_shortcode');

function wecoop_handle_contact_form() {
    if (!isset($_POST['wecoop_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wecoop_contact_nonce'])), 'wecoop_contact_nonce')) {
        wp_die('Nonce non valido');
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    if (empty($name) || !is_email($email) || empty($message)) {
        wp_safe_redirect(add_query_arg('contact', 'invalid', wp_get_referer()));
        exit;
    }

    $body = "Nome: {$name}\nEmail: {$email}\n\nMessaggio:\n{$message}";
    wp_mail(get_option('admin_email'), 'Nuovo contatto dal sito WECOOP', $body);

    wp_safe_redirect(add_query_arg('contact', 'ok', wp_get_referer()));
    exit;
}
add_action('admin_post_nopriv_wecoop_contact_submit', 'wecoop_handle_contact_form');
add_action('admin_post_wecoop_contact_submit', 'wecoop_handle_contact_form');

function wecoop_default_home_content() {
    return '<section class="wecoop-section hero"><h1>WECOOP: access to services, education, and work in network</h1><p>A model that combines physical local presence with a digital platform to generate real opportunities.</p><a class="wecoop-btn" href="/contact">Contact us</a></section>'
        . '<section class="wecoop-section"><h2>The problem</h2><p>Many people struggle to access services, guidance, and opportunities due to language, digital, and network barriers.</p></section>'
        . '<section class="wecoop-section"><h2>The solution: The WECOOP model</h2><p>A local access point + WECOOP App + partner network supporting people, families, and organizations.</p></section>'
        . '<section class="wecoop-section"><h2>How it works: Physical + Digital</h2><ul><li>Listening and guidance in person.</li><li>Digital platform for requests and tracking.</li><li>Active collaboration with local partners.</li></ul></section>'
        . '<section class="wecoop-section"><h2>Intervention areas</h2><p>Services, training, work inclusion, administrative support, and mediation.</p></section>'
        . '<section class="wecoop-section"><h2>PASSAPAROLA Project</h2><p>Community activation project connecting people, institutions, and local resources.</p><a class="wecoop-btn wecoop-btn-outline" href="/passaparola-project">View project</a></section>'
        . '<section class="wecoop-section"><h2>Digital platform - WECOOP App</h2><p>Digital experience to manage requests, documents, payments, and service tracking.</p><a class="wecoop-btn wecoop-btn-outline" href="/wecoop-app">Discover the app</a></section>'
        . '<section class="wecoop-section"><h2>Social impact</h2><p>Measurable results in service access, employability, and community connection.</p><a class="wecoop-btn wecoop-btn-outline" href="/social-impact">View impact</a></section>'
        . '<section class="wecoop-section"><h2>Partners</h2><p>WECOOP works in network with social entities, companies, schools, and institutions.</p><a class="wecoop-btn wecoop-btn-outline" href="/partners">Meet our partners</a></section>'
        . '<section class="wecoop-section wecoop-cta"><h2>Collaborate with WECOOP</h2><p>If you are an institution or an interested individual, we can build a joint project together.</p><p><a class="wecoop-btn" href="/contact">Contact us</a> <a class="wecoop-btn wecoop-btn-outline" href="/collaborate-with-us">Collaborate</a></p></section>';
}

function wecoop_seed_pages_and_menu() {
    $pages = [
        'home' => ['title' => 'Home', 'content' => wecoop_default_home_content()],
        'wecoop-model' => ['title' => 'The WECOOP Model', 'content' => '<h1>The WECOOP Model</h1><p>Territorial + digital model, scalable and impact-oriented.</p>'],
        'passaparola-project' => ['title' => 'PASSAPAROLA Project', 'content' => '<h1>PASSAPAROLA Project</h1><p>Community activation and network-based guidance program.</p>'],
        'wecoop-app' => ['title' => 'WECOOP App', 'content' => '<h1>WECOOP App</h1><p>Digital platform for services, tracking, and future integrations.</p>'],
        'social-impact' => ['title' => 'Social Impact', 'content' => '<h1>Social Impact</h1><p>Indicators, outcomes, and continuous improvement.</p>'],
        'partners' => ['title' => 'Partners', 'content' => '<h1>Partners</h1><p>Network of WECOOP allied organizations.</p>'],
        'collaborate-with-us' => ['title' => 'Collaborate with Us', 'content' => '<h1>Collaborate with Us</h1><p>Space for organizations, institutions, and professionals who want to contribute.</p>'],
        'contact' => ['title' => 'Contact', 'content' => '<h1>Contact</h1><p>Write to us and we will reply as soon as possible.</p>[wecoop_contact_form]'],
        'news' => ['title' => 'News', 'content' => '<h1>News</h1><p>Institutional updates and blog content.</p>'],
    ];

    $created = [];

    foreach ($pages as $slug => $config) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            $created[$slug] = (int) $existing->ID;
            continue;
        }

        $created[$slug] = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $config['title'],
            'post_name' => $slug,
            'post_content' => $config['content'],
        ]);
    }

    if (!empty($created['home']) && !empty($created['news'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $created['home']);
        update_option('page_for_posts', (int) $created['news']);
    }

    $menu_name = 'Menu Principal WECOOP';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu($menu_name);

    if ($menu_id && is_array($created)) {
        $order = ['home', 'wecoop-model', 'passaparola-project', 'wecoop-app', 'social-impact', 'partners', 'collaborate-with-us', 'contact'];
        foreach ($order as $slug) {
            if (empty($created[$slug])) {
                continue;
            }

            $already = false;
            $items = wp_get_nav_menu_items($menu_id);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ((int) $item->object_id === (int) $created[$slug]) {
                        $already = true;
                        break;
                    }
                }
            }

            if (!$already) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => $pages[$slug]['title'],
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => (int) $created[$slug],
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish',
                ]);
            }
        }

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['main-menu'] = $menu_id;
        $locations['footer-menu'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}
add_action('after_switch_theme', 'wecoop_seed_pages_and_menu');

function wecoop_run_seed_once() {
    if (get_option('wecoop_refactor_seeded') === '1') {
        return;
    }

    wecoop_seed_pages_and_menu();
    update_option('wecoop_refactor_seeded', '1');
}
add_action('admin_init', 'wecoop_run_seed_once');

function wecoop_migrate_legacy_slugs() {
    $map = [
        'modelo-wecoop' => 'wecoop-model',
        'proyecto-passaparola' => 'passaparola-project',
        'app-wecoop' => 'wecoop-app',
        'impacto-social' => 'social-impact',
        'colabora-con-nosotros' => 'collaborate-with-us',
        'contactos' => 'contact',
        'noticias' => 'news',
    ];

    foreach ($map as $old_slug => $new_slug) {
        $old_page = get_page_by_path($old_slug);
        $new_page = get_page_by_path($new_slug);

        if ($old_page instanceof WP_Post && !($new_page instanceof WP_Post)) {
            wp_update_post([
                'ID' => (int) $old_page->ID,
                'post_name' => $new_slug,
            ]);
        }
    }
}
add_action('admin_init', 'wecoop_migrate_legacy_slugs');

function wecoop_redirect_legacy_slugs() {
    if (is_admin()) {
        return;
    }

    $requested_path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $redirect_map = [
        'modelo-wecoop' => 'wecoop-model',
        'proyecto-passaparola' => 'passaparola-project',
        'app-wecoop' => 'wecoop-app',
        'impacto-social' => 'social-impact',
        'colabora-con-nosotros' => 'collaborate-with-us',
        'contactos' => 'contact',
        'noticias' => 'news',
    ];

    if (isset($redirect_map[$requested_path])) {
        wp_safe_redirect(home_url('/' . $redirect_map[$requested_path] . '/'), 301);
        exit;
    }
}
add_action('template_redirect', 'wecoop_redirect_legacy_slugs');

function wecoop_refactor_admin_notice() {
    if (!is_admin() || !current_user_can('activate_plugins')) {
        return;
    }

    $missing = [];

    if (!function_exists('pll_the_languages') && !defined('ICL_SITEPRESS_VERSION')) {
        $missing[] = 'Multilingua: Polylang o WPML';
    }
    if (!defined('WPSEO_VERSION') && !defined('RANK_MATH_VERSION')) {
        $missing[] = 'SEO: Yoast SEO o Rank Math';
    }
    if (!function_exists('wpforms') && !class_exists('WPCF7')) {
        $missing[] = 'Form: WPForms o Contact Form 7';
    }
    if (!class_exists('UpdraftPlus')) {
        $missing[] = 'Backup: UpdraftPlus (o equivalente)';
    }

    if (empty($missing)) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>WECOOP Theme Refactor:</strong> Plugin consigliati per completare i requisiti: ' . esc_html(implode(' | ', $missing)) . '</p></div>';
}
add_action('admin_notices', 'wecoop_refactor_admin_notice');

function wecoop_theme_security_headers() {
    if (is_admin()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
add_action('send_headers', 'wecoop_theme_security_headers');

function wecoop_ws_page_shell_start($aria_label = '') {
    $tr = static function($key, $default = '') {
        return translate_string($key, $default);
    };

    $current_lang = wecoop_language();
    $lang_base_url = remove_query_arg('lang');
    ?>
    <style>
        body.page .wecoop-header,
        body.page .wecoop-footer {
            display: none !important;
        }

        body.page .wecoop-site-content {
            min-height: 0;
        }
    </style>

    <main class="ws-site" aria-label="<?php echo esc_attr($aria_label !== '' ? $aria_label : $tr('page.aria.default', 'WECOOP page')); ?>">
        <nav class="ws-nav">
            <div class="ws-container ws-nav__inner">
                <a class="ws-brand" href="<?php echo esc_url(home_url('/')); ?>#inicio">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                </a>
                <div class="ws-links" aria-label="<?php echo esc_attr($tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>#que-es"><?php echo esc_html($tr('frontpage.nav.about', 'Cos\'e WECOOP')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#servizi"><?php echo esc_html($tr('frontpage.nav.services', 'Servizi')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#come-funziona"><?php echo esc_html($tr('frontpage.nav.how', 'Come funziona')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#passaparola"><?php echo esc_html($tr('frontpage.nav.passaparola', 'Passaparola')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#plataforma"><?php echo esc_html($tr('frontpage.nav.platform', 'Piattaforma Digitale')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#impacto"><?php echo esc_html($tr('frontpage.nav.impact', 'Impatto')); ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>#contacto"><?php echo esc_html($tr('frontpage.nav.contact', 'Contatti')); ?></a>
                </div>
                <div class="ws-lang-switcher" aria-label="Language switcher">
                    <a class="<?php echo $current_lang === 'it' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'it', $lang_base_url)); ?>">IT</a>
                    <a class="<?php echo $current_lang === 'en' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'en', $lang_base_url)); ?>">EN</a>
                    <a class="<?php echo $current_lang === 'es' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'es', $lang_base_url)); ?>">ES</a>
                </div>
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.nav.cta', 'Collabora')); ?></a>
            </div>
        </nav>
    <?php
}

function wecoop_ws_page_shell_end() {
    $tr = static function($key, $default = '') {
        return translate_string($key, $default);
    };
    ?>
        <footer class="ws-footer">
            <div class="ws-container">
                <div class="ws-grid-4">
                    <div>
                        <div class="ws-footer-brand">
                            <img class="ws-footer-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                            <span>WECOOP</span>
                        </div>
                        <p><?php echo esc_html($tr('frontpage.footer.description', 'Un ecosistema de inclusion y oportunidades para todos.')); ?></p>
                    </div>
                    <div>
                        <h4><?php echo esc_html($tr('frontpage.footer.col1_title', 'WECOOP')); ?></h4>
                        <a href="<?php echo esc_url(home_url('/')); ?>#que-es"><?php echo esc_html($tr('frontpage.footer.col1_link1', 'Cos\'e WECOOP')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#servizi"><?php echo esc_html($tr('frontpage.footer.col1_link2', 'Servizi')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#come-funziona"><?php echo esc_html($tr('frontpage.footer.col1_link3', 'Come funziona')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#passaparola"><?php echo esc_html($tr('frontpage.footer.col1_link2', 'Passaparola')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#plataforma"><?php echo esc_html($tr('frontpage.footer.col1_link4', 'Piattaforma Digitale')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#impacto"><?php echo esc_html($tr('frontpage.footer.col1_link5', 'Impatto')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#contacto"><?php echo esc_html($tr('frontpage.footer.col1_link6', 'Contatti')); ?></a>
                    </div>
                    <div>
                        <h4><?php echo esc_html($tr('frontpage.footer.col2_title', 'Colabora')); ?></h4>
                        <a href="<?php echo esc_url(home_url('/')); ?>#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link1', 'Empresas')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link2', 'Instituciones')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link3', 'Fundaciones')); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link4', 'Voluntarios')); ?></a>
                    </div>
                    <div>
                        <h4><?php echo esc_html($tr('frontpage.footer.col3_title', 'Contacto')); ?></h4>
                        <span><?php echo esc_html($tr('frontpage.contact.value_address', 'Via Populonia 8, Milano, Italia')); ?></span>
                        <span><?php echo esc_html($tr('frontpage.contact.value_email', 'info@wecoop.org')); ?></span>
                        <span><?php echo esc_html($tr('frontpage.contact.value_phone', '+39 351 511 2113')); ?></span>
                    </div>
                </div>
                <div class="ws-footer-bottom">
                    <p><?php echo esc_html($tr('frontpage.footer.rights', '© 2026 WECOOP. Todos los derechos reservados.')); ?></p>
                    <div class="ws-footer-brands">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.footer.brand1_alt', 'Passaparola')); ?>">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.footer.brand2_alt', 'APP WECOOP')); ?>">
                    </div>
                </div>
            </div>
        </footer>
    </main>
    <?php
}

if (file_exists(get_template_directory() . '/inc/custom-functions.php')) {
    require_once get_template_directory() . '/inc/custom-functions.php';
}
