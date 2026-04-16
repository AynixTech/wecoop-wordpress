<?php
/**
 * Template Name: Impatto Sociale
 */
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('impact.aria.page', 'Il nostro impatto sociale – WECOOP'));
?>

    <!-- HERO -->
    <section class="cw-hero" id="impatto-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('impact.hero.eyebrow', 'Impatto sociale')); ?></span>
                    <h1><?php echo esc_html($tr('impact.hero.title', 'Il nostro impatto sociale')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('impact.hero.subtitle', 'Trasformiamo bisogni reali in percorsi concreti di autonomia.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            <?php echo esc_html($tr('impact.hero.cta1', 'Collabora con noi')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <?php echo esc_html($tr('impact.hero.cta2', 'Contattaci')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        <?php echo esc_html($tr('impact.hero.microcopy', 'Ogni numero rappresenta una persona accompagnata nel suo percorso.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-users"></i><span>400+ <?php echo esc_html($tr('impact.hero.badge1', 'persone')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-graduation-cap"></i><span>150+ <?php echo esc_html($tr('impact.hero.badge2', 'formazioni')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-briefcase"></i><span>80+ <?php echo esc_html($tr('impact.hero.badge3', 'lavori')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-mobile-screen-button"></i><span>300+ <?php echo esc_html($tr('impact.hero.badge4', 'utenti')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTRODUZIONE -->
    <section class="ws-section cw-institutional" id="impatto-intro">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('impact.intro.eyebrow', 'Chi siamo')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('impact.intro.title', 'Un impatto che parte dalle persone')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('impact.intro.p1', 'WECOOP nasce per facilitare l\'accesso ai servizi, al lavoro e alle opportunità per persone in situazione di vulnerabilità.')); ?></p>
                    <p><?php echo esc_html($tr('impact.intro.p2', 'Ogni intervento è costruito a partire dai bisogni reali delle persone, accompagnandole in un percorso di autonomia e inclusione.')); ?></p>
                    <p><?php echo esc_html($tr('impact.intro.p3', 'Dietro ogni numero c\'è una storia, un percorso e un cambiamento concreto.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- I NUMERI -->
    <section class="ws-section cw-impact" id="impatto-numbers">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('impact.numbers.eyebrow', 'I dati')); ?></span>
                <h2><?php echo esc_html($tr('impact.numbers.title', 'I risultati')); ?></h2>
            </div>
            <div class="cw-stats-grid">
                <div class="cw-stat cw-stat--blue">
                    <div class="cw-stat__icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val">400+</strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('impact.numbers.stat1', 'Persone supportate')); ?></span>
                </div>
                <div class="cw-stat cw-stat--green">
                    <div class="cw-stat__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val">150+</strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('impact.numbers.stat2', 'Percorsi di formazione attivati')); ?></span>
                </div>
                <div class="cw-stat cw-stat--pink">
                    <div class="cw-stat__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val">80+</strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('impact.numbers.stat3', 'Inserimenti lavorativi')); ?></span>
                </div>
                <div class="cw-stat cw-stat--yellow">
                    <div class="cw-stat__icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val">300+</strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('impact.numbers.stat4', 'Utenti attivi sulla piattaforma')); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- OLTRE I NUMERI -->
    <section class="ws-section ws-section--soft" id="impatto-beyond">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('impact.beyond.eyebrow', 'Il significato')); ?></span>
                <h2><?php echo esc_html($tr('impact.beyond.title', 'Oltre i numeri')); ?></h2>
            </div>
            <div class="cw-scard-grid">
                <article class="cw-scard cw-scard--blue">
                    <div class="cw-scard__icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('impact.beyond.block1.title', 'Persone supportate')); ?></h3>
                        <p><?php echo esc_html($tr('impact.beyond.block1.body', 'Accesso ai servizi, orientamento e accompagnamento personalizzato.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--green">
                    <div class="cw-scard__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('impact.beyond.block2.title', 'Formazione')); ?></h3>
                        <p><?php echo esc_html($tr('impact.beyond.block2.body', 'Sviluppo di competenze per migliorare l\'autonomia e le opportunità.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--pink">
                    <div class="cw-scard__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('impact.beyond.block3.title', 'Inserimenti lavorativi')); ?></h3>
                        <p><?php echo esc_html($tr('impact.beyond.block3.body', 'Accesso concreto al lavoro e integrazione nel contesto sociale.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--lime">
                    <div class="cw-scard__icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('impact.beyond.block4.title', 'Piattaforma digitale')); ?></h3>
                        <p><?php echo esc_html($tr('impact.beyond.block4.body', 'Maggiore autonomia nella gestione dei servizi e del proprio percorso.')); ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- COSA CAMBIA -->
    <section class="ws-section" id="impatto-change">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('impact.change.eyebrow', 'Il cambiamento')); ?></span>
                <h2><?php echo esc_html($tr('impact.change.title', 'Cosa cambia concretamente')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-person-walking-arrow-right"></i></span>
                    <h3><?php echo esc_html($tr('impact.change.item1', 'Maggiore autonomia personale')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-scale-balanced"></i></span>
                    <h3><?php echo esc_html($tr('impact.change.item2', 'Accesso ai diritti e ai servizi')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
                    <h3><?php echo esc_html($tr('impact.change.item3', 'Integrazione sociale')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span>
                    <h3><?php echo esc_html($tr('impact.change.item4', 'Accesso al lavoro')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-heart-pulse"></i></span>
                    <h3><?php echo esc_html($tr('impact.change.item5', 'Miglioramento della qualità della vita')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- MODELLO DI IMPATTO -->
    <section class="ws-section cw-institutional ws-section--soft" id="impatto-model">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('impact.model.eyebrow', 'Il modello')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('impact.model.title', 'Come generiamo impatto')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('impact.model.p1', 'WECOOP genera impatto attraverso un modello integrato che combina:')); ?></p>
                    <ul style="margin:12px 0 16px; padding-left:1.4em; line-height:1.9;">
                        <li><?php echo esc_html($tr('impact.model.item1', 'Servizi sul territorio')); ?></li>
                        <li><?php echo esc_html($tr('impact.model.item2', 'Orientamento e accompagnamento')); ?></li>
                        <li><?php echo esc_html($tr('impact.model.item3', 'Formazione')); ?></li>
                        <li><?php echo esc_html($tr('impact.model.item4', 'Piattaforma digitale')); ?></li>
                    </ul>
                    <p><?php echo esc_html($tr('impact.model.p2', 'Questo approccio permette di costruire percorsi completi, continui e sostenibili nel tempo.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- RETE -->
    <section class="ws-section" id="impatto-network">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('impact.network.eyebrow', 'Collaborazioni')); ?></span>
                <h2><?php echo esc_html($tr('impact.network.title', 'Un lavoro di rete')); ?></h2>
            </div>
            <p class="cw-text-lg"><?php echo esc_html($tr('impact.network.p1', 'L\'impatto di WECOOP è possibile grazie alla collaborazione con partner pubblici e privati.')); ?></p>
            <div class="ws-grid-3" style="margin-top:32px;">
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-landmark"></i></span>
                    <h3><?php echo esc_html($tr('impact.network.item1', 'Istituzioni')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-people-group"></i></span>
                    <h3><?php echo esc_html($tr('impact.network.item2', 'Associazioni')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-building"></i></span>
                    <h3><?php echo esc_html($tr('impact.network.item3', 'Imprese')); ?></h3>
                </article>
                <article class="ws-feature-item">
                    <span class="ws-feature-item__icon" aria-hidden="true"><i class="fa-solid fa-microchip"></i></span>
                    <h3><?php echo esc_html($tr('impact.network.item4', 'Partner tecnologici')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="ws-section ws-section--cta" id="impatto-cta" style="text-align:center;">
        <div class="ws-container">
            <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('impact.cta.eyebrow', 'Partecipa')); ?></span>
            <h2><?php echo esc_html($tr('impact.cta.title', 'Vuoi contribuire al nostro impatto?')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('impact.cta.body', 'Collabora con noi per sviluppare nuove opportunità e ampliare l\'impatto sul territorio.')); ?></p>
            <div class="ws-hero-ctas">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    <?php echo esc_html($tr('impact.cta.btn1', 'Collabora con noi')); ?>
                </a>
                <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                    <?php echo esc_html($tr('impact.cta.btn2', 'Contattaci')); ?>
                </a>
            </div>
            <p class="ws-microcopy ws-microcopy--light">
                <i class="fa-solid fa-seedling" aria-hidden="true"></i>
                <?php echo esc_html($tr('impact.cta.microcopy', 'Costruiamo insieme nuove opportunità.')); ?>
            </p>
        </div>
    </section>

<?php wecoop_ws_page_shell_end(); get_footer();
