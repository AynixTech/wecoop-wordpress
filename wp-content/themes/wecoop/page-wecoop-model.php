<?php
/**
 * Template Name: Come funziona WECOOP
 */
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('model.aria.page', 'Come funziona WECOOP'));
$wa_num = esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113')));
$wa_msg = esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei capire come posso iniziare il mio percorso.')));
?>

    <!-- HERO -->
    <section class="cw-hero" id="model-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('model.hero.eyebrow', 'Come funziona')); ?></span>
                    <h1><?php echo esc_html($tr('model.hero.title', 'Il percorso WECOOP, passo dopo passo')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('model.hero.subtitle', 'Ti ascoltiamo, ti orientiamo e ti accompagniamo verso il lavoro e l\'autonomia. Un percorso semplice, concreto e umano.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <?php echo esc_html($tr('model.hero.cta1', 'Prenota un appuntamento')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <?php echo esc_html($tr('model.hero.cta2', 'Scrivici su WhatsApp')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('model.hero.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-ear-listen"></i><span><?php echo esc_html($tr('model.hero.badge1', 'Ascolto')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-compass"></i><span><?php echo esc_html($tr('model.hero.badge2', 'Orientamento')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-graduation-cap"></i><span><?php echo esc_html($tr('model.hero.badge3', 'Formazione')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-briefcase"></i><span><?php echo esc_html($tr('model.hero.badge4', 'Lavoro')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- IL PERCORSO: 6 STEPS -->
    <section class="ws-section ws-section--soft" id="model-percorso">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('model.percorso.eyebrow', 'Il percorso')); ?></span>
                <h2><?php echo esc_html($tr('model.percorso.title', 'Come ti aiutiamo')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('model.percorso.lead', 'Sei passaggi concreti per accompagnarti dall\'ascolto al lavoro.')); ?></p>
            </div>
            <div class="ws-grid-3">
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-ear-listen"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step1.title', '1. Ti ascoltiamo')); ?></h3>
                        <p><?php echo esc_html($tr('model.step1.body', 'Capiamo la tua situazione, i tuoi bisogni e le tue aspettative senza giudicarti.')); ?></p>
                    </div>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-compass"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step2.title', '2. Ti orientiamo')); ?></h3>
                        <p><?php echo esc_html($tr('model.step2.body', 'Ti spieghiamo le possibilita, i diritti e i percorsi disponibili per la tua situazione.')); ?></p>
                    </div>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-hand-holding-heart"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step3.title', '3. Ti aiutiamo')); ?></h3>
                        <p><?php echo esc_html($tr('model.step3.body', 'Ti supportiamo con documenti, pratiche burocratiche, servizi e strumenti concreti.')); ?></p>
                    </div>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step4.title', '4. Ti formiamo')); ?></h3>
                        <p><?php echo esc_html($tr('model.step4.body', 'Sviluppi competenze pratiche e digitali utili per il mondo del lavoro italiano.')); ?></p>
                    </div>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step5.title', '5. Ti accompagniamo al lavoro')); ?></h3>
                        <p><?php echo esc_html($tr('model.step5.body', 'Ti aiutiamo a candidarti, prepararti ai colloqui e trovare opportunita concrete.')); ?></p>
                    </div>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-seedling"></i></span>
                    <div class="ws-tile__content">
                        <h3><?php echo esc_html($tr('model.step6.title', '6. Ti supportiamo a lungo termine')); ?></h3>
                        <p><?php echo esc_html($tr('model.step6.body', 'Restiamo vicini anche dopo il primo inserimento lavorativo, per aiutarti a crescere.')); ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- COSA SUCCEDE DOPO -->
    <section class="ws-section cw-institutional" id="model-dopo">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('model.dopo.eyebrow', 'Dopo il primo incontro')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('model.dopo.title', 'Cosa succede dopo il primo incontro')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('model.dopo.body', 'Dopo il primo colloquio, costruiamo insieme un piano personalizzato. Non esiste un percorso uguale per tutti: ascoltiamo la tua storia e definiamo insieme i passi successivi.')); ?></p>
                    <ul class="ws-check-list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.dopo.item1', 'Ricevi un piano di accompagnamento personalizzato')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.dopo.item2', 'Vieni seguito da un operatore di riferimento')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.dopo.item3', 'Hai accesso alla piattaforma digitale WECOOP')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.dopo.item4', 'Puoi tornare in ogni momento per nuove necessita')); ?></li>
                    </ul>
                    <div class="ws-highlight-box">
                        <i class="fa-solid fa-quote-left" aria-hidden="true"></i>
                        <p><?php echo esc_html($tr('model.dopo.quote', 'Non importa da dove parti: importa che tu non sia solo nel percorso.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- UN UNICO PUNTO DI ACCESSO -->
    <section class="ws-section ws-section--soft" id="model-accesso">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('model.accesso.eyebrow', 'Un punto unico')); ?></span>
                <h2><?php echo esc_html($tr('model.accesso.title', 'Un unico punto di accesso a tutto')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('model.accesso.lead', 'Con WECOOP non devi cercare tanti sportelli diversi. Qui trovi tutto quello di cui hai bisogno.')); ?></p>
            </div>
            <div class="ws-grid-3">
                <div class="ws-feature-item">
                    <i class="fa-solid fa-passport" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item1', 'Documenti e permessi di soggiorno')); ?></p>
                </div>
                <div class="ws-feature-item">
                    <i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item2', 'Servizi fiscali e dichiarazioni')); ?></p>
                </div>
                <div class="ws-feature-item">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item3', 'Ricerca lavoro e candidature')); ?></p>
                </div>
                <div class="ws-feature-item">
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item4', 'Partita IVA e attivita in proprio')); ?></p>
                </div>
                <div class="ws-feature-item">
                    <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item5', 'Formazione e competenze digitali')); ?></p>
                </div>
                <div class="ws-feature-item">
                    <i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i>
                    <p><?php echo esc_html($tr('model.accesso.item6', 'Accesso al credito e microcredito')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- COME ACCEDERE: fisico + digitale -->
    <section class="ws-section" id="model-phygital">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('model.phygital.eyebrow', 'Come accedere')); ?></span>
                <h2><?php echo esc_html($tr('model.phygital.title', 'Fisico e digitale, sempre')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('model.phygital.lead', 'Puoi incontrarci di persona oppure usare la piattaforma digitale: il supporto e sempre disponibile.')); ?></p>
            </div>
            <div class="ws-grid-2 ws-phygital-grid">
                <article class="ws-phygital-card ws-phygital-card--physical">
                    <span class="ws-phygital-card__icon" aria-hidden="true"><i class="fa-regular fa-building"></i></span>
                    <h3><?php echo esc_html($tr('model.phygital.physical.title', 'Sportello fisico')); ?></h3>
                    <p><?php echo esc_html($tr('model.phygital.physical.lead', 'Vieni al nostro sportello per:')); ?></p>
                    <ul class="ws-phygital-list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.physical.item1', 'Parlare con un operatore')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.physical.item2', 'Avere aiuto con i documenti')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.physical.item3', 'Partecipare a corsi di formazione')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.physical.item4', 'Ricevere supporto personalizzato')); ?></li>
                    </ul>
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <?php echo esc_html($tr('model.phygital.physical.cta', 'Prenota un appuntamento')); ?>
                    </a>
                </article>
                <article class="ws-phygital-card ws-phygital-card--digital">
                    <span class="ws-phygital-card__icon" aria-hidden="true"><i class="fa-solid fa-laptop"></i></span>
                    <h3><?php echo esc_html($tr('model.phygital.digital.title', 'Piattaforma digitale')); ?></h3>
                    <p><?php echo esc_html($tr('model.phygital.digital.lead', 'Usa la nostra app per:')); ?></p>
                    <ul class="ws-phygital-list">
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.digital.item1', 'Gestire i tuoi documenti')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.digital.item2', 'Cercare offerte di lavoro')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.digital.item3', 'Seguire corsi online')); ?></li>
                        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><?php echo esc_html($tr('model.phygital.digital.item4', 'Restare in contatto con il tuo operatore')); ?></li>
                    </ul>
                    <a class="ws-btn cw-btn--ghost" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <?php echo esc_html($tr('model.phygital.digital.cta', 'Scrivici su WhatsApp')); ?>
                    </a>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="ws-section ws-section--cta" id="model-cta">
        <div class="ws-container">
            <div class="ws-section-cta ws-section-cta--center">
                <h2><?php echo esc_html($tr('model.cta.title', 'Inizia il tuo percorso con WECOOP')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('model.cta.lead', 'Il primo passo e il piu importante. Contattaci oggi, senza impegno.')); ?></p>
                <div class="ws-hero-ctas">
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <?php echo esc_html($tr('model.cta.cta1', 'Prenota un appuntamento')); ?>
                    </a>
                    <a class="ws-btn cw-btn--ghost" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <?php echo esc_html($tr('model.cta.cta2', 'Scrivici su WhatsApp')); ?>
                    </a>
                </div>
                <p class="ws-microcopy">
                    <i class="fa-solid fa-shoe-prints" aria-hidden="true"></i>
                    <?php echo esc_html($tr('model.cta.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare, passo dopo passo.')); ?>
                </p>
            </div>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
