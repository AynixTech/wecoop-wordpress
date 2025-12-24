<?php
get_header();
?>

<div class="container">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
            get_template_part('content', 'single'); // Qui viene incluso content-single.php
        endwhile;
        ?>
    </main>
</div>

<?php
get_footer();
?>
