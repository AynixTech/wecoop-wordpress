<footer>
    <div class="overlay-layer"></div>
    <div class="container-footer">
        <div class="col-md-4">
            <div class="logo-footer">
                <a href="<?php echo esc_url(home_url()); ?>">
                    <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/05/wecooplogo2.png')); ?>" alt="WeCoop footer logo" />
                </a>
                <p class="description"><?php echo theme_translate('footer.site_description'); ?></p>
                <p class="description">WeCoop</p>
                <p class="description"><?php echo theme_translate('footer.association_type'); ?></p>
                <p class="description"><?php echo theme_translate('footer.address'); ?></p>
                <p class="description">20159 Milano (MI)</p>
                <p class="description"><?php echo theme_translate('footer.tax_code'); ?>: CF 97977210158</p>
            </div>

            <a href="https://wa.me/393341390175?text=Ciao%20WeCoop!" class="whatsapp-link" target="_blank">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" class="whatsapp-icon">
                <?php echo theme_translate('footer.contact_us'); ?>
            </a>

            <div class="language-switcher">
                <label for="language-select"><?php echo theme_translate('select_language'); ?>:</label>
                <select id="language-select" style="cursor: pointer;">
                    <?php 
                    $current_lang = isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : 'it';
                    if (!in_array($current_lang, ['it', 'en', 'es'])) {
                        $current_lang = 'it';
                    }
                    ?>
                    <option value="it" <?php echo $current_lang === 'it' ? 'selected' : ''; ?>>🇮🇹 Italiano</option>
                    <option value="en" <?php echo $current_lang === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                    <option value="es" <?php echo $current_lang === 'es' ? 'selected' : ''; ?>>🇪🇸 Español</option>
                </select>
            </div>

            <script>
            // All'avvio, leggi da localStorage e imposta il selettore
            (function() {
                try {
                    const savedLang = localStorage.getItem('site_lang');
                    console.log('[WECOOP] localStorage lingua:', savedLang);
                    
                    if (savedLang && ['it', 'en', 'es'].includes(savedLang)) {
                        const select = document.getElementById('language-select');
                        console.log('[WECOOP] Select corrente:', select.value);
                        console.log('[WECOOP] Lingua salvata:', savedLang);
                        
                        if (select.value !== savedLang) {
                            console.log('[WECOOP] Reindirizzamento a:', window.location.pathname + '?lang=' + savedLang);
                            // Reindirizza con parametro per forzare il cambio lingua
                            window.location.href = window.location.pathname + '?lang=' + savedLang;
                        } else {
                            console.log('[WECOOP] Lingua già corretta');
                        }
                    } else {
                        console.log('[WECOOP] Nessuna lingua salvata in localStorage');
                    }
                } catch(e) {
                    console.error('[WECOOP] Errore localStorage:', e);
                }
            })();

            // Quando cambi lingua dal selettore
            document.getElementById('language-select').addEventListener('change', function () {
                const selectedLang = this.value;
                console.log('[WECOOP] Cambiata lingua a:', selectedLang);
                
                // 1. Salva in localStorage (persiste sempre, anche senza cookie)
                try {
                    localStorage.setItem('site_lang', selectedLang);
                    console.log('[WECOOP] Salvata in localStorage');
                } catch(e) {
                    console.error('[WECOOP] Errore salvataggio localStorage:', e);
                }
                
                // 2. Salva cookie (per PHP)
                const expires = new Date();
                expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
                document.cookie = "site_lang=" + selectedLang + ";expires=" + expires.toUTCString() + ";path=/";
                console.log('[WECOOP] Cookie salvato:', document.cookie);
                
                // 3. Reindirizza con parametro URL (fallback garantito)
                const newUrl = window.location.pathname + '?lang=' + selectedLang;
                console.log('[WECOOP] Reindirizzamento a:', newUrl);
                window.location.href = newUrl;
            });
            </script>
        </div>

        <div class="col-md-4 links-container">
            <h5><?php echo theme_translate('footer.links'); ?></h5>
            <ul>
                <li><a href="/chi-siamo"><?php echo theme_translate('footer.about_us'); ?></a></li>
                <li><a href="/diventa-volontario"><?php echo theme_translate('footer.become_volunteer'); ?></a></li>
                <li><a href="/hai-un-idea"><?php echo theme_translate('footer.have_idea'); ?></a></li>
                <li><a href="/sostienici"><?php echo theme_translate('footer.support_us'); ?></a></li>
            </ul>
        </div>

        <div class="col-md-4 social-icons">
            <h5><?php echo esc_html(theme_translate('follow_us')); ?></h5>
            <ul>
                <li><a href="https://www.facebook.com/profile.php?id=61568241435990" class="facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a></li>
                <li><a href="https://www.instagram.com/wecoop_aps?utm_source=qr&igsh=MXFraHN1cG4zNmh1ZA%3D%3D" class="instagram" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
            </ul>
        </div>
    </div>

    <div class="text-center">
        <p>&copy; <?php echo date("Y"); ?> <?php bloginfo('name'); ?> - <?php echo theme_translate('footer.all_rights_reserved'); ?>.</p>
    </div>

    <?php wp_footer(); ?>
</footer>

<div id="page-loader">
    <div class="loader"></div>
</div>
