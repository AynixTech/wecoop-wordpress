<?php
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('cose.aria.page', 'Cos\'è WECOOP'));
$wa_num = esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113')));
$wa_msg = esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei capire come può aiutarmi.')));
?>

    <!-- HERO -->
    <section class="cw-hero" id="cose-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('cose.hero.eyebrow', 'Chi siamo')); ?></span>
                    <h1><?php echo esc_html($tr('cose.hero.title', 'Cos\'è WECOOP')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('cose.hero.subtitle', 'WECOOP è un modello integrato che facilita l\'accesso a servizi, lavoro e opportunità, attraverso un sistema fisico e digitale.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/servizi/')); ?>">
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            <?php echo esc_html($tr('cose.hero.cta1', 'Scopri i servizi')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <?php echo esc_html($tr('cose.hero.cta2', 'Contattaci')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('cose.hero.microcopy', 'Ti aiutiamo a capire da dove iniziare, passo dopo passo.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-location-dot"></i><span><?php echo esc_html($tr('cose.model.card1.title', 'Centro territoriale')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-graduation-cap"></i><span><?php echo esc_html($tr('cose.model.card2.title', 'Formazione e lavoro')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-mobile-screen-button"></i><span><?php echo esc_html($tr('cose.model.card3.title', 'Piattaforma digitale')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-users"></i><span>400+ <?php echo esc_html($tr('cose.impact.stat1_label', 'beneficiari')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEZIONE ISTITUZIONALE -->
    <section class="ws-section cw-institutional" id="cose-institutional">
        <div class="ws-container">
            <div class="cw-institutional__inner">
                <div class="cw-institutional__label">
                    <span class="cw-eyebrow cw-eyebrow--soft"><?php echo esc_html($tr('cose.institutional.eyebrow', 'Chi siamo')); ?></span>
                </div>
                <div class="cw-institutional__content">
                    <h2><?php echo esc_html($tr('cose.institutional.title', 'Un modello integrato di inclusione')); ?></h2>
                    <p class="cw-text-lg"><?php echo esc_html($tr('cose.institutional.p1', 'WECOOP è un modello integrato di inclusione sociale e lavorativa che combina servizi territoriali, accompagnamento personalizzato e una piattaforma digitale per facilitare l\'accesso ai diritti, ai servizi e alle opportunità.')); ?></p>
                    <p><?php echo esc_html($tr('cose.institutional.p2', 'Il progetto si rivolge in particolare a cittadini migranti e persone in condizione di vulnerabilità, supportandoli nella gestione di pratiche amministrative e fiscali, nello sviluppo di competenze, nell\'accesso al lavoro e nei percorsi di autonomia economica.')); ?></p>
                    <p><?php echo esc_html($tr('cose.institutional.p3', 'Attraverso un approccio strutturato e scalabile, WECOOP connette territorio, istituzioni e partner privati, generando impatto sociale misurabile e favorendo l\'inclusione attiva.')); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- COSA FACCIAMO -->
    <section class="ws-section ws-section--soft cw-whatwedo" id="cose-whatwedo">
        <div class="ws-container">
            <div class="cw-section-head">
                <span class="cw-eyebrow"><?php echo esc_html($tr('cose.whatwedo.eyebrow', 'Servizi')); ?></span>
                <h2><?php echo esc_html($tr('cose.whatwedo.title', 'Cosa facciamo concretamente')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('cose.whatwedo.intro', 'Traduciamo il modello in servizi concreti per le persone.')); ?></p>
            </div>
            <div class="cw-scard-grid">
                <article class="cw-scard cw-scard--blue">
                    <div class="cw-scard__icon"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('cose.whatwedo.block1.title', 'Servizi amministrativi e fiscali')); ?></h3>
                        <p><?php echo esc_html($tr('cose.whatwedo.block1.body', 'Supporto nella gestione di documenti, dichiarazioni e pratiche per vivere in Italia.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--green">
                    <div class="cw-scard__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('cose.whatwedo.block2.title', 'Lavoro e orientamento')); ?></h3>
                        <p><?php echo esc_html($tr('cose.whatwedo.block2.body', 'Orientamento, costruzione del percorso professionale e accesso a opportunità lavorative.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--pink">
                    <div class="cw-scard__icon"><i class="fa-solid fa-store" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('cose.whatwedo.block3.title', 'Impresa e Partita IVA')); ?></h3>
                        <p><?php echo esc_html($tr('cose.whatwedo.block3.body', 'Supporto per avviare e gestire un\'attività in modo semplice e sostenibile.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--yellow">
                    <div class="cw-scard__icon"><i class="fa-solid fa-piggy-bank" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('cose.whatwedo.block4.title', 'Educazione finanziaria e accesso al credito')); ?></h3>
                        <p><?php echo esc_html($tr('cose.whatwedo.block4.body', 'Percorsi per comprendere il sistema finanziario e accedere al credito in modo consapevole.')); ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- MODELLO WECOOP -->
    <section class="ws-section cw-model" id="cose-model">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('cose.model.eyebrow', 'Il modello')); ?></span>
                <h2><?php echo esc_html($tr('cose.model.title', 'Il modello WECOOP')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('cose.model.subtitle', 'Un sistema integrato')); ?></p>
            </div>
            <div class="cw-model-grid">
                <article class="cw-model-card cw-model-card--blue">
                    <div class="cw-model-card__num">01</div>
                    <div class="cw-model-card__icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('cose.model.card1.title', 'Centro territoriale')); ?></h3>
                    <p><?php echo esc_html($tr('cose.model.card1.body', 'Uno spazio fisico dove le persone ricevono orientamento, supporto e accompagnamento personalizzato.')); ?></p>
                </article>
                <article class="cw-model-card cw-model-card--green">
                    <div class="cw-model-card__num">02</div>
                    <div class="cw-model-card__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('cose.model.card2.title', 'Formazione e lavoro')); ?></h3>
                    <p><?php echo esc_html($tr('cose.model.card2.body', 'Percorsi formativi e sviluppo di competenze per migliorare le opportunità lavorative.')); ?></p>
                </article>
                <article class="cw-model-card cw-model-card--pink">
                    <div class="cw-model-card__num">03</div>
                    <div class="cw-model-card__icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>
                    <h3><?php echo esc_html($tr('cose.model.card3.title', 'Piattaforma digitale')); ?></h3>
                    <p><?php echo esc_html($tr('cose.model.card3.body', 'Una piattaforma per accedere ai servizi, prenotare appuntamenti e seguire il proprio percorso.')); ?></p>
                </article>
            </div>
        </div>
    </section>

    <!-- COME FUNZIONA -->
    <section class="ws-section ws-section--soft cw-how" id="cose-how">
        <div class="ws-container">
            <div class="cw-section-head cw-section-head--center">
                <span class="cw-eyebrow"><?php echo esc_html($tr('cose.how.eyebrow', 'Il percorso')); ?></span>
                <h2><?php echo esc_html($tr('cose.how.title', 'Come funziona il percorso')); ?></h2>
            </div>
            <div class="cw-how-grid">
                <?php
                $steps = [
                    ['num' => '1', 'icon' => 'fa-ear-listen',   'title' => $tr('cose.how.step1.title', 'Ti ascoltiamo'),    'body' => $tr('cose.how.step1.body', 'Capiamo la tua situazione e i tuoi bisogni')],
                    ['num' => '2', 'icon' => 'fa-compass',       'title' => $tr('cose.how.step2.title', 'Ti orientiamo'),    'body' => $tr('cose.how.step2.body', 'Ti spieghiamo cosa fare e da dove iniziare')],
                    ['num' => '3', 'icon' => 'fa-hands-holding', 'title' => $tr('cose.how.step3.title', 'Ti aiutiamo'),      'body' => $tr('cose.how.step3.body', 'Ti supportiamo con servizi, documenti e strumenti')],
                    ['num' => '4', 'icon' => 'fa-route',         'title' => $tr('cose.how.step4.title', 'Ti accompagniamo'), 'body' => $tr('cose.how.step4.body', 'Ti seguiamo nel tempo nel tuo percorso')],
                ];
                foreach ($steps as $s) : ?>
                <div class="cw-step">
                    <div class="cw-step__circle">
                        <i class="fa-solid <?php echo esc_attr($s['icon']); ?>" aria-hidden="true"></i>
                        <span class="cw-step__num"><?php echo esc_html($s['num']); ?></span>
                    </div>
                    <div class="cw-step__content">
                        <h3><?php echo esc_html($s['title']); ?></h3>
                        <p><?php echo esc_html($s['body']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- A CHI È RIVOLTO -->
    <section class="ws-section cw-audience" id="cose-audience">
        <div class="ws-container">
            <div class="cw-audience__inner">
                <div class="cw-audience__header">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('cose.audience.eyebrow', 'Target')); ?></span>
                    <h2><?php echo esc_html($tr('cose.audience.title', 'Per chi è WECOOP')); ?></h2>
                </div>
                <ul class="cw-audience__list">
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item1', 'Persone straniere che vivono o vogliono vivere in Italia')); ?></span></li>
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item2', 'Persone in situazione di vulnerabilità')); ?></span></li>
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item3', 'Chi ha bisogno di supporto amministrativo e fiscale')); ?></span></li>
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item4', 'Chi cerca lavoro o vuole migliorare la propria situazione')); ?></span></li>
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item5', 'Chi vuole avviare un\'attività')); ?></span></li>
                    <li><span class="cw-audience__check" aria-hidden="true"><i class="fa-solid fa-check"></i></span><span><?php echo esc_html($tr('cose.audience.item6', 'Chi vuole accedere al credito in modo consapevole')); ?></span></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- IMPATTO -->
    <section class="cw-impact" id="cose-impact">
        <div class="ws-container">
            <div class="cw-impact__header">
                <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('cose.impact.eyebrow', 'Risultati')); ?></span>
                <h2><?php echo esc_html($tr('cose.impact.title', 'Il nostro impatto')); ?></h2>
            </div>
            <div class="cw-impact__stats">
                <div class="cw-stat cw-stat--blue">
                    <div class="cw-stat__icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('cose.impact.stat1', '400+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('cose.impact.stat1_label', 'beneficiari')); ?></span>
                </div>
                <div class="cw-stat cw-stat--green">
                    <div class="cw-stat__icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('cose.impact.stat2', '150+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('cose.impact.stat2_label', 'percorsi formativi')); ?></span>
                </div>
                <div class="cw-stat cw-stat--pink">
                    <div class="cw-stat__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('cose.impact.stat3', '80+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('cose.impact.stat3_label', 'inserimenti lavorativi')); ?></span>
                </div>
                <div class="cw-stat cw-stat--yellow">
                    <div class="cw-stat__icon"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i></div>
                    <strong class="cw-stat__val"><?php echo esc_html($tr('cose.impact.stat4', '300+')); ?></strong>
                    <span class="cw-stat__label"><?php echo esc_html($tr('cose.impact.stat4_label', 'utenti piattaforma')); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="cw-cta-final" id="cose-cta">
        <div class="ws-container">
            <div class="cw-cta-final__box">
                <span class="cw-eyebrow cw-eyebrow--light"><?php echo esc_html($tr('cose.cta.eyebrow', 'Inizia ora')); ?></span>
                <h2><?php echo esc_html($tr('cose.cta.title', 'Vuoi capire come WECOOP può aiutarti?')); ?></h2>
                <p><?php echo esc_html($tr('cose.cta.body', 'Contattaci e ti aiutiamo a capire il percorso più adatto alla tua situazione.')); ?></p>
                <div class="ws-hero-ctas">
                    <a class="ws-btn cw-btn--white" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <?php echo esc_html($tr('cose.cta.btn1', 'Prenota un appuntamento')); ?>
                    </a>
                    <a class="ws-btn cw-btn--whatsapp" href="https://wa.me/<?php echo $wa_num; ?>?text=<?php echo $wa_msg; ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <?php echo esc_html($tr('cose.cta.btn2', 'Scrivici su WhatsApp')); ?>
                    </a>
                </div>
                <p class="ws-microcopy">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <?php echo esc_html($tr('cose.cta.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare.')); ?>
                </p>
            </div>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
