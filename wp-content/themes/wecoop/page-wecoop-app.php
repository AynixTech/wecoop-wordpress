<?php
get_header();
wecoop_ws_page_shell_start(translate_string('app.aria.page', 'WECOOP app page'));

$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};

$wa_phone = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113'));
$wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, ho bisogno di supporto sulla piattaforma digitale.'));
$whatsapp_url = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
?>

<section class="ws-section ws-section--soft-blue">
    <div class="ws-container ws-grid-2 ws-pass">
        <div>
            <img class="ws-logo-app" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
            <h1><?php echo esc_html($tr('app.page.hero.title', 'Piattaforma Digitale WECOOP')); ?></h1>
            <p class="ws-lead"><?php echo esc_html($tr('app.page.hero.subtitle', 'La piattaforma digitale che ti aiuta ad accedere ai servizi e seguire il tuo percorso in modo semplice.')); ?></p>
            <ul class="ws-checks">
                <li><?php echo esc_html($tr('app.page.feature1', 'Gestione richieste servizi')); ?></li>
                <li><?php echo esc_html($tr('app.page.feature2', 'Caricamento e archiviazione documenti')); ?></li>
                <li><?php echo esc_html($tr('app.page.feature3', 'Firma e validazione pratiche')); ?></li>
                <li><?php echo esc_html($tr('app.page.feature4', 'Monitoraggio stato in tempo reale')); ?></li>
            </ul>
            <div class="ws-actions">
                <a class="ws-btn ws-btn--primary" href="<?php echo esc_url(home_url('/wecoop-app/')); ?>"><?php echo esc_html($tr('app.page.cta.register', 'Registrati alla piattaforma')); ?></a>
                <a class="ws-btn ws-btn--ghost" href="#"><?php echo esc_html($tr('app.page.cta.login', 'Accedi ai servizi digitali')); ?></a>
            </div>
            <div class="ws-actions" style="margin-top:10px;">
                <a class="ws-btn ws-btn--ghost" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($tr('app.page.cta.help', 'Hai bisogno di aiuto? Scrivici su WhatsApp')); ?></a>
            </div>
        </div>
        <div class="ws-grid-2 ws-media-grid">
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_navigating_a_mobile_app_for_booking_appointments_and_accessing_services,_finge_122886.png'); ?>" alt="<?php echo esc_attr($tr('app.page.image1_alt', 'App mobile WECOOP')); ?>">
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_person_using_a_smartphone_and_laptop_to_access_online_services_platform,_clean_modern_536372_(1).png'); ?>" alt="<?php echo esc_attr($tr('app.page.image2_alt', 'Piattaforma servizi digitali')); ?>">
            <img class="ws-img ws-img--wide" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_social_worker_managing_appointments_and_digital_services_on_a_laptop_while_talking_wi_122886.png'); ?>" alt="<?php echo esc_attr($tr('app.page.image3_alt', 'Gestione servizi WECOOP')); ?>">
        </div>
    </div>
</section>

<?php
wecoop_ws_page_shell_end();
get_footer();
