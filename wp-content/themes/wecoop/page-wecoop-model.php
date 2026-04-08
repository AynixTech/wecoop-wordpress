<?php
get_header();
wecoop_ws_page_shell_start(translate_string('model.aria.page', 'WECOOP model page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>Come funziona WECOOP</h1>
            <p>Un percorso semplice per aiutarti a risolvere i tuoi bisogni e accedere al lavoro.</p>
        </section>

        <section class="wecoop-section">
            <h2>Il percorso</h2>
            <ul>
                <li><strong>Ti ascoltiamo:</strong> capiamo la tua situazione e i tuoi bisogni.</li>
                <li><strong>Ti orientiamo:</strong> ti spieghiamo le possibilita e da dove iniziare.</li>
                <li><strong>Ti aiutiamo:</strong> supporto su documenti, servizi e strumenti.</li>
                <li><strong>Ti formiamo:</strong> sviluppi competenze utili per il lavoro.</li>
                <li><strong>Ti accompagniamo al lavoro:</strong> candidatura e opportunita concrete.</li>
            </ul>
        </section>

        <section class="wecoop-section">
            <h2>Un sistema fisico + digitale</h2>
            <p>WECOOP combina uno sportello sul territorio con una piattaforma digitale per accompagnarti in tutto il percorso.</p>
        </section>

        <section class="wecoop-section wecoop-cta">
            <h2>Inizia ora</h2>
            <p>
                <a class="wecoop-btn" href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393341390175'))); ?>?text=<?php echo esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei iniziare il mio percorso.'))); ?>" target="_blank" rel="noopener">Inizia il tuo percorso</a>
                <a class="wecoop-btn wecoop-btn-outline" href="<?php echo esc_url(home_url('/contact/')); ?>">Parla con un operatore</a>
            </p>
            <p>Ti aiutiamo passo dopo passo</p>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
