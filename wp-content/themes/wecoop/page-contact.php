<?php
get_header();
wecoop_ws_page_shell_start(translate_string('contact.aria.page', 'Contact page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>Contattaci</h1>
            <p>Siamo qui per aiutarti. Parlaci e ti guideremo.</p>
        </section>

        <section class="wecoop-section">
            <h2>Parla con noi</h2>
            <p><a class="wecoop-btn" href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393341390175'))); ?>?text=<?php echo esc_attr(rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei parlare con un operatore.'))); ?>" target="_blank" rel="noopener">Scrivici su WhatsApp</a></p>
            <p><a class="wecoop-btn wecoop-btn-outline" href="tel:+3902XXXXXXX">Chiama ora</a></p>
            <p>Ti rispondiamo rapidamente</p>
        </section>

        <section class="wecoop-section">
            <h2>Prenota un appuntamento</h2>
            <p><a class="wecoop-btn" href="<?php echo esc_url(home_url('/contact/')); ?>">Prenota</a></p>
        </section>

        <section class="wecoop-section">
            <h2>Dove siamo</h2>
            <p>Via Populonia 8, Milano</p>
            <p><a href="https://maps.google.com/?q=Via+Populonia+8,+Milano" target="_blank" rel="noopener">Apri su Google Maps</a></p>
            <h3>Orari</h3>
            <p>Lunedi - Venerdi</p>
        </section>

        <section class="wecoop-section">
            <h2>Oppure scrivici</h2>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
