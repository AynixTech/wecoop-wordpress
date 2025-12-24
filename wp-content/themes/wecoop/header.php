<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo theme_translate('meta.title'); ?></title>

    <meta name="description" content="<?php echo theme_translate('meta.description'); ?>">
    <meta property="og:title" content="<?php echo theme_translate('meta.title'); ?>" />
    <meta property="og:description" content="<?php echo theme_translate('meta.description'); ?>" />
    <meta property="og:image" content="https://www.wecoop.org/wp-content/uploads/2025/05/wecooplogo2.png" />
    <meta property="og:url" content="https://www.wecoop.org" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="WeCoop" />

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="top-menu">
    <ul class="top-menu__list">
        <li><a href="<?php echo esc_url(home_url('/chi-siamo')); ?>"><?php echo theme_translate('nav.about'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/diventa-volontario')); ?>"><?php echo theme_translate('nav.volunteer'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/hai-un-idea')); ?>"><?php echo theme_translate('nav.idea'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/sostienici')); ?>"><?php echo theme_translate('nav.support'); ?></a></li>
    </ul>
</div>

<header class="header">
   <nav class="header__nav">
    <ul class="nav-menu">
        <li><a href="<?php echo home_url(); ?>"><?php echo theme_translate('nav.home'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/servizi')); ?>"><?php echo theme_translate('nav.services'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/progetti')); ?>"><?php echo theme_translate('nav.projects'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/iniziative')); ?>"><?php echo theme_translate('nav.initiatives'); ?></a></li>
    </ul>
</nav>
<div class="header__logo">
    <a href="<?php echo esc_url(home_url()); ?>">
        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/wecooplogo2.png')); ?>" alt="WeCoop logo" />
    </a>
</div>

<div class="header__contact">
    <a href="<?php echo esc_url(home_url('/contatti')); ?>" class="contact-button">
         <button class="btn-primary"><?php echo theme_translate('nav.contact'); ?></button>
    </a>
</div>
<div class="hamburger" onclick="toggleMenu()">
    <div class="line"></div>
    <div class="line"></div>
    <div class="line"></div>
</div>
</header>

<div id="modal-menu" class="modal-menu">
    <div class="menu-content">
        <div class="menu-close-wrapper">
            <button class="menu-close" onclick="toggleMenu()">✖</button>
        </div>
        <nav>
            <ul class="mobile-nav-menu">
                <li><a href="<?php echo home_url(); ?>"><?php echo theme_translate('nav.home'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/servizi')); ?>"><?php echo theme_translate('nav.services'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/progetti')); ?>"><?php echo theme_translate('nav.projects'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/iniziative')); ?>"><?php echo theme_translate('nav.initiatives'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/contatti')); ?>"><?php echo theme_translate('nav.contact'); ?></a></li>
            </ul>
        </nav>
    </div>
</div>

<script>
    function toggleMenu() {
        const modal = document.getElementById('modal-menu');
        modal.classList.toggle('active');
    }
</script>
</body>
</html>
