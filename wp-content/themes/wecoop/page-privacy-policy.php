<?php
/**
 * Template Name: Privacy Policy
 * Template Post Type: page
 *
 * Contenuto hardcoded — nessuna dipendenza dal DB.
 * URL: /privacy-policy/
 *
 * @package WeCoop
 */

get_header();
wecoop_ws_page_shell_start( get_the_title() );

$_t      = 'translate_string';
$wl_slug = 'privacy-policy';

$wl_sections = [
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

require __DIR__ . '/inc/legal-page-renderer.php';

wecoop_ws_page_shell_end();
get_footer();
