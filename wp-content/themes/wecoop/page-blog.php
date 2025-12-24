<?php
/*
Template Name: Blog Custom with Sidebar
*/
get_header();
?>

<main class="container">
    <div class="page-layout">
         <section class="page-header">
            <h1><?php echo theme_translate('blog.title'); ?></h1>
            <p><?php echo theme_translate('blog.description'); ?></p>
        </section>
        <div class="blog-layout">
            <!-- Contenuto principale -->
            <div class="blog-content">
                <div class="left-content">
                    <?php
                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 4, // Limita a 4 articoli per pagina
                        'paged' => get_query_var('paged', 1), // Aggiungi il parametro per la paginazione
                    );
                    $query = new WP_Query($args);
                    if ($query->have_posts()):
                        while ($query->have_posts()): $query->the_post(); ?>
                            <article class="post-blog">
                                <?php if (has_post_thumbnail()): ?>
                                    <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                <?php endif; ?>
                                <div class="post-content">
                                    <h2><a href="<?php the_permalink(); ?>" class="post-title"><?php the_title(); ?></a></h2>
                                    <p class="post-excerpt"><?php the_excerpt(); ?></p>
                                </div>
                            </article>
                        <?php endwhile;
                        // Paginazione
                        echo '<div class="pagination">';
                        echo paginate_links(array(
                            'total' => $query->max_num_pages,
                        ));
                        echo '</div>';
                        wp_reset_postdata();
                    else:
                        echo '<p>Nessun articolo trovato.</p>';
                    endif;
                    ?>
                </div>

                <!-- Sidebar -->
                <div class="right-content">
                    
                    <h2>Categorie</h2>
                    <ul>
                        <?php wp_list_categories(['title_li' => '']); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
