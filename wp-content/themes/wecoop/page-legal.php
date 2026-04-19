<?php
/**
 * Template Name: Pagina Legale
 * Template Post Type: page
 *
 * Usato per: Privacy Policy, Cookie Policy, Note Legali
 */

get_header();
wecoop_ws_page_shell_start(get_the_title());

// Icone per ogni pagina legale
$icons = [
    'privacy-policy' => 'fa-shield-halved',
    'cookie-policy'  => 'fa-cookie-bite',
    'note-legali'    => 'fa-scale-balanced',
];
$slug = basename(get_permalink());
$icon = $icons[$slug] ?? 'fa-file-lines';
?>

<!-- HERO LEGALE -->
<div class="wl-hero">
    <div class="ws-container">
        <div class="wl-hero__inner">
            <div class="wl-hero__icon">
                <i class="fa-solid <?php echo esc_attr($icon); ?>"></i>
            </div>
            <div class="wl-hero__text">
                <div class="wl-hero__breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                    <span aria-hidden="true">›</span>
                    <span><?php the_title(); ?></span>
                </div>
                <h1 class="wl-hero__title"><?php the_title(); ?></h1>
                <?php
                $modified = get_the_modified_date('d F Y');
                if ($modified) :
                ?>
                <p class="wl-hero__meta">
                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                    Ultimo aggiornamento: <strong><?php echo esc_html($modified); ?></strong>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- BODY -->
<section class="wl-body">
    <div class="ws-container">
        <div class="wl-layout">

            <!-- SIDEBAR INDICE -->
            <aside class="wl-toc" id="wl-toc" aria-label="Indice della pagina">
                <div class="wl-toc__sticky">
                    <p class="wl-toc__label">
                        <i class="fa-solid fa-list" aria-hidden="true"></i> Indice
                    </p>
                    <nav id="wl-toc-nav"></nav>
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
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <div class="wl-prose">
                        <?php the_content(); ?>
                    </div>
                <?php endwhile; endif; ?>

                <!-- LINK CORRELATI -->
                <div class="wl-related">
                    <p class="wl-related__label">Documenti correlati</p>
                    <div class="wl-related__links">
                        <?php if ($slug !== 'privacy-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Privacy Policy</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($slug !== 'cookie-policy') : ?>
                        <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-cookie-bite"></i>
                            <span>Cookie Policy</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($slug !== 'note-legali') : ?>
                        <a href="<?php echo esc_url(home_url('/note-legali/')); ?>" class="wl-related__card">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Note Legali</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

            </article>

        </div>
    </div>
</section>

<script>
// Genera automaticamente l'indice dai titoli h2 del contenuto
document.addEventListener('DOMContentLoaded', function () {
    const content = document.getElementById('wl-content');
    const nav = document.getElementById('wl-toc-nav');
    if (!content || !nav) return;

    const headings = content.querySelectorAll('h2');
    if (headings.length === 0) {
        document.getElementById('wl-toc').style.display = 'none';
        return;
    }

    headings.forEach(function (h, i) {
        const id = 'wl-section-' + i;
        h.id = id;
        const a = document.createElement('a');
        a.href = '#' + id;
        a.textContent = h.textContent;
        a.className = 'wl-toc__link';
        nav.appendChild(a);
    });

    // Highlight voce attiva allo scroll
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            const link = nav.querySelector('a[href="#' + entry.target.id + '"]');
            if (!link) return;
            if (entry.isIntersecting) {
                nav.querySelectorAll('a').forEach(function (a) { a.classList.remove('wl-toc__link--active'); });
                link.classList.add('wl-toc__link--active');
            }
        });
    }, { rootMargin: '-10% 0px -80% 0px' });

    headings.forEach(function (h) { observer.observe(h); });
});
</script>

<?php
wecoop_ws_page_shell_end();
get_footer();
