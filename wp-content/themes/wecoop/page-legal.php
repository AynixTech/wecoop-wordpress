<?php
/**
 * Template Name: Pagina Legale
 * Template Post Type: page
 *
 * Usato per: Privacy Policy, Cookie Policy, Note Legali
 * Tutto il contenuto è gestito via translate_string() / JSON multilingua.
 */

get_header();
wecoop_ws_page_shell_start(get_the_title());

$_t   = 'translate_string';
$lang = wecoop_language();
$dir  = in_array( $lang, ['ar'], true ) ? 'rtl' : 'ltr';

// Slug della pagina corrente
$slug = basename( get_permalink() );

$icons = [
    'privacy-policy' => 'fa-shield-halved',
    'cookie-policy'  => 'fa-cookie-bite',
    'note-legali'    => 'fa-scale-balanced',
];
$icon = $icons[ $slug ] ?? 'fa-file-lines';

$hero_descs = [
    'privacy-policy' => $_t('privacy.hero.desc', 'Come raccogliamo, usiamo e proteggiamo i tuoi dati personali.'),
    'cookie-policy'  => $_t('cookie.hero.desc', 'Informazioni sui cookie e tecnologie di tracciamento usate su questo sito.'),
    'note-legali'    => $_t('legal.hero.desc', 'Informazioni legali, proprietà intellettuale e responsabilità.'),
];
$hero_desc = $hero_descs[ $slug ] ?? '';

/* ================================================================
   HELPER: sezione con titolo h2 + array di paragrafi/items
   ================================================================ */
function wl_section( string $title, array $blocks ): void {
    echo '<div class="wl-section">';
    echo '<h2>' . esc_html( $title ) . '</h2>';
    foreach ( $blocks as $b ) {
        if ( isset( $b['p'] ) ) {
            echo '<p>' . wp_kses( $b['p'], [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[], 'em'=>[], 'br'=>[] ] ) . '</p>';
        } elseif ( isset( $b['ul'] ) ) {
            echo '<ul>';
            foreach ( $b['ul'] as $li ) {
                echo '<li>' . wp_kses( $li, [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[] ] ) . '</li>';
            }
            echo '</ul>';
        } elseif ( isset( $b['table'] ) ) {
            echo '<div class="wl-table-wrap"><table><thead><tr>';
            foreach ( $b['table']['head'] as $th ) { echo '<th>' . esc_html( $th ) . '</th>'; }
            echo '</tr></thead><tbody>';
            foreach ( $b['table']['rows'] as $row ) {
                echo '<tr>';
                foreach ( $row as $td ) { echo '<td>' . esc_html( $td ) . '</td>'; }
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        }
    }
    echo '</div>';
}

/* ================================================================
   CONTENUTE PER PAGINA
   ================================================================ */

if ( $slug === 'privacy-policy' ) :

    $sections = [
        [
            'title'  => $_t('pp.s1.title', '1. Titolare del Trattamento'),
            'blocks' => [
                [ 'p' => '<strong>WECOOP</strong> | Via Populonia 8, 20133 Milano (MI), Italia<br>Email: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a><br>Tel: +39 351 511 2113' ],
            ],
        ],
        [
            'title'  => $_t('pp.s2.title', '2. Tipologie di Dati Raccolti'),
            'blocks' => [
                [ 'p' => $_t('pp.s2.p1', 'Tra i Dati Personali raccolti da questa Applicazione, in modo autonomo o tramite terze parti, ci sono: nome, cognome, numero di telefono, indirizzo e-mail, dati di utilizzo, Cookie e varie tipologie di Dati.') ],
                [ 'p' => $_t('pp.s2.p2', 'I Dati Personali possono essere liberamente forniti dall\'Utente, o, nel caso dei Dati di Utilizzo, raccolti automaticamente durante l\'uso di questa Applicazione.') ],
            ],
        ],
        [
            'title'  => $_t('pp.s3.title', '3. Finalità del Trattamento'),
            'blocks' => [
                [ 'ul' => [
                    '<strong>' . $_t('pp.s3.li1a', 'Erogazione dei servizi:') . '</strong> ' . $_t('pp.s3.li1b', 'gestione delle richieste di supporto, accesso alla piattaforma, iscrizione ai programmi WeCoop.'),
                    '<strong>' . $_t('pp.s3.li2a', 'Comunicazioni:') . '</strong> ' . $_t('pp.s3.li2b', 'invio di newsletter, aggiornamenti su progetti e opportunità (previo consenso).'),
                    '<strong>' . $_t('pp.s3.li3a', 'Analisi statistica:') . '</strong> ' . $_t('pp.s3.li3b', 'miglioramento della piattaforma tramite dati anonimi e aggregati.'),
                    '<strong>' . $_t('pp.s3.li4a', 'Obblighi legali:') . '</strong> ' . $_t('pp.s3.li4b', 'adempimento a obblighi di legge o contrattuali.'),
                ]],
            ],
        ],
        [
            'title'  => $_t('pp.s4.title', '4. Base Giuridica del Trattamento'),
            'blocks' => [
                [ 'p' => $_t('pp.s4.p1', 'Il trattamento dei dati si basa su: (a) consenso dell\'interessato; (b) esecuzione di un contratto; (c) legittimo interesse del titolare; (d) obbligo legale, ai sensi dell\'art. 6 del Regolamento UE 2016/679 (GDPR).') ],
            ],
        ],
        [
            'title'  => $_t('pp.s5.title', '5. Destinatari dei Dati'),
            'blocks' => [
                [ 'p' => $_t('pp.s5.p1', 'I Dati Personali non vengono venduti a terzi. Possono essere condivisi con: fornitori di servizi tecnici (hosting, email, analytics), partner istituzionali nell\'ambito dei progetti WeCoop, autorità competenti ove richiesto per legge.') ],
            ],
        ],
        [
            'title'  => $_t('pp.s6.title', '6. Trasferimento dei Dati'),
            'blocks' => [
                [ 'p' => $_t('pp.s6.p1', 'I dati sono trattati prevalentemente in Italia e nell\'Unione Europea. Eventuali trasferimenti extra-UE avvengono nel rispetto delle garanzie previste dagli artt. 46-49 GDPR (clausole contrattuali standard, scudo privacy o decisioni di adeguatezza).') ],
            ],
        ],
        [
            'title'  => $_t('pp.s7.title', '7. Periodo di Conservazione'),
            'blocks' => [
                [ 'p' => $_t('pp.s7.p1', 'I dati sono conservati per il tempo strettamente necessario alle finalità per cui sono stati raccolti: dati di contatto fino a revoca del consenso o cessazione del servizio; dati contabili e fiscali per 10 anni; log tecnici per massimo 12 mesi.') ],
            ],
        ],
        [
            'title'  => $_t('pp.s8.title', '8. Diritti dell\'Interessato'),
            'blocks' => [
                [ 'p' => $_t('pp.s8.p1', 'Ai sensi degli artt. 15-22 GDPR, l\'Utente ha diritto di:') ],
                [ 'ul' => [
                    $_t('pp.s8.li1', 'Accedere ai propri dati personali'),
                    $_t('pp.s8.li2', 'Richiederne la rettifica o cancellazione'),
                    $_t('pp.s8.li3', 'Limitare od opporsi al trattamento'),
                    $_t('pp.s8.li4', 'Richiedere la portabilità dei dati'),
                    $_t('pp.s8.li5', 'Revocare il consenso in qualsiasi momento'),
                    $_t('pp.s8.li6', 'Proporre reclamo al Garante Privacy (<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener noreferrer">garanteprivacy.it</a>)'),
                ]],
                [ 'p' => $_t('pp.s8.contact', 'Per esercitare i propri diritti:') . ' <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a>' ],
            ],
        ],
        [
            'title'  => $_t('pp.s9.title', '9. Cookie e Tecnologie di Tracciamento'),
            'blocks' => [
                [ 'p' => $_t('pp.s9.p1', 'WeCoop utilizza Cookie e tecnologie simili. Per informazioni dettagliate consulta la nostra') . ' <a href="' . esc_url(home_url('/cookie-policy/')) . '">' . $_t('cookie.title', 'Cookie Policy') . '</a>.' ],
            ],
        ],
        [
            'title'  => $_t('pp.s10.title', '10. Minori'),
            'blocks' => [
                [ 'p' => $_t('pp.s10.p1', 'I servizi WeCoop non sono destinati a minori di 16 anni. Non raccogliamo consapevolmente dati personali di minori. Se rilevi un caso di raccolta involontaria, contattaci immediatamente.') ],
            ],
        ],
        [
            'title'  => $_t('pp.s11.title', '11. Modifiche alla Privacy Policy'),
            'blocks' => [
                [ 'p' => $_t('pp.s11.p1', 'Il Titolare si riserva il diritto di apportare modifiche alla presente policy. Gli Utenti saranno informati tramite avviso in questa pagina. Si consiglia di consultare regolarmente questa pagina.') ],
            ],
        ],
    ];

elseif ( $slug === 'cookie-policy' ) :

    $sections = [
        [
            'title'  => $_t('cp.s1.title', 'Cosa sono i Cookie?'),
            'blocks' => [
                [ 'p' => $_t('cp.s1.p1', 'I Cookie sono piccoli file di testo salvati nel browser dell\'Utente quando visita un sito web. Permettono al sito di memorizzare informazioni per migliorare l\'esperienza di navigazione e, in alcuni casi, fornire dati a terze parti.') ],
            ],
        ],
        [
            'title'  => $_t('cp.s2.title', 'Tipologie di Cookie Utilizzati'),
            'blocks' => [],
        ],
        [
            'title'  => $_t('cp.s2a.title', 'Cookie Tecnici (Strettamente Necessari)'),
            'blocks' => [
                [ 'p' => $_t('cp.s2a.p1', 'Necessari per il corretto funzionamento del sito. Non richiedono consenso.') ],
                [ 'table' => [
                    'head' => [ $_t('cp.table.name','Nome'), $_t('cp.table.provider','Fornitore'), $_t('cp.table.duration','Durata'), $_t('cp.table.purpose','Scopo') ],
                    'rows' => [
                        ['wordpress_*', 'WeCoop', $_t('cp.session','Sessione'), $_t('cp.wp_auth','Autenticazione WordPress')],
                        ['wp-settings-*', 'WeCoop', '1 ' . $_t('cp.year','anno'), $_t('cp.wp_pref','Preferenze interfaccia admin')],
                        ['cmplz_*', 'Complianz', '1 ' . $_t('cp.year','anno'), $_t('cp.consent','Memorizzazione consenso cookie')],
                    ],
                ]],
            ],
        ],
        [
            'title'  => $_t('cp.s2b.title', 'Cookie Analitici'),
            'blocks' => [
                [ 'p' => $_t('cp.s2b.p1', 'Utilizzati per analizzare il traffico e il comportamento degli utenti sul sito. Attivati solo con consenso.') ],
                [ 'table' => [
                    'head' => [ $_t('cp.table.name','Nome'), $_t('cp.table.provider','Fornitore'), $_t('cp.table.duration','Durata'), $_t('cp.table.purpose','Scopo') ],
                    'rows' => [
                        ['_ga', 'Google Analytics', '2 ' . $_t('cp.years','anni'), $_t('cp.ga_uniq','Distinzione utenti unici')],
                        ['_ga_*', 'Google Analytics', '2 ' . $_t('cp.years','anni'), $_t('cp.ga_sess','Memorizzazione stato sessione')],
                        ['_gid', 'Google Analytics', '24 ' . $_t('cp.hours','ore'), $_t('cp.ga_uniq','Distinzione utenti')],
                    ],
                ]],
            ],
        ],
        [
            'title'  => $_t('cp.s2c.title', 'Cookie di Marketing e Tracciamento'),
            'blocks' => [
                [ 'p' => $_t('cp.s2c.p1', 'Utilizzati per mostrare contenuti pubblicitari pertinenti. Attivati solo con consenso esplicito.') ],
                [ 'table' => [
                    'head' => [ $_t('cp.table.name','Nome'), $_t('cp.table.provider','Fornitore'), $_t('cp.table.duration','Durata'), $_t('cp.table.purpose','Scopo') ],
                    'rows' => [
                        ['_fbp', 'Meta / Facebook', '3 ' . $_t('cp.months','mesi'), $_t('cp.fb_ads','Annunci Facebook e Instagram')],
                        ['tt_webid', 'TikTok', '1 ' . $_t('cp.year','anno'), $_t('cp.tt_ads','Targeting pubblicitario TikTok')],
                    ],
                ]],
            ],
        ],
        [
            'title'  => $_t('cp.s3.title', 'Come Gestire i Cookie'),
            'blocks' => [
                [ 'p' => $_t('cp.s3.p1', 'Puoi gestire le tue preferenze in qualsiasi momento tramite il pannello cookie nel footer del sito, oppure configurando il tuo browser:') ],
                [ 'ul' => [
                    '<a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer">Google Chrome</a>',
                    '<a href="https://support.mozilla.org/it/kb/protezione-antitracciamento-avanzata-firefox" target="_blank" rel="noopener noreferrer">Mozilla Firefox</a>',
                    '<a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">Apple Safari</a>',
                    '<a href="https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer">Microsoft Edge</a>',
                ]],
                [ 'p' => $_t('cp.s3.p2', 'La disabilitazione di alcuni Cookie potrebbe compromettere alcune funzionalità del sito.') ],
            ],
        ],
        [
            'title'  => $_t('cp.s4.title', 'Cookie di Terze Parti'),
            'blocks' => [
                [ 'p' => $_t('cp.s4.p1', 'Alcuni Cookie vengono impostati da servizi di terze parti. Per ulteriori informazioni consulta le rispettive privacy policy:') ],
                [ 'ul' => [
                    '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Google Privacy Policy</a>',
                    '<a href="https://www.facebook.com/privacy/policy/" target="_blank" rel="noopener noreferrer">Meta Privacy Policy</a>',
                    '<a href="https://www.tiktok.com/legal/page/row/privacy-policy/it" target="_blank" rel="noopener noreferrer">TikTok Privacy Policy</a>',
                ]],
            ],
        ],
        [
            'title'  => $_t('cp.s5.title', 'Modifiche alla Cookie Policy'),
            'blocks' => [
                [ 'p' => $_t('cp.s5.p1', 'WeCoop si riserva il diritto di aggiornare questa Cookie Policy. Le modifiche saranno effettive dalla data di pubblicazione su questa pagina.') . ' ' . $_t('cp.s5.contact', 'Per domande:') . ' <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a>' ],
            ],
        ],
    ];

elseif ( $slug === 'note-legali' ) :

    $sections = [
        [
            'title'  => $_t('nl.s1.title', 'Informazioni Societarie'),
            'blocks' => [
                [ 'p' => '<strong>WECOOP</strong><br>Via Populonia 8, 20133 Milano (MI), Italia<br>Email: <a href="mailto:info@wecoop.org">info@wecoop.org</a><br>Tel: +39 351 511 2113<br>' . $_t('nl.s1.web','Sito web:') . ' <a href="https://wecoop.org">wecoop.org</a>' ],
            ],
        ],
        [
            'title'  => $_t('nl.s2.title', 'Proprietà Intellettuale'),
            'blocks' => [
                [ 'p' => $_t('nl.s2.p1', 'Tutti i contenuti presenti su questo sito (testi, immagini, loghi, grafica, software) sono di proprietà di WeCoop o dei rispettivi autori e sono protetti dalle normative vigenti in materia di diritto d\'autore e proprietà intellettuale.') ],
                [ 'p' => $_t('nl.s2.p2', 'È vietata la riproduzione, distribuzione, modifica o utilizzo dei contenuti senza previa autorizzazione scritta di WeCoop, ad eccezione dell\'uso personale non commerciale.') ],
            ],
        ],
        [
            'title'  => $_t('nl.s3.title', 'Limitazione di Responsabilità'),
            'blocks' => [
                [ 'p' => $_t('nl.s3.p1', 'WeCoop si impegna a mantenere le informazioni pubblicate aggiornate e accurate, ma non garantisce la completezza o l\'assenza di errori. WeCoop non è responsabile di danni diretti o indiretti derivanti dall\'utilizzo del sito o dall\'impossibilità di accedervi.') ],
            ],
        ],
        [
            'title'  => $_t('nl.s4.title', 'Link a Siti Terzi'),
            'blocks' => [
                [ 'p' => $_t('nl.s4.p1', 'Il sito può contenere link a siti web esterni. WeCoop non è responsabile dei contenuti di tali siti né delle loro pratiche in materia di privacy. Si consiglia di consultare le rispettive informative prima di interagire.') ],
            ],
        ],
        [
            'title'  => $_t('nl.s5.title', 'Legge Applicabile e Foro Competente'),
            'blocks' => [
                [ 'p' => $_t('nl.s5.p1', 'Le presenti Note Legali sono disciplinate dalla legge italiana. Per qualsiasi controversia derivante dall\'utilizzo del sito, il Foro competente è quello di Milano, Italia, salvo diversa disposizione normativa inderogabile a favore del consumatore.') ],
            ],
        ],
        [
            'title'  => $_t('nl.s6.title', 'Contatti'),
            'blocks' => [
                [ 'p' => $_t('nl.s6.p1', 'Per segnalazioni o richieste di natura legale:') . ' <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a>' ],
            ],
        ],
    ];

else :
    $sections = [];
endif;
?>

<!-- HERO LEGALE -->
<div class="wl-hero" dir="<?php echo esc_attr($dir); ?>">
    <div class="ws-container">
        <div class="wl-hero__inner">
            <div class="wl-hero__icon">
                <i class="fa-solid <?php echo esc_attr($icon); ?>"></i>
            </div>
            <div class="wl-hero__text">
                <div class="wl-hero__breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($_t('legal.breadcrumb.home', 'Home')); ?></a>
                    <span aria-hidden="true">›</span>
                    <span><?php the_title(); ?></span>
                </div>
                <h1 class="wl-hero__title"><?php the_title(); ?></h1>
                <?php if ($hero_desc) : ?>
                <p class="wl-hero__subtitle"><?php echo esc_html($hero_desc); ?></p>
                <?php endif; ?>
                <p class="wl-hero__meta">
                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                    <?php echo esc_html($_t('legal.hero.updated', 'Ultimo aggiornamento:')); ?>
                    <strong><?php echo esc_html(get_the_modified_date('d/m/Y')); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- BODY -->
<section class="wl-body" dir="<?php echo esc_attr($dir); ?>">
    <div class="ws-container">
        <div class="wl-layout">

            <!-- SIDEBAR INDICE -->
            <aside class="wl-toc" id="wl-toc" aria-label="<?php echo esc_attr($_t('legal.toc.label', 'Indice')); ?>">
                <div class="wl-toc__sticky">
                    <p class="wl-toc__label">
                        <i class="fa-solid fa-list" aria-hidden="true"></i>
                        <?php echo esc_html($_t('legal.toc.label', 'Indice')); ?>
                    </p>
                    <nav id="wl-toc-nav">
                        <?php foreach ( $sections as $i => $s ) : ?>
                        <a class="wl-toc__link" href="#wl-s-<?php echo $i; ?>"><?php echo esc_html($s['title']); ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="wl-toc__links">
                        <a href="mailto:privacy@wecoop.org" class="wl-toc__contact">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            privacy@wecoop.org
                        </a>
                    </div>
                </div>
            </aside>

            <!-- CONTENUTO -->
            <article class="wl-content" id="wl-content">
                <div class="wl-prose">
                    <?php foreach ( $sections as $i => $s ) : ?>
                    <div class="wl-section" id="wl-s-<?php echo $i; ?>">
                        <h2><?php echo esc_html($s['title']); ?></h2>
                        <?php foreach ( $s['blocks'] as $b ) : ?>
                            <?php if ( isset($b['p']) ) : ?>
                                <p><?php echo wp_kses( $b['p'], [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[], 'em'=>[], 'br'=>[] ] ); ?></p>
                            <?php elseif ( isset($b['ul']) ) : ?>
                                <ul>
                                <?php foreach ( $b['ul'] as $li ) : ?>
                                    <li><?php echo wp_kses( $li, [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[] ] ); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php elseif ( isset($b['table']) ) : ?>
                                <div class="wl-table-wrap">
                                <table>
                                    <thead><tr>
                                    <?php foreach ( $b['table']['head'] as $th ) : ?>
                                        <th><?php echo esc_html($th); ?></th>
                                    <?php endforeach; ?>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ( $b['table']['rows'] as $row ) : ?>
                                        <tr><?php foreach ( $row as $td ) : ?><td><?php echo esc_html($td); ?></td><?php endforeach; ?></tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- DOCUMENTI CORRELATI -->
                <div class="wl-related">
                    <p class="wl-related__label"><?php echo esc_html($_t('legal.related.label', 'Documenti correlati')); ?></p>
                    <div class="wl-related__links">
                        <?php if ($slug !== 'privacy-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span><?php echo esc_html($_t('privacy.title', 'Privacy Policy')); ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if ($slug !== 'cookie-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-cookie-bite"></i>
                            <span><?php echo esc_html($_t('cookie.title', 'Cookie Policy')); ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if ($slug !== 'note-legali') : ?>
                        <a href="<?php echo esc_url(home_url('/note-legali/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span><?php echo esc_html($_t('legal.title', 'Note Legali')); ?></span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

        </div>
    </div>
</section>

<script>
// Highlight voce TOC attiva allo scroll
document.addEventListener('DOMContentLoaded', function () {
    var sections = document.querySelectorAll('.wl-section[id]');
    var links    = document.querySelectorAll('#wl-toc-nav .wl-toc__link');
    if (!sections.length || !links.length) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            links.forEach(function (a) { a.classList.remove('wl-toc__link--active'); });
            var active = document.querySelector('#wl-toc-nav a[href="#' + entry.target.id + '"]');
            if (active) active.classList.add('wl-toc__link--active');
        });
    }, { rootMargin: '-10% 0px -75% 0px' });

    sections.forEach(function (s) { observer.observe(s); });
});
</script>

<?php
wecoop_ws_page_shell_end();
get_footer();
