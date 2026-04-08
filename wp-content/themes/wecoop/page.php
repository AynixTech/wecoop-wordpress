<?php get_header();
wecoop_ws_page_shell_start(translate_string('page.aria.default', 'WECOOP page'));
?>

<section class="ws-section">
    <div class="ws-container">
        <div class="wecoop-page-content">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article class="wecoop-section">
                    <h1><?php the_title(); ?></h1>
                    <div class="content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
