<?php
/**
 * Template Name: About Aynix
 */
get_header(); ?>

<main class="container">
    <div class="page-layout">
        <section class="page-header">
            <h1><?php echo theme_translate('about_us.title'); ?></h1>
            <p><?php echo theme_translate('about_us.description'); ?></p>
        </section>


        <section class="section">
            <h2><?php echo theme_translate('about_us.vision_title'); ?></h2>
            <div class="vision-box">
                <p><?php echo theme_translate('about_us.vision_description'); ?></p>
            </div>
        </section>

        <section class="section">
            <h2><?php echo theme_translate('about_us.how_we_help'); ?></h2>
            <div class="founders">
                <div class="founder-card">
                    <h3><?php echo theme_translate('about_us.training_youth'); ?></h3>
                    <p><?php echo theme_translate('about_us.training_description'); ?></p>
                </div>
                <div class="founder-card">
                    <h3><?php echo theme_translate('about_us.digital_innovation'); ?></h3>
                    <p><?php echo theme_translate('about_us.innovation_description'); ?></p>
                </div>
                <div class="founder-card">
                    <h3><?php echo theme_translate('about_us.tech_migration'); ?></h3>
                    <p><?php echo theme_translate('about_us.migration_description'); ?></p>
                </div>
            </div>
        </section>

    </div>
</main>
<?php get_footer(); ?>