<?php
get_header();
?>

<main class="wecoop-main wecoop-blog-page">
    <section class="wecoop-archive-head">
        <h1>News</h1>
        <p>Updates about the WECOOP project, activities, and announcements.</p>
    </section>

    <div class="wecoop-news-grid">
        <?php
        if (have_posts()) :
            while (have_posts()) :
                the_post();
                ?>
                <article class="wecoop-news-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="wecoop-news-thumb"><?php the_post_thumbnail('large'); ?></a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p>
                    <a class="wecoop-link" href="<?php the_permalink(); ?>">Read more</a>
                </article>
                <?php
            endwhile;
        else :
            ?>
            <p>No posts available.</p>
            <?php
        endif;
        ?>
    </div>

    <div class="wecoop-pagination"><?php the_posts_pagination(); ?></div>
</main>

<?php
get_footer();
