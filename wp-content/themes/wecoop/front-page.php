<?php
get_header();
?>

<main class="wecoop-main wecoop-front-page">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('wecoop-page-content'); ?>>
                <?php the_content(); ?>
            </article>
            <?php
        endwhile;
    endif;
    ?>

    <section class="wecoop-refactor-visuals">
        <div class="wecoop-section-head">
            <h2>WECOOP in Action</h2>
            <p>Images imported from the new refactor package.</p>
        </div>
        <div class="wecoop-refactor-visuals__grid">
            <figure>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_diverse_people_connecting_through_technology_in_a_modern_community_hub,_smartphones_a_903873.png'); ?>" alt="Community and digital connection">
            </figure>
            <figure>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png'); ?>" alt="Social worker supporting service users">
            </figure>
            <figure>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="User using mobile app for services">
            </figure>
        </div>
    </section>

    <section class="wecoop-news-preview">
        <div class="wecoop-section-head">
            <h2>Blog & News</h2>
            <a href="<?php echo esc_url(get_permalink((int) get_option('page_for_posts'))); ?>">View all</a>
        </div>

        <div class="wecoop-news-grid">
            <?php
            $news = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 3,
                'post_status' => 'publish',
            ]);

            if ($news->have_posts()) :
                while ($news->have_posts()) :
                    $news->the_post();
                    ?>
                    <article class="wecoop-news-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="wecoop-news-thumb"><?php the_post_thumbnail('medium_large'); ?></a>
                        <?php endif; ?>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <p>No news posts available yet.</p>
                <?php
            endif;
            ?>
        </div>
    </section>
</main>

<?php
get_footer();
