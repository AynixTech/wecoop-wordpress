<?php
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('servizi.aria.page', 'Servizi WECOOP'));
$wa_num = esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113')));
$wa_msg = esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei capire come può aiutarmi.')));
?>

    <!-- HERO -->
    <section class="cw-hero" id="servizi-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('servizi.hero.eyebrow', 'I nostri servizi')); ?></span>
                    <h1><?php echo esc_html($tr('servizi.hero.title', 'I servizi WECOOP per vivere, lavorare e crescere in Italia')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('servizi.hero.subtitle', 'Supporto concreto per documenti, lavoro, fiscalità e accesso al credito. Ti accompagniamo passo dopo passo, con un servizio fisico e digitale integrato.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <?php echo esc_html($tr('servizi.hero.cta1', 'Prenota un appuntamento')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <?php echo esc_html($tr('servizi.hero.cta2', 'Scrivici su WhatsApp')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('servizi.hero.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare, passo dopo passo.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-passport"></i><span><?php echo esc_html($tr('servizi.s1.title', 'Vivere in Italia')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-file-invoice-dollar"></i><span><?php echo esc_html($tr('servizi.s2.title', 'Servizi fiscali')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-store"></i><span><?php echo esc_html($tr('servizi.s3.title', 'Partita IVA')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-briefcase"></i><span><?php echo esc_html($tr('servizi.s4.title', 'Lavoro')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTRO -->
    <section class="ws-section cw-institutional" id="servizi-intro">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('servizi.intro.eyebrow', 'Come possiamo aiutarti')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('servizi.intro.title', 'Come possiamo aiutarti')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('servizi.intro.body', 'WECOOP è un punto di accesso unico a servizi fondamentali per la vita in Italia. Che tu debba gestire documenti, trovare lavoro, avviare un\'attività o accedere al credito, ti orientiamo e ti accompagniamo in ogni fase.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5 SERVIZI GRID -->
    <section class="ws-section ws-section--soft cw-whatwedo" id="servizi-grid">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('servizi.grid.eyebrow', 'I servizi')); ?></span>
                <h2><?php echo esc_html($tr('servizi.grid.title', 'Tutti i servizi WECOOP')); ?></h2>
            </div>
            <div class="sv-grid">

                <!-- 1. Vivere in Italia -->
                <article class="sv-card sv-card--blue" id="vivere-in-italia">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-passport" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">01</span>
                            <h3><?php echo esc_html($tr('servizi.s1.title', 'Vivere in Italia')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s1.desc', 'Supporto pratico per orientarti nella vita quotidiana e amministrativa in Italia.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s1.item1', 'Permesso di soggiorno')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s1.item2', 'Cittadinanza')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s1.item3', 'Ricongiungimento familiare')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s1.item4', 'Asilo politico')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s1.item5', 'Visti')); ?></li>
                    </ul>
                </article>

                <!-- 2. Servizi fiscali -->
                <article class="sv-card sv-card--green" id="servizi-fiscali">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">02</span>
                            <h3><?php echo esc_html($tr('servizi.s2.title', 'Servizi fiscali')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s2.desc', 'Mediazione e supporto per comprendere e gestire obblighi fiscali e dichiarazioni.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s2.item1', 'Modello 730')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s2.item2', 'Modello PF (ex Unico)')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s2.item3', 'Tasse e contributi')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s2.item4', 'Mediazione fiscale')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s2.item5', 'Supporto con gli enti')); ?></li>
                    </ul>
                </article>

                <!-- 3. Partita IVA -->
                <article class="sv-card sv-card--pink" id="partita-iva">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-store" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">03</span>
                            <h3><?php echo esc_html($tr('servizi.s3.title', 'Partita IVA')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s3.desc', 'Supporto completo per avviare e gestire un\'attività in regime forfettario.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s3.item1', 'Apertura Partita IVA')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s3.item2', 'Gestione contabile')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s3.item3', 'Fatturazione')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s3.item4', 'Modifiche o chiusura attività')); ?></li>
                    </ul>
                </article>

                <!-- 4. Lavoro e orientamento -->
                <article class="sv-card sv-card--teal" id="lavoro">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">04</span>
                            <h3><?php echo esc_html($tr('servizi.s4.title', 'Lavoro e orientamento')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s4.desc', 'Ti aiutiamo a capire come muoverti nel mercato del lavoro e a costruire il tuo percorso.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s4.item1', 'Orientamento al lavoro')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s4.item2', 'Creazione CV')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s4.item3', 'Preparazione candidatura')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s4.item4', 'Accesso a opportunità lavorative')); ?></li>
                    </ul>
                </article>

                <!-- 5. Finanza e credito -->
                <article class="sv-card sv-card--yellow" id="credito">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-piggy-bank" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">05</span>
                            <h3><?php echo esc_html($tr('servizi.s5.title', 'Finanza e accesso al credito')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s5.desc', 'Educazione finanziaria e accompagnamento per accedere a strumenti di credito in modo consapevole.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s5.item1', 'Educazione finanziaria')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s5.item2', 'Orientamento al credito')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s5.item3', 'Supporto nella richiesta')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s5.item4', 'Connessione con partner finanziari')); ?></li>
                    </ul>
                </article>

                <!-- 6. Studiare in Italia -->
                <article class="sv-card sv-card--purple" id="studiare-italia">
                    <div class="sv-card__head">
                        <div class="sv-card__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                        <div>
                            <span class="sv-card__num">06</span>
                            <h3><?php echo esc_html($tr('servizi.s6.title', 'Studiare in Italia')); ?></h3>
                        </div>
                    </div>
                    <p class="sv-card__desc"><?php echo esc_html($tr('servizi.s6.desc', 'Accompagnamento per accedere alla formazione e ai percorsi scolastici in Italia.')); ?></p>
                    <ul class="sv-card__list">
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s6.item1', 'Percorsi formativi')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s6.item2', 'Corsi professionali')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s6.item3', 'Accesso a istituti scolastici')); ?></li>
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i><?php echo esc_html($tr('servizi.s6.item4', 'Supporto linguistico')); ?></li>
                    </ul>
                </article>

            </div>
        </div>
    </section>

    <!-- SEZIONE MODELLO -->
    <section class="ws-section cw-model" id="servizi-model">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('servizi.model.eyebrow', 'Il percorso')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('servizi.model.title', 'Un unico percorso, non servizi separati')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('servizi.model.body', 'WECOOP non offre servizi isolati, ma un percorso integrato. Partiamo dai tuoi bisogni e ti accompagniamo tra documenti, lavoro, formazione e accesso ai servizi.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="cw-cta-final" id="servizi-cta">
        <div class="ws-container">
            <div class="cw-cta-final__box">
                <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('servizi.cta.eyebrow', 'Inizia ora')); ?></span>
                <h2><?php echo esc_html($tr('servizi.cta.title', 'Hai bisogno di supporto?')); ?></h2>
                <p><?php echo esc_html($tr('servizi.cta.body', 'Contattaci e ti aiutiamo a capire da dove iniziare.')); ?></p>
                <div class="ws-hero-ctas">
                    <a class="ws-btn cw-btn--white" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <?php echo esc_html($tr('servizi.cta.btn1', 'Prenota un appuntamento')); ?>
                    </a>
                    <a class="ws-btn cw-btn--whatsapp" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <?php echo esc_html($tr('servizi.cta.btn2', 'Scrivici su WhatsApp')); ?>
                    </a>
                </div>
                <p class="ws-microcopy">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <?php echo esc_html($tr('servizi.cta.microcopy', 'Ti rispondiamo rapidamente.')); ?>
                </p>
            </div>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
