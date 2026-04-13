<?php
get_header();
?>

<main class="ws-site" aria-label="Single content">
    <div class="ws-container" style="padding-top: 24px; padding-bottom: 24px;">
        <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
            get_template_part('content', 'single'); // Qui viene incluso content-single.php
        endwhile;
        ?>
        </main>
    </div>
</main>

<?php
get_footer();
?>
