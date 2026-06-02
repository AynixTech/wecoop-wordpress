<?php
get_header();
$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};
$current_lang = wecoop_language();
$lang_base_url = remove_query_arg('lang');
$wa_phone = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei parlare con un operatore.'));
$whatsapp_url = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
$operator_url = home_url('/contact/');
$job_offers_url = home_url('/annunci-lavoro-wecoop/');
$lang_default = static function($it, $en, $es) use ($current_lang) {
    if ($current_lang === 'en') {
        return $en;
    }
    if ($current_lang === 'es') {
        return $es;
    }
    return $it;
};
?>

<main class="ws-site" aria-label="<?php echo esc_attr($tr('frontpage.aria.homepage', 'WECOOP homepage')); ?>">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="#inicio">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
            </a>
            <div id="ws-main-nav-home" class="ws-links" aria-label="<?php echo esc_attr($tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                <a href="#que-es"><?php echo esc_html($tr('frontpage.nav.about', $lang_default('Cos\'e WECOOP', 'What is WECOOP', 'Que es WECOOP'))); ?></a>
                <a href="#servizi"><?php echo esc_html($tr('frontpage.nav.services', $lang_default('Servizi', 'Services', 'Servicios'))); ?></a>
                <a href="#come-funziona"><?php echo esc_html($tr('frontpage.nav.how', $lang_default('Come funziona', 'How it works', 'Como funciona'))); ?></a>
                <a href="#passaparola"><?php echo esc_html($tr('frontpage.nav.passaparola', $lang_default('Passaparola', 'Passaparola', 'Passaparola'))); ?></a>
                <a href="#plataforma"><?php echo esc_html($tr('frontpage.nav.platform', $lang_default('Piattaforma Digitale', 'Digital Platform', 'Plataforma Digital'))); ?></a>
                <a href="#contacto"><?php echo esc_html($tr('frontpage.nav.contact', $lang_default('Contatti', 'Contact', 'Contacto'))); ?></a>
            </div>
            <div class="ws-nav__actions">
                <button class="ws-lang-trigger" id="ws-lang-trigger" aria-haspopup="dialog" aria-label="Select language">
                    <span class="ws-lang-trigger__flag"><?php echo esc_html(strtoupper($current_lang)); ?></span>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button class="ws-menu-toggle" id="ws-nav-toggle" aria-expanded="false" aria-controls="ws-main-nav-home" aria-label="<?php echo esc_attr($tr('frontpage.nav.menu_aria', 'Apri menu')); ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <section id="inicio" class="ws-hero">
        <div class="ws-container ws-grid-2 ws-hero__grid">
            <div>
                <h1>
                    <?php echo esc_html($tr('frontpage.hero.title.prefix', 'Un punto di accesso a documenti,')); ?>
                    <span class="ws-grad ws-grad--green"><?php echo esc_html($tr('frontpage.hero.word.services', 'servizi')); ?></span>,
                    <span class="ws-grad ws-grad--blue"><?php echo esc_html($tr('frontpage.hero.word.training', 'formazione')); ?></span>
                    <?php echo esc_html($tr('frontpage.hero.title_join', 'e')); ?>
                    <span class="ws-grad ws-grad--orange"><?php echo esc_html($tr('frontpage.hero.word.job_opportunities', 'opportunità di lavoro')); ?></span>
                </h1>
                <p><?php echo esc_html($tr('frontpage.hero.description', 'WECOOP ti accompagna passo dopo passo tra pratiche, orientamento e servizi, integrando supporto sul territorio e tecnologia digitale per facilitare l\'accesso al lavoro e all\'autonomia.')); ?></p>
                <div class="ws-actions">
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($operator_url); ?>"><i class="fa-solid fa-calendar-check" aria-hidden="true" style="color:#fff;margin-right:8px;"></i><?php echo esc_html($tr('frontpage.hero.cta_primary', 'Prenota un appuntamento')); ?></a>
                    <a class="ws-btn ws-btn--success" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true" style="color:#fff;margin-right:8px;"></i><?php echo esc_html($tr('frontpage.hero.cta_secondary', 'Scrivici su WhatsApp')); ?></a>
                    <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url($job_offers_url); ?>"><i class="fa-solid fa-briefcase" aria-hidden="true" style="margin-right:8px;"></i><?php echo esc_html($tr('frontpage.hero.cta_jobs', 'Vedi offerte di lavoro')); ?></a>
                </div>
                <p class="ws-hero__microcopy"><?php echo esc_html($tr('frontpage.hero.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare, passo dopo passo.')); ?></p>
            </div>
            <div class="ws-hero__media">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecoop-hero-community.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.hero.image_alt', 'Comunidad WECOOP')); ?>">
                <div class="ws-kpi-card" aria-label="<?php echo esc_attr($tr('frontpage.hero.kpi_label', 'Beneficiari')); ?>">
                    <span class="ws-kpi-card__icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                    <div>
                        <strong>400+</strong>
                        <small><?php echo esc_html($tr('frontpage.hero.kpi_label', 'Beneficiari')); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="que-es" class="ws-section">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.about.title', 'Modello WECOOP')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.about.lead', 'Un sistema integrato che combina servizi, formazione e tecnologia per accompagnarti nella vita, nel lavoro e nell\'accesso alle opportunità.')); ?></p>
            <div class="ws-grid-3">
                <article class="ws-card ws-card--blue">
                    <span class="ws-icon ws-icon--blue" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card1.title', 'Centro Territoriale')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card1.body', 'Uno spazio fisico di riferimento dove le persone trovano orientamento, accompagnamento personalizzato e accesso ai servizi.')); ?></p>
                </article>
                <article class="ws-card ws-card--green">
                    <span class="ws-icon ws-icon--green" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card2.title', 'Formazione e Lavoro')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card2.body', 'Percorsi formativi personalizzati, sviluppo di competenze e connessione diretta con opportunità lavorative.')); ?></p>
                </article>
                <article class="ws-card ws-card--pink">
                    <span class="ws-icon ws-icon--pink" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card3.title', 'Piattaforma Digitale')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card3.body', 'Tecnologia accessibile per gestire appuntamenti, accedere alla formazione e monitorare il percorso.')); ?></p>
                </article>
            </div>
            <div class="ws-section-cta">
                <a class="ws-btn ws-btn--outline" href="<?php echo esc_url(home_url('/cos-e-wecoop/')); ?>"><?php echo esc_html($tr('frontpage.about.cta', 'Scopri di più')); ?></a>
            </div>
        </div>
    </section>

    <section id="modello-sistema" class="ws-section ws-section--soft">
        <div class="ws-container">
            <div class="ws-model-intro">
                <h2><?php echo esc_html($tr('frontpage.model.title', 'Un sistema fisico + digitale')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('frontpage.model.lead', 'WECOOP combina uno sportello sul territorio con una piattaforma digitale per accompagnarti in tutto il percorso.')); ?></p>
            </div>
            <div class="ws-grid-3 ws-model-grid">
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile1.title', 'Territorio')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile1.body', 'Presenza locale e comunitaria')); ?></p></div></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile2.title', 'Persone')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile2.body', 'Al centro del sistema')); ?></p></div></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-regular fa-heart"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile3.title', 'Servizi')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile3.body', 'Orientamento e accompagnamento')); ?></p></div></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile4.title', 'Formazione')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile4.body', 'Sviluppo di competenze')); ?></p></div></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile5.title', 'Opportunita')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile5.body', 'Accesso al lavoro')); ?></p></div></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-diagram-project"></i></span><div class="ws-tile__content"><h3><?php echo esc_html($tr('frontpage.model.tile6.title', 'Piattaforma Digitale')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile6.body', 'Tecnologia inclusiva')); ?></p></div></article>
            </div>
            <div class="ws-section-cta">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/servizi/')); ?>">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    <?php echo esc_html($tr('frontpage.model.cta', 'Scopri i nostri servizi')); ?>
                </a>
            </div>
        </div>
    </section>

    <section id="come-funziona" class="ws-section ws-section--soft">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.how.title', 'Come funziona WECOOP')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.how.lead', 'Un percorso semplice per aiutarti a risolvere i tuoi bisogni e accedere al lavoro.')); ?></p>
            <div class="ws-grid-3">
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-ear-listen"></i></span><h3><?php echo esc_html($tr('frontpage.how.step1.title', '1. Ti ascoltiamo')); ?></h3><p><?php echo esc_html($tr('frontpage.how.step1.body', 'Capiamo la tua situazione e i tuoi bisogni.')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></span><h3><?php echo esc_html($tr('frontpage.how.step2.title', '2. Ti orientiamo')); ?></h3><p><?php echo esc_html($tr('frontpage.how.step2.body', 'Ti spieghiamo le possibilita e da dove iniziare.')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-hand-holding-heart"></i></span><h3><?php echo esc_html($tr('frontpage.how.step3.title', '3. Ti aiutiamo')); ?></h3><p><?php echo esc_html($tr('frontpage.how.step3.body', 'Ti supportiamo con documenti, servizi e strumenti.')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span><h3><?php echo esc_html($tr('frontpage.how.step4.title', '4. Ti formiamo')); ?></h3><p><?php echo esc_html($tr('frontpage.how.step4.body', 'Sviluppi competenze utili per il lavoro.')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span><h3><?php echo esc_html($tr('frontpage.how.step5.title', '5. Ti accompagniamo al lavoro')); ?></h3><p><?php echo esc_html($tr('frontpage.how.step5.body', 'Ti aiutiamo a candidarti e trovare opportunita.')); ?></p></article>
            </div>
            <div class="ws-section-cta">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/come-funziona-wecoop/')); ?>">
                    <i class="fa-solid fa-route" aria-hidden="true"></i>
                    <?php echo esc_html($tr('frontpage.how.cta', 'Scopri come funziona')); ?>
                </a>
                <p class="ws-microcopy">
                    <i class="fa-solid fa-shoe-prints" aria-hidden="true"></i>
                    <?php echo esc_html($tr('frontpage.how.microcopy', 'Ti spieghiamo il percorso passo dopo passo.')); ?>
                </p>
            </div>
        </div>
    </section>

    <section id="passaparola" class="ws-section">
        <div class="ws-container">
            <div class="ws-grid-2 ws-pass">
                <div>
                    <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <h2><?php echo esc_html($tr('frontpage.passaparola.title', 'Proyecto Passaparola')); ?></h2>
                    <p class="ws-lead"><?php echo esc_html($tr('frontpage.passaparola.lead', 'Persone, connessioni e opportunita: un proyecto dedicado al apoyo integral de personas migrantes y en situacion de vulnerabilidad.')); ?></p>
                </div>
                <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.passaparola.image_alt', 'Equipo Passaparola')); ?>">
            </div>
            <div class="ws-grid-4">
                <article class="ws-mini ws-mini--pink"><span class="ws-mini__icon" aria-hidden="true"><i class="fa-solid fa-bullseye"></i></span><h3><?php echo esc_html($tr('frontpage.passaparola.problem.title', 'Problema')); ?></h3><p><?php echo esc_html($tr('frontpage.passaparola.problem.body', 'Dificultad de acceso a servicios y oportunidades para personas migrantes y vulnerables.')); ?></p></article>
                <article class="ws-mini ws-mini--blue"><span class="ws-mini__icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span><h3><?php echo esc_html($tr('frontpage.passaparola.solution.title', 'Solucion')); ?></h3><p><?php echo esc_html($tr('frontpage.passaparola.solution.body', 'Sistema integrado de orientacion, formacion y conexion con oportunidades laborales.')); ?></p></article>
                <article class="ws-mini ws-mini--green"><span class="ws-mini__icon" aria-hidden="true"><i class="fa-solid fa-list-check"></i></span><h3><?php echo esc_html($tr('frontpage.passaparola.activities.title', 'Actividades')); ?></h3><p><?php echo esc_html($tr('frontpage.passaparola.activities.body', 'Orientacion personalizada, formacion profesional, insercion laboral y seguimiento continuo.')); ?></p></article>
                <article class="ws-mini ws-mini--lime"><span class="ws-mini__icon" aria-hidden="true"><i class="fa-solid fa-chart-line"></i></span><h3><?php echo esc_html($tr('frontpage.passaparola.impact.title', 'Impacto')); ?></h3><p><?php echo esc_html($tr('frontpage.passaparola.impact.body', 'Inclusion social efectiva, autonomia personal y acceso real al mercado laboral.')); ?></p></article>
            </div>
            <div class="ws-actions" style="margin-top:18px;">
                <a class="ws-btn ws-btn--secondary" href="<?php echo esc_url(home_url('/passaparola/')); ?>"><?php echo esc_html($tr('frontpage.passaparola.cta1', 'Scopri Passaparola')); ?></a>
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>"><?php echo esc_html($tr('frontpage.passaparola.cta2', 'Collabora con noi')); ?></a>
            </div>
        </div>
    </section>

    <section id="plataforma" class="ws-section ws-section--soft-blue">
        <div class="ws-container">
            <div class="ws-grid-2 ws-pass">
                <div>
                    <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
                    <h2><?php echo esc_html($tr('frontpage.platform.title', 'Piattaforma Digitale WECOOP')); ?></h2>
                    <p class="ws-lead"><?php echo esc_html($tr('frontpage.platform.lead', 'Gestisci servizi, appuntamenti e il tuo percorso direttamente online.')); ?></p>
                    <p><?php echo esc_html($tr('frontpage.platform.intro', 'La piattaforma digitale di WECOOP ti permette di accedere ai servizi in modo semplice, veloce e guidato.')); ?></p>
                    <ul class="ws-checks">
                        <li><?php echo esc_html($tr('frontpage.platform.check1', 'Crea il tuo profilo e accedi ai servizi')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check2', 'Prenota appuntamenti con gli operatori')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check3', 'Accedi a contenuti e percorsi formativi')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check4', 'Comunica con noi e segui il tuo percorso')); ?></li>
                    </ul>
                    <div class="ws-actions" style="margin-top:14px;">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/piattaforma/')); ?>">
                            <?php echo esc_html($tr('frontpage.platform.cta1', 'Registrati alla piattaforma')); ?>
                        </a>
                    </div>
                    <div class="ws-store-badges" style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <a href="https://play.google.com/store/apps/details?id=org.wecoop.app" target="_blank" rel="noopener noreferrer" aria-label="Scarica su Google Play">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badges/playstore.svg'); ?>" alt="Google Play" style="height:40px;">
                        </a>
                        <a href="https://apps.apple.com/app/wecoop/id0000000000" target="_blank" rel="noopener noreferrer" aria-label="Scarica su App Store">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/badges/appstpre.png'); ?>" alt="App Store" style="height:40px;">
                        </a>
                    </div>
                </div>
                <div class="ws-grid-2 ws-media-grid">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image1_alt', 'App mobile WECOOP')); ?>">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image2_alt', 'Piattaforma digitale')); ?>">
                    <img class="ws-img ws-img--wide" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image3_alt', 'Gestione servizi')); ?>">
                </div>
            </div>
        </div>
    </section>

    <section id="impacto" class="ws-section">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.impact.title', 'Il nostro impatto sociale')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.impact.lead', 'Risultati concreti che migliorano la vita delle persone.')); ?></p>
            <p><?php echo esc_html($tr('frontpage.impact.human', 'Dietro ogni numero c\'è una persona accompagnata nel suo percorso.')); ?></p>
            <div class="ws-grid-4">
                <article class="ws-stat ws-stat--users"><i class="fa-solid fa-users ws-stat__icon" aria-hidden="true"></i><strong>400+</strong><span><?php echo esc_html($tr('frontpage.impact.stat1', 'Persone supportate')); ?></span></article>
                <article class="ws-stat ws-stat--learn"><i class="fa-solid fa-graduation-cap ws-stat__icon" aria-hidden="true"></i><strong>150+</strong><span><?php echo esc_html($tr('frontpage.impact.stat2', 'Percorsi di formazione attivati')); ?></span></article>
                <article class="ws-stat ws-stat--work"><i class="fa-solid fa-briefcase ws-stat__icon" aria-hidden="true"></i><strong>80+</strong><span><?php echo esc_html($tr('frontpage.impact.stat3', 'Persone inserite nel lavoro')); ?></span></article>
                <article class="ws-stat ws-stat--app"><i class="fa-solid fa-mobile-screen-button ws-stat__icon" aria-hidden="true"></i><strong>300+</strong><span><?php echo esc_html($tr('frontpage.impact.stat4', 'Utenti attivi sulla piattaforma')); ?></span></article>
            </div>
            <div class="ws-actions" style="margin-top:18px;">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/impatto/')); ?>"><?php echo esc_html($tr('frontpage.impact.cta1', 'Scopri il nostro impatto')); ?></a>
                <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>"><?php echo esc_html($tr('frontpage.impact.cta2', 'Collabora con noi')); ?></a>
            </div>
        </div>
    </section>

    <section id="partners" class="ws-section ws-section--gray">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.partners.title', 'Red de Partners')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.partners.lead', 'Colaboramos con instituciones, organizaciones y empresas comprometidas con la inclusion social.')); ?></p>
            <div class="ws-partners-box">
                <?php
                $partners_query = new WP_Query([
                    'post_type'      => 'wecoop_partner',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'meta_value_num',
                    'meta_key'       => 'ordine',
                    'order'          => 'ASC',
                ]);
                if ($partners_query->have_posts()) :
                    while ($partners_query->have_posts()) : $partners_query->the_post();
                        $partner_url  = get_post_meta(get_the_ID(), 'website_url', true);
                        $logo_url     = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        $partner_name = get_the_title();
                        $tag_open  = $partner_url ? '<a class="ws-partner-card" href="' . esc_url($partner_url) . '" target="_blank" rel="noopener noreferrer">' : '<div class="ws-partner-card">';
                        $tag_close = $partner_url ? '</a>' : '</div>';
                        echo $tag_open;
                        if ($logo_url) {
                            echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($partner_name) . '" loading="lazy">';
                        } else {
                            echo '<span class="ws-partner-card__name">' . esc_html($partner_name) . '</span>';
                        }
                        echo $tag_close;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <section id="colabora" class="ws-section ws-section--cta">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2><?php echo esc_html($tr('frontpage.collab.title', 'Collabora con WECOOP')); ?></h2>
                <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('frontpage.collab.lead', 'Costruiamo insieme progetti concreti di inclusione e sviluppo sul territorio.')); ?></p>
                <p class="ws-lead ws-lead--light" style="font-size:.9rem; opacity:.8;"><?php echo esc_html($tr('frontpage.collab.sublead', 'WECOOP è una piattaforma aperta a partner pubblici e privati.')); ?></p>
                <div class="ws-grid-2" style="margin-top:16px;">
                    <article class="ws-glass">
                        <h3><?php echo esc_html($tr('frontpage.collab.card1.title', 'Istituzioni Pubbliche')); ?></h3>
                        <p><?php echo esc_html($tr('frontpage.collab.card1.body', 'Sviluppiamo progetti di inclusione sociale e accesso ai servizi sul territorio')); ?></p>
                    </article>
                    <article class="ws-glass">
                        <h3><?php echo esc_html($tr('frontpage.collab.card2.title', 'Imprese')); ?></h3>
                        <p><?php echo esc_html($tr('frontpage.collab.card2.body', 'Creiamo opportunità di lavoro e progetti di responsabilità sociale')); ?></p>
                    </article>
                    <article class="ws-glass">
                        <h3><?php echo esc_html($tr('frontpage.collab.card3.title', 'Fondazioni')); ?></h3>
                        <p><?php echo esc_html($tr('frontpage.collab.card3.body', 'Sviluppiamo progetti ad alto impatto sociale e misurabile')); ?></p>
                    </article>
                    <article class="ws-glass">
                        <h3><?php echo esc_html($tr('frontpage.collab.card4.title', 'Volontari')); ?></h3>
                        <p><?php echo esc_html($tr('frontpage.collab.card4.body', 'Contribuisci attivamente ai progetti WECOOP sul territorio')); ?></p>
                    </article>
                </div>
                <div class="ws-actions" style="margin-top:20px;">
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/sostieni-wecoop/')); ?>">
                        <?php echo esc_html($tr('frontpage.collab.cta1', 'Collabora con noi')); ?>
                    </a>
                    <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <?php echo esc_html($tr('frontpage.collab.cta2', 'Contattaci')); ?>
                    </a>
                </div>
            </div>
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.collab.image_alt', 'Collaborazione WECOOP')); ?>">
        </div>
    </section>

    <section id="contacto" class="ws-section ws-section--cta">
        <div class="ws-container" style="text-align:center; max-width:640px;">
            <h2><?php echo esc_html($tr('frontpage.contact.title', 'Hai bisogno di supporto?')); ?></h2>
            <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('frontpage.contact.lead', 'Ti aiutiamo a capire cosa fare e da dove iniziare, passo dopo passo.')); ?></p>
            <div class="ws-actions" style="justify-content:center; margin-top:20px;">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                    <?php echo esc_html($tr('frontpage.contact.cta1', 'Scrivici su WhatsApp')); ?>
                </a>
                <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                    <?php echo esc_html($tr('frontpage.contact.cta2', 'Contattaci')); ?>
                </a>
            </div>
            <p class="ws-microcopy" style="margin-top:14px; opacity:.85;">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                <?php echo esc_html($tr('frontpage.contact.microcopy', 'Ti rispondiamo rapidamente e ti guidiamo nel primo passo.')); ?>
            </p>
        </div>
    </section>

    <?php
    $modal_langs = [
        'it' => ['label' => 'Italiano', 'flag' => '🇮🇹'],
        'en' => ['label' => 'English',  'flag' => '🇬🇧'],
        'es' => ['label' => 'Español',  'flag' => '🇪🇸'],
        'ar' => ['label' => 'العربية',  'flag' => '🇸🇦'],
        'zh' => ['label' => '中文',      'flag' => '🇨🇳'],
    ];
    $modal_base_url = remove_query_arg('lang');
    ?>
    <div class="ws-lang-modal" id="ws-lang-modal" role="dialog" aria-modal="true" aria-labelledby="ws-lang-modal-title" hidden>
        <div class="ws-lang-modal__backdrop" id="ws-lang-modal-backdrop"></div>
        <div class="ws-lang-modal__panel">
            <div class="ws-lang-modal__header">
                <span id="ws-lang-modal-title" class="ws-lang-modal__title"><?php echo esc_html($tr('nav.select_language', 'Select Language')); ?></span>
                <button class="ws-lang-modal__close" id="ws-lang-modal-close" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M3 3l12 12M15 3L3 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <ul class="ws-lang-modal__list" role="listbox" aria-label="Languages">
                <?php foreach ($modal_langs as $code => $info) : ?>
                <li role="option" aria-selected="<?php echo $current_lang === $code ? 'true' : 'false'; ?>">
                    <a class="ws-lang-modal__option <?php echo $current_lang === $code ? 'is-active' : ''; ?>"
                       href="<?php echo esc_url(add_query_arg('lang', $code, $modal_base_url)); ?>"
                       <?php if ($code === 'ar') echo 'dir="rtl" lang="ar"'; ?>
                       <?php if ($code === 'zh') echo 'lang="zh"'; ?>>
                        <span class="ws-lang-modal__flag" aria-hidden="true"><?php echo $info['flag']; ?></span>
                        <span class="ws-lang-modal__name"><?php echo esc_html($info['label']); ?></span>
                        <?php if ($current_lang === $code) : ?>
                        <svg class="ws-lang-modal__check" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8l3.5 3.5L13 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</main>

<?php
get_footer();
