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
            <div class="ws-links" aria-label="<?php echo esc_attr($tr('frontpage.nav.main_aria', 'Main navigation')); ?>">
                <a href="#que-es"><?php echo esc_html($tr('frontpage.nav.about', $lang_default('Cos\'e WECOOP', 'What is WECOOP', 'Que es WECOOP'))); ?></a>
                <a href="#servizi"><?php echo esc_html($tr('frontpage.nav.services', $lang_default('Servizi', 'Services', 'Servicios'))); ?></a>
                <a href="#come-funziona"><?php echo esc_html($tr('frontpage.nav.how', $lang_default('Come funziona', 'How it works', 'Como funciona'))); ?></a>
                <a href="<?php echo esc_url($job_offers_url); ?>"><?php echo esc_html($tr('frontpage.nav.jobs', $lang_default('Offerte di lavoro', 'Job Offers', 'Ofertas de trabajo'))); ?></a>
                <a href="#passaparola"><?php echo esc_html($tr('frontpage.nav.passaparola', $lang_default('Passaparola', 'Passaparola', 'Passaparola'))); ?></a>
                <a href="#plataforma"><?php echo esc_html($tr('frontpage.nav.platform', $lang_default('Piattaforma Digitale', 'Digital Platform', 'Plataforma Digital'))); ?></a>
                <a href="#impacto"><?php echo esc_html($tr('frontpage.nav.impact', $lang_default('Impatto', 'Impact', 'Impacto'))); ?></a>
                <a href="#contacto"><?php echo esc_html($tr('frontpage.nav.contact', $lang_default('Contatti', 'Contact', 'Contacto'))); ?></a>
            </div>
            <button class="ws-lang-trigger" id="ws-lang-trigger" aria-haspopup="dialog" aria-label="Select language">
                <span class="ws-lang-trigger__flag"><?php echo esc_html(strtoupper($current_lang)); ?></span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($tr('frontpage.nav.cta', 'Collabora')); ?></a>
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

    <section id="sistema-fisico-digitale" class="ws-section ws-section--soft">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('frontpage.phygital.eyebrow', 'Come funziona')); ?></span>
                <h2><?php echo esc_html($tr('frontpage.phygital.title', 'Un sistema fisico + digitale')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('frontpage.phygital.lead', 'WECOOP combina uno sportello sul territorio con una piattaforma digitale per accompagnarti in tutto il percorso.')); ?></p>
            </div>
            <div class="ws-grid-2 ws-phygital-grid">
                <article class="ws-phygital-card ws-phygital-card--physical">
                    <span class="ws-phygital-card__icon" aria-hidden="true"><i class="fa-regular fa-building"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.phygital.physical.title', 'Sportello fisico')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.phygital.physical.lead', 'Un luogo dove puoi venire di persona per:')); ?></p>
                    <ul class="ws-phygital-list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.physical.item1', 'Parlare con un operatore')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.physical.item2', 'Ricevere assistenza personalizzata')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.physical.item3', 'Gestire documenti e pratiche')); ?></li>
                    </ul>
                </article>
                <article class="ws-phygital-card ws-phygital-card--digital">
                    <span class="ws-phygital-card__icon" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <h3><?php echo esc_html($tr('frontpage.phygital.digital.title', 'Piattaforma digitale')); ?></h3>
                    <p><?php echo esc_html($tr('frontpage.phygital.digital.lead', 'Accedi ai servizi online quando vuoi:')); ?></p>
                    <ul class="ws-phygital-list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.digital.item1', 'Prenota appuntamenti')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.digital.item2', 'Segui la tua formazione')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.phygital.digital.item3', 'Monitora il tuo percorso')); ?></li>
                    </ul>
                </article>
            </div>
            <div class="ws-section-cta">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/servizi/')); ?>">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    <?php echo esc_html($tr('frontpage.phygital.cta', 'Scopri di più sui servizi')); ?>
                </a>
            </div>
        </div>
    </section>

    <section id="servizi-modulari" class="ws-section">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('frontpage.detailservices.title', 'I nostri servizi')); ?></h2>
            <div class="ws-services-stack">
                <article class="ws-service-band ws-service-band--blue">
                    <h3><?php echo esc_html($tr('frontpage.detailservices.block1.title', 'Vivere in Italia')); ?></h3>
                    <ul class="ws-service-band__list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block1.item1', 'Permesso di soggiorno')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block1.item2', 'Cittadinanza')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block1.item3', 'Ricongiungimento familiare')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block1.item4', 'Asilo politico')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block1.item5', 'Visto per turismo')); ?></li>
                    </ul>
                </article>

                <article class="ws-service-band ws-service-band--green">
                    <h3><?php echo esc_html($tr('frontpage.detailservices.block2.title', 'Servizi fiscali')); ?></h3>
                    <ul class="ws-service-band__list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item1', 'Modello 730')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item2', 'Modello PF (ex Unico)')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item3', 'Dichiarazioni redditi')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item4', 'Tasse e contributi (IMU, TARI, ecc.)')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item5', 'Consulenza fiscale')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block2.item6', 'Chiarimenti personalizzati')); ?></li>
                    </ul>
                </article>

                <article class="ws-service-band ws-service-band--pink">
                    <h3><?php echo esc_html($tr('frontpage.detailservices.block3.title', 'Partita IVA e contabilita')); ?></h3>
                    <ul class="ws-service-band__list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block3.item1', 'Apertura Partita IVA (regime forfettario)')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block3.item2', 'Gestione contabile')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block3.item3', 'Fatturazione')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block3.item4', 'Chiusura o modifica attivita')); ?></li>
                    </ul>
                </article>

                <article class="ws-service-band ws-service-band--lime">
                    <h3><?php echo esc_html($tr('frontpage.detailservices.block4.title', 'Lavoro e orientamento')); ?></h3>
                    <ul class="ws-service-band__list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block4.item1', 'Creazione CV')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block4.item2', 'Ricerca lavoro')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block4.item3', 'Preparazione colloqui')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block4.item4', 'Orientamento professionale')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block4.item5', 'Supporto candidature')); ?></li>
                    </ul>
                </article>

                <article class="ws-service-band ws-service-band--blue">
                    <h3><?php echo esc_html($tr('frontpage.detailservices.block5.title', 'Educazione finanziaria e accesso al credito')); ?></h3>
                    <ul class="ws-service-band__list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block5.item1', 'Educazione finanziaria di base')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block5.item2', 'Supporto gestione del denaro')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block5.item3', 'Orientamento al credito')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('frontpage.detailservices.block5.item4', 'Supporto accesso a finanziamenti')); ?></li>
                    </ul>
                </article>
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
