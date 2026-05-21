<?php
/**
 * Template Name: Sostieni WECOOP
 * Template Post Type: page
 *
 * @package WeCoop
 */

get_header();

$tr = 'translate_string';
$support_cta_url = trim((string) get_option('wecoop_donation_url', ''));
if ($support_cta_url === '') {
    $support_cta_url = home_url('/contatti/');
}

wecoop_ws_page_shell_start($tr('donations.aria.page', 'Sostieni WECOOP - WeCoop'));
?>

<section class="cw-hero" id="sostieni-wecoop-hero">
    <div class="ws-container">
        <div class="cw-hero__inner">
            <div class="cw-hero__text">
                <span class="cw-eyebrow">
                    <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                    <?php echo esc_html($tr('donations.hero.eyebrow', 'Sostieni WECOOP')); ?>
                </span>
                <h1><?php echo esc_html($tr('donations.hero.title', 'Dona per creare opportunita concrete')); ?></h1>
                <p class="cw-hero__lead"><?php echo esc_html($tr('donations.hero.lead', 'Con il tuo contributo aiutiamo persone, famiglie, giovani e studenti internazionali ad accedere a servizi, orientamento e percorsi di inclusione sul territorio.')); ?></p>
            </div>
        </div>
    </div>
</section>

<section class="ws-section" id="sostieni-wecoop-main">
    <div class="ws-container">
        <div class="ws-grid-2">
            <article class="ws-form-shell">
                <h2>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <?php echo esc_html($tr('donations.five.title', '5x1000')); ?>
                </h2>
                <h3><?php echo esc_html($tr('donations.five.subtitle', 'Il tuo 5x1000 per WECOOP')); ?></h3>
                <p><?php echo esc_html($tr('donations.five.lead', 'Con il tuo 5x1000 puoi sostenere concretamente attivita di orientamento, inclusione e supporto per cittadini, famiglie, giovani e studenti internazionali.')); ?></p>
                <p><?php echo esc_html($tr('donations.five.services_intro', 'Attraverso WECOOP sviluppiamo servizi territoriali e digitali per facilitare l\'accesso a:')); ?></p>
                <ul class="ws-contact-list ws-contact-list--icons">
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-compass" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.five.item1', 'orientamento e accompagnamento')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-language" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.five.item2', 'mediazione linguistica')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-file-circle-check" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.five.item3', 'supporto amministrativo')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.five.item4', 'accesso al lavoro e alla formazione')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.five.item5', 'integrazione sociale')); ?></span></li>
                </ul>
                <p><strong><?php echo esc_html($tr('donations.five.cf_intro', 'Inserisci il nostro codice fiscale nella tua dichiarazione dei redditi:')); ?></strong></p>
                <p><strong><?php echo esc_html($tr('donations.five.cf_value', 'CF 97977210158')); ?></strong></p>
            </article>

            <article class="ws-form-shell">
                <h2>
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <?php echo esc_html($tr('donations.free.title', 'Donazione libera')); ?>
                </h2>
                <h3><?php echo esc_html($tr('donations.free.subtitle', 'Sostieni lo sviluppo delle attivita WECOOP')); ?></h3>
                <p><?php echo esc_html($tr('donations.free.lead', 'La tua donazione ci aiuta a sviluppare servizi concreti di supporto, inclusione e accompagnamento sul territorio.')); ?></p>
                <p><?php echo esc_html($tr('donations.free.can_intro', 'Con il tuo contributo possiamo:')); ?></p>
                <ul class="ws-contact-list ws-contact-list--icons">
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-life-ring" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.free.item1', 'supportare utenti in difficolta')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-laptop-code" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.free.item2', 'sviluppare nuovi servizi digitali')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.free.item3', 'migliorare il supporto operativo')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.free.item4', 'coinvolgere studenti e volontari')); ?></span></li>
                    <li><span class="ws-contact-list__icon"><i class="fa-solid fa-network-wired" aria-hidden="true"></i></span><span><?php echo esc_html($tr('donations.free.item5', 'ampliare la rete territoriale')); ?></span></li>
                </ul>
                <p><?php echo esc_html($tr('donations.free.closing', 'Ogni contributo aiuta WECOOP a costruire un impatto reale e sostenibile.')); ?></p>
                <p>
                    <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($support_cta_url); ?>">
                        <i class="fa-solid fa-heart" aria-hidden="true"></i>
                        <?php echo esc_html($tr('donations.free.cta', 'SOSTIENI WECOOP')); ?>
                    </a>
                </p>
            </article>
        </div>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
