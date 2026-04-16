<?php
/**
 * Template Name: Piattaforma Digitale
 */
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('platform.aria.page', 'Piattaforma Digitale WECOOP'));
?>

    <!-- HERO -->
    <section class="cw-hero" id="piattaforma-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('platform.hero.eyebrow', 'Piattaforma digitale')); ?></span>
                    <h1><?php echo esc_html($tr('platform.hero.title', 'Piattaforma Digitale WECOOP')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('platform.hero.subtitle', 'Accedi ai servizi, prenota appuntamenti e segui il tuo percorso in modo semplice e guidato.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/wecoop-app/')); ?>">
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            <?php echo esc_html($tr('platform.hero.cta1', 'Registrati alla piattaforma')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <?php echo esc_html($tr('platform.hero.cta2', 'Contattaci')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('platform.hero.microcopy', 'Gestisci tutto in un unico spazio, in modo semplice e veloce.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-mobile-screen-button"></i><span><?php echo esc_html($tr('platform.hero.badge1', 'App mobile')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-calendar-check"></i><span><?php echo esc_html($tr('platform.hero.badge2', 'Appuntamenti')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-file-lines"></i><span><?php echo esc_html($tr('platform.hero.badge3', 'Documenti')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-users"></i><span>300+ <?php echo esc_html($tr('platform.hero.badge4', 'utenti')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- COS'È LA PIATTAFORMA -->
    <section class="ws-section cw-institutional" id="piattaforma-intro">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('platform.intro.eyebrow', 'Il servizio')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('platform.intro.title', 'Un accesso semplice ai servizi')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('platform.intro.p1', 'La piattaforma digitale WECOOP permette di accedere ai servizi, gestire appuntamenti e seguire il proprio percorso in modo organizzato e continuo.')); ?></p>
                    <p><?php echo esc_html($tr('platform.intro.p2', 'È progettata per rendere più semplice l\'accesso ai servizi, ridurre i tempi e migliorare la comunicazione con gli operatori.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- COSA PUOI FARE -->
    <section class="ws-section ws-section--soft" id="piattaforma-features">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('platform.features.eyebrow', 'Funzionalità')); ?></span>
                <h2><?php echo esc_html($tr('platform.features.title', 'Cosa puoi fare con la piattaforma')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-user-circle"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item1', 'Crea il tuo profilo personale')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item2', 'Prenota appuntamenti con gli operatori')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-list-check"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item3', 'Accedi ai servizi WECOOP')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-file-arrow-up"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item4', 'Carica documenti in modo semplice')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item5', 'Segui il tuo percorso passo dopo passo')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-comments"></i></span>
                    <h3><?php echo esc_html($tr('platform.features.item6', 'Comunica direttamente con il team')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- SISTEMA INTEGRATO -->
    <section class="ws-section cw-institutional" id="piattaforma-system">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('platform.system.eyebrow', 'Il modello')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('platform.system.title', 'Un sistema integrato tra fisico e digitale')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('platform.system.p1', 'La piattaforma digitale WECOOP è integrata con lo sportello fisico e i servizi sul territorio.')); ?></p>
                    <p><?php echo esc_html($tr('platform.system.p2', 'Questo permette di costruire un percorso continuo tra online e presenza, senza interruzioni e con supporto costante.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- PERCHÉ USARLA -->
    <section class="ws-section ws-section--soft" id="piattaforma-why">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('platform.why.eyebrow', 'I vantaggi')); ?></span>
                <h2><?php echo esc_html($tr('platform.why.title', 'Perché utilizzare la piattaforma')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-layer-group"></i></span>
                    <h3><?php echo esc_html($tr('platform.why.item1', 'Tutto in un unico posto')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-bolt"></i></span>
                    <h3><?php echo esc_html($tr('platform.why.item2', 'Meno tempo e meno complicazioni')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-unlock"></i></span>
                    <h3><?php echo esc_html($tr('platform.why.item3', 'Accesso semplice ai servizi')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                    <h3><?php echo esc_html($tr('platform.why.item4', 'Supporto continuo')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-person-walking-arrow-right"></i></span>
                    <h3><?php echo esc_html($tr('platform.why.item5', 'Maggiore autonomia nella gestione del percorso')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- A CHI È RIVOLTA -->
    <section class="ws-section" id="piattaforma-target">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('platform.target.eyebrow', 'Per chi')); ?></span>
                <h2><?php echo esc_html($tr('platform.target.title', 'Per chi è la piattaforma')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
                    <h3><?php echo esc_html($tr('platform.target.item1', 'Utenti WECOOP')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-laptop-mobile"></i></span>
                    <h3><?php echo esc_html($tr('platform.target.item2', 'Persone che vogliono gestire servizi online')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></span>
                    <h3><?php echo esc_html($tr('platform.target.item3', 'Chi ha bisogno di supporto guidato')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
                    <h3><?php echo esc_html($tr('platform.target.item4', 'Chi vuole seguire il proprio percorso in modo semplice')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="ws-section ws-section--cta" id="piattaforma-cta" style="text-align:center;">
        <div class="ws-container">
            <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('platform.cta.eyebrow', 'Inizia ora')); ?></span>
            <h2><?php echo esc_html($tr('platform.cta.title', 'Accedi alla piattaforma')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('platform.cta.body', 'Registrati e inizia a gestire il tuo percorso con WECOOP.')); ?></p>
            <div class="ws-hero-ctas">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/wecoop-app/')); ?>">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    <?php echo esc_html($tr('platform.cta.btn1', 'Registrati alla piattaforma')); ?>
                </a>
                <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                    <?php echo esc_html($tr('platform.cta.btn2', 'Contattaci')); ?>
                </a>
            </div>
            <p class="ws-microcopy ws-microcopy--light">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                <?php echo esc_html($tr('platform.cta.microcopy', 'Ti guidiamo passo dopo passo.')); ?>
            </p>
            <div class="ws-store-badges" style="margin-top:20px; display:flex; gap:12px; align-items:center; justify-content:center; flex-wrap:wrap;">
                <a href="https://play.google.com/store/apps/details?id=com.wecoop.app" target="_blank" rel="noopener noreferrer" aria-label="Scarica su Google Play">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badges/playstore.svg'); ?>" alt="Google Play" style="height:44px;">
                </a>
                <a href="https://apps.apple.com/app/wecoop/id0000000000" target="_blank" rel="noopener noreferrer" aria-label="Scarica su App Store">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badges/appstpre.png'); ?>" alt="App Store" style="height:44px;">
                </a>
            </div>
        </div>
    </section>

<?php wecoop_ws_page_shell_end(); get_footer();
