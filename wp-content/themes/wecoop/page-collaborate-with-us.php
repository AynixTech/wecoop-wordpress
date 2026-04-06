<?php
get_header();
?>

<main class="wecoop-main">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>Collaborate with Us</h1>
            <p>We open collaborations with public entities, private organizations, and third sector actors to multiply social impact.</p>
        </section>

        <section class="wecoop-section">
            <h2>Ways to Collaborate</h2>
            <ul>
                <li>Development of local programs.</li>
                <li>Sponsorship and support for social projects.</li>
                <li>Training, job inclusion, and mentoring.</li>
                <li>Technical integrations with the WECOOP App.</li>
            </ul>
        </section>

        <section class="wecoop-section">
            <h2>Send us your proposal</h2>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </section>
    </article>
</main>

<?php
get_footer();
