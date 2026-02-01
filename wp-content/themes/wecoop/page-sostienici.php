<?php
/**
 * Template Name: Sostienici
 */
get_header(); ?>

<main class="sostienici-page">
    <!-- Hero Section -->
    <section class="sostienici-hero">
        <div class="container">
            <h1><?php echo theme_translate('support_page.hero.title'); ?></h1>
            <p class="hero-subtitle"><?php echo theme_translate('support_page.hero.subtitle'); ?></p>
            <a href="#5x1000" class="btn btn-primary btn-hero">
                <?php echo theme_translate('support_page.hero.cta'); ?>
                <span class="btn-microcopy"><?php echo theme_translate('support_page.hero.cta_microcopy'); ?></span>
            </a>
        </div>
    </section>

    <div class="container sostienici-container">
        <!-- Sezione 5x1000 -->
        <section id="5x1000" class="section-5x1000">
            <div class="section-content">
                <h2><?php echo theme_translate('support_page.5x1000.title'); ?></h2>
                <p class="section-description"><?php echo theme_translate('support_page.5x1000.description'); ?></p>
                
                <div class="codice-fiscale-box">
                    <label class="cf-label"><?php echo theme_translate('support_page.5x1000.cf_label'); ?></label>
                    <div class="cf-display" id="codiceFiscale">97977210158</div>
                    <button class="btn btn-copy" id="copyCfBtn" onclick="copyCF()">
                        <i class="fas fa-copy"></i> <?php echo theme_translate('support_page.5x1000.copy_btn'); ?>
                    </button>
                    <p class="cf-note"><?php echo theme_translate('support_page.5x1000.note'); ?></p>
                </div>
                
                <div id="copyToast" class="copy-toast"><?php echo theme_translate('support_page.5x1000.copied_message'); ?></div>
            </div>
        </section>

        <!-- A cosa serve il tuo sostegno -->
        <section class="section-purpose">
            <div class="section-content">
                <h2><?php echo theme_translate('support_page.purpose.title'); ?></h2>
                <p class="section-intro"><?php echo theme_translate('support_page.purpose.intro'); ?></p>
                
                <ul class="purpose-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo theme_translate('support_page.purpose.item1'); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo theme_translate('support_page.purpose.item2'); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo theme_translate('support_page.purpose.item3'); ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo theme_translate('support_page.purpose.item4'); ?></span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Altri modi per sostenerci -->
        <section class="section-other-support">
            <div class="section-content">
                <h2><?php echo theme_translate('support_page.other_support.title'); ?></h2>
                <p class="section-description"><?php echo theme_translate('support_page.other_support.description'); ?></p>
                
                <div class="donation-box">
                    <div class="donation-info">
                        <div class="donation-field">
                            <label><?php echo theme_translate('support_page.other_support.beneficiary'); ?></label>
                            <div class="field-value">WECOOP APS</div>
                        </div>
                        <div class="donation-field">
                            <label>IBAN</label>
                            <div class="field-value iban-value">IT96O0569601614000008698X43</div>
                        </div>
                        <div class="donation-field">
                            <label><?php echo theme_translate('support_page.other_support.causale_label'); ?></label>
                            <div class="field-value"><?php echo theme_translate('support_page.other_support.causale'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Blocco fiducia / trasparenza -->
        <section class="section-transparency">
            <div class="section-content">
                <div class="transparency-box">
                    <p class="transparency-text"><?php echo theme_translate('support_page.transparency.text'); ?></p>
                    
                    <div class="transparency-details">
                        <div class="detail-item">
                            <strong><?php echo theme_translate('support_page.transparency.cf_label'); ?></strong>
                            <span>97977210158</span>
                        </div>
                        <div class="detail-item">
                            <strong><?php echo theme_translate('support_page.transparency.contacts_label'); ?></strong>
                            <span>
                                <a href="mailto:info@wecoop.org">info@wecoop.org</a><br>
                                <a href="https://www.wecoop.org" target="_blank">www.wecoop.org</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
function copyCF() {
    const cfText = document.getElementById('codiceFiscale').textContent;
    const toast = document.getElementById('copyToast');
    
    navigator.clipboard.writeText(cfText).then(function() {
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
        }, 2500);
    }).catch(function(err) {
        console.error('Errore nella copia:', err);
    });
}

// Smooth scroll per anchor link
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?php get_footer(); ?>
