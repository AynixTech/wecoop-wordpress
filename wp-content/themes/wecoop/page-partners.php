<?php
get_header();
?>

<main class="wecoop-main">
    <article class="wecoop-page-content">
        <section class="wecoop-section hero">
            <h1>Partners</h1>
            <p>WECOOP works in network with social organizations, companies, schools, and public institutions.</p>
        </section>

        <section class="wecoop-section">
            <h2>Local Network</h2>
            <div class="wecoop-news-grid">
                <?php
                $partners = new WP_Query([
                    'post_type' => 'wecoop_partner',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC',
                ]);

                if ($partners->have_posts()) :
                    while ($partners->have_posts()) :
                        $partners->the_post();
                        ?>
                        <article class="wecoop-news-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="wecoop-news-thumb"><?php the_post_thumbnail('medium'); ?></div>
                            <?php endif; ?>
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: get_the_content(), 20)); ?></p>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <p>No partners have been added yet. You can add them from the admin panel in the Partners section.</p>
                    <?php
                endif;
                ?>
            </div>
        </section>

        <section class="wecoop-section wecoop-cta">
            <h2>Would you like to become a WECOOP partner?</h2>
            <p><a class="wecoop-btn" href="<?php echo esc_url(home_url('/collaborate-with-us')); ?>">Propose a partnership</a></p>
        </section>
    </article>
</main>

<?php
get_footer();
