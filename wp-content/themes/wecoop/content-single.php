<?php
/**
 * Template part for displaying single post content
 *
 * @package YourThemeName
 */
?>
<div class="page-layout">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php if (has_post_thumbnail()) : ?>
            <div class="entry-image">
                <?php the_post_thumbnail('full'); ?>
            </div>
        <?php endif; ?>

        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
        <div class="entry-meta">
            <span class="posted-on">Pubblicato il <?php echo get_the_date(); ?></span>
            <span class="byline"> di <?php the_author_posts_link(); ?></span>
        </div>
    </header>

    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <footer class="entry-footer">
        <div class="post-categories">Categorie: <?php the_category(', '); ?></div>
        <div class="post-tags">Tag: <?php the_tags('', ', ', ''); ?></div>
    </footer>
</article>

<?php if (comments_open() || get_comments_number()) : ?>
    <div class="comments-section">
        <?php comments_template(); ?>
    </div>
<?php endif; ?>
</div>

