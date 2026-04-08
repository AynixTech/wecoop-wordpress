<?php
get_header();
wecoop_ws_page_shell_start(translate_string('app.aria.page', 'WECOOP app page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>WECOOP App</h1>
            <p>The digital platform that supports local field work: requests, documents, tracking, and communication.</p>
        </section>

        <section class="wecoop-section">
            <h2>Key Features</h2>
            <ul>
                <li>Service request management.</li>
                <li>Document upload and archiving.</li>
                <li>Signature and validation workflows.</li>
                <li>Real-time status tracking.</li>
            </ul>
        </section>

        <section class="wecoop-section">
            <h2>Ready for Future Integrations</h2>
            <p>The architecture is ready for login, private areas, booking, and advanced services without redesigning the website.</p>
        </section>

        <section class="wecoop-section wecoop-cta">
            <h2>Request a demo</h2>
            <p><a class="wecoop-btn" href="<?php echo esc_url(home_url('/contact')); ?>">Contact us</a></p>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
