<?php
get_header();
wecoop_ws_page_shell_start(translate_string('contact.aria.page', 'Contact page'));

$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};

$wa_phone = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393341390175'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, vorrei parlare con un operatore.'));
$whatsapp_url = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
?>

<section class="ws-section ws-section--cta">
    <div class="ws-container ws-grid-2 ws-pass">
        <div>
            <h1><?php echo esc_html($tr('contact.page.hero.title', 'Contattaci')); ?></h1>
            <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('contact.page.hero.subtitle', 'Siamo qui per aiutarti. Parlaci e ti guideremo.')); ?></p>
            <div class="ws-actions">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($tr('contact.page.cta.whatsapp', 'Scrivici su WhatsApp')); ?></a>
                <a class="ws-btn ws-btn--ghost" href="tel:+3902XXXXXXX"><?php echo esc_html($tr('contact.page.cta.call', 'Chiama ora')); ?></a>
            </div>
            <p class="ws-kpi"><?php echo esc_html($tr('contact.page.microcopy', 'Ti rispondiamo rapidamente')); ?></p>
        </div>
        <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="<?php echo esc_attr($tr('contact.page.hero.image_alt', 'Contatti WECOOP')); ?>">
    </div>
</section>

<section class="ws-section">
    <div class="ws-container ws-grid-2 ws-pass">
        <div>
            <h2><?php echo esc_html($tr('contact.page.booking.title', 'Prenota un appuntamento')); ?></h2>
            <p class="ws-lead"><?php echo esc_html($tr('contact.page.booking.subtitle', 'Prenota un incontro con un operatore WECOOP.')); ?></p>
            <p><a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html($tr('contact.page.booking.cta', 'Prenota')); ?></a></p>

            <h2 style="margin-top:24px;"><?php echo esc_html($tr('contact.page.location.title', 'Dove siamo')); ?></h2>
            <ul class="ws-contact-list">
                <li><strong><?php echo esc_html($tr('contact.page.location.address_label', 'Indirizzo')); ?></strong><span><?php echo esc_html($tr('contact.page.location.address_value', 'Via Populonia 8, Milano')); ?></span></li>
                <li><strong><?php echo esc_html($tr('contact.page.location.hours_label', 'Orari')); ?></strong><span><?php echo esc_html($tr('contact.page.location.hours_value', 'Lunedi - Venerdi')); ?></span></li>
                <li><strong><?php echo esc_html($tr('contact.page.location.map_label', 'Mappa')); ?></strong><span><a href="https://maps.google.com/?q=Via+Populonia+8,+Milano" target="_blank" rel="noopener"><?php echo esc_html($tr('contact.page.location.map_cta', 'Apri su Google Maps')); ?></a></span></li>
            </ul>
        </div>
        <div class="ws-form-shell">
            <h2><?php echo esc_html($tr('contact.page.form.title', 'Oppure scrivici')); ?></h2>
            <?php echo do_shortcode('[wecoop_contact_form]'); ?>
        </div>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
