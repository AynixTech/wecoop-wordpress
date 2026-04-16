<?php
/**
 * Template Name: Contatti
 */
get_header();
$tr = 'translate_string';
wecoop_ws_page_shell_start($tr('contact.aria.page', 'Contatti WECOOP'));

$wa_phone   = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei parlare con un operatore.'));
$wa_url     = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
?>

    <!-- HERO -->
    <section class="cw-hero" id="contatti-hero">
        <div class="ws-container">
            <div class="cw-hero__inner">
                <div class="cw-hero__text">
                    <span class="cw-eyebrow"><?php echo esc_html($tr('contact.hero.eyebrow', 'Siamo qui per te')); ?></span>
                    <h1><?php echo esc_html($tr('contact.hero.title', 'Contattaci')); ?></h1>
                    <p class="cw-hero__lead"><?php echo esc_html($tr('contact.hero.subtitle', 'Siamo qui per aiutarti. Contattaci per ricevere supporto sui servizi WECOOP o per costruire nuove collaborazioni.')); ?></p>
                    <div class="ws-hero-ctas">
                        <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <?php echo esc_html($tr('contact.hero.cta1', 'Scrivici su WhatsApp')); ?>
                        </a>
                        <a class="ws-btn cw-btn--ghost" href="tel:+393515112113">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <?php echo esc_html($tr('contact.hero.cta2', 'Chiama ora')); ?>
                        </a>
                    </div>
                    <p class="ws-microcopy">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo esc_html($tr('contact.hero.microcopy', 'Ti rispondiamo rapidamente e ti aiutiamo a capire da dove iniziare.')); ?>
                    </p>
                </div>
                <div class="cw-hero__badges" aria-hidden="true">
                    <div class="cw-badge cw-badge--blue"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></div>
                    <div class="cw-badge cw-badge--green"><i class="fa-solid fa-location-dot"></i><span>Milano</span></div>
                    <div class="cw-badge cw-badge--pink"><i class="fa-regular fa-envelope"></i><span>Email</span></div>
                    <div class="cw-badge cw-badge--yellow"><i class="fa-solid fa-phone"></i><span><?php echo esc_html($tr('contact.hero.badge4', 'Telefono')); ?></span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- INFO + FORM -->
    <section class="ws-section" id="contatti-main">
        <div class="ws-container">
            <div class="ws-grid-2 ws-contact-grid">

                <!-- Colonna sinistra: informazioni di contatto -->
                <div class="ws-contact-info">
                    <h2><?php echo esc_html($tr('contact.info.title', 'Come contattarci')); ?></h2>

                    <ul class="ws-contact-list ws-contact-list--icons">
                        <li>
                            <span class="ws-contact-list__icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html($tr('contact.info.address.label', 'Indirizzo')); ?></strong>
                                <span><?php echo esc_html($tr('contact.info.address.value', 'Via Populonia 8, Milano, Italia')); ?></span>
                                <a href="https://maps.google.com/?q=Via+Populonia+8,+Milano" target="_blank" rel="noopener" class="ws-contact-list__link">
                                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    <?php echo esc_html($tr('contact.info.address.map', 'Apri su Google Maps')); ?>
                                </a>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html($tr('contact.info.phone.label', 'Telefono')); ?></strong>
                                <a href="tel:+393515112113" class="ws-contact-list__link">+39 351 511 2113</a>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html($tr('contact.info.email.label', 'Email')); ?></strong>
                                <a href="mailto:info@wecoop.org" class="ws-contact-list__link">info@wecoop.org</a>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
                            <div>
                                <strong><?php echo esc_html($tr('contact.info.hours.label', 'Orari')); ?></strong>
                                <span><?php echo esc_html($tr('contact.info.hours.value', 'Lunedì – Venerdì')); ?></span>
                            </div>
                        </li>
                        <li>
                            <span class="ws-contact-list__icon ws-contact-list__icon--whatsapp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                            <div>
                                <strong>WhatsApp</strong>
                                <a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer" class="ws-contact-list__link">
                                    <?php echo esc_html($tr('contact.info.whatsapp.cta', 'Scrivici su WhatsApp')); ?>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Colonna destra: form -->
                <div class="ws-form-shell">
                    <h2><?php echo esc_html($tr('contact.form.section_title', 'Oppure inviaci un messaggio')); ?></h2>
                    <p><?php echo esc_html($tr('contact.form.section_lead', 'Compila il modulo e ti risponderemo il prima possibile.')); ?></p>
                    <?php echo do_shortcode('[wecoop_contact_form]'); ?>
                </div>

            </div>
        </div>
    </section>

    <!-- MAPPA -->
    <section class="ws-section ws-section--soft" id="contatti-mappa">
        <div class="ws-container">
            <h2><?php echo esc_html($tr('contact.map.title', 'Dove siamo')); ?></h2>
            <div class="ws-map-embed" style="border-radius:16px; overflow:hidden; margin-top:20px; box-shadow:0 4px 20px rgba(0,0,0,.08);">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2797.8!2d9.1734!3d45.4566!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4786c6b0e7a3f4a9%3A0x0!2sVia+Populonia+8%2C+20135+Milano+MI!5e0!3m2!1sit!2sit!4v1"
                    width="100%" height="380" style="border:0; display:block;"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    title="<?php echo esc_attr($tr('contact.map.iframe_title', 'Mappa sede WECOOP - Via Populonia 8, Milano')); ?>">
                </iframe>
            </div>
        </div>
    </section>

<?php
wecoop_ws_page_shell_end();
get_footer();
