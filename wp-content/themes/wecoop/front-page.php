<?php
get_header();

$asset_base = trailingslashit(get_template_directory_uri() . '/assets/img/refactor');
?>

<main class="wecoop-main wecoop-front-page wecoop-figma-home" id="inicio">
    <section class="wf-hero">
        <div class="wf-hero__content">
            <p class="wf-eyebrow">Un ecosistema de inclusion y oportunidades</p>
            <h1>WECOOP integra servicios territoriales, formacion y tecnologia digital.</h1>
            <p>
                Conectamos personas vulnerables con oportunidades reales de empleo,
                acompanamiento y desarrollo personal a traves de un modelo fisico + digital.
            </p>
            <div class="wf-actions">
                <a class="wf-btn wf-btn--primary" href="#que-es">Descubre mas</a>
                <a class="wf-btn wf-btn--ghost" href="#contacto">Contactanos</a>
            </div>
            <div class="wf-kpi">400+ Beneficiarios</div>
        </div>
        <figure class="wf-hero__media">
            <img src="<?php echo esc_url($asset_base . 'Firefly_Gemini_Flash_diverse_people_connecting_through_technology_in_a_modern_community_hub,_smartphones_a_903873.png'); ?>" alt="Comunidad conectada con tecnologia">
        </figure>
    </section>

    <section class="wf-section" id="que-es">
        <h2>Que es WECOOP</h2>
        <p class="wf-lead">
            Un modelo innovador que combina un centro territorial, servicios de orientacion,
            formacion, oportunidades laborales y una plataforma digital integrada.
        </p>
        <div class="wf-grid wf-grid--3">
            <article class="wf-card">
                <h3>Centro Territorial</h3>
                <p>Un espacio fisico de referencia con acompanamiento personalizado y acceso a servicios.</p>
            </article>
            <article class="wf-card">
                <h3>Formacion y Empleo</h3>
                <p>Recorridos formativos y conexion directa con oportunidades laborales reales.</p>
            </article>
            <article class="wf-card">
                <h3>Plataforma Digital</h3>
                <p>Tecnologia accesible para citas, comunicacion, formacion y seguimiento.</p>
            </article>
        </div>
    </section>

    <section class="wf-section" id="modelo">
        <h2>El Modelo Fisico + Digital</h2>
        <p class="wf-lead">Un ecosistema integrado que conecta territorio, personas, servicios y tecnologia.</p>
        <div class="wf-grid wf-grid--6">
            <div class="wf-pill">Territorio</div>
            <div class="wf-pill">Personas</div>
            <div class="wf-pill">Servicios</div>
            <div class="wf-pill">Formacion</div>
            <div class="wf-pill">Oportunidades</div>
            <div class="wf-pill">Plataforma Digital</div>
        </div>
        <figure class="wf-inline-logo">
            <img src="<?php echo esc_url($asset_base . 'Recurso_3@3x.png'); ?>" alt="Passaparola">
        </figure>
    </section>

    <section class="wf-section" id="passaparola">
        <h2>Proyecto Passaparola</h2>
        <p class="wf-lead">Personas, conexiones y oportunidades para quienes enfrentan mayor vulnerabilidad.</p>
        <figure class="wf-wide-image">
            <img src="<?php echo esc_url($asset_base . 'Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png'); ?>" alt="Equipo Passaparola trabajando">
        </figure>
        <div class="wf-grid wf-grid--4">
            <article class="wf-card"><h3>Problema</h3><p>Dificultad de acceso a servicios y oportunidades para personas migrantes y vulnerables.</p></article>
            <article class="wf-card"><h3>Solucion</h3><p>Sistema integrado de orientacion, formacion y conexion con empleo.</p></article>
            <article class="wf-card"><h3>Actividades</h3><p>Orientacion, formacion profesional, insercion laboral y seguimiento continuo.</p></article>
            <article class="wf-card"><h3>Impacto</h3><p>Inclusion social efectiva, autonomia personal y acceso real al mercado laboral.</p></article>
        </div>
    </section>

    <section class="wf-section" id="plataforma">
        <h2>Plataforma Digital</h2>
        <p class="wf-lead">
            Desarrollada con AYNIX, la APP WECOOP democratiza el acceso a servicios y oportunidades.
        </p>
        <div class="wf-grid wf-grid--2">
            <figure class="wf-media-card">
                <img src="<?php echo esc_url($asset_base . 'Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="Uso de app movil WECOOP">
            </figure>
            <figure class="wf-media-card">
                <img src="<?php echo esc_url($asset_base . 'Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="Plataforma digital WECOOP">
            </figure>
        </div>
        <h3 class="wf-subtitle">Como funciona el sistema</h3>
        <ol class="wf-steps">
            <li>Registro</li>
            <li>Acceso a servicios</li>
            <li>Reserva de citas</li>
            <li>Formacion</li>
            <li>Comunicacion</li>
            <li>Seguimiento</li>
        </ol>
    </section>

    <section class="wf-section" id="impacto">
        <h2>Nuestro Impacto Social</h2>
        <p class="wf-lead">Resultados medibles de un modelo que transforma vidas.</p>
        <div class="wf-grid wf-grid--4">
            <article class="wf-stat"><strong>400+</strong><span>Beneficiarios</span></article>
            <article class="wf-stat"><strong>150+</strong><span>Recorridos Formativos</span></article>
            <article class="wf-stat"><strong>80+</strong><span>Inserciones Laborales</span></article>
            <article class="wf-stat"><strong>300+</strong><span>Usuarios Plataforma</span></article>
        </div>
        <div class="wf-partners">
            <h3>Red de Partners</h3>
            <div class="wf-partners__logos">
                <img src="<?php echo esc_url($asset_base . 'Recurso_2.png'); ?>" alt="AYNIX">
                <img src="<?php echo esc_url($asset_base . 'Recurso_3@3x.png'); ?>" alt="Passaparola">
                <img src="<?php echo esc_url($asset_base . 'Recurso_1@3x.png'); ?>" alt="App WECOOP">
                <img src="<?php echo esc_url($asset_base . 'wecooplogo2.png'); ?>" alt="WECOOP">
            </div>
        </div>
    </section>

    <section class="wf-section" id="colabora">
        <h2>Colabora con WECOOP</h2>
        <p class="wf-lead">Unete a nuestra red para crear oportunidades de inclusion social y laboral.</p>
        <div class="wf-grid wf-grid--4">
            <article class="wf-card"><h3>Instituciones Publicas</h3><p>Alianzas estrategicas para ampliar el impacto.</p></article>
            <article class="wf-card"><h3>Empresas</h3><p>Oportunidades de empleo y responsabilidad social.</p></article>
            <article class="wf-card"><h3>Fundaciones</h3><p>Apoyo a proyectos de inclusion e innovacion social.</p></article>
            <article class="wf-card"><h3>Voluntarios</h3><p>Comparte tiempo y talento para fortalecer la comunidad.</p></article>
        </div>
        <div class="wf-cta-row">
            <a class="wf-btn wf-btn--primary" href="#contacto">Quiero colaborar</a>
            <img src="<?php echo esc_url($asset_base . 'Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="Colaboracion comunitaria">
        </div>
    </section>

    <section class="wf-section wf-contact" id="contacto">
        <h2>Contactanos</h2>
        <p class="wf-lead">Estamos aqui para escucharte. Escribenos para informacion o colaboraciones.</p>
        <div class="wf-grid wf-grid--2 wf-contact__grid">
            <div class="wf-contact__info">
                <p><strong>Direccion</strong><br>Via Populonia 8<br>Milano, Italia</p>
                <p><strong>Email</strong><br>info@wecoop.it</p>
                <p><strong>Telefono</strong><br>+39 02 XXXX XXXX</p>
                <img src="<?php echo esc_url($asset_base . 'wecooplogo2.png'); ?>" alt="WECOOP">
            </div>
            <div class="wf-contact__form">
                <?php echo do_shortcode('[wecoop_contact_form]'); ?>
            </div>
        </div>
    </section>

    <section class="wf-section wf-links">
        <h2>Additional Links</h2>
        <div class="wf-links__list">
            <a href="#inicio">WECOOP</a>
            <a href="#que-es">Que es WECOOP</a>
            <a href="#passaparola">Passaparola</a>
            <a href="#plataforma">Plataforma Digital</a>
            <a href="#impacto">Impacto</a>
            <a href="#contacto">Contacto</a>
            <a href="#colabora">Colabora</a>
        </div>
    </section>
</main>

<?php
get_footer();
