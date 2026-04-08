<?php
get_header();

$tr = static function($key, $default = '') {
    return translate_string($key, $default);
};

wecoop_ws_page_shell_start($tr('collab.aria.page', 'Collaborate with us'));
?>

    <section class="ws-section ws-section--cta">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h1><?php echo esc_html($tr('frontpage.collab.title', 'Colabora con WECOOP')); ?></h1>
                <p class="ws-lead ws-lead--light"><?php echo esc_html($tr('frontpage.collab.lead', 'Unete a nuestra red de partners y contribuye a crear oportunidades de inclusion social y laboral.')); ?></p>
                <div class="ws-grid-2">
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card1.title', 'Instituciones Publicas')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card1.body', 'Alianzas estrategicas para ampliar el impacto')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card2.title', 'Empresas')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card2.body', 'Oportunidades de empleo y RSC')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card3.title', 'Fundaciones')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card3.body', 'Apoyo a proyectos de inclusion')); ?></p></article>
                    <article class="ws-glass"><h3><?php echo esc_html($tr('frontpage.collab.card4.title', 'Voluntarios')); ?></h3><p><?php echo esc_html($tr('frontpage.collab.card4.body', 'Comparte tu tiempo y talento')); ?></p></article>
                </div>
            </div>
            <img class="ws-img" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Firefly_Gemini_Flash_inspiring_realistic_photo_of_diverse_people_walking_together_in_an_urban_european_env_725555-3.png'); ?>" alt="<?php echo esc_attr($tr('frontpage.collab.image_alt', 'Colaboracion')); ?>">
        </div>
    </section>

    <section id="contacto" class="ws-section">
        <div class="ws-container ws-grid-2 ws-pass">
            <div>
                <h2><?php echo esc_html($tr('frontpage.contact.title', 'Contactanos')); ?></h2>
                <p class="ws-lead"><?php echo esc_html($tr('frontpage.contact.lead', 'Estamos aqui para escucharte. Contactanos para mas informacion sobre nuestros servicios o para explorar oportunidades de colaboracion.')); ?></p>
                <ul class="ws-contact-list">
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_address', 'Direccion')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_address', 'Via Populonia 8, Milano, Italia')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_email', 'Email')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_email', 'info@wecoop.it')); ?></span></li>
                    <li><strong><?php echo esc_html($tr('frontpage.contact.label_phone', 'Telefono')); ?></strong><span><?php echo esc_html($tr('frontpage.contact.value_phone', '+39 02 XXXX XXXX')); ?></span></li>
                </ul>
            </div>
            <div class="ws-form-shell">
                <?php echo do_shortcode('[wecoop_contact_form]'); ?>
            </div>
        </div>
    </section>


<?php
wecoop_ws_page_shell_end();
get_footer();
