<?php
/**
 * Template Name: Passaparola
 */
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('passaparola.aria.page', 'Passaparola – il progetto WECOOP sul territorio'));
$wa_num = esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113')));
$wa_msg = esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei sapere di più sul progetto Passaparola.')));
?>

    <!-- HERO -->
    <section class="cw-hero" id="passaparola-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <img class="ws-logo-app ws-logo-app--on-dark" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('passaparola.hero.eyebrow', 'Progetto territoriale')); ?></span>
                    <h1><?php echo esc_html($tr('passaparola.hero.title', 'Passaparola – il progetto WECOOP sul territorio')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('passaparola.hero.subtitle', 'Il progetto territoriale di WECOOP che connette persone, servizi e opportunità.')); ?></p>
                    <p class="cw-hero__strategic"><?php echo esc_html($tr('passaparola.hero.strategic', 'Passaparola è il punto di accesso fisico al modello WECOOP sul territorio.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <?php echo esc_html($tr('passaparola.hero.cta1', 'Contattaci')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>">
                            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                            <?php echo esc_html($tr('passaparola.hero.cta2', 'Collabora con noi')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('passaparola.hero.microcopy', 'Ti rispondiamo rapidamente.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-location-dot"></i><span><?php echo esc_html($tr('passaparola.hero.badge1', 'Territorio')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-users"></i><span><?php echo esc_html($tr('passaparola.hero.badge2', 'Inclusione')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-briefcase"></i><span><?php echo esc_html($tr('passaparola.hero.badge3', 'Lavoro')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-star"></i><span>400+ <?php echo esc_html($tr('passaparola.hero.badge4', 'beneficiari')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- COS'È PASSAPAROLA -->
    <section class="ws-section cw-institutional" id="passaparola-cose">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('passaparola.cose.eyebrow', 'Il progetto')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('passaparola.cose.title', "Cos'è Passaparola")); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('passaparola.cose.p1', 'Passaparola è il progetto con cui WECOOP opera direttamente sul territorio, offrendo orientamento, supporto e accompagnamento alle persone.')); ?></p>
                    <p><?php echo esc_html($tr('passaparola.cose.p2', 'Rappresenta il punto di accesso fisico al modello WECOOP, dove le persone possono ricevere aiuto concreto per affrontare bisogni amministrativi, lavorativi e personali.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROBLEMA / SOLUZIONE -->
    <section class="ws-section ws-section--soft cw-whatwedo" id="passaparola-problema">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('passaparola.cards.eyebrow', 'Il contesto')); ?></span>
                <h2><?php echo esc_html($tr('passaparola.cards.title', 'Problema e soluzione')); ?></h2>
            </div>
            <div class="cw-scard-grid">
                <article class="cw-scard cw-scard--blue">
                    <div class="cw-scard__icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('passaparola.cards.problema.title', 'Il problema')); ?></h3>
                        <p><?php echo esc_html($tr('passaparola.cards.problema.body', 'Difficoltà di accesso a servizi e opportunità per persone migranti e vulnerabili.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--green">
                    <div class="cw-scard__icon"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('passaparola.cards.soluzione.title', 'La soluzione')); ?></h3>
                        <p><?php echo esc_html($tr('passaparola.cards.soluzione.body', 'Un sistema integrato che unisce orientamento, formazione e accesso ai servizi.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--pink">
                    <div class="cw-scard__icon"><i class="fa-solid fa-gears" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('passaparola.cards.attivita.title', 'Attività')); ?></h3>
                        <p><?php echo esc_html($tr('passaparola.cards.attivita.body', 'Orientamento, formazione e supporto per lavoro, servizi e autonomia.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--yellow">
                    <div class="cw-scard__icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('passaparola.cards.impatto.title', 'Impatto')); ?></h3>
                        <p><?php echo esc_html($tr('passaparola.cards.impatto.body', 'Inclusione reale, autonomia personale e accesso concreto alle opportunità.')); ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- COSA FACCIAMO -->
    <section class="ws-section" id="passaparola-whatwedo">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('passaparola.whatwedo.eyebrow', 'Concretamente')); ?></span>
                <h2><?php echo esc_html($tr('passaparola.whatwedo.title', 'Cosa facciamo concretamente')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-door-open" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item1', 'Accoglienza e primo contatto')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-compass" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item2', 'Orientamento personalizzato')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item3', 'Supporto per documenti e pratiche')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-calculator" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item4', 'Supporto fiscale')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item5', 'Accompagnamento al lavoro')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.whatwedo.item6', 'Percorsi di formazione')); ?></h3>
                </div>
            </div>
        </div>
    </section>

    <!-- COLLEGAMENTO WECOOP -->
    <section class="ws-section ws-section--soft cw-institutional" id="passaparola-model">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('passaparola.model.eyebrow', 'Il modello')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('passaparola.model.title', 'Un progetto integrato nel modello WECOOP')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('passaparola.model.p1', 'Passaparola è la componente territoriale del modello WECOOP. Lavora in connessione con i servizi WECOOP, la piattaforma digitale e i partner sul territorio.')); ?></p>
                    <p><?php echo esc_html($tr('passaparola.model.p2', 'Permettendo di costruire un percorso completo, continuo e accessibile per tutte le persone che ne hanno bisogno.')); ?></p>
                    <div class="ws-hero-ctas" style="margin-top:24px;">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/servizi/')); ?>">
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            <?php echo esc_html($tr('passaparola.model.cta_services', 'Scopri i servizi')); ?>
                        </a>
                        <a class="ws-btn ws-btn--outline" href="<?php echo esc_url(home_url('/come-funziona-wecoop/')); ?>">
                            <?php echo esc_html($tr('passaparola.model.cta_model', 'Come funziona WECOOP')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- IMPATTO -->
    <section class="ws-section cw-impact" id="passaparola-impact">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('passaparola.impact.eyebrow', 'I numeri')); ?></span>
                <h2><?php echo esc_html($tr('passaparola.impact.title', 'Impatto sociale')); ?></h2>
            </div>
            <div class="cw-stats-grid">
                <div class="cw-stat cw-stat--blue">
                    <div class="cw-stat__icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('passaparola.impact.stat1', '400+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('passaparola.impact.stat1_label', 'beneficiari')); ?></span>
                </div>
                <div class="cw-stat cw-stat--green">
                    <div class="cw-stat__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('passaparola.impact.stat2', '150+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('passaparola.impact.stat2_label', 'percorsi formativi')); ?></span>
                </div>
                <div class="cw-stat cw-stat--pink">
                    <div class="cw-stat__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('passaparola.impact.stat3', '80+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('passaparola.impact.stat3_label', 'inserimenti lavorativi')); ?></span>
                </div>
                <div class="cw-stat cw-stat--yellow">
                    <div class="cw-stat__icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('passaparola.impact.stat4', '300+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('passaparola.impact.stat4_label', 'utenti piattaforma')); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- A CHI SI RIVOLGE -->
    <section class="ws-section ws-section--soft" id="passaparola-target">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('passaparola.target.eyebrow', 'Per chi')); ?></span>
                <h2><?php echo esc_html($tr('passaparola.target.title', 'A chi si rivolge')); ?></h2>
            </div>
            <div class="ws-grid-3">
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-passport" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.target.item1', 'Cittadini migranti')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.target.item2', 'Persone in situazione di vulnerabilità')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-compass" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.target.item3', 'Chi ha bisogno di orientamento')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.target.item4', 'Chi cerca lavoro')); ?></h3>
                </div>
                <div class="ws-feature-item">
                    <div class="ws-feature-item__icon"><i class="fa-solid fa-person-walking-arrow-right" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('passaparola.target.item5', 'Chi vuole costruire autonomia')); ?></h3>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="cw-cta-final" id="passaparola-cta">
        <div class="ws-container">
            <div class="cw-cta-final__box">
                <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('passaparola.cta.eyebrow', 'Partecipa')); ?></span>
                <h2><?php echo esc_html($tr('passaparola.cta.title', 'Vuoi partecipare o collaborare?')); ?></h2>
                <p><?php echo esc_html($tr('passaparola.cta.body', 'Contattaci per accedere ai servizi o per costruire insieme nuove opportunità.')); ?></p>
                <div class="ws-hero-ctas">
                    <a class="ws-btn cw-btn--white" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <?php echo esc_html($tr('passaparola.cta.btn1', 'Contattaci')); ?>
                    </a>
                    <a class="ws-btn cw-btn--whatsapp" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>">
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                        <?php echo esc_html($tr('passaparola.cta.btn2', 'Collabora con noi')); ?>
                    </a>
                </div>
                <p class="ws-microcopy">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <?php echo esc_html($tr('passaparola.cta.microcopy', 'Ti rispondiamo rapidamente.')); ?>
                </p>
            </div>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
