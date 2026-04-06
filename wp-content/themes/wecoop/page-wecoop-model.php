<?php
get_header();
?>

<main class="wecoop-main">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>The WECOOP Model</h1>
            <p>An institutional model that connects local territory, technology, and a partner network to improve access to services, education, and work opportunities.</p>
        </section>

        <section class="wecoop-section">
            <h2>Model Pillars</h2>
            <ul>
                <li>Local physical access point in the community.</li>
                <li>Digital platform for scalability and case tracking.</li>
                <li>Collaborative governance with institutions and organizations.</li>
            </ul>
        </section>

        <section class="wecoop-section">
            <h2>How It Works</h2>
            <p>People access a WECOOP point, define their need, activate a support path, and continue service management through the app with human guidance.</p>
        </section>

        <section class="wecoop-section wecoop-cta">
            <h2>Do you want to activate this model in your area?</h2>
            <p><a class="wecoop-btn" href="<?php echo esc_url(home_url('/contact')); ?>">Contact us</a></p>
        </section>
    </article>
</main>

<?php
get_footer();
