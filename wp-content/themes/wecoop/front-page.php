<?php
get_header();
?>

<main class="wecoop-main wecoop-figma-home" aria-label="WECOOP homepage">
    <section id="home" class="wf-hero">
        <div class="wf-hero__content">
            <p class="wf-eyebrow">Ecosistema WECOOP</p>
            <h1>Inclusione sociale e opportunita concrete con un modello territoriale + digitale</h1>
            <p class="wf-lead">WECOOP connette persone, servizi, formazione e lavoro attraverso un hub locale, una piattaforma digitale accessibile e una rete di partner attivi.</p>
            <div class="wf-actions">
                <a class="wf-btn wf-btn--primary" href="<?php echo esc_url(home_url('/wecoop-model/')); ?>">Scopri il modello</a>
                <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">Collabora con noi</a>
            </div>
            <span class="wf-kpi">400+ persone accompagnate nel percorso WECOOP</span>
            <p class="wf-inline-logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP logo">
            </p>
        </div>
        <figure class="wf-hero__media">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_diverse_people_connecting_through_technology_in_a_modern_community_hub,_smartphones_a_903873.png'); ?>" alt="Comunita connessa WECOOP">
        </figure>
    </section>

    <section class="wf-section wf-linkbar" aria-label="Navigazione rapida homepage">
        <a href="#model">Modello</a>
        <a href="#passaparola">Passaparola</a>
        <a href="#app">Piattaforma</a>
        <a href="#impact">Impatto</a>
        <a href="#partners">Partner</a>
        <a href="#contact">Contatti</a>
    </section>

    <section id="model" class="wf-section">
        <p class="wf-eyebrow">Il modello WECOOP</p>
        <h2>Un sistema integrato che parte dal territorio</h2>
        <p class="wf-lead">Centro territoriale, accompagnamento, formazione, opportunita lavorative e app WECOOP in un unico percorso.</p>
        <div class="wf-grid wf-grid--3">
            <article class="wf-card">
                <h3>Centro territoriale</h3>
                <p>Accoglienza, orientamento e presa in carico con operatori dedicati.</p>
            </article>
            <article class="wf-card">
                <h3>Formazione</h3>
                <p>Percorsi pratici su competenze professionali, digitali e trasversali.</p>
            </article>
            <article class="wf-card">
                <h3>Inserimento</h3>
                <p>Connessione con partner e imprese per opportunita di lavoro reali.</p>
            </article>
        </div>
        <p class="wf-band">Obiettivo: ridurre le barriere di accesso ai servizi e trasformare i percorsi in risultati misurabili.</p>
    </section>

    <section id="passaparola" class="wf-section">
        <p class="wf-eyebrow">Progetto PASSAPAROLA</p>
        <h2>Persone, connessioni e opportunita</h2>
        <div class="wf-grid wf-grid--4">
            <article class="wf-card">
                <h3>Problema</h3>
                <p>Difficolta di accesso a servizi e opportunita per persone in condizione di fragilita.</p>
            </article>
            <article class="wf-card">
                <h3>Soluzione</h3>
                <p>Accompagnamento personalizzato e connessione con rete territoriale e strumenti digitali.</p>
            </article>
            <article class="wf-card">
                <h3>Attivita</h3>
                <p>Orientamento, mediazione, formazione e supporto continuo lungo tutto il percorso.</p>
            </article>
            <article class="wf-card">
                <h3>Impatto</h3>
                <p>Migliore autonomia personale, inclusione sociale e accesso al lavoro.</p>
            </article>
        </div>
        <div class="wf-grid wf-grid--2">
            <figure class="wf-media-card">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_team_of_professionals_discussing_project_planning_around_a_table_with_laptop,_documen_771206.png'); ?>" alt="Team progetto Passaparola">
            </figure>
            <div>
                <p class="wf-lead">Il progetto supporta persone in condizione di vulnerabilita con orientamento, mediazione e accesso ai servizi.</p>
                <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/passaparola-project/')); ?>">Vai alla pagina progetto</a>
            </div>
        </div>
    </section>

    <section id="app" class="wf-section">
        <p class="wf-eyebrow">Piattaforma digitale</p>
        <h2>APP WECOOP: servizi e monitoraggio in tempo reale</h2>
        <div class="wf-grid wf-grid--2 wf-contact__grid">
            <div>
                <p class="wf-lead">Prenotazioni, notifiche, comunicazione con operatori e avanzamento del percorso in un ambiente semplice e mobile-first.</p>
                <ul class="wf-steps">
                    <li>Registrazione</li>
                    <li>Accesso servizi</li>
                    <li>Prenota appuntamenti</li>
                    <li>Partecipa a formazione</li>
                    <li>Comunica con operatori</li>
                    <li>Monitora obiettivi</li>
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
        <p class="wf-eyebrow">Impatto sociale</p>
        <h2>Risultati misurabili sul territorio</h2>
        <div class="wf-grid wf-grid--4">
            <div class="wf-stat"><strong>400+</strong>Beneficiari</div>
            <div class="wf-stat"><strong>150+</strong>Percorsi formativi</div>
            <div class="wf-stat"><strong>80+</strong>Inserimenti lavorativi</div>
            <div class="wf-stat"><strong>300+</strong>Utenti piattaforma</div>
        </div>
    </section>

    <section id="partners" class="wf-section wf-partners">
        <p class="wf-eyebrow">Partner network</p>
        <h2>Una rete collaborativa in crescita</h2>
        <div class="wf-partners__logos">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_2.png'); ?>" alt="Partner AYNIX">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="PASSAPAROLA">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_5.png'); ?>" alt="Collaborazione territoriale">
        </div>
        <p class="wf-actions">
            <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/partners/')); ?>">Vedi tutti i partner</a>
        </p>
    </section>

    <section id="contact" class="wf-section wf-contact">
        <p class="wf-eyebrow">Contatti</p>
        <h2>Costruiamo insieme il prossimo passo</h2>
        <div class="wf-grid wf-grid--2 wf-contact__grid">
            <div class="wf-contact__info">
                <p class="wf-lead">WECOOP - Via Populonia 8, Milano. Scrivici per collaborazioni, attivazione servizi o coprogettazione.</p>
                <div class="wf-links__list">
                    <a href="mailto:info@wecoop.org">info@wecoop.org</a>
                    <a href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">Collabora con noi</a>
                </div>
            </div>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </div>
    </section>

    <section class="wf-section">
        <p class="wf-eyebrow">Call to action</p>
        <h2>Sei un ente, un partner o un professionista?</h2>
        <p class="wf-lead">Attiviamo insieme una collaborazione concreta per generare impatto locale.</p>
        <div class="wf-actions">
            <a class="wf-btn wf-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>">Avvia una collaborazione</a>
            <a class="wf-btn wf-btn--ghost" href="<?php echo esc_url(home_url('/contact/')); ?>">Parla con il team</a>
        </div>
    </section>
</main>

<?php
get_footer();
