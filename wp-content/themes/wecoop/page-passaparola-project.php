<?php
get_header();
wecoop_ws_page_shell_start(translate_string('passaparola.aria.page', 'Passaparola page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>PASSAPAROLA Project</h1>
            <p>PASSAPAROLA builds connections between people, resources, and local stakeholders to turn information into concrete opportunities.</p>
        </section>

        <section class="wecoop-section">
            <h2>Goals</h2>
            <ul>
                <li>Improve access to essential services.</li>
                <li>Increase social and employment inclusion.</li>
                <li>Strengthen the local support network.</li>
            </ul>
        </section>

        <section class="wecoop-section">
            <h2>Action Areas</h2>
            <p>Guidance, administrative support, intercultural mediation, training workshops, and local network activation with institutions and companies.</p>
        </section>

        <section class="wecoop-section wecoop-cta">
            <h2>Join the project</h2>
            <p>
                <a class="wecoop-btn" href="<?php echo esc_url(home_url('/collaborate-with-us')); ?>">Collaborate</a>
                <a class="wecoop-btn wecoop-btn-outline" href="<?php echo esc_url(home_url('/contact')); ?>">Contact us</a>
            </p>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
