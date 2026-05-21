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
$current_lang = wecoop_language();
$is_front = is_front_page();
$_tr = 'translate_string';
?>

<header class="ws-header">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="WECOOP Home">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP" class="ws-brand__logo">
            </a>

            <div id="ws-main-nav" class="ws-links" aria-label="<?php echo esc_attr($_tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                <a href="<?php echo esc_url(home_url('/#que-es')); ?>"><?php echo esc_html($_tr('frontpage.nav.about', "Cos'e WECOOP")); ?></a>
                <a href="<?php echo esc_url(home_url('/#servizi')); ?>"><?php echo esc_html($_tr('frontpage.nav.services', 'Servizi')); ?></a>
                <a href="<?php echo esc_url(home_url('/#come-funziona')); ?>"><?php echo esc_html($_tr('frontpage.nav.how', 'Come funziona')); ?></a>
                <a href="<?php echo esc_url(home_url('/annunci-lavoro-wecoop/')); ?>"><?php echo esc_html($_tr('frontpage.nav.jobs', 'Offerte di lavoro')); ?></a>
                <a href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>"><?php echo esc_html($_tr('frontpage.nav.donations', 'Sostieni WECOOP')); ?></a>
                <a href="<?php echo esc_url(home_url('/#contacto')); ?>"><?php echo esc_html($_tr('frontpage.nav.contact', 'Contatti')); ?></a>
            </div>

            <div class="ws-nav__actions">
                <button class="ws-lang-trigger" id="ws-lang-trigger" aria-haspopup="dialog" aria-label="Select language">
                    <span class="ws-lang-trigger__flag"><?php echo esc_html(strtoupper($current_lang)); ?></span>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                <button class="ws-menu-toggle" id="ws-nav-toggle" type="button" aria-expanded="false" aria-controls="ws-main-nav">
                    <span></span><span></span><span></span>
                </button>
            </div>

            <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>"><?php echo esc_html($_tr('frontpage.nav.cta', 'Sostieni WECOOP')); ?></a>
        </div>
    </nav>
</header>

<div id="content" class="ws-site-content">
