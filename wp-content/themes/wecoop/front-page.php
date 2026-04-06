<?php
get_header();
?>

<main class="wecoop-main wecoop-figma-home" aria-label="WECOOP homepage">
    <section id="inicio" class="wf-hero">
        <div class="wf-hero__content">
            <p class="wf-eyebrow">Ecosistema WECOOP</p>
            <h1>Un ecosistema de inclusion y oportunidades</h1>
            <p class="wf-lead">WECOOP integra servicios territoriales, formacion profesional y tecnologia digital para conectar personas vulnerables con oportunidades de empleo y desarrollo personal.</p>
            <div class="wf-actions">
                <a class="wf-btn wf-btn--primary" href="#que-es">Descubre mas</a>
                <a class="wf-btn wf-btn--ghost" href="#contacto">Contactanos</a>
            </div>
            <span class="wf-kpi">400+ beneficiarios</span>
            <p class="wf-inline-logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP logo">
            </p>
        </div>
        <figure class="wf-hero__media">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_diverse_people_connecting_through_technology_in_a_modern_community_hub,_smartphones_a_903873.png'); ?>" alt="Comunita connessa WECOOP">
        </figure>
    </section>

    <section class="wf-section wf-linkbar" aria-label="Navigazione rapida homepage">
        <a href="#que-es">Que es WECOOP</a>
        <a href="#model">Modelo</a>
        <a href="#passaparola">Passaparola</a>
        <a href="#app">Plataforma</a>
        <a href="#impact">Impacto</a>
        <a href="#partners">Partner</a>
        <a href="#colabora">Colabora</a>
        <a href="#contacto">Contacto</a>
    </section>

    <section id="que-es" class="wf-section">
        <p class="wf-eyebrow">Que es WECOOP</p>
        <h2>Un modelo innovador fisico + digital</h2>
        <p class="wf-lead">Combinamos centro territorial, orientacion, formacion, oportunidades laborales y plataforma digital integrada.</p>
        <div class="wf-grid wf-grid--3">
            <article class="wf-card">
                <h3>Centro Territorial</h3>
                <p>Espacio de referencia con acompanamiento personalizado y acceso a servicios de inclusion social.</p>
            </article>
            <article class="wf-card">
                <h3>Formacion y Empleo</h3>
                <p>Recorridos formativos, desarrollo de competencias y conexion con oportunidades laborales reales.</p>
            </article>
            <article class="wf-card">
                <h3>Plataforma Digital</h3>
                <p>Tecnologia accesible para gestionar citas, formacion, comunicacion con operadores y seguimiento.</p>
            </article>
        </div>
    </section>

    <section id="model" class="wf-section">
        <p class="wf-eyebrow">El Modelo Fisico + Digital</p>
        <h2>Un ecosistema integrado que conecta territorio, personas y tecnologia</h2>
        <p class="wf-lead">Un sistema completo para reducir barreras de acceso a servicios y convertir recorridos en resultados medibles.</p>
        <div class="wf-grid wf-grid--3">
            <article class="wf-card">
                <h3>Territorio</h3>
                <p>Presencia local y comunitaria.</p>
            </article>
            <article class="wf-card">
                <h3>Personas</h3>
                <p>En el centro del sistema.</p>
            </article>
            <article class="wf-card">
                <h3>Servicios</h3>
                <p>Orientacion y acompanamiento continuo.</p>
            </article>
            <article class="wf-card">
                <h3>Formacion</h3>
                <p>Desarrollo de competencias profesionales.</p>
            </article>
            <article class="wf-card">
                <h3>Oportunidades</h3>
                <p>Acceso concreto al empleo.</p>
            </article>
            <article class="wf-card">
                <h3>Plataforma Digital</h3>
                <p>Tecnologia inclusiva y accesible.</p>
            </article>
        </div>
        <p class="wf-band">Objetivo: inclusion social efectiva, autonomia personal y acceso real al mercado laboral.</p>
    </section>

    <section id="passaparola" class="wf-section">
        <p class="wf-eyebrow">Proyecto Passaparola</p>
        <h2>Personas, conexiones y oportunidades</h2>
        <div class="wf-grid wf-grid--4">
            <article class="wf-card">
                <h3>Problema</h3>
                <p>Dificultad de acceso a servicios y oportunidades para personas migrantes y vulnerables.</p>
            </article>
            <article class="wf-card">
                <h3>Solucion</h3>
                <p>Sistema integrado de orientacion, formacion y conexion con oportunidades laborales.</p>
            </article>
            <article class="wf-card">
                <h3>Actividades</h3>
                <p>Orientacion personalizada, formacion profesional, insercion laboral y seguimiento continuo.</p>
            </article>
            <article class="wf-card">
                <h3>Impatto</h3>
                <p>Inclusion social efectiva, autonomia personal y acceso real al mercado laboral.</p>
            </article>
        </div>
        <div class="wf-grid wf-grid--2">
            <figure class="wf-media-card">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png'); ?>" alt="Team progetto Passaparola">
            </figure>
            <div>
                <p class="wf-lead">Proyecto dedicado al apoyo integral de personas migrantes y en situacion de vulnerabilidad.</p>
                <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/passaparola-project/')); ?>">Ver proyecto</a>
            </div>
        </div>
    </section>

    <section id="app" class="wf-section">
        <p class="wf-eyebrow">Plataforma Digital</p>
        <h2>APP WECOOP: servicios y seguimiento en tiempo real</h2>
        <div class="wf-grid wf-grid--2 wf-contact__grid">
            <div>
                <p class="wf-lead">Desarrollada con nuestro partner tecnologico AYNIX, APP WECOOP democratiza el acceso a servicios y oportunidades con una experiencia intuitiva.</p>
                <h3 class="wf-subtitle">Como funciona el sistema</h3>
                <ul class="wf-steps">
                    <li>Registro</li>
                    <li>Acceso a servicios</li>
                    <li>Reserva de citas</li>
                    <li>Formacion</li>
                    <li>Comunicacion</li>
                    <li>Seguimiento</li>
                </ul>
            </div>
            <div class="wf-grid wf-grid--2">
                <figure class="wf-media-card">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="Persona che usa app WECOOP">
                </figure>
                <figure class="wf-media-card">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="Accesso ai servizi WECOOP da smartphone e laptop">
                </figure>
            </div>
        </div>
    </section>

    <section id="impact" class="wf-section">
        <p class="wf-eyebrow">Nuestro Impacto Social</p>
        <h2>Resultados medibles de un modelo que transforma vidas</h2>
        <div class="wf-grid wf-grid--4">
            <div class="wf-stat"><strong>400+</strong>Beneficiarios</div>
            <div class="wf-stat"><strong>150+</strong>Recorridos formativos</div>
            <div class="wf-stat"><strong>80+</strong>Inserciones laborales</div>
            <div class="wf-stat"><strong>300+</strong>Usuarios plataforma</div>
        </div>
    </section>

    <section id="partners" class="wf-section wf-partners">
        <p class="wf-eyebrow">Red de Partners</p>
        <h2>Colaboramos con instituciones, organizaciones y empresas</h2>
        <div class="wf-partners__logos">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_2.png'); ?>" alt="Partner AYNIX">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="PASSAPAROLA">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_5.png'); ?>" alt="Collaborazione territoriale">
        </div>
        <p class="wf-actions">
            <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/partners/')); ?>">Ver todos los partners</a>
        </p>
    </section>

    <section id="colabora" class="wf-section">
        <p class="wf-eyebrow">Colabora con WECOOP</p>
        <h2>Unete a nuestra red y crea oportunidades de inclusion</h2>
        <div class="wf-grid wf-grid--4">
            <article class="wf-card">
                <h3>Instituciones Publicas</h3>
                <p>Alianzas estrategicas para ampliar impacto territorial.</p>
            </article>
            <article class="wf-card">
                <h3>Empresas</h3>
                <p>Oportunidades de empleo, formacion y RSC con impacto real.</p>
            </article>
            <article class="wf-card">
                <h3>Fundaciones</h3>
                <p>Apoyo a proyectos de inclusion social y laboral sostenibles.</p>
            </article>
            <article class="wf-card">
                <h3>Voluntarios</h3>
                <p>Comparte tiempo y talento para fortalecer la comunidad WECOOP.</p>
            </article>
        </div>
        <div class="wf-actions">
            <a class="wf-btn wf-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">Quiero colaborar</a>
        </div>
    </section>

    <section id="contacto" class="wf-section wf-contact">
        <p class="wf-eyebrow">Contactanos</p>
        <h2>Estamos aqui para escucharte</h2>
        <div class="wf-grid wf-grid--2 wf-contact__grid">
            <div class="wf-contact__info">
                <p class="wf-lead">Contactanos para mas informacion sobre nuestros servicios o para explorar oportunidades de colaboracion.</p>
                <div class="wf-links__list">
                    <a href="#">Via Populonia 8, Milano</a>
                    <a href="mailto:info@wecoop.it">info@wecoop.it</a>
                    <a href="tel:+390200000000">+39 02 XXXX XXXX</a>
                </div>
            </div>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </div>
    </section>

    <section class="wf-section wf-mini-footer">
        <div class="wf-grid wf-grid--3">
            <div>
                <h3>WECOOP</h3>
                <div class="wf-links__list">
                    <a href="#que-es">Que es WECOOP</a>
                    <a href="#passaparola">Passaparola</a>
                    <a href="#app">Plataforma Digital</a>
                    <a href="#impact">Impacto</a>
                </div>
            </div>
            <div>
                <h3>Colabora</h3>
                <div class="wf-links__list">
                    <a href="#colabora">Empresas</a>
                    <a href="#colabora">Instituciones</a>
                    <a href="#colabora">Fundaciones</a>
                    <a href="#colabora">Voluntarios</a>
                </div>
            </div>
            <div>
                <h3>Contacto</h3>
                <div class="wf-links__list">
                    <a href="mailto:info@wecoop.it">info@wecoop.it</a>
                    <a href="tel:+390200000000">+39 02 XXXX XXXX</a>
                </div>
            </div>
        </div>
        <p class="wf-lead">&copy; 2026 WECOOP. Todos los derechos reservados.</p>
    </section>
</main>

<?php
get_footer();
