<?php
get_header();
$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};
$current_lang = wecoop_language();
$lang_base_url = remove_query_arg('lang');
$wa_phone = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393341390175'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei parlare con un operatore.'));
$whatsapp_url = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
$operator_url = home_url('/contact/');
?>

<main class="ws-site" aria-label="<?php echo esc_attr($tr('frontpage.aria.homepage', 'WECOOP homepage')); ?>">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="#inicio">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
            </a>
            <div class="ws-links" aria-label="<?php echo esc_attr($tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                <a href="#que-es"><?php echo esc_html($tr('frontpage.nav.about', 'Cos\'e WECOOP')); ?></a>
                <a href="#servizi"><?php echo esc_html($tr('frontpage.nav.services', 'Servizi')); ?></a>
                <a href="#come-funziona"><?php echo esc_html($tr('frontpage.nav.how', 'Come funziona')); ?></a>
                <a href="#passaparola"><?php echo esc_html($tr('frontpage.nav.passaparola', 'Passaparola')); ?></a>
                <a href="#plataforma"><?php echo esc_html($tr('frontpage.nav.platform', 'Piattaforma Digitale')); ?></a>
                <a href="#impacto"><?php echo esc_html($tr('frontpage.nav.impact', 'Impatto')); ?></a>
                <a href="#contacto"><?php echo esc_html($tr('frontpage.nav.contact', 'Contatti')); ?></a>
            </div>
            <div class="ws-lang-switcher" aria-label="Language switcher">
                <a class="<?php echo $current_lang === 'it' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'it', $lang_base_url)); ?>">IT</a>
                <a class="<?php echo $current_lang === 'en' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'en', $lang_base_url)); ?>">EN</a>
                <a class="<?php echo $current_lang === 'es' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('lang', 'es', $lang_base_url)); ?>">ES</a>
            </div>
            <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.nav.cta', 'Collabora')); ?></a>
        </div>
    </nav>

    <section id="inicio" class="ws-hero">
        <div class="ws-container ws-grid-2 ws-hero__grid">
            <div>
                <h1>
                    <?php echo esc_html($tr('frontpage.hero.title.prefix', 'Un punto di accesso a')); ?>
                    <span class="ws-grad ws-grad--green"><?php echo esc_html($tr('frontpage.hero.word.services', 'servizi')); ?></span>,
                    <span class="ws-grad ws-grad--blue"><?php echo esc_html($tr('frontpage.hero.word.training', 'formazione')); ?></span>
                    <?php echo esc_html($tr('frontpage.hero.title_join', 'e')); ?>
                    <span class="ws-grad ws-grad--orange"><?php echo esc_html($tr('frontpage.hero.word.job_opportunities', 'opportunita di lavoro')); ?></span>
                </h1>
                <p><?php echo esc_html($tr('frontpage.hero.description', 'WECOOP ti aiuta a gestire documenti, accedere ai servizi e costruire un percorso verso il lavoro, attraverso un sistema integrato tra sportello fisico e piattaforma digitale.')); ?></p>
                <div class="ws-actions">
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($operator_url); ?>"><?php echo esc_html($tr('frontpage.hero.cta_primary', 'Parla con un operatore')); ?></a>
                    <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($tr('frontpage.hero.cta_secondary', 'Scrivici su WhatsApp')); ?></a>
                </div>
                <div class="ws-kpi"><?php echo esc_html($tr('frontpage.hero.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare')); ?></div>
            </div>
            <div class="ws-hero__media">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecoop-hero-community.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.hero.image_alt', 'Comunidad WECOOP')); ?>">
            </div>
        </div>
    </section>

    <section id="que-es" class="ws-section">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.about.title', 'Que es WECOOP?')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.about.lead', 'Un modelo innovador que combina un centro territorial, servicios de orientacion, formacion, oportunidades laborales y una plataforma digital integrada.')); ?></p>
            <div class="ws-grid-3">
                <article class="ws-card ws-card--blue">
                    <span class="ws-icon ws-icon--blue" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card1.title', 'Centro Territorial')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card1.body', 'Un espacio fisico de referencia donde las personas encuentran orientacion, acompanamiento personalizado y acceso a servicios de inclusion social.')); ?></p>
                </article>
                <article class="ws-card ws-card--green">
                    <span class="ws-icon ws-icon--green" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card2.title', 'Formacion y Empleo')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card2.body', 'Recorridos formativos personalizados, desarrollo de competencias profesionales y conexion directa con oportunidades laborales reales.')); ?></p>
                </article>
                <article class="ws-card ws-card--pink">
                    <span class="ws-icon ws-icon--pink" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.about.card3.title', 'Plataforma Digital')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.about.card3.body', 'Tecnologia accesible que permite gestionar citas, acceder a formacion, comunicarse con operadores y hacer seguimiento del recorrido personal.')); ?></p>
                </article>
            </div>
            <div class="ws-actions" style="margin-top:18px;">
                <a class="ws-btn ws-btn--primary" href="#come-funziona"><?php echo esc_html($tr('frontpage.about.cta', 'Scopri come funziona')); ?></a>
            </div>
        </div>
    </section>

    <section id="servizi" class="ws-section ws-section--soft-blue">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.services.title', 'Servizi')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.services.lead', 'Supporto specializzato su fiscalita, mediazione e adempimenti per vivere e lavorare in Italia.')); ?></p>
            <div class="ws-grid-4">
                <article class="ws-card ws-card--blue">
                    <span class="ws-icon ws-icon--blue" aria-hidden="true"><i class="fa-solid fa-house"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.services.service1.title', 'Vivere in Italia')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.services.service1.body', 'Supporto pratico per orientarti nella vita quotidiana e amministrativa in Italia.')); ?></p>
                </article>
                <article class="ws-card ws-card--green">
                    <span class="ws-icon ws-icon--green" aria-hidden="true"><i class="fa-solid fa-handshake-angle"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.services.service2.title', 'Mediazione fiscale')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.services.service2.body', 'Mediazione e accompagnamento per comprendere procedure e comunicazioni fiscali.')); ?></p>
                </article>
                <article class="ws-card ws-card--pink">
                    <span class="ws-icon ws-icon--pink" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.services.service3.title', 'Supporto contabile per P.IVA forfettaria')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.services.service3.body', 'Supporto contabile dedicato a chi opera con partita IVA in regime forfettario.')); ?></p>
                </article>
                <article class="ws-card ws-card--lime">
                    <span class="ws-icon ws-icon--lime" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.services.service4.title', 'Orientamenti e chiarimenti fiscali')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.services.service4.body', 'Orientamento e chiarimenti su dubbi fiscali ricorrenti e adempimenti principali.')); ?></p>
                </article>
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
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-globe"></i></span><h3><?php echo esc_html($tr('frontpage.how.model.title', 'Fisico + Digitale')); ?></h3><p><?php echo esc_html($tr('frontpage.how.model.body', 'Sportello sul territorio e piattaforma digitale in un unico percorso.')); ?></p></article>
            </div>
            <p class="ws-kpi"><?php echo esc_html($tr('frontpage.how.microcopy', 'Ti aiutiamo passo dopo passo')); ?></p>
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
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.passaparola.cta2', 'Collabora con noi')); ?></a>
            </div>
        </div>
    </section>

    <section id="plataforma" class="ws-section ws-section--soft-blue">
        <div class="ws-container">
            <div class="ws-grid-2 ws-pass">
                <div>
                    <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
                    <h2><?php echo esc_html($tr('frontpage.platform.title', 'Plataforma Digital')); ?></h2>
                    <p class="ws-lead"><?php echo esc_html($tr('frontpage.platform.lead', 'Desarrollada en colaboracion con nuestro partner tecnologico AYNIX, la plataforma APP WECOOP democratiza el acceso a servicios y oportunidades a traves de una solucion digital intuitiva y accesible.')); ?></p>
                    <ul class="ws-checks">
                        <li><?php echo esc_html($tr('frontpage.platform.check1', 'Registro y perfil personalizado')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check2', 'Reserva de citas con operadores')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check3', 'Acceso a formacion y recursos')); ?></li>
                        <li><?php echo esc_html($tr('frontpage.platform.check4', 'Comunicacion directa y seguimiento')); ?></li>
                    </ul>
                    <div class="ws-actions" style="margin-top:14px;">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/wecoop-app/')); ?>"><?php echo esc_html($tr('frontpage.platform.cta1', 'Registrati alla piattaforma')); ?></a>
                    </div>
                </div>
                <div class="ws-grid-2 ws-media-grid">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image1_alt', 'App movil')); ?>">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image2_alt', 'Plataforma digital')); ?>">
                    <img class="ws-img ws-img--wide" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.platform.image3_alt', 'Gestion de servicios')); ?>">
                </div>
            </div>
        </div>
    </section>

    <section id="impacto" class="ws-section">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.impact.title', 'Nuestro Impacto Social')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.impact.lead', 'Resultados medibles de un modelo que transforma vidas y genera oportunidades reales.')); ?></p>
            <div class="ws-grid-4">
                <article class="ws-stat ws-stat--users"><i class="fa-solid fa-users ws-stat__icon" aria-hidden="true"></i><strong>400+</strong><span><?php echo esc_html($tr('frontpage.impact.stat1', 'Beneficiarios')); ?></span></article>
                <article class="ws-stat ws-stat--learn"><i class="fa-solid fa-graduation-cap ws-stat__icon" aria-hidden="true"></i><strong>150+</strong><span><?php echo esc_html($tr('frontpage.impact.stat2', 'Recorridos Formativos')); ?></span></article>
                <article class="ws-stat ws-stat--work"><i class="fa-solid fa-briefcase ws-stat__icon" aria-hidden="true"></i><strong>80+</strong><span><?php echo esc_html($tr('frontpage.impact.stat3', 'Inserciones Laborales')); ?></span></article>
                <article class="ws-stat ws-stat--app"><i class="fa-solid fa-mobile-screen-button ws-stat__icon" aria-hidden="true"></i><strong>300+</strong><span><?php echo esc_html($tr('frontpage.impact.stat4', 'Usuarios Plataforma')); ?></span></article>
            </div>
            <div class="ws-actions" style="margin-top:18px;">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.impact.cta1', 'Scopri come partecipare')); ?></a>
                <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.impact.cta2', 'Collabora con noi')); ?></a>
            </div>
        </div>
    </section>

    <section id="partners" class="ws-section ws-section--gray">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.partners.title', 'Red de Partners')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.partners.lead', 'Colaboramos con instituciones, organizaciones y empresas comprometidas con la inclusion social.')); ?></p>
            <div class="ws-partners-box">
                <div class="ws-partner-tech">
                    <strong><?php echo esc_html($tr('frontpage.partners.tech_label', 'Partner Tecnologico')); ?></strong>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_2.png'); ?>" alt="AYNIX">
                </div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
                <div class="ws-partner-placeholder"><span aria-hidden="true"><i class="fa-regular fa-building"></i></span></div>
            </div>
        </div>
    </section>

    <section id="colabora" class="ws-section ws-section--cta">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2><?php echo esc_html($tr('frontpage.collab.title', 'Colabora con WECOOP')); ?></h2>
                <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('frontpage.collab.lead', 'Unete a nuestra red de partners y contribuye a crear oportunidades de inclusion social y laboral.')); ?></p>
                <div class="ws-grid-2">
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card1.title', 'Instituciones Publicas')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card1.body', 'Alianzas estrategicas para ampliar el impacto')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card2.title', 'Empresas')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card2.body', 'Oportunidades de empleo y RSC')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card3.title', 'Fundaciones')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card3.body', 'Apoyo a proyectos de inclusion')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card4.title', 'Voluntarios')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card4.body', 'Comparte tu tiempo y talento')); ?></p></article>
                </div>
                <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('frontpage.final.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare')); ?></p>
            </div>
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.collab.image_alt', 'Colaboracion')); ?>">
        </div>
    </section>

    <section id="contacto" class="ws-section">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2><?php echo esc_html($tr('frontpage.contact.title', 'Contattaci')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('frontpage.contact.lead', 'Siamo qui per aiutarti. Parlaci e ti guideremo.')); ?></p>
                <div class="ws-actions" style="margin-bottom:12px;">
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($tr('frontpage.contact.cta1', 'Scrivici su WhatsApp')); ?></a>
                    <a class="ws-btn ws-btn--ghost" href="tel:+3902XXXXXXX"><?php echo esc_html($tr('frontpage.contact.cta2', 'Chiama ora')); ?></a>
                </div>
                <p class="ws-kpi"><?php echo esc_html($tr('frontpage.contact.microcopy', 'Ti rispondiamo rapidamente')); ?></p>
                <ul class="ws-contact-list">
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_address', 'Dove siamo')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_address', 'Via Populonia 8, Milano')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_hours', 'Orari')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_hours', 'Lunedi - Venerdi')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_email', 'Email')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_email', 'info@wecoop.org')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_phone', 'Telefono')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_phone', '+39 351 511 2113')); ?></span></li>
                </ul>
            </div>
            <div class="ws-form-shell">
                <h3><?php echo esc_html($tr('frontpage.contact.form_title', 'Oppure scrivici')); ?></h3>
                <?php echo do_shortcode('[wecoop_contact_form]'); ?>
            </div>
        </div>
    </section>

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
                    <a href="#que-es"><?php echo esc_html($tr('frontpage.footer.col1_link1', 'Cos\'e WECOOP')); ?></a>
                    <a href="#servizi"><?php echo esc_html($tr('frontpage.footer.col1_link2', 'Servizi')); ?></a>
                    <a href="#come-funziona"><?php echo esc_html($tr('frontpage.footer.col1_link3', 'Come funziona')); ?></a>
                    <a href="#passaparola"><?php echo esc_html($tr('frontpage.footer.col1_link2', 'Passaparola')); ?></a>
                    <a href="#plataforma"><?php echo esc_html($tr('frontpage.footer.col1_link4', 'Piattaforma Digitale')); ?></a>
                    <a href="#impacto"><?php echo esc_html($tr('frontpage.footer.col1_link5', 'Impatto')); ?></a>
                    <a href="#contacto"><?php echo esc_html($tr('frontpage.footer.col1_link6', 'Contatti')); ?></a>
                </div>
                <div>
                    <h4><?php echo esc_html($tr('frontpage.footer.col2_title', 'Colabora')); ?></h4>
                    <a href="#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link1', 'Empresas')); ?></a>
                    <a href="#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link2', 'Instituciones')); ?></a>
                    <a href="#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link3', 'Fundaciones')); ?></a>
                    <a href="#colabora"><?php echo esc_html($tr('frontpage.footer.col2_link4', 'Voluntarios')); ?></a>
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
get_footer();
