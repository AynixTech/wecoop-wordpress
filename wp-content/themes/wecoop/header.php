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
            <ul class="wecoop-nav__list">
                <li><a href="<?php echo esc_url($section_url('inicio')); ?>">Inicio</a></li>
                <li><a href="<?php echo esc_url($section_url('que-es')); ?>">Que es WECOOP</a></li>
                <li><a href="<?php echo esc_url($section_url('servizi')); ?>">Servizi</a></li>
                <li><a href="<?php echo esc_url(home_url('/annunci-lavoro-wecoop/')); ?>">Offerte di lavoro</a></li>
                <li><a href="<?php echo esc_url($section_url('passaparola')); ?>">Passaparola</a></li>
                <li><a href="<?php echo esc_url($section_url('plataforma')); ?>">Plataforma Digital</a></li>
                <li><a href="<?php echo esc_url($section_url('impacto')); ?>">Impacto</a></li>
                <li><a href="<?php echo esc_url($section_url('contacto')); ?>">Contacto</a></li>
            </ul>
        </nav>

        <div class="wecoop-header__actions">
            <a class="wecoop-lang <?php echo $current_lang === 'en' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'en', $current_url_no_lang)); ?>" aria-label="Switch to English">EN</a>
            <a class="wecoop-lang <?php echo $current_lang === 'it' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'it', $current_url_no_lang)); ?>" aria-label="Passa a Italiano">IT</a>
            <a class="wecoop-lang <?php echo $current_lang === 'es' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'es', $current_url_no_lang)); ?>" aria-label="Cambiar a Español">ES</a>
            <a class="wecoop-contact-btn" href="<?php echo esc_url($section_url('contacto')); ?>"><?php echo esc_html(wecoop_t('Contactar', 'Contatti')); ?></a>
        </div>
    </div>
</header>

<div id="content" class="wecoop-site-content">
