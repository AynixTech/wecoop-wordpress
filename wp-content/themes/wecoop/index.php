<?php
get_header();
?>

<main class="wecoop-main">
    <?php if (have_posts()) : ?>
        <div class="wecoop-news-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article class="wecoop-news-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <a class="wecoop-news-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large'); ?></a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 28)); ?></p>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="wecoop-pagination"><?php the_posts_pagination(); ?></div>
    <?php else : ?>
        <p><?php echo esc_html(wecoop_t('No hay contenidos publicados.', 'Nessun contenuto pubblicato.')); ?></p>
    <?php endif; ?>
</main>

<?php
get_footer();
