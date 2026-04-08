<?php
get_header();
wecoop_ws_page_shell_start(translate_string('contact.aria.page', 'Contact page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>Contact</h1>
            <p>We are available for people, institutions, and partners. Write to us and we will reply shortly.</p>
        </section>

        <section class="wecoop-section">
            <h2>Contact Form</h2>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </section>

        <section class="wecoop-section">
            <h2>Quick Contact</h2>
            <p>Email: <a href="mailto:info@wecoop.org">info@wecoop.org</a></p>
            <p>WhatsApp: <a href="https://wa.me/393341390175" target="_blank" rel="noopener">+39 334 1390175</a></p>
        </section>

        <section class="wecoop-section">
            <h2>Newsletter</h2>
            <?php echo do_shortcode('[wecoop_newsletter]'); ?>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
