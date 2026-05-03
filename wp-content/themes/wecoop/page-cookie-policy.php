<?php
/**
 * Template Name: Cookie Policy
 * Template Post Type: page
 *
 * Contenuto hardcoded — nessuna dipendenza dal DB.
 * URL: /cookie-policy/
 *
 * @package WeCoop
 */

get_header();
wecoop_ws_page_shell_start( get_the_title() );

$_t      = 'translate_string';
$wl_slug = 'cookie-policy';

$wl_sections = [
    [
        'title'  => $_t('cp.s1.title', 'Cosa sono i Cookie?'),
        'blocks' => [
            [ 'p' => $_t('cp.s1.p1', 'I Cookie sono piccoli file di testo salvati nel browser dell\'Utente quando visita un sito web. Permettono al sito di memorizzare informazioni per migliorare l\'esperienza di navigazione e, in alcuni casi, fornire dati a terze parti.') ],
        ],
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

require __DIR__ . '/inc/legal-page-renderer.php';

wecoop_ws_page_shell_end();
get_footer();
