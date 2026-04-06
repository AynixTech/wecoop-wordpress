<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$current_request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
$current_url_no_lang = remove_query_arg('lang', home_url($current_request_uri));
?>

<header class="wecoop-header">
    <div class="wecoop-header__inner">
        <a class="wecoop-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="WECOOP Home">
            <span class="wecoop-brand__name">WECOOP</span>
            <span class="wecoop-brand__tagline"><?php echo esc_html(wecoop_t('Fisico + Digital', 'Fisico + Digitale')); ?></span>
        </a>

        <button class="wecoop-menu-toggle" type="button" aria-expanded="false" aria-controls="wecoop-main-nav">
            <span></span><span></span><span></span>
        </button>

        <nav id="wecoop-main-nav" class="wecoop-nav" aria-label="Main Navigation">
            <?php
            wp_nav_menu([
                'theme_location' => 'main-menu',
                'container' => false,
                'menu_class' => 'wecoop-nav__list',
                'fallback_cb' => false,
            ]);
            ?>
        </nav>

        <div class="wecoop-header__actions">
            <a class="wecoop-lang" href="<?php echo esc_url(add_query_arg('lang', 'es', $current_url_no_lang)); ?>">ES</a>
            <a class="wecoop-lang" href="<?php echo esc_url(add_query_arg('lang', 'it', $current_url_no_lang)); ?>">IT</a>
            <a class="wecoop-contact-btn" href="<?php echo esc_url(home_url('/contact')); ?>"><?php echo esc_html(wecoop_t('Contactar', 'Contatti')); ?></a>
        </div>
    </div>
</header>

<div id="content" class="wecoop-site-content">
