<?php get_header(); ?>

<main>
    <div class="homepage-layout">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-background">
                <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/la-gente-che-impila-insieme-le-mani-nel-parco-1.jpg')); ?>" alt="<?php echo esc_attr(theme_translate('hero_section.image_alt')); ?>"/>
            </div>
            <div class="hero-left">
                <h1>
                    <?php echo theme_translate('hero_section.title'); ?><br>
                    <?php echo theme_translate('hero_section.subtitle'); ?>
                </h1>
                <!--
                <div class="hero-buttons">
                    <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn btn-primary">
                        <?php echo theme_translate('hero_section.button_left.text'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/contact-us')); ?>" class="btn btn-secondary">
                        <?php echo theme_translate('hero_section.button_right.text'); ?>
                    </a>
                </div>
                -->
            </div>
        </section>

        <div class="container">
            <!-- Services Section -->
            <section class="services">
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-hands-helping"></i>
                        <h3><?php echo theme_translate('services.orientation.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.orientation.description'); ?></p>
                </div>
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-project-diagram"></i>
                        <h3><?php echo theme_translate('services.project_support.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.project_support.description'); ?></p>
                </div>
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-language"></i>
                        <h3><?php echo theme_translate('services.mediation.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.mediation.description'); ?></p>
                </div>
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-chalkboard"></i>
                        <h3><?php echo theme_translate('services.training.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.training.description'); ?></p>
                </div>
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-info-circle"></i>
                        <h3><?php echo theme_translate('services.events.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.events.description'); ?></p>
                </div>
                <div class="service">
                    <div class="service-header">
                        <i class="fas fa-school"></i>
                        <h3><?php echo theme_translate('services.schools.title'); ?></h3>
                    </div>
                    <p class="service-description"><?php echo theme_translate('services.schools.description'); ?></p>
                </div>
            </section>

            <!-- Presentation -->
            <div class="container-presentation">
                <div class="section-title">
                    <h2><?php echo theme_translate('presentation.title'); ?></h2>
                    <p><?php echo theme_translate('presentation.description'); ?></p>
                </div>
            </div>

            <!-- Projects -->
            <div class="container-projects">
                <div class="section-title">
                    <h3><?php echo theme_translate('projects.title'); ?></h3>
                </div>
                <div class="projects-grid">
                    <a href="<?php echo esc_url(home_url('/we-focus')); ?>" class="project-card" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/we-focus.jpg')); ?>" alt="WE-FOCUS">
                        <h4>WE-FOCUS</h4>
                        <p><?php echo esc_html(theme_translate('projects.we_focus.description')); ?></p>
                    </a>
                    <a href="<?php echo esc_url(home_url('/we-educate/')); ?>" class="project-card" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/we-educate.jpg')); ?>" alt="WE-EDUCATE">
                        <h4>WE-EDUCATE</h4>
                        <p><?php echo esc_html(theme_translate('projects.we_educate.description')); ?></p>
                    </a>
                    <a href="<?php echo esc_url(home_url('/we-work-2/')); ?>" class="project-card" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/we-work.jpg')); ?>" alt="WE-WORK">
                        <h4>WE-WORK</h4>
                        <p><?php echo esc_html(theme_translate('projects.we_work.description')); ?></p>
                    </a>
                    <a href="<?php echo esc_url(home_url('/ufficio-progettazione/')); ?>" class="project-card" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/progettazioni.jpg')); ?>" alt="Ufficio Progettazione">
                        <h4><?php echo theme_translate('projects.office.title'); ?></h4>
                        <p><?php echo theme_translate('projects.office.description'); ?></p>
                    </a>
                </div>
            </div>
             <div class="container-facebook-feed">
                <h3>Facebook Post</h3>
                <?php echo do_shortcode('[custom-facebook-feed feed=2]'); ?>
            </div>

            <!-- Initiatives -->
            <div class="container-carousel">
                <div class="section-title">
                    <h3><?php echo theme_translate('initiatives.title'); ?></h3>
                </div>
                <div class="swiper iniziative-carousel">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="iniziativa-card" style="background-image: url('<?php echo esc_url(home_url('/wp-content/uploads/2025/05/casatutti_banner.jpg')); ?>');">
                                <div class="overlay"></div>
                                <img class="card-logo" src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/casatutti.png')); ?>" alt="La Casa di tutti">
                                <h3><?php echo esc_html(theme_translate('initiatives.casatutti.title')); ?></h3>
                                <p><?php echo esc_html(theme_translate('initiatives.casatutti.description')); ?></p>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="iniziativa-card" style="background-image: url('<?php echo esc_url(home_url('/wp-content/uploads/2025/05/bocadelpozo_banner.jpg')); ?>');">
                                <div class="overlay"></div>
                                <img class="card-logo" src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/bocadelpozo.png')); ?>" alt="Calcio di strada">
                                <h3><?php echo theme_translate('initiatives.calcio.title'); ?></h3>
                                <p><?php echo theme_translate('initiatives.calcio.description'); ?></p>
                            </div>
                        </div>
                        <!--Umanitaria-->
                         <div class="swiper-slide">
                            <div class="iniziativa-card" style="background-image: url('<?php echo esc_url(home_url('/wp-content/uploads/2026/01/uniuma_banner.jpg')); ?>');">
                                <div class="overlay"></div>
                                <img class="card-logo" src="<?php echo esc_url(home_url('/wp-content/uploads/2025/11/Logo-Uniuma.png')); ?>" alt="UNIUMA">
                                <h3><?php echo esc_html(theme_translate('initiatives.uniuma.title')); ?></h3>
                                <p><?php echo esc_html(theme_translate('initiatives.uniuma.description')); ?></p>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>

            <!-- Latest Articles -->
            <div class="container-latest-posts">
                <div class="section-title">
                    <h3><?php echo __('Ultimi Articoli', 'wecoop'); ?></h3>
                </div>
                <div class="latest-posts-grid">
                    <?php
                    $latest_posts = new WP_Query(array(
                        'posts_per_page' => 3, // numero di articoli da mostrare
                        'post_status'    => 'publish'
                    ));
                    if ($latest_posts->have_posts()) :
                        while ($latest_posts->have_posts()) : $latest_posts->the_post(); ?>
                            <div class="post-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', ['class' => 'post-thumbnail']); ?>
                                    </a>
                                <?php endif; ?>
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <p><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                                <a href="<?php the_permalink(); ?>" class="read-more"><?php echo __('Leggi di più', 'wecoop'); ?></a>
                            </div>
                        <?php endwhile;
                        wp_reset_postdata();
                    else : ?>
                        <p><?php echo __('Nessun articolo disponibile.', 'wecoop'); ?></p>
                    <?php endif; ?>
                </div>
            </div>


            <!-- Partners -->
            <div class="section-title">
                <h3><?php echo theme_translate('partners.title'); ?></h3>
            </div>
            <!-- Swiper Container dedicato ai partner -->
            <div class="swiper swiper-partners">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/11/Logo-Uniuma.png')); ?>" alt="Partner 1 Logo" class="partner-logo" />
                            <h3 class="partner-name">UNIUMA – S.S.M.L</h3>
                            <div class="partner-links">
                                <a href="https://www.uniuma.it/" target="_blank" rel="noopener" aria-label="Website"><i class="fas fa-globe"></i></a>
                                <a href="https://www.facebook.com/uniumanitaria" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/uni_umanitaria" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="https://www.wecoop.org/wp-content/uploads/2025/05/concaf-removebg-preview.png" alt="Partner 1 Logo" class="partner-logo" />
                            <h3 class="partner-name">CONCAF</h3>
                            <div class="partner-links">
                                <a href="https://www.concaf.it/" target="_blank" aria-label="Website"><i class="fas fa-globe"></i></a>
                                <a href="https://www.facebook.com/info.concaf/?locale=it_IT" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.instagram.com/myconcaf/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="https://www.wecoop.org/wp-content/uploads/2025/06/LOGO-1-COLOR-scaled-e1749062768264.png" alt="Partner 2 Logo" class="partner-logo" />
                            <h3 class="partner-name">Aynix</h3>
                            <div class="partner-links">
                                <a href="https://aynix.tech" target="_blank" aria-label="Website"><i class="fas fa-globe"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="https://www.wecoop.org/wp-content/uploads/2025/05/logo_Ciq-removebg-preview.png" alt="Partner 3 Logo" class="partner-logo" />
                            <h3 class="partner-name">CIQ</h3>
                            <div class="partner-links">
                                <a href="https://www.ciqmilano.it/" target="_blank" aria-label="Website"><i class="fas fa-globe"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="https://www.wecoop.org/wp-content/uploads/2025/05/rsz_2logo.png" alt="Partner 4 Logo" class="partner-logo" />
                            <h3 class="partner-name">Sunugal</h3>
                            <div class="partner-links">
                                <a href="https://www.sunugal.it/" target="_blank" aria-label="Website"><i class="fas fa-globe"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="partner">
                            <img src="https://www.wecoop.org/wp-content/uploads/2025/05/306470475_104539322412341_6883141003823502444_n-removebg-preview.png" alt="Partner 5 Logo" class="partner-logo" />
                            <h3 class="partner-name">C.A.T.</h3>
                            <div class="partner-links">
                                <a href="https://www.facebook.com/cityambassadorsteam/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- opzionale: frecce di navigazione -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
            <!-- Facebook Feed -->
           

    </div>
</main>

<?php get_footer(); ?>
