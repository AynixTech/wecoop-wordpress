<?php
get_header();
$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};
?>

<main class="ws-site" aria-label="<?php echo esc_attr($tr('frontpage.aria.homepage', 'WECOOP homepage')); ?>">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="#inicio">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
            </a>
            <div class="ws-links" aria-label="<?php echo esc_attr($tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                <a href="#que-es"><?php echo esc_html($tr('frontpage.nav.about', 'Que es WECOOP')); ?></a>
                <a href="#passaparola"><?php echo esc_html($tr('frontpage.nav.passaparola', 'Passaparola')); ?></a>
                <a href="#plataforma"><?php echo esc_html($tr('frontpage.nav.platform', 'Plataforma Digital')); ?></a>
                <a href="#impacto"><?php echo esc_html($tr('frontpage.nav.impact', 'Impacto')); ?></a>
                <a href="#contacto"><?php echo esc_html($tr('frontpage.nav.contact', 'Contacto')); ?></a>
            </div>
            <a class="ws-btn ws-btn--primary" href="#contacto"><?php echo esc_html($tr('frontpage.nav.cta', 'Colabora')); ?></a>
        </div>
    </nav>

    <section id="inicio" class="ws-hero">
        <div class="ws-container ws-grid-2 ws-hero__grid">
            <div>
                <h1><?php echo esc_html($tr('frontpage.hero.title_intro', 'Un ecosistema de')); ?> <span><?php echo esc_html($tr('frontpage.hero.title_highlight_1', 'inclusion')); ?></span> <?php echo esc_html($tr('frontpage.hero.title_join', 'y')); ?> <span><?php echo esc_html($tr('frontpage.hero.title_highlight_2', 'oportunidades')); ?></span></h1>
                <p><?php echo esc_html($tr('frontpage.hero.description', 'WECOOP integra servicios territoriales, formacion profesional y tecnologia digital para conectar personas vulnerables con oportunidades de empleo y desarrollo personal.')); ?></p>
                <div class="ws-actions">
                    <a class="ws-btn ws-btn--primary" href="#que-es"><?php echo esc_html($tr('frontpage.hero.cta_primary', 'Descubre mas')); ?></a>
                    <a class="ws-btn ws-btn--ghost" href="#contacto"><?php echo esc_html($tr('frontpage.hero.cta_secondary', 'Contactanos')); ?></a>
                </div>
                <div class="ws-kpi"><strong>400+</strong> <?php echo esc_html($tr('frontpage.hero.kpi_label', 'Beneficiarios')); ?></div>
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
        </div>
    </section>

    <section id="modelo" class="ws-section ws-section--soft">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.model.title', 'El Modelo Fisico + Digital')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('frontpage.model.lead', 'Un ecosistema integrado que conecta territorio, personas, servicios y tecnologia.')); ?></p>
            <div class="ws-grid-3">
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile1.title', 'Territorio')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile1.body', 'Presencia local y comunitaria')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile2.title', 'Personas')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile2.body', 'En el centro del sistema')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-heart"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile3.title', 'Servicios')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile3.body', 'Orientacion y acompanamiento')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile4.title', 'Formacion')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile4.body', 'Desarrollo de competencias')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile5.title', 'Oportunidades')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile5.body', 'Acceso al empleo')); ?></p></article>
                <article class="ws-tile"><span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-globe"></i></span><h3><?php echo esc_html($tr('frontpage.model.tile6.title', 'Plataforma Digital')); ?></h3><p><?php echo esc_html($tr('frontpage.model.tile6.body', 'Tecnologia inclusiva')); ?></p></article>
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
                <a class="ws-btn ws-btn--light" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.collab.cta', 'Quiero colaborar')); ?></a>
            </div>
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.collab.image_alt', 'Colaboracion')); ?>">
        </div>
    </section>

    <section id="contacto" class="ws-section">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2><?php echo esc_html($tr('frontpage.contact.title', 'Contactanos')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('frontpage.contact.lead', 'Estamos aqui para escucharte. Contactanos para mas informacion sobre nuestros servicios o para explorar oportunidades de colaboracion.')); ?></p>
                <ul class="ws-contact-list">
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_address', 'Direccion')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_address', 'Via Populonia 8, Milano, Italia')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_email', 'Email')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_email', 'info@wecoop.it')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_phone', 'Telefono')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_phone', '+39 02 XXXX XXXX')); ?></span></li>
                </ul>
            </div>
            <div class="ws-form-shell">
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
                    <a href="#que-es"><?php echo esc_html($tr('frontpage.footer.col1_link1', 'Que es WECOOP')); ?></a>
                    <a href="#passaparola"><?php echo esc_html($tr('frontpage.footer.col1_link2', 'Passaparola')); ?></a>
                    <a href="#plataforma"><?php echo esc_html($tr('frontpage.footer.col1_link3', 'Plataforma Digital')); ?></a>
                    <a href="#impacto"><?php echo esc_html($tr('frontpage.footer.col1_link4', 'Impacto')); ?></a>
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
                    <span><?php echo esc_html($tr('frontpage.contact.value_email', 'info@wecoop.it')); ?></span>
                    <span><?php echo esc_html($tr('frontpage.contact.value_phone', '+39 02 XXXX XXXX')); ?></span>
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
