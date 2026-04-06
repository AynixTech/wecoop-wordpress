<?php get_header(); ?>

<main class="wecoop-main">
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
</main>

<?php get_footer(); ?>
