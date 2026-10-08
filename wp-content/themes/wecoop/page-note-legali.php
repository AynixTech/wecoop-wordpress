<?php
/**
 * Template Name: Note Legali
 * Template Post Type: page
 *
 * Contenuto hardcoded — nessuna dipendenza dal DB.
 * URL: /note-legali/
 *
 * @package WeCoop
 */

get_header();
wecoop_ws_page_shell_start( get_the_title() );

$_t      = 'translate_string';
$wl_slug = 'note-legali';

$wl_sections = [
    [
        'title'  => $_t('nl.s1.title', 'Informazioni Societarie'),
        'blocks' => [
            [ 'p' => wecoop_org_anagrafica_html() ],
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
            [ 'p' => $_t('nl.s6.p1', 'Per segnalazioni o richieste di natura legale:') . ' <a href="mailto:info@wecoop.org">info@wecoop.org</a>' ],
        ],
    ],
];

require __DIR__ . '/inc/legal-page-renderer.php';

wecoop_ws_page_shell_end();
get_footer();
