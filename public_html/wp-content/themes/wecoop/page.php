<?php get_header(); ?>

<main class="container">
    <div class="page-layout">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article >
                <h1><?php the_title(); ?></h1>
                <div class="content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
