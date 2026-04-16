    </div>

    <?php $_ftr = 'translate_string'; ?>
    <footer class="ws-footer">
        <div class="ws-container">
            <div class="ws-grid-4">
                <div>
                    <div class="ws-footer-brand">
                        <img class="ws-footer-logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/wecooplogo2.png'); ?>" alt="WECOOP">
                        <span>WECOOP</span>
                    </div>
                    <p><?php echo esc_html($_ftr('frontpage.footer.description', 'Un ecosistema di inclusione e opportunita per tutti.')); ?></p>
                </div>
                <div>
                    <h4><?php echo esc_html($_ftr('frontpage.footer.col1_title', 'WECOOP')); ?></h4>
                    <a href="<?php echo esc_url(home_url('/#que-es')); ?>"><?php echo esc_html($_ftr('frontpage.nav.about', "Cos'e WECOOP")); ?></a>
                    <a href="<?php echo esc_url(home_url('/#servizi')); ?>"><?php echo esc_html($_ftr('frontpage.nav.services', 'Servizi')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#come-funziona')); ?>"><?php echo esc_html($_ftr('frontpage.nav.how', 'Come funziona')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#passaparola')); ?>"><?php echo esc_html($_ftr('frontpage.nav.passaparola', 'Passaparola')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#plataforma')); ?>"><?php echo esc_html($_ftr('frontpage.nav.platform', 'Piattaforma Digitale')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#impacto')); ?>"><?php echo esc_html($_ftr('frontpage.nav.impact', 'Impatto')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#contacto')); ?>"><?php echo esc_html($_ftr('frontpage.nav.contact', 'Contatti')); ?></a>
                </div>
                <div>
                    <h4><?php echo esc_html($_ftr('frontpage.footer.col2_title', 'Collabora')); ?></h4>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html($_ftr('frontpage.footer.col2_link1', 'Imprese')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html($_ftr('frontpage.footer.col2_link2', 'Istituzioni')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html($_ftr('frontpage.footer.col2_link3', 'Fondazioni')); ?></a>
                    <a href="<?php echo esc_url(home_url('/#colabora')); ?>"><?php echo esc_html($_ftr('frontpage.footer.col2_link4', 'Volontari')); ?></a>
                </div>
                <div>
                    <h4><?php echo esc_html($_ftr('frontpage.footer.col3_title', 'Contatto')); ?></h4>
                    <span>Via Populonia 8, Milano, Italia</span>
                    <span>info@wecoop.org</span>
                    <span>+39 351 511 2113</span>
                </div>
            </div>
            <div class="ws-footer-bottom">
                <p><?php echo esc_html($_ftr('frontpage.footer.rights', '© ' . gmdate('Y') . ' WECOOP. Tutti i diritti riservati.')); ?></p>
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
