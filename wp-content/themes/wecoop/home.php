<?php
get_header();

$posts = wecoop_fetch_news_posts(20);
$lang = function_exists('wecoop_language') ? wecoop_language() : 'it';
$headings = [
    'it' => ['title' => 'Notizie', 'sub' => 'Leggi, documenti, lavoro e diritti: aggiornamenti utili per chi vive in Italia.'],
    'es' => ['title' => 'Noticias', 'sub' => 'Leyes, documentos, trabajo y derechos: novedades útiles para quien vive en Italia.'],
    'en' => ['title' => 'News', 'sub' => 'Laws, documents, work and rights: useful updates for people living in Italy.'],
];
$h = $headings[$lang] ?? $headings['it'];
?>

<main class="wecoop-main wecoop-blog-page">
    <section class="wecoop-archive-head">
        <h1><?php echo esc_html($h['title']); ?></h1>
        <p><?php echo esc_html($h['sub']); ?></p>
    </section>

    <div class="wecoop-news-grid">
        <?php if (!empty($posts)) : ?>
            <?php foreach ($posts as $post) :
                $title = isset($post['title']) ? (string) $post['title'] : '';
                $excerpt = isset($post['excerpt']) ? wp_strip_all_tags((string) $post['excerpt']) : '';
                $link = !empty($post['link']) ? (string) $post['link'] : '';
                $image = !empty($post['image_url']) ? (string) $post['image_url'] : '';
                $source = !empty($post['source_name']) ? (string) $post['source_name'] : '';
                if ($title === '' || $link === '') {
                    continue;
                }
                ?>
                <article class="wecoop-news-card">
                    <?php if ($image !== '') : ?>
                        <a href="<?php echo esc_url($link); ?>" class="wecoop-news-thumb" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" />
                        </a>
                    <?php endif; ?>
                    <h2><a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($title); ?></a></h2>
                    <?php if ($source !== '') : ?>
                        <p class="wecoop-news-source"><?php echo esc_html($source); ?></p>
                    <?php endif; ?>
                    <?php if ($excerpt !== '') : ?>
                        <p><?php echo esc_html(wp_trim_words($excerpt, 30)); ?></p>
                    <?php endif; ?>
                    <a class="wecoop-link" href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo esc_html(function_exists('wecoop_t') ? wecoop_t('Leer más', 'Leggi di più', 'Read more') : 'Leggi di più'); ?>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php else : ?>
            <p><?php echo esc_html(function_exists('wecoop_t') ? wecoop_t('No hay noticias disponibles por el momento.', 'Nessuna notizia disponibile al momento.', 'No news available at the moment.') : 'Nessuna notizia disponibile al momento.'); ?></p>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
