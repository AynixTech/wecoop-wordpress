    </div>

    <?php
    $_ftr    = 'translate_string';
    $wa_phone   = preg_replace('/[^0-9]/', '', (string) get_option('wecoop_whatsapp_number', '393515112113'));
    $wa_message = rawurlencode((string) get_option('wecoop_whatsapp_message', 'Ciao WECOOP, ho bisogno di supporto.'));
    $wa_url     = 'https://wa.me/' . $wa_phone . '?text=' . $wa_message;
    ?>
    <footer class="ws-footer">
        <div class="ws-container">

            <!-- COLONNE PRINCIPALI -->
            <div class="ws-grid-4 ws-footer-cols">

                <!-- Col 1: Identità -->
                <div class="ws-footer-col">
                    <div class="ws-footer-brand">
                        <img class="ws-footer-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                        <span>WECOOP</span>
                    </div>
                    <p class="ws-footer-desc"><?php echo esc_html($_ftr('footer.col1.desc', 'Un ecosistema di servizi, formazione e opportunità per l\'inclusione sociale e lavorativa.')); ?></p>
                </div>

                <!-- Col 2: Navigazione -->
                <div class="ws-footer-col">
                    <h4><?php echo esc_html($_ftr('footer.col2.title', 'WECOOP')); ?></h4>
                    <nav aria-label="<?php echo esc_attr($_ftr('footer.col2.aria', 'Footer navigazione WECOOP')); ?>">
                        <a href="<?php echo esc_url(home_url('/cos-e-wecoop/')); ?>"><?php echo esc_html($_ftr('footer.col2.link1', "Cos'è WECOOP")); ?></a>
                        <a href="<?php echo esc_url(home_url('/servizi/')); ?>"><?php echo esc_html($_ftr('footer.col2.link2', 'Servizi')); ?></a>
                        <a href="<?php echo esc_url(home_url('/come-funziona-wecoop/')); ?>"><?php echo esc_html($_ftr('footer.col2.link3', 'Come funziona')); ?></a>
                        <a href="<?php echo esc_url(home_url('/passaparola/')); ?>"><?php echo esc_html($_ftr('footer.col2.link4', 'Passaparola')); ?></a>
                        <a href="<?php echo esc_url(home_url('/piattaforma/')); ?>"><?php echo esc_html($_ftr('footer.col2.link5', 'Piattaforma Digitale')); ?></a>
                        <a href="<?php echo esc_url(home_url('/impatto/')); ?>"><?php echo esc_html($_ftr('footer.col2.link6', 'Impatto')); ?></a>
                        <a href="<?php echo esc_url(home_url('/contatti/')); ?>"><?php echo esc_html($_ftr('footer.col2.link7', 'Contatti')); ?></a>
                    </nav>
                </div>

                <!-- Col 3: Collabora (azioni) -->
                <div class="ws-footer-col">
                    <h4><?php echo esc_html($_ftr('footer.col3.title', 'Collabora')); ?></h4>
                    <nav aria-label="<?php echo esc_attr($_ftr('footer.col3.aria', 'Footer collabora')); ?>">
                        <a href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($_ftr('footer.col3.link1', 'Collabora con WECOOP')); ?></a>
                        <a href="<?php echo esc_url(home_url('/collaborate-with-us/')); ?>"><?php echo esc_html($_ftr('footer.col3.link2', 'Diventa partner')); ?></a>
                        <a href="<?php echo esc_url(home_url('/passaparola/')); ?>"><?php echo esc_html($_ftr('footer.col3.link3', 'Progetti e iniziative')); ?></a>
                        <a href="<?php echo esc_url(home_url('/contatti/')); ?>"><?php echo esc_html($_ftr('footer.col3.link4', 'Contattaci')); ?></a>
                    </nav>
                </div>

                <!-- Col 4: Contatti -->
                <div class="ws-footer-col">
                    <h4><?php echo esc_html($_ftr('footer.col4.title', 'Contatti')); ?></h4>
                    <ul class="ws-footer-contact-list">
                        <li>
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            <span>Via Populonia 8, Milano, Italia</span>
                        </li>
                        <li>
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:info@wecoop.org">info@wecoop.org</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <a href="tel:+393515112113">+39 351 511 2113</a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- MINI CTA -->
            <div class="ws-footer-cta">
                <p class="ws-footer-cta__text"><?php echo esc_html($_ftr('footer.cta.title', 'Hai bisogno di supporto?')); ?></p>
                <div class="ws-footer-cta__actions">
                    <a class="ws-btn ws-btn--primary ws-btn--sm" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <?php echo esc_html($_ftr('footer.cta.whatsapp', 'Scrivici su WhatsApp')); ?>
                    </a>
                    <a class="ws-btn ws-btn--ghost ws-btn--sm" href="<?php echo esc_url(home_url('/contatti/')); ?>">
                        <?php echo esc_html($_ftr('footer.cta.contact', 'Contattaci')); ?>
                    </a>
                </div>
                <p class="ws-footer-cta__microcopy"><?php echo esc_html($_ftr('footer.cta.microcopy', 'Ti rispondiamo rapidamente.')); ?></p>
            </div>

            <hr class="ws-footer-divider">

            <!-- FOOTER BOTTOM -->
            <div class="ws-footer-bottom">
                <p><?php echo esc_html('© ' . gmdate('Y') . ' WECOOP. ' . $_ftr('footer.bottom.rights', 'Tutti i diritti riservati.')); ?></p>
                <nav class="ws-footer-legal" aria-label="<?php echo esc_attr($_ftr('footer.bottom.legal_aria', 'Link legali')); ?>">
                    <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php echo esc_html($_ftr('footer.bottom.privacy', 'Privacy Policy')); ?></a>
                    <span aria-hidden="true">·</span>
                    <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>"><?php echo esc_html($_ftr('footer.bottom.cookie', 'Cookie Policy')); ?></a>
                    <span aria-hidden="true">·</span>
                    <a href="<?php echo esc_url(home_url('/note-legali/')); ?>"><?php echo esc_html($_ftr('footer.bottom.legal', 'Note Legali')); ?></a>
                </nav>
                <div class="ws-footer-brands">
                    <span class="ws-footer-brands__label"><?php echo esc_html($_ftr('footer.bottom.brands_label', 'Progetti e piattaforma WECOOP')); ?></span>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="APP WECOOP">
                </div>
            </div>

        </div>
    </footer>

    <?php echo do_shortcode('[wecoop_whatsapp]'); ?>
    <?php wp_footer(); ?>
</body>
</html>
