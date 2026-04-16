    </div>

    <footer class="ws-footer">
        <div class="ws-container">
            <div class="ws-grid-4">
                <div>
                    <div class="ws-footer-brand">
                        <img class="ws-footer-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                        <span>WECOOP</span>
                    </div>
                    <p><?php echo esc_html(wecoop_t('Un ecosistema di inclusione e opportunita per tutti.', 'Un ecosistema di inclusione e opportunita per tutti.')); ?></p>
                </div>
                <div>
                    <h4><?php echo esc_html(wecoop_t('WECOOP', 'WECOOP')); ?></h4>
                    <a href="<?php echo esc_url(home_url('/#que-es')); ?>"><?php echo esc_html(wecoop_t('Cos\'e WECOOP', 'Cos\'e WECOOP')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#servizi')); ?>"><?php echo esc_html(wecoop_t('Servizi', 'Servizi')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#come-funziona')); ?>"><?php echo esc_html(wecoop_t('Come funziona', 'Come funziona')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#passaparola')); ?>"><?php echo esc_html(wecoop_t('Passaparola', 'Passaparola')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#plataforma')); ?>"><?php echo esc_html(wecoop_t('Piattaforma Digitale', 'Piattaforma Digitale')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#impacto')); ?>"><?php echo esc_html(wecoop_t('Impatto', 'Impatto')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#contacto')); ?>"><?php echo esc_html(wecoop_t('Contatti', 'Contatti')); ?></a>
                </div>
                <div>
                    <h4><?php echo esc_html(wecoop_t('Collabora', 'Collabora')); ?></h4>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html(wecoop_t('Imprese', 'Imprese')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html(wecoop_t('Istituzioni', 'Istituzioni')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html(wecoop_t('Fondazioni', 'Fondazioni')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html(wecoop_t('Volontari', 'Volontari')); ?></a>
                </div>
                <div>
                    <h4><?php echo esc_html(wecoop_t('Contatto', 'Contatto')); ?></h4>
                    <span><?php echo esc_html(wecoop_t('Via Populonia 8, Milano, Italia', 'Via Populonia 8, Milano, Italia')); ?></span>
                    <span>info@wecoop.org</span>
                    <span>+39 351 511 2113</span>
                </div>
            </div>
            <div class="ws-footer-bottom">
                <p>&copy; <?php echo esc_html(gmdate('Y')); ?> WECOOP. <?php echo esc_html(wecoop_t('Tutti i diritti riservati.', 'Tutti i diritti riservati.')); ?></p>
                <div class="ws-footer-brands">
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
