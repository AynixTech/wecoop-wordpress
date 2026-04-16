<?php
get_header();

$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};

$wa_phone   = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei collaborare con voi.'));
$wa_url     = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;

wecoop_ws_page_shell_start($tr('collab.aria.page', 'Collabora con WECOOP'));
?>

    <!-- HERO -->
    <section class="cw-hero" id="collabora-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('collab.hero.eyebrow', 'Partner & Collaborazioni')); ?></span>
                    <h1><?php echo esc_html($tr('collab.hero.title', 'Collabora con WECOOP')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('collab.hero.subtitle', 'Costruiamo insieme progetti concreti di inclusione e sviluppo sul territorio.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <?php echo esc_html($tr('collab.hero.cta1', 'Contattaci')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <?php echo esc_html($tr('collab.hero.cta2', 'Scrivici su WhatsApp')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                        <?php echo esc_html($tr('collab.hero.microcopy', 'WECOOP è una piattaforma aperta a partner pubblici e privati.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-solid fa-building-columns"></i><span><?php echo esc_html($tr('collab.hero.badge1', 'Istituzioni')); ?></span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-briefcase"></i><span><?php echo esc_html($tr('collab.hero.badge2', 'Imprese')); ?></span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-solid fa-hand-holding-heart"></i><span><?php echo esc_html($tr('collab.hero.badge3', 'Fondazioni')); ?></span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-people-group"></i><span><?php echo esc_html($tr('collab.hero.badge4', 'Volontari')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTRODUZIONE -->
    <section class="ws-section" id="collabora-intro">
        <div class="ws-container">
            <div class="cw-institutional">
                <h2><?php echo esc_html($tr('collab.intro.title', 'Un modello aperto alla collaborazione')); ?></h2>
                <p><?php echo esc_html($tr('collab.intro.body1', 'WECOOP lavora con istituzioni, imprese e partner per sviluppare progetti concreti di inclusione sociale, accesso ai servizi e inserimento lavorativo.')); ?></p>
                <p><?php echo esc_html($tr('collab.intro.body2', 'Attraverso un approccio integrato che combina servizi territoriali e piattaforma digitale, creiamo soluzioni scalabili e ad alto impatto.')); ?></p>
            </div>
        </div>
    </section>

    <!-- PERCHÉ COLLABORARE -->
    <section class="ws-section ws-section--soft" id="collabora-perche">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('collab.why.title', 'Perché collaborare con WECOOP')); ?></h2>
            <div class="ws-grid-3">
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-network-wired"></i></span>
                    <h3><?php echo esc_html($tr('collab.why.item1.title', 'Ecosistema attivo')); ?></h3>
                    <p><?php echo esc_html($tr('collab.why.item1.body', 'Accesso a un ecosistema attivo sul territorio con reti consolidate')); ?></p>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-chart-line"></i></span>
                    <h3><?php echo esc_html($tr('collab.why.item2.title', 'Alto impatto sociale')); ?></h3>
                    <p><?php echo esc_html($tr('collab.why.item2.body', 'Sviluppo di progetti ad alto impatto sociale e misurabile')); ?></p>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-arrows-up-to-line"></i></span>
                    <h3><?php echo esc_html($tr('collab.why.item3.title', 'Soluzioni scalabili')); ?></h3>
                    <p><?php echo esc_html($tr('collab.why.item3.body', 'Possibilità di creare soluzioni scalabili e replicabili')); ?></p>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <h3><?php echo esc_html($tr('collab.why.item4.title', 'Piattaforma digitale')); ?></h3>
                    <p><?php echo esc_html($tr('collab.why.item4.body', 'Integrazione con piattaforma digitale WECOOP già operativa')); ?></p>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                    <h3><?php echo esc_html($tr('collab.why.item5.title', 'Connessione diretta')); ?></h3>
                    <p><?php echo esc_html($tr('collab.why.item5.body', 'Connessione diretta con beneficiari e utenti sul territorio')); ?></p>
                </article>
            </div>
        </div>
    </section>

    <!-- CON CHI COLLABORIAMO -->
    <section class="ws-section" id="collabora-chi">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('collab.who.title', 'Con chi collaboriamo')); ?></h2>
            <div class="ws-grid-2" style="margin-top:24px; gap:24px;">
                <article class="cw-scard cw-scard--blue">
                    <div class="cw-scard__icon"><i class="fa-solid fa-building-columns" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('collab.who.card1.title', 'Istituzioni Pubbliche')); ?></h3>
                        <p><?php echo esc_html($tr('collab.who.card1.body', 'Sviluppiamo progetti di inclusione sociale, accesso ai servizi e interventi sul territorio.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--green">
                    <div class="cw-scard__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('collab.who.card2.title', 'Imprese')); ?></h3>
                        <p><?php echo esc_html($tr('collab.who.card2.body', 'Creiamo opportunità di lavoro e progetti di responsabilità sociale (CSR).')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--pink">
                    <div class="cw-scard__icon"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('collab.who.card3.title', 'Fondazioni')); ?></h3>
                        <p><?php echo esc_html($tr('collab.who.card3.body', 'Sviluppiamo progetti finanziati ad alto impatto sociale e misurabile.')); ?></p>
                    </div>
                </article>
                <article class="cw-scard cw-scard--lime">
                    <div class="cw-scard__icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></div>
                    <div class="cw-scard__body">
                        <h3><?php echo esc_html($tr('collab.who.card4.title', 'Volontari e Professionisti')); ?></h3>
                        <p><?php echo esc_html($tr('collab.who.card4.body', 'Contribuiscono allo sviluppo e alla realizzazione delle attività WECOOP.')); ?></p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- COSA POSSIAMO COSTRUIRE INSIEME -->
    <section class="ws-section ws-section--soft" id="collabora-cosa">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('collab.together.title', 'Cosa possiamo costruire insieme')); ?></h2>
            <div class="ws-grid-3" style="margin-top:24px;">
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-people-arrows"></i></span>
                    <h3><?php echo esc_html($tr('collab.together.item1', 'Inclusione sociale')); ?></h3>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                    <h3><?php echo esc_html($tr('collab.together.item2', 'Percorsi formativi')); ?></h3>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span>
                    <h3><?php echo esc_html($tr('collab.together.item3', 'Inserimento lavorativo')); ?></h3>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span>
                    <h3><?php echo esc_html($tr('collab.together.item4', 'Accesso ai servizi')); ?></h3>
                </article>
                <article class="ws-tile">
                    <span class="ws-tile__icon" aria-hidden="true"><i class="fa-solid fa-diagram-project"></i></span>
                    <h3><?php echo esc_html($tr('collab.together.item5', 'Soluzioni digitali')); ?></h3>
                </article>
            </div>
        </div>
    </section>

    <!-- COME FUNZIONA LA COLLABORAZIONE -->
    <section class="ws-section" id="collabora-steps">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('collab.steps.title', 'Come funziona la collaborazione')); ?></h2>
            <div class="cw-steps" style="margin-top:32px;">
                <div class="cw-step">
                    <div class="cw-step__num">1</div>
                    <div class="cw-step__body">
                        <h3><?php echo esc_html($tr('collab.steps.step1.title', 'Ci contatti')); ?></h3>
                        <p><?php echo esc_html($tr('collab.steps.step1.body', 'Compila il form o scrivici via WhatsApp per presentarti.')); ?></p>
                    </div>
                </div>
                <div class="cw-step">
                    <div class="cw-step__num">2</div>
                    <div class="cw-step__body">
                        <h3><?php echo esc_html($tr('collab.steps.step2.title', 'Analizziamo insieme')); ?></h3>
                        <p><?php echo esc_html($tr('collab.steps.step2.body', 'Valutiamo esigenze, obiettivi e possibili sinergie.')); ?></p>
                    </div>
                </div>
                <div class="cw-step">
                    <div class="cw-step__num">3</div>
                    <div class="cw-step__body">
                        <h3><?php echo esc_html($tr('collab.steps.step3.title', 'Definiamo il progetto')); ?></h3>
                        <p><?php echo esc_html($tr('collab.steps.step3.body', 'Strutturiamo insieme attività, ruoli e obiettivi.')); ?></p>
                    </div>
                </div>
                <div class="cw-step">
                    <div class="cw-step__num">4</div>
                    <div class="cw-step__body">
                        <h3><?php echo esc_html($tr('collab.steps.step4.title', 'Avviamo la collaborazione')); ?></h3>
                        <p><?php echo esc_html($tr('collab.steps.step4.body', 'Partiamo con il progetto e monitoriamo i risultati insieme.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROGETTI WECOOP -->
    <section class="ws-section ws-section--soft" id="collabora-progetti">
        <div class="ws-container">
            <div class="cw-institutional">
                <h2><?php echo esc_html($tr('collab.projects.title', 'Progetti WECOOP')); ?></h2>
                <p><?php echo esc_html($tr('collab.projects.lead', 'WECOOP sviluppa iniziative concrete sul territorio, tra cui:')); ?></p>
                <ul class="cw-list">
                    <li><?php echo esc_html($tr('collab.projects.item1', 'Progetto Passaparola')); ?></li>
                    <li><?php echo esc_html($tr('collab.projects.item2', 'Servizi di orientamento e supporto')); ?></li>
                    <li><?php echo esc_html($tr('collab.projects.item3', 'Formazione e inserimento lavorativo')); ?></li>
                    <li><?php echo esc_html($tr('collab.projects.item4', 'Sviluppo della piattaforma digitale')); ?></li>
                </ul>
                <a class="ws-btn ws-btn--outline" href="<?php echo esc_url(home_url('/passaparola/')); ?>">
                    <?php echo esc_html($tr('collab.projects.cta', 'Scopri il progetto Passaparola')); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA FINALE -->
    <section class="ws-section ws-section--cta" id="collabora-cta">
        <div class="ws-container" style="text-align:center; max-width:680px;">
            <h2><?php echo esc_html($tr('collab.cta.title', 'Vuoi collaborare con noi?')); ?></h2>
            <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('collab.cta.body', 'Contattaci e costruiamo insieme una collaborazione.')); ?></p>
            <div class="ws-actions" style="justify-content:center; margin-top:20px;">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                    <?php echo esc_html($tr('collab.cta.cta1', 'Contattaci')); ?>
                </a>
                <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                    <?php echo esc_html($tr('collab.cta.cta2', 'Scrivici su WhatsApp')); ?>
                </a>
            </div>
            <p class="ws-microcopy" style="margin-top:14px; opacity:.85;">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                <?php echo esc_html($tr('collab.cta.microcopy', 'Rispondiamo rapidamente e valutiamo insieme le opportunità.')); ?>
            </p>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();

