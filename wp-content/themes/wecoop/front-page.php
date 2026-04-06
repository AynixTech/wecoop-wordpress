<?php
get_header();
?>

<main class="ws-site" aria-label="WECOOP homepage">
    <nav class="ws-nav">
        <div class="ws-container ws-nav__inner">
            <a class="ws-brand" href="#inicio">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                <span>WECOOP</span>
            </a>
            <div class="ws-links" aria-label="Main navigation">
                <a href="#que-es">Que es WECOOP</a>
                <a href="#passaparola">Passaparola</a>
                <a href="#plataforma">Plataforma Digital</a>
                <a href="#impacto">Impacto</a>
                <a href="#contacto">Contacto</a>
            </div>
            <a class="ws-btn ws-btn--primary" href="#contacto">Colabora</a>
        </div>
    </nav>

    <section id="inicio" class="ws-hero">
        <div class="ws-container ws-grid-2 ws-hero__grid">
            <div>
                <h1>Un ecosistema de <span>inclusion</span> y <span>oportunidades</span></h1>
                <p>WECOOP integra servicios territoriales, formacion profesional y tecnologia digital para conectar personas vulnerables con oportunidades de empleo y desarrollo personal.</p>
                <div class="ws-actions">
                    <a class="ws-btn ws-btn--primary" href="#que-es">Descubre mas</a>
                    <a class="ws-btn ws-btn--ghost" href="#contacto">Contactanos</a>
                </div>
                <div class="ws-kpi"><strong>400+</strong> Beneficiarios</div>
            </div>
            <div class="ws-hero__media">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecoop-hero-community.png'); ?>" alt="Comunidad WECOOP">
            </div>
        </div>
    </section>

    <section id="que-es" class="ws-section">
        <div class="ws-container">
            <h2>Que es WECOOP?</h2>
            <p class="ws-lead">Un modelo innovador que combina un centro territorial, servicios de orientacion, formacion, oportunidades laborales y una plataforma digital integrada.</p>
            <div class="ws-grid-3">
                <article class="ws-card ws-card--blue">
                    <h3>Centro Territorial</h3>
                    <p>Un espacio fisico de referencia donde las personas encuentran orientacion, acompanamiento personalizado y acceso a servicios de inclusion social.</p>
                </article>
                <article class="ws-card ws-card--green">
                    <h3>Formacion y Empleo</h3>
                    <p>Recorridos formativos personalizados, desarrollo de competencias profesionales y conexion directa con oportunidades laborales reales.</p>
                </article>
                <article class="ws-card ws-card--pink">
                    <h3>Plataforma Digital</h3>
                    <p>Tecnologia accesible que permite gestionar citas, acceder a formacion, comunicarse con operadores y hacer seguimiento del recorrido personal.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="modelo" class="ws-section ws-section--soft">
        <div class="ws-container">
            <h2>El Modelo Fisico + Digital</h2>
            <p class="ws-lead">Un ecosistema integrado que conecta territorio, personas, servicios y tecnologia.</p>
            <div class="ws-grid-3">
                <article class="ws-tile"><h3>Territorio</h3><p>Presencia local y comunitaria</p></article>
                <article class="ws-tile"><h3>Personas</h3><p>En el centro del sistema</p></article>
                <article class="ws-tile"><h3>Servicios</h3><p>Orientacion y acompanamiento</p></article>
                <article class="ws-tile"><h3>Formacion</h3><p>Desarrollo de competencias</p></article>
                <article class="ws-tile"><h3>Oportunidades</h3><p>Acceso al empleo</p></article>
                <article class="ws-tile"><h3>Plataforma Digital</h3><p>Tecnologia inclusiva</p></article>
            </div>
        </div>
    </section>

    <section id="passaparola" class="ws-section">
        <div class="ws-container">
            <div class="ws-grid-2 ws-pass">
                <div>
                    <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <h2>Proyecto Passaparola</h2>
                    <p class="ws-lead">Persone, connessioni e opportunita: un proyecto dedicado al apoyo integral de personas migrantes y en situacion de vulnerabilidad.</p>
                </div>
                <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png'); ?>" alt="Equipo Passaparola">
            </div>
            <div class="ws-grid-4">
                <article class="ws-mini ws-mini--pink"><h3>Problema</h3><p>Dificultad de acceso a servicios y oportunidades para personas migrantes y vulnerables.</p></article>
                <article class="ws-mini ws-mini--blue"><h3>Solucion</h3><p>Sistema integrado de orientacion, formacion y conexion con oportunidades laborales.</p></article>
                <article class="ws-mini ws-mini--green"><h3>Actividades</h3><p>Orientacion personalizada, formacion profesional, insercion laboral y seguimiento continuo.</p></article>
                <article class="ws-mini ws-mini--lime"><h3>Impacto</h3><p>Inclusion social efectiva, autonomia personal y acceso real al mercado laboral.</p></article>
            </div>
        </div>
    </section>

    <section id="plataforma" class="ws-section ws-section--soft-blue">
        <div class="ws-container">
            <div class="ws-grid-2 ws-pass">
                <div>
                    <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
                    <h2>Plataforma Digital</h2>
                    <p class="ws-lead">Desarrollada en colaboracion con nuestro partner tecnologico AYNIX, la plataforma APP WECOOP democratiza el acceso a servicios y oportunidades a traves de una solucion digital intuitiva y accesible.</p>
                    <ul class="ws-checks">
                        <li>Registro y perfil personalizado</li>
                        <li>Reserva de citas con operadores</li>
                        <li>Acceso a formacion y recursos</li>
                        <li>Comunicacion directa y seguimiento</li>
                    </ul>
                </div>
                <div class="ws-grid-2 ws-media-grid">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="App movil">
                    <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="Plataforma digital">
                    <img class="ws-img ws-img--wide" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png'); ?>" alt="Gestion de servicios">
                </div>
            </div>
        </div>
    </section>

    <section id="impacto" class="ws-section">
        <div class="ws-container">
            <h2>Nuestro Impacto Social</h2>
            <p class="ws-lead">Resultados medibles de un modelo que transforma vidas y genera oportunidades reales.</p>
            <div class="ws-grid-4">
                <article class="ws-stat"><strong>400+</strong><span>Beneficiarios</span></article>
                <article class="ws-stat"><strong>150+</strong><span>Recorridos Formativos</span></article>
                <article class="ws-stat"><strong>80+</strong><span>Inserciones Laborales</span></article>
                <article class="ws-stat"><strong>300+</strong><span>Usuarios Plataforma</span></article>
            </div>
        </div>
    </section>

    <section id="partners" class="ws-section ws-section--gray">
        <div class="ws-container">
            <h2>Red de Partners</h2>
            <p class="ws-lead">Colaboramos con instituciones, organizaciones y empresas comprometidas con la inclusion social.</p>
            <div class="ws-partners-box">
                <div class="ws-partner-tech">
                    <strong>Partner Tecnologico</strong>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_2.png'); ?>" alt="AYNIX">
                </div>
                <div class="ws-partner-placeholder"></div>
                <div class="ws-partner-placeholder"></div>
                <div class="ws-partner-placeholder"></div>
                <div class="ws-partner-placeholder"></div>
                <div class="ws-partner-placeholder"></div>
                <div class="ws-partner-placeholder"></div>
            </div>
        </div>
    </section>

    <section id="colabora" class="ws-section ws-section--cta">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2>Colabora con WECOOP</h2>
                <p class="ws-lead ws-lead--light">Unete a nuestra red de partners y contribuye a crear oportunidades de inclusion social y laboral.</p>
                <div class="ws-grid-2">
                    <article class="ws-glass"><h3>Instituciones Publicas</h3><p>Alianzas estrategicas para ampliar el impacto</p></article>
                    <article class="ws-glass"><h3>Empresas</h3><p>Oportunidades de empleo y RSC</p></article>
                    <article class="ws-glass"><h3>Fundaciones</h3><p>Apoyo a proyectos de inclusion</p></article>
                    <article class="ws-glass"><h3>Voluntarios</h3><p>Comparte tu tiempo y talento</p></article>
                </div>
                <a class="ws-btn ws-btn--light" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">Quiero colaborar</a>
            </div>
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="Colaboracion">
        </div>
    </section>

    <section id="contacto" class="ws-section">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2>Contactanos</h2>
                <p class="ws-lead">Estamos aqui para escucharte. Contactanos para mas informacion sobre nuestros servicios o para explorar oportunidades de colaboracion.</p>
                <ul class="ws-contact-list">
                    <li><strong>Direccion</strong><span>Via Populonia 8, Milano, Italia</span></li>
                    <li><strong>Email</strong><span>info@wecoop.it</span></li>
                    <li><strong>Telefono</strong><span>+39 02 XXXX XXXX</span></li>
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
                    <p>Un ecosistema de inclusion y oportunidades para todos.</p>
                </div>
                <div>
                    <h4>WECOOP</h4>
                    <a href="#que-es">Que es WECOOP</a>
                    <a href="#passaparola">Passaparola</a>
                    <a href="#plataforma">Plataforma Digital</a>
                    <a href="#impacto">Impacto</a>
                </div>
                <div>
                    <h4>Colabora</h4>
                    <a href="#colabora">Empresas</a>
                    <a href="#colabora">Instituciones</a>
                    <a href="#colabora">Fundaciones</a>
                    <a href="#colabora">Voluntarios</a>
                </div>
                <div>
                    <h4>Contacto</h4>
                    <span>Via Populonia 8, Milano</span>
                    <span>info@wecoop.it</span>
                    <span>+39 02 XXXX XXXX</span>
                </div>
            </div>
            <div class="ws-footer-bottom">
                <p>&copy; 2026 WECOOP. Todos los derechos reservados.</p>
                <div class="ws-footer-brands">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
                </div>
            </div>
        </div>
    </footer>
</main>

<?php
get_footer();
