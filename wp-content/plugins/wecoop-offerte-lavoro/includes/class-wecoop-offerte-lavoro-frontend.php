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

        $base_offer_args = [
            'post_type' => [
                WeCoop_Offerte_Lavoro_CPT::OFFER_CPT,
                WeCoop_Offerte_Lavoro_CPT::SUBMISSION_CPT,
            ],
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($meta_query)) {
            $base_offer_args['meta_query'] = $meta_query;
        }

        if ($search !== '') {
            $base_offer_args['s'] = $search;
        }

        if ($categoria !== '') {
            $base_offer_args['tax_query'] = [[
                'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
                'field' => 'slug',
                'terms' => $categoria,
            ]];
        }

        $fallback_meta_query = [
            [
                'key' => 'submitted_from_app',
                'value' => '1',
                'compare' => '=',
            ],
        ];

        if ($ambito === 'seek') {
            $fallback_meta_query[] = [
                'key' => 'category_direction',
                'value' => 'seek',
                'compare' => '=',
            ];
        } elseif ($ambito === 'offer') {
            $fallback_meta_query[] = [
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
            $fallback_meta_query[] = [
                'key' => 'category_scope',
                'value' => $force_scope,
                'compare' => '=',
            ];
        }

        if ($categoria !== '') {
            $fallback_meta_query[] = [
                'key' => 'category_slug',
                'value' => $categoria,
                'compare' => '=',
            ];
        }

        $fallback_args = [
            'post_type' => 'post',
            'post_status' => ['publish', 'pending'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => $fallback_meta_query,
        ];

        if ($search !== '') {
            $fallback_args['s'] = $search;
        }

        $offer_posts = get_posts($base_offer_args);
        $fallback_posts = get_posts($fallback_args);

        $combined_posts = [];
        foreach (array_merge($offer_posts, $fallback_posts) as $post_item) {
            $combined_posts[$post_item->ID] = $post_item;
        }

        $combined_posts = array_values($combined_posts);
        usort($combined_posts, static function ($a, $b) {
            $a_featured = (int) get_post_meta((int) $a->ID, 'is_featured', true);
            $b_featured = (int) get_post_meta((int) $b->ID, 'is_featured', true);
            if ($a_featured !== $b_featured) {
                return $b_featured <=> $a_featured;
            }

            return strtotime((string) $b->post_date_gmt) <=> strtotime((string) $a->post_date_gmt);
        });

        $per_page = 12;
        $total_items = count($combined_posts);
        $total_pages = max(1, (int) ceil($total_items / $per_page));
        $paged = min($paged, $total_pages);
        $offset = ($paged - 1) * $per_page;
        $paged_posts = array_slice($combined_posts, $offset, $per_page);
        $terms = get_terms([
            'taxonomy' => WeCoop_Offerte_Lavoro_CPT::CATEGORY_TAX,
            'hide_empty' => false,
        ]);

        ob_start();
        ?>
        <div class="wecoop-annunci-wrap">
            <div class="wecoop-annunci-header">
                <span class="cw-eyebrow"><?php echo esc_html((string) $atts['title']); ?></span>
                <p class="ws-lead"><?php echo esc_html((string) $atts['subtitle']); ?></p>
            </div>

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
                <button type="submit" class="ws-btn ws-btn--primary">Filtra</button>
            </form>

            <?php if (empty($paged_posts)) : ?>
                <p>Nessun annuncio disponibile con questi filtri.</p>
            <?php else : ?>
                <div class="wecoop-annunci-grid">
                    <?php
                    foreach ($paged_posts as $post_item) :
                        $id = (int) $post_item->ID;
                        $direction = (string) get_post_meta($id, 'category_direction', true);
                        if ($direction === '') {
                            $direction = 'offer';
                        }
                        $badge_class = $direction === 'seek' ? 'wecoop-badge-seek' : 'wecoop-badge-offer';
                        $badge_text = $direction === 'seek' ? 'Cerco' : 'Offro';
                        $city = (string) get_post_meta($id, 'city', true);
                        $contract_type = (string) get_post_meta($id, 'contract_type', true);
                        $excerpt = (string) $post_item->post_excerpt;
                        if (trim((string) $excerpt) === '') {
                            $excerpt = (string) get_post_meta($id, 'description', true);
                        }
                        if (trim((string) $excerpt) === '') {
                            $excerpt = (string) $post_item->post_content;
                        }
                        ?>
                        <article class="wecoop-annuncio-card ws-card">
                            <div class="wecoop-annuncio-head">
                                <h3 class="wecoop-annuncio-title"><?php echo esc_html(get_the_title($id)); ?></h3>
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
                                <a class="ws-btn ws-btn--primary wecoop-btn-dettagli" href="<?php echo esc_url(get_permalink($id)); ?>">Dettagli annuncio</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php
                $pagination = paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $paged,
                    'total' => $total_pages,
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

        return (string) ob_get_clean();
    }
}
