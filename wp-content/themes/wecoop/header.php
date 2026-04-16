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

$section_url = static function ($anchor) use ($is_front) {
    return $is_front ? '#' . $anchor : home_url('/#' . $anchor);
};
?>

<header class="ws-header">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="WECOOP Home">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP" class="ws-brand__logo">
            </a>

            <button class="ws-menu-toggle" type="button" aria-expanded="false" aria-controls="ws-main-nav">
                <span></span><span></span><span></span>
            </button>

            <div id="ws-main-nav" class="ws-links" aria-label="<?php echo esc_attr(wecoop_t('Main navigation', 'Navigazione principale')); ?>">
                <a href="<?php echo esc_url(home_url('/#que-es')); ?>"><?php echo esc_html(wecoop_t('Cos\'e WECOOP', 'Cos\'e WECOOP')); ?></a>
                <a href="<?php echo esc_url(home_url('/#servizi')); ?>"><?php echo esc_html(wecoop_t('Servizi', 'Servizi')); ?></a>
                <a href="<?php echo esc_url(home_url('/#come-funziona')); ?>"><?php echo esc_html(wecoop_t('Come funziona', 'Come funziona')); ?></a>
                <a href="<?php echo esc_url(home_url('/annunci-lavoro-wecoop/')); ?>"><?php echo esc_html(wecoop_t('Offerte di lavoro', 'Offerte di lavoro')); ?></a>
                <a href="<?php echo esc_url(home_url('/#passaparola')); ?>"><?php echo esc_html(wecoop_t('Passaparola', 'Passaparola')); ?></a>
                <a href="<?php echo esc_url(home_url('/#plataforma')); ?>"><?php echo esc_html(wecoop_t('Piattaforma Digitale', 'Piattaforma Digitale')); ?></a>
                <a href="<?php echo esc_url(home_url('/#impacto')); ?>"><?php echo esc_html(wecoop_t('Impatto', 'Impatto')); ?></a>
                <a href="<?php echo esc_url(home_url('/#contacto')); ?>"><?php echo esc_html(wecoop_t('Contatti', 'Contatti')); ?></a>
            </div>

            <button class="ws-lang-trigger" id="ws-lang-trigger" aria-haspopup="dialog" aria-label="Select language">
                <span class="ws-lang-trigger__flag"><?php echo esc_html(strtoupper($current_lang)); ?></span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>

            <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html(wecoop_t('Colabora', 'Collabora')); ?></a>
        </div>
    </nav>
</header>

<div id="content" class="ws-site-content">
