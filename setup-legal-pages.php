<?php
/**
 * Setup Legal Pages — WeCoop
 * ---------------------------------------------------------------
 * Crea Privacy Policy, Cookie Policy e Cookie Banner come pagine WP
 * e le aggiunge al menu footer-menu / footer bottom bar.
 *
 * COME USARE:
 *   1. Visita https://wecoop.local/setup-legal-pages.php
 *   2. Verifica l'output — le pagine vengono create una sola volta
 *   3. ELIMINA questo file dopo l'esecuzione
 * ---------------------------------------------------------------
 */

if ( ! defined('ABSPATH') ) {
    require_once __DIR__ . '/wp-load.php';
}

// Token di sicurezza — cambia questo valore dopo l'uso
define('SETUP_SECRET', 'wecoop2026legal');

if ( empty($_GET['token']) || $_GET['token'] !== SETUP_SECRET ) {
    wp_die('Accesso negato. Aggiungi ?token=wecoop2026legal all\'URL.');
}

echo '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px}h2{color:#1a472a}.ok{color:green}.skip{color:#888}.err{color:red}</style>';
echo '<h1>🔧 Setup Pagine Legali — WeCoop</h1>';

/* ================================================================
   CONTENUTI PAGINE
   ================================================================ */

$pages = [

    /* ---- PRIVACY POLICY ---- */
    [
        'slug'    => 'privacy-policy',
        'title'   => 'Privacy Policy',
        'content' => '<!-- wp:paragraph -->
<p><strong>Ultimo aggiornamento:</strong> ' . date('d/m/Y') . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>1. Titolare del Trattamento</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><strong>WECOOP</strong> | Via Populonia 8, 20133 Milano (MI), Italia<br>Email: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a><br>Tel: +39 351 511 2113</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>2. Tipologie di Dati Raccolti</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Tra i Dati Personali raccolti da questa Applicazione, in modo autonomo o tramite terze parti, ci sono: nome, cognome, numero di telefono, indirizzo e-mail, dati di utilizzo, Cookie e varie tipologie di Dati.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>I Dati Personali possono essere liberamente forniti dall\'Utente, o, nel caso dei Dati di Utilizzo, raccolti automaticamente durante l\'uso di questa Applicazione.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>3. Finalità del Trattamento</h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
<li><strong>Erogazione dei servizi:</strong> gestione delle richieste di supporto, accesso alla piattaforma, iscrizione ai programmi WeCoop.</li>
<li><strong>Comunicazioni:</strong> invio di newsletter, aggiornamenti su progetti e opportunità (previo consenso).</li>
<li><strong>Analisi statistica:</strong> miglioramento della piattaforma tramite dati anonimi e aggregati.</li>
<li><strong>Obblighi legali:</strong> adempimento a obblighi di legge o contrattuali.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>4. Base Giuridica del Trattamento</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Il trattamento dei dati si basa su: (a) consenso dell\'interessato; (b) esecuzione di un contratto; (c) legittimo interesse del titolare; (d) obbligo legale, ai sensi dell\'art. 6 del Regolamento UE 2016/679 (GDPR).</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>5. Destinatari dei Dati</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>I Dati Personali non vengono venduti a terzi. Possono essere condivisi con: fornitori di servizi tecnici (hosting, email, analytics), partner istituzionali nell\'ambito dei progetti WeCoop, autorità competenti ove richiesto per legge.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>6. Trasferimento dei Dati</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>I dati sono trattati prevalentemente in Italia e nell\'Unione Europea. Eventuali trasferimenti extra-UE avvengono nel rispetto delle garanzie previste dagli artt. 46-49 GDPR (clausole contrattuali standard, scudo privacy o decisioni di adeguatezza).</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>7. Periodo di Conservazione</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>I dati sono conservati per il tempo strettamente necessario alle finalità per cui sono stati raccolti: dati di contatto fino a revoca del consenso o cessazione del servizio; dati contabili e fiscali per 10 anni; log tecnici per massimo 12 mesi.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>8. Diritti dell\'Interessato</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Ai sensi degli artt. 15-22 GDPR, l\'Utente ha diritto di:</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul>
<li>Accedere ai propri dati personali</li>
<li>Richiederne la rettifica o cancellazione</li>
<li>Limitare od opporsi al trattamento</li>
<li>Richiedere la portabilità dei dati</li>
<li>Revocare il consenso in qualsiasi momento</li>
<li>Proporre reclamo al Garante Privacy (www.garanteprivacy.it)</li>
</ul>
<!-- /wp:list -->
<!-- wp:paragraph -->
<p>Per esercitare i propri diritti: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>9. Cookie e Tecnologie di Tracciamento</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>WeCoop utilizza Cookie e tecnologie simili. Per informazioni dettagliate consulta la nostra <a href="/cookie-policy/">Cookie Policy</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>10. Minori</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>I servizi WeCoop non sono destinati a minori di 16 anni. Non raccogliamo consapevolmente dati personali di minori. Se rilevi un caso di raccolta involontaria, contattaci immediatamente.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>11. Modifiche alla Privacy Policy</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Il Titolare si riserva il diritto di apportare modifiche alla presente policy. Gli Utenti saranno informati tramite avviso in questa pagina. Si consiglia di consultare regolarmente questa pagina.</p>
<!-- /wp:paragraph -->',
    ],

    /* ---- COOKIE POLICY ---- */
    [
        'slug'    => 'cookie-policy',
        'title'   => 'Cookie Policy',
        'content' => '<!-- wp:paragraph -->
<p><strong>Ultimo aggiornamento:</strong> ' . date('d/m/Y') . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Cosa sono i Cookie?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>I Cookie sono piccoli file di testo salvati nel browser dell\'Utente quando visita un sito web. Permettono al sito di memorizzare informazioni per migliorare l\'esperienza di navigazione e, in alcuni casi, fornire dati a terze parti.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Tipologie di Cookie Utilizzati</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>Cookie Tecnici (Strettamente Necessari)</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Necessari per il corretto funzionamento del sito. Non richiedono consenso.</p>
<!-- /wp:paragraph -->
<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Nome</th><th>Fornitore</th><th>Durata</th><th>Scopo</th></tr></thead><tbody>
<tr><td>wordpress_*</td><td>WeCoop</td><td>Sessione</td><td>Autenticazione WordPress</td></tr>
<tr><td>wp-settings-*</td><td>WeCoop</td><td>1 anno</td><td>Preferenze interfaccia admin</td></tr>
<tr><td>cmplz_*</td><td>Complianz</td><td>1 anno</td><td>Memorizzazione consenso cookie</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading {"level":3} -->
<h3>Cookie Analitici</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Utilizzati per analizzare il traffico e il comportamento degli utenti sul sito. Attivati solo con consenso.</p>
<!-- /wp:paragraph -->
<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Nome</th><th>Fornitore</th><th>Durata</th><th>Scopo</th></tr></thead><tbody>
<tr><td>_ga</td><td>Google Analytics</td><td>2 anni</td><td>Distinzione utenti unici</td></tr>
<tr><td>_ga_*</td><td>Google Analytics</td><td>2 anni</td><td>Memorizzazione stato sessione</td></tr>
<tr><td>_gid</td><td>Google Analytics</td><td>24 ore</td><td>Distinzione utenti</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading {"level":3} -->
<h3>Cookie di Marketing e Tracciamento</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Utilizzati per mostrare contenuti pubblicitari pertinenti. Attivati solo con consenso esplicito.</p>
<!-- /wp:paragraph -->
<!-- wp:table -->
<figure class="wp-block-table"><table><thead><tr><th>Nome</th><th>Fornitore</th><th>Durata</th><th>Scopo</th></tr></thead><tbody>
<tr><td>_fbp</td><td>Meta / Facebook</td><td>3 mesi</td><td>Annunci Facebook e Instagram</td></tr>
<tr><td>tt_webid</td><td>TikTok</td><td>1 anno</td><td>Targeting pubblicitario TikTok</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2>Come Gestire i Cookie</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Puoi gestire le tue preferenze in qualsiasi momento tramite il <strong>pannello cookie</strong> presente nel footer del sito, oppure configurando il tuo browser:</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul>
<li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer">Google Chrome</a></li>
<li><a href="https://support.mozilla.org/it/kb/protezione-antitracciamento-avanzata-firefox" target="_blank" rel="noopener noreferrer">Mozilla Firefox</a></li>
<li><a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">Apple Safari</a></li>
<li><a href="https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer">Microsoft Edge</a></li>
</ul>
<!-- /wp:list -->
<!-- wp:paragraph -->
<p>La disabilitazione di alcuni Cookie potrebbe compromettere alcune funzionalità del sito.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Cookie di Terze Parti</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Alcuni Cookie vengono impostati da servizi di terze parti. Per ulteriori informazioni consulta le rispettive privacy policy:</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul>
<li><a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Google Privacy Policy</a></li>
<li><a href="https://www.facebook.com/privacy/policy/" target="_blank" rel="noopener noreferrer">Meta Privacy Policy</a></li>
<li><a href="https://www.tiktok.com/legal/page/row/privacy-policy/it" target="_blank" rel="noopener noreferrer">TikTok Privacy Policy</a></li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Modifiche alla Cookie Policy</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>WeCoop si riserva il diritto di aggiornare questa Cookie Policy. Le modifiche saranno effettive dalla data di pubblicazione su questa pagina. Per domande: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a></p>
<!-- /wp:paragraph -->',
    ],

    /* ---- NOTE LEGALI ---- */
    [
        'slug'    => 'note-legali',
        'title'   => 'Note Legali',
        'content' => '<!-- wp:paragraph -->
<p><strong>Ultimo aggiornamento:</strong> ' . date('d/m/Y') . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Informazioni Societarie</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><strong>WECOOP</strong><br>Via Populonia 8, 20133 Milano (MI), Italia<br>Email: <a href="mailto:info@wecoop.org">info@wecoop.org</a><br>Tel: +39 351 511 2113<br>Sito web: <a href="https://wecoop.org">wecoop.org</a></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Proprietà Intellettuale</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Tutti i contenuti presenti su questo sito (testi, immagini, loghi, grafica, software) sono di proprietà di WeCoop o dei rispettivi autori e sono protetti dalle normative vigenti in materia di diritto d\'autore e proprietà intellettuale.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>È vietata la riproduzione, distribuzione, modifica o utilizzo dei contenuti senza previa autorizzazione scritta di WeCoop, ad eccezione dell\'uso personale non commerciale.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Limitazione di Responsabilità</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>WeCoop si impegna a mantenere le informazioni pubblicate aggiornate e accurate, ma non garantisce la completezza o l\'assenza di errori. WeCoop non è responsabile di danni diretti o indiretti derivanti dall\'utilizzo del sito o dall\'impossibilità di accedervi.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Link a Siti Terzi</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Il sito può contenere link a siti web esterni. WeCoop non è responsabile dei contenuti di tali siti né delle loro pratiche in materia di privacy. Si consiglia di consultare le rispettive informative prima di interagire.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Legge Applicabile e Foro Competente</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Le presenti Note Legali sono disciplinate dalla legge italiana. Per qualsiasi controversia derivante dall\'utilizzo del sito, il Foro competente è quello di Milano, Italia, salvo diversa disposizione normativa inderogabile a favore del consumatore.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Contatti</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Per segnalazioni o richieste di natura legale: <a href="mailto:privacy@wecoop.org">privacy@wecoop.org</a></p>
<!-- /wp:paragraph -->',
    ],

];

/* ================================================================
   CREAZIONE PAGINE
   ================================================================ */

$created_ids = [];

foreach ( $pages as $page_data ) {
    $existing = get_page_by_path( $page_data['slug'] );

    if ( $existing ) {
        $created_ids[ $page_data['slug'] ] = $existing->ID;
        // Aggiorna il template anche se la pagina esiste già
        update_post_meta( $existing->ID, '_wp_page_template', 'page-legal.php' );
        echo '<p class="skip">⏭ Pagina già esistente: <strong>' . esc_html($page_data['title']) . '</strong> — template aggiornato (ID: ' . $existing->ID . ')</p>';
        continue;
    }

    $page_id = wp_insert_post([
        'post_title'   => $page_data['title'],
        'post_name'    => $page_data['slug'],
        'post_content' => $page_data['content'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    ]);

    if ( is_wp_error($page_id) ) {
        echo '<p class="err">❌ Errore creazione: <strong>' . esc_html($page_data['title']) . '</strong> — ' . esc_html($page_id->get_error_message()) . '</p>';
    } else {
        $created_ids[ $page_data['slug'] ] = $page_id;
        // Assegna il template grafico
        update_post_meta( $page_id, '_wp_page_template', 'page-legal.php' );
        echo '<p class="ok">✅ Creata: <strong>' . esc_html($page_data['title']) . '</strong> → <a href="' . esc_url(get_permalink($page_id)) . '" target="_blank">' . esc_url(get_permalink($page_id)) . '</a></p>';
    }
}

/* ================================================================
   IMPOSTAZIONE PAGINA PRIVACY NATIVA WP
   ================================================================ */
if ( isset($created_ids['privacy-policy']) ) {
    update_option('wp_page_for_privacy_policy', $created_ids['privacy-policy']);
    echo '<p class="ok">✅ Impostata pagina Privacy Policy nativa WordPress.</p>';
}

/* ================================================================
   AGGIUNTA AL MENU FOOTER
   ================================================================ */
$menu_name = 'footer-menu';
$menu      = get_term_by('slug', $menu_name, 'nav_menu');

if ( ! $menu ) {
    $menu_id = wp_create_nav_menu( 'Menu Footer' );
    $menu    = get_term( $menu_id, 'nav_menu' );
    echo '<p class="ok">✅ Menu footer-menu creato.</p>';
}

if ( $menu && ! is_wp_error($menu) ) {

    // Recupera le voci già presenti nel menu
    $existing_items = wp_get_nav_menu_items( $menu->term_id );
    $existing_urls  = [];
    if ( $existing_items ) {
        foreach ( $existing_items as $item ) {
            $existing_urls[] = $item->url;
        }
    }

    $menu_pages = [
        'privacy-policy' => 'Privacy Policy',
        'cookie-policy'  => 'Cookie Policy',
        'note-legali'    => 'Note Legali',
    ];

    foreach ( $menu_pages as $slug => $label ) {
        if ( ! isset($created_ids[$slug]) ) continue;

        $url = get_permalink($created_ids[$slug]);
        if ( in_array($url, $existing_urls, true) ) {
            echo '<p class="skip">⏭ Già nel menu: <strong>' . esc_html($label) . '</strong></p>';
            continue;
        }

        $item_id = wp_update_nav_menu_item( $menu->term_id, 0, [
            'menu-item-title'     => $label,
            'menu-item-object-id' => $created_ids[$slug],
            'menu-item-object'    => 'page',
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish',
        ]);

        if ( is_wp_error($item_id) ) {
            echo '<p class="err">❌ Errore aggiunta al menu: ' . esc_html($label) . '</p>';
        } else {
            echo '<p class="ok">✅ Aggiunto al menu footer: <strong>' . esc_html($label) . '</strong></p>';
        }
    }

    // Assegna il menu alla posizione footer-menu del tema
    $locations = get_theme_mod('nav_menu_locations');
    if ( empty($locations['footer-menu']) ) {
        $locations['footer-menu'] = $menu->term_id;
        set_theme_mod('nav_menu_locations', $locations);
        echo '<p class="ok">✅ Menu assegnato alla posizione footer-menu del tema.</p>';
    }
}

/* ================================================================
   FINE
   ================================================================ */
echo '<hr>';
echo '<h2>✅ Setup completato</h2>';
echo '<p><strong>⚠️ Importante:</strong> elimina questo file dopo l\'esecuzione: <code>setup-legal-pages.php</code></p>';
echo '<p><a href="' . esc_url(admin_url('nav-menus.php')) . '">→ Vai a Menu</a> | ';
echo '<a href="' . esc_url(admin_url('edit.php?post_type=page')) . '">→ Vai a Pagine</a></p>';
