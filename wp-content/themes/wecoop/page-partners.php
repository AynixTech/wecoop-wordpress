<?php
get_header();
wecoop_ws_page_shell_start(translate_string('partners.aria.page', 'Partners page'));
?>

<section class="ws-section">
    <div class="ws-container">
    <article class="ws-page-content">
        <section class="ws-inner-hero">
            <h1>Partners</h1>
            <p>WECOOP works in network with social organizations, companies, schools, and public institutions.</p>
        </section>

        <section class="ws-content-section">
            <h2>Local Network</h2>
            <div class="ws-grid-3">
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
                        <article class="ws-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="ws-card__thumb"><?php the_post_thumbnail('medium'); ?></div>
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

        <section class="ws-cta-box">
            <h2>Would you like to become a WECOOP partner?</h2>
            <p><a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/collaborate-with-us')); ?>">Propose a partnership</a></p>
        </section>
    </article>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
