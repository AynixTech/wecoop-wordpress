    </div>

    <footer class="wecoop-footer">
        <div class="wecoop-footer__inner">
            <div>
                <h3>WECOOP</h3>
                <p><?php echo esc_html(wecoop_t('Punto de acceso a servicios, formacion y trabajo en red.', 'Punto di accesso a servizi, formazione e lavoro in rete.')); ?></p>
                <p>Via Benefattori dell'Ospedale, 3 - Milano</p>
                <p>CF 97977210158</p>
            </div>

            <div>
                <h4><?php echo esc_html(wecoop_t('Menu', 'Menu')); ?></h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer-menu',
                    'container' => false,
                    'menu_class' => 'wecoop-footer__menu',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>

            <div>
                <h4><?php echo esc_html(wecoop_t('Newsletter', 'Newsletter')); ?></h4>
                <?php echo do_shortcode('[wecoop_newsletter]'); ?>
                <div class="wecoop-footer__social">
                    <a href="https://www.facebook.com/profile.php?id=61568241435990" target="_blank" rel="noopener">Facebook</a>
                    <a href="https://www.instagram.com/wecoop_aps" target="_blank" rel="noopener">Instagram</a>
                </div>
            </div>
        </div>

        <div class="wecoop-footer__bottom">
            <p>&copy; <?php echo esc_html(gmdate('Y')); ?> WECOOP - <?php echo esc_html(wecoop_t('Todos los derechos reservados.', 'Tutti i diritti riservati.')); ?></p>
        </div>
    </footer>

    <?php echo do_shortcode('[wecoop_whatsapp]'); ?>
    <?php wp_footer(); ?>
</body>
</html>
