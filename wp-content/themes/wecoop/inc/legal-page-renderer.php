<?php
/**
 * Partial condiviso per le pagine legali.
 *
 * Variabili attese dal template chiamante:
 *   string   $wl_slug      – slug della pagina ('privacy-policy'|'cookie-policy'|'note-legali')
 *   array    $wl_sections  – array di sezioni [ ['title'=>'', 'blocks'=>[...]], ... ]
 *
 * Ogni blocco può essere:
 *   [ 'p'     => 'testo (HTML kses)' ]
 *   [ 'ul'    => ['<li>...</li>', ...] ]
 *   [ 'table' => [ 'head' => [...], 'rows' => [[...], ...] ] ]
 *
 * @package WeCoop
 */

if ( ! defined('ABSPATH') ) exit;

$_t  = 'translate_string';
$dir = in_array( wecoop_language(), ['ar'], true ) ? 'rtl' : 'ltr';

$icons = [
    'privacy-policy' => 'fa-shield-halved',
    'cookie-policy'  => 'fa-cookie-bite',
    'note-legali'    => 'fa-scale-balanced',
];
$icon = $icons[ $wl_slug ] ?? 'fa-file-lines';

$hero_descs = [
    'privacy-policy' => $_t('privacy.hero.desc', 'Come raccogliamo, usiamo e proteggiamo i tuoi dati personali.'),
    'cookie-policy'  => $_t('cookie.hero.desc', 'Informazioni sui cookie e tecnologie di tracciamento usate su questo sito.'),
    'note-legali'    => $_t('legal.hero.desc', 'Informazioni legali, proprietà intellettuale e responsabilità.'),
];
$hero_desc = $hero_descs[ $wl_slug ] ?? '';
?>

<!-- HERO LEGALE -->
<div class="wl-hero" dir="<?php echo esc_attr($dir); ?>">
    <div class="ws-container">
        <div class="wl-hero__inner">
            <div class="wl-hero__icon">
                <i class="fa-solid <?php echo esc_attr($icon); ?>"></i>
            </div>
            <div class="wl-hero__text">
                <div class="wl-hero__breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($_t('legal.breadcrumb.home', 'Home')); ?></a>
                    <span aria-hidden="true">›</span>
                    <span><?php the_title(); ?></span>
                </div>
                <h1 class="wl-hero__title"><?php the_title(); ?></h1>
                <?php if ($hero_desc) : ?>
                <p class="wl-hero__subtitle"><?php echo esc_html($hero_desc); ?></p>
                <?php endif; ?>
                <p class="wl-hero__meta">
                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                    <?php echo esc_html($_t('legal.hero.updated', 'Ultimo aggiornamento:')); ?>
                    <strong><?php echo esc_html(get_the_modified_date('d/m/Y')); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- BODY -->
<section class="wl-body" dir="<?php echo esc_attr($dir); ?>">
    <div class="ws-container">
        <div class="wl-layout">

            <!-- SIDEBAR INDICE -->
            <aside class="wl-toc" id="wl-toc" aria-label="<?php echo esc_attr($_t('legal.toc.label', 'Indice')); ?>">
                <div class="wl-toc__sticky">
                    <p class="wl-toc__label">
                        <i class="fa-solid fa-list" aria-hidden="true"></i>
                        <?php echo esc_html($_t('legal.toc.label', 'Indice')); ?>
                    </p>
                    <nav id="wl-toc-nav">
                        <?php foreach ( $wl_sections as $i => $s ) : ?>
                        <a class="wl-toc__link" href="#wl-s-<?php echo $i; ?>"><?php echo esc_html($s['title']); ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="wl-toc__links">
                        <a href="mailto:privacy@wecoop.org" class="wl-toc__contact">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            privacy@wecoop.org
                        </a>
                    </div>
                </div>
            </aside>

            <!-- CONTENUTO -->
            <article class="wl-content" id="wl-content">
                <div class="wl-prose">
                    <?php foreach ( $wl_sections as $i => $s ) : ?>
                    <div class="wl-section" id="wl-s-<?php echo $i; ?>">
                        <h2><?php echo esc_html($s['title']); ?></h2>
                        <?php foreach ( $s['blocks'] as $b ) : ?>
                            <?php if ( isset($b['p']) ) : ?>
                                <p><?php echo wp_kses( $b['p'], [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[], 'em'=>[], 'br'=>[] ] ); ?></p>
                            <?php elseif ( isset($b['ul']) ) : ?>
                                <ul>
                                <?php foreach ( $b['ul'] as $li ) : ?>
                                    <li><?php echo wp_kses( $li, [ 'a' => ['href'=>[],'target'=>[],'rel'=>[]], 'strong'=>[] ] ); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php elseif ( isset($b['table']) ) : ?>
                                <div class="wl-table-wrap">
                                <table>
                                    <thead><tr>
                                    <?php foreach ( $b['table']['head'] as $th ) : ?>
                                        <th><?php echo esc_html($th); ?></th>
                                    <?php endforeach; ?>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach ( $b['table']['rows'] as $row ) : ?>
                                        <tr><?php foreach ( $row as $td ) : ?><td><?php echo esc_html($td); ?></td><?php endforeach; ?></tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- DOCUMENTI CORRELATI -->
                <div class="wl-related">
                    <p class="wl-related__label"><?php echo esc_html($_t('legal.related.label', 'Documenti correlati')); ?></p>
                    <div class="wl-related__links">
                        <?php if ($wl_slug !== 'privacy-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span><?php echo esc_html($_t('privacy.title', 'Privacy Policy')); ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if ($wl_slug !== 'cookie-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-cookie-bite"></i>
                            <span><?php echo esc_html($_t('cookie.title', 'Cookie Policy')); ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if ($wl_slug !== 'note-legali') : ?>
                        <a href="<?php echo esc_url(home_url('/note-legali/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span><?php echo esc_html($_t('legal.title', 'Note Legali')); ?></span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sections = document.querySelectorAll('.wl-section[id]');
    var links    = document.querySelectorAll('#wl-toc-nav .wl-toc__link');
    if (!sections.length || !links.length) return;
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            links.forEach(function (a) { a.classList.remove('wl-toc__link--active'); });
            var active = document.querySelector('#wl-toc-nav a[href="#' + entry.target.id + '"]');
            if (active) active.classList.add('wl-toc__link--active');
        });
    }, { rootMargin: '-10% 0px -75% 0px' });
    sections.forEach(function (s) { observer.observe(s); });
});
</script>
