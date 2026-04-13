<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Offerte_Lavoro_Frontend {

    public static function init() {
        add_shortcode('wecoop_annunci_lavoro_web', [__CLASS__, 'render_annunci_page']);
        add_shortcode('wecoop_annunci_cerco_servizi', [__CLASS__, 'render_cerco_servizi_page']);
        add_shortcode('wecoop_annunci_offro_servizi', [__CLASS__, 'render_offro_servizi_page']);
        add_action('init', [__CLASS__, 'maybe_create_default_page'], 25);
    }

    public static function render_cerco_servizi_page($atts = []) {
        $atts['force_scope'] = 'service';
        $atts['force_direction'] = 'seek';
        $atts['title'] = 'Cerco servizi';
        $atts['subtitle'] = 'Annunci di chi cerca un servizio.';
        return self::render_annunci_page($atts);
    }

    public static function render_offro_servizi_page($atts = []) {
        $atts['force_scope'] = 'service';
        $atts['force_direction'] = 'offer';
        $atts['title'] = 'Offro servizi';
        $atts['subtitle'] = 'Annunci di chi offre un servizio.';
        return self::render_annunci_page($atts);
    }

    public static function maybe_create_default_page() {
        $option_key = 'wecoop_offerte_lavoro_web_page_created_v1';
        if ((string) get_option($option_key, '0') === '1') {
            return;
        }

        $existing = get_page_by_path('annunci-lavoro-wecoop');
        if (!$existing) {
            wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'Annunci Lavoro WECOOP',
                'post_name' => 'annunci-lavoro-wecoop',
                'post_content' => '[wecoop_annunci_lavoro_web]',
            ]);
        }

        update_option($option_key, '1');
    }

    public static function render_annunci_page($atts = []) {
        $atts = shortcode_atts([
            'force_scope' => '',
            'force_direction' => '',
            'title' => 'Annunci lavoro e servizi',
            'subtitle' => 'Filtra gli annunci e distingui subito tra chi cerca e chi offre.',
        ], (array) $atts, 'wecoop_annunci_lavoro_web');

        $force_scope = in_array((string) $atts['force_scope'], ['job', 'service'], true)
            ? (string) $atts['force_scope']
            : '';
        $force_direction = in_array((string) $atts['force_direction'], ['seek', 'offer'], true)
            ? (string) $atts['force_direction']
            : '';

        $search = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $ambito = $force_direction !== ''
            ? $force_direction
            : (isset($_GET['ambito']) ? sanitize_text_field(wp_unslash($_GET['ambito'])) : 'all');
        $categoria = isset($_GET['categoria']) ? sanitize_text_field(wp_unslash($_GET['categoria'])) : '';
        $paged = max(1, (int) get_query_var('paged', 1));

        if (!in_array($ambito, ['all', 'seek', 'offer'], true)) {
            $ambito = 'all';
        }

        $meta_query = [];

        if ($ambito === 'seek') {
            $meta_query[] = [
                'key' => 'category_direction',
                'value' => 'seek',
                'compare' => '=',
            ];
        } elseif ($ambito === 'offer') {
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key' => 'category_direction',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'category_direction',
                    'value' => 'offer',
                    'compare' => '=',
                ],
            ];
        }

        if ($force_scope !== '') {
            $meta_query[] = [
                'key' => 'category_scope',
                'value' => $force_scope,
                'compare' => '=',
            ];
        }

        $args = [
            'post_type' => [
                WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT,
            ],
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $paged,
            'orderby' => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
            'meta_key' => 'is_featured',
        ];

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        if ($search !== '') {
            $args['s'] = $search;
        }

        if ($categoria !== '') {
            $args['tax_query'] = [[
                'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
                'field' => 'slug',
                'terms' => $categoria,
            ]];
        }

        $query = new WP_Query($args);
        $terms = get_terms([
            'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
            'hide_empty' => false,
        ]);

        ob_start();
        ?>
        <style>
            .wecoop-annunci-wrap { max-width: 980px; margin: 0 auto; }
            .wecoop-annunci-filters { display: grid; grid-template-columns: 1fr 180px 240px 120px; gap: 10px; margin: 16px 0 20px; }
            .wecoop-annunci-filters input,
            .wecoop-annunci-filters select,
            .wecoop-annunci-filters button { padding: 10px; border: 1px solid #d9d9d9; border-radius: 8px; }
            .wecoop-annunci-filters button { background: #0e7a3d; color: #fff; cursor: pointer; }
            .wecoop-annunci-grid { display: grid; gap: 14px; }
            .wecoop-annuncio-card { border: 1px solid #ececec; border-radius: 12px; padding: 14px; background: #fff; }
            .wecoop-annuncio-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
            .wecoop-annuncio-title { margin: 0 0 6px; font-size: 18px; }
            .wecoop-badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
            .wecoop-badge-offer { background: #eaf8ef; color: #17693a; border: 1px solid #bde8cb; }
            .wecoop-badge-seek { background: #fff4e7; color: #9b5600; border: 1px solid #ffd8ad; }
            .wecoop-annuncio-meta { font-size: 13px; color: #5a5a5a; margin-bottom: 8px; }
            .wecoop-annuncio-excerpt { margin: 0; color: #2f2f2f; }
            .wecoop-annuncio-actions { margin-top: 12px; }
            .wecoop-btn-dettagli { display: inline-block; background: #0e7a3d; color: #fff; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
            .wecoop-btn-dettagli:hover { background: #0b6532; }
            .wecoop-annunci-pagination { margin-top: 18px; display: flex; gap: 8px; flex-wrap: wrap; }
            .wecoop-annunci-pagination a,
            .wecoop-annunci-pagination span { padding: 8px 10px; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; }
            .wecoop-annunci-pagination .current { background: #0e7a3d; color: #fff; border-color: #0e7a3d; }
            @media (max-width: 760px) {
                .wecoop-annunci-filters { grid-template-columns: 1fr; }
                .wecoop-annuncio-head { align-items: flex-start; flex-direction: column; }
            }
        </style>

        <div class="wecoop-annunci-wrap">
            <h2><?php echo esc_html((string) $atts['title']); ?></h2>
            <p><?php echo esc_html((string) $atts['subtitle']); ?></p>

            <form method="get" class="wecoop-annunci-filters">
                <input type="text" name="q" placeholder="Cerca annuncio..." value="<?php echo esc_attr($search); ?>" />
                <?php if ($force_direction === '') : ?>
                    <select name="ambito">
                        <option value="all" <?php selected($ambito, 'all'); ?>>Tutti</option>
                        <option value="seek" <?php selected($ambito, 'seek'); ?>>Cerco</option>
                        <option value="offer" <?php selected($ambito, 'offer'); ?>>Offro</option>
                    </select>
                <?php else : ?>
                    <input type="hidden" name="ambito" value="<?php echo esc_attr($force_direction); ?>" />
                    <input type="hidden" name="scope" value="<?php echo esc_attr($force_scope); ?>" />
                    <input type="text" value="<?php echo esc_attr($force_direction === 'seek' ? 'Cerco' : 'Offro'); ?>" disabled="disabled" />
                <?php endif; ?>
                <select name="categoria">
                    <option value="">Tutte le categorie</option>
                    <?php
                    if (!is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->slug) . '" ' . selected($categoria, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
                <button type="submit">Filtra</button>
            </form>

            <?php if (!$query->have_posts()) : ?>
                <p>Nessun annuncio disponibile con questi filtri.</p>
            <?php else : ?>
                <div class="wecoop-annunci-grid">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        $id = get_the_ID();
                        $direction = (string) get_post_meta($id, 'category_direction', true);
                        if ($direction === '') {
                            $direction = 'offer';
                        }
                        $badge_class = $direction === 'seek' ? 'wecoop-badge-seek' : 'wecoop-badge-offer';
                        $badge_text = $direction === 'seek' ? 'Cerco' : 'Offro';
                        $city = (string) get_post_meta($id, 'city', true);
                        $contract_type = (string) get_post_meta($id, 'contract_type', true);
                        $excerpt = get_the_excerpt();
                        if (trim((string) $excerpt) === '') {
                            $excerpt = (string) get_post_meta($id, 'description', true);
                        }
                        ?>
                        <article class="wecoop-annuncio-card">
                            <div class="wecoop-annuncio-head">
                                <h3 class="wecoop-annuncio-title"><?php echo esc_html(get_the_title()); ?></h3>
                                <span class="wecoop-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></span>
                            </div>
                            <div class="wecoop-annuncio-meta">
                                <?php if ($city !== '') : ?>
                                    <span><?php echo esc_html($city); ?></span>
                                <?php endif; ?>
                                <?php if ($contract_type !== '') : ?>
                                    <span> · <?php echo esc_html($contract_type); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="wecoop-annuncio-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($excerpt), 30)); ?></p>
                            <div class="wecoop-annuncio-actions">
                                <a class="wecoop-btn-dettagli" href="<?php echo esc_url(get_permalink($id)); ?>">Dettagli annuncio</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php
                $pagination = paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $paged,
                    'total' => max(1, (int) $query->max_num_pages),
                    'type' => 'array',
                    'add_args' => [
                        'q' => $search,
                        'ambito' => $ambito,
                        'categoria' => $categoria,
                        'scope' => $force_scope,
                    ],
                ]);

                if (!empty($pagination)) {
                    echo '<div class="wecoop-annunci-pagination">';
                    foreach ($pagination as $link) {
                        echo wp_kses_post($link);
                    }
                    echo '</div>';
                }
                ?>
            <?php endif; ?>
        </div>
        <?php

        wp_reset_postdata();
        return (string) ob_get_clean();
    }
}
