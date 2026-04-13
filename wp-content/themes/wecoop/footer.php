    </div>

    <footer class="wecoop-footer">
        <div class="wecoop-footer__inner">
            <div>
                <h3>WECOOP</h3>
                <p>Un ecosistema de inclusion y oportunidades para todos.</p>
                <ul class="wecoop-footer__menu">
                    <li><a href="<?php echo esc_url(home_url('/#que-es')); ?>">Que es WECOOP</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#servizi')); ?>">Servizi</a></li>
                    <li><a href="<?php echo esc_url(home_url('/annunci-lavoro-wecoop/')); ?>">Offerte di lavoro</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#passaparola')); ?>">Passaparola</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#plataforma')); ?>">Plataforma Digital</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#impacto')); ?>">Impacto</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#contacto')); ?>">Contacto</a></li>
                </ul>
                <div class="wecoop-footer__brand-logos">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_3@3x.png'); ?>" alt="Passaparola">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/refactor/Recurso_1@3x.png'); ?>" alt="App WECOOP">
                </div>
            </div>

            <div>
                <h4>Colabora</h4>
                <ul class="wecoop-footer__menu">
                    <li><a href="<?php echo esc_url(home_url('/#colabora')); ?>">Empresas</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#colabora')); ?>">Instituciones</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#colabora')); ?>">Fundaciones</a></li>
                    <li><a href="<?php echo esc_url(home_url('/#colabora')); ?>">Voluntarios</a></li>
                </ul>
            </div>

            <div>
                <h4>Contacto</h4>
                <ul class="wecoop-footer__menu">
                    <li>Via Populonia 8, Milano</li>
                    <li><a href="mailto:info@wecoop.org">info@wecoop.org</a></li>
                    <li><a href="tel:+393515112113">+39 351 511 2113</a></li>
                </ul>
            </div>
        </div>

        <div class="wecoop-footer__bottom">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> WECOOP. Todos los derechos reservados.</p>
        </div>
    </footer>

    <?php echo do_shortcode('[wecoop_whatsapp]'); ?>
    <?php wp_footer(); ?>
</body>
</html>
