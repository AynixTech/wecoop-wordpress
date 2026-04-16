<?php
get_header();
wecoop_ws_page_shell_start(translate_string('impact.aria.page', 'Social impact page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="ws-page-content">
        <section class="ws-inner-hero">
            <h1>Social Impact</h1>
            <p>We measure outcomes to improve continuously: access, inclusion, autonomy, and community activation.</p>
        </section>

        <section class="ws-content-section">
            <h2>Impact Indicators</h2>
            <ul>
                <li>People oriented and supported.</li>
                <li>Service requests completed successfully.</li>
                <li>Partner network activations.</li>
                <li>Training and job inclusion pathways.</li>
            </ul>
        </section>

        <section class="ws-content-section">
            <h2>Transparency and Improvement</h2>
            <p>We publish regular updates and use data to adapt services to real local needs.</p>
        </section>

        <section class="ws-cta-box">
            <h2>Discover active projects</h2>
            <p><a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/passaparola-project')); ?>">View PASSAPAROLA</a></p>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
