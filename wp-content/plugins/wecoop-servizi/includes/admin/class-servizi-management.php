<?php
/**
 * Admin Management: Richieste Servizi
 * 
 * Gestione interfaccia admin per richieste servizi
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Management {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_edit_richiesta_servizio', [__CLASS__, 'ajax_edit_richiesta']);
        add_action('wp_ajax_save_richiesta_servizio', [__CLASS__, 'ajax_save_richiesta']);
        add_action('wp_ajax_delete_richiesta_servizio', [__CLASS__, 'ajax_delete_richiesta']);
        add_action('wp_ajax_update_stato_richiesta', [__CLASS__, 'ajax_update_stato']);
        add_action('wp_ajax_send_payment_request', [__CLASS__, 'ajax_send_payment_request']);
        
        // Row actions
        add_filter('post_row_actions', [__CLASS__, 'add_row_actions'], 10, 2);
    }
    
    /**
     * Menu admin
     */
    public static function add_admin_menu() {
        add_menu_page(
            'Richieste Servizi',
            'Richieste Servizi',
            'manage_options',
            'wecoop-richieste-servizi',
            [__CLASS__, 'render_list'],
            'dashicons-clipboard',
            30
        );
        
        // Dashboard Analytics
        add_submenu_page(
            'wecoop-richieste-servizi',
            'Dashboard Analytics',
            '📊 Dashboard',
            'manage_options',
            'wecoop-richieste-dashboard',
            [__CLASS__, 'render_dashboard']
        );
        
        // Lista richieste (rinominato)
        add_submenu_page(
            'wecoop-richieste-servizi',
            'Tutte le Richieste',
            '📋 Tutte le Richieste',
            'manage_options',
            'wecoop-richieste-servizi',
            [__CLASS__, 'render_list']
        );
        
        // Pagina nascosta per dettaglio utente
        add_submenu_page(
            null, // Nessun menu parent = pagina nascosta
            'Dettaglio Utente',
            'Dettaglio Utente',
            'manage_options',
            'wecoop-user-detail',
            [__CLASS__, 'render_user_detail']
        );
    }
    
    /**
     * Carica assets admin
     */
    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wecoop-richieste') === false) {
            return;
        }
        
        wp_enqueue_style('wecoop-servizi-admin', plugin_dir_url(WECOOP_SERVIZI_FILE) . 'assets/css/admin.css', [], WECOOP_SERVIZI_VERSION);
    }
    
    /**
     * Dashboard
     */
    /**
     * Dashboard Analytics Completa
     */
    public static function render_dashboard() {
        // Ottieni tutte le richieste
        $all_richieste = new WP_Query([
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        // Statistiche globali
        $stats = [
            'totale' => 0,
            'pending' => 0,
            'awaiting_payment' => 0,
            'processing' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'importo_totale' => 0,
            'importo_pagato' => 0,
            'importo_attesa' => 0,
            'tasso_conversione' => 0,
            'tempo_medio_completamento' => 0,
            'utenti_unici' => [],
            'servizi_popolari' => [],
            'richieste_per_mese' => [],
            'entrate_per_mese' => []
        ];
        
        $tempi_completamento = [];
        $oggi = strtotime('today');
        $questo_mese = date('Y-m');
        $mese_scorso = date('Y-m', strtotime('-1 month'));
        
        $stats_mese_corrente = ['totale' => 0, 'importo' => 0];
        $stats_mese_scorso = ['totale' => 0, 'importo' => 0];
        
        if ($all_richieste->have_posts()) {
            while ($all_richieste->have_posts()) {
                $all_richieste->the_post();
                $post_id = get_the_ID();
                $stato = get_post_meta($post_id, 'stato', true);
                $importo = floatval(get_post_meta($post_id, 'importo', true));
                $payment_status = get_post_meta($post_id, 'payment_status', true);
                $user_id = get_post_meta($post_id, 'user_id', true);
                $servizio = get_post_meta($post_id, 'servizio', true);
                $data_richiesta = get_the_date('Y-m-d');
                $mese = date('Y-m', strtotime($data_richiesta));
                
                $stats['totale']++;
                
                if (isset($stats[$stato])) {
                    $stats[$stato]++;
                }
                
                if ($importo > 0) {
                    $stats['importo_totale'] += $importo;
                    
                    if ($payment_status === 'paid' || $stato === 'completed') {
                        $stats['importo_pagato'] += $importo;
                    } elseif ($stato === 'awaiting_payment' || $payment_status === 'pending') {
                        $stats['importo_attesa'] += $importo;
                    }
                }
                
                // Utenti unici
                if ($user_id && !in_array($user_id, $stats['utenti_unici'])) {
                    $stats['utenti_unici'][] = $user_id;
                }
                
                // Servizi popolari
                if ($servizio) {
                    if (!isset($stats['servizi_popolari'][$servizio])) {
                        $stats['servizi_popolari'][$servizio] = 0;
                    }
                    $stats['servizi_popolari'][$servizio]++;
                }
                
                // Richieste per mese
                if (!isset($stats['richieste_per_mese'][$mese])) {
                    $stats['richieste_per_mese'][$mese] = 0;
                }
                $stats['richieste_per_mese'][$mese]++;
                
                // Entrate per mese
                if ($payment_status === 'paid' || $stato === 'completed') {
                    if (!isset($stats['entrate_per_mese'][$mese])) {
                        $stats['entrate_per_mese'][$mese] = 0;
                    }
                    $stats['entrate_per_mese'][$mese] += $importo;
                }
                
                // Stats mese corrente vs scorso
                if (strpos($data_richiesta, $questo_mese) === 0) {
                    $stats_mese_corrente['totale']++;
                    if ($payment_status === 'paid') {
                        $stats_mese_corrente['importo'] += $importo;
                    }
                } elseif (strpos($data_richiesta, $mese_scorso) === 0) {
                    $stats_mese_scorso['totale']++;
                    if ($payment_status === 'paid') {
                        $stats_mese_scorso['importo'] += $importo;
                    }
                }
                
                // Tempo medio completamento
                if ($stato === 'completed') {
                    $data_creazione = strtotime(get_the_date('Y-m-d H:i:s'));
                    $data_completamento = strtotime(get_post_meta($post_id, 'payment_paid_at', true) ?: get_the_modified_date('Y-m-d H:i:s'));
                    $giorni = ($data_completamento - $data_creazione) / 86400;
                    if ($giorni >= 0) {
                        $tempi_completamento[] = $giorni;
                    }
                }
            }
            wp_reset_postdata();
        }
        
        // Calcoli finali
        $stats['utenti_unici_count'] = count($stats['utenti_unici']);
        $stats['tasso_conversione'] = $stats['totale'] > 0 ? round(($stats['completed'] / $stats['totale']) * 100, 1) : 0;
        $stats['tempo_medio_completamento'] = !empty($tempi_completamento) ? round(array_sum($tempi_completamento) / count($tempi_completamento), 1) : 0;
        
        // Ordina servizi popolari
        arsort($stats['servizi_popolari']);
        $top_servizi = array_slice($stats['servizi_popolari'], 0, 5, true);
        
        // Trend mese corrente
        $trend_richieste = $stats_mese_scorso['totale'] > 0 
            ? round((($stats_mese_corrente['totale'] - $stats_mese_scorso['totale']) / $stats_mese_scorso['totale']) * 100, 1)
            : 0;
        $trend_entrate = $stats_mese_scorso['importo'] > 0
            ? round((($stats_mese_corrente['importo'] - $stats_mese_scorso['importo']) / $stats_mese_scorso['importo']) * 100, 1)
            : 0;
        
        ?>
        <div class="wrap">
            <h1>📊 Dashboard Analytics - Richieste Servizi</h1>
            
            <!-- KPI Principali -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
                <!-- Totale Richieste -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">📋 Totale Richieste</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;"><?php echo $stats['totale']; ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">
                        <?php echo $trend_richieste > 0 ? '📈' : '📉'; ?> 
                        <?php echo abs($trend_richieste); ?>% vs mese scorso
                    </div>
                </div>
                
                <!-- Importo Totale Pagato -->
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">💰 Totale Incassato</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">€ <?php echo number_format($stats['importo_pagato'], 2, ',', '.'); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">
                        <?php echo $trend_entrate > 0 ? '📈' : '📉'; ?>
                        <?php echo abs($trend_entrate); ?>% vs mese scorso
                    </div>
                </div>
                
                <!-- In Attesa di Pagamento -->
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">⏳ In Attesa Pagamento</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;">€ <?php echo number_format($stats['importo_attesa'], 2, ',', '.'); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;"><?php echo $stats['awaiting_payment']; ?> richieste da pagare</div>
                </div>
                
                <!-- Tasso Conversione -->
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">📊 Tasso Completamento</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;"><?php echo $stats['tasso_conversione']; ?>%</div>
                    <div style="font-size: 12px; opacity: 0.8;"><?php echo $stats['completed']; ?> completate / <?php echo $stats['totale']; ?> totali</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Statistiche per Stato -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>📈 Richieste per Stato</h2>
                    </div>
                    <div class="inside">
                        <table class="wp-list-table widefat" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Stato</th>
                                    <th>Quantità</th>
                                    <th>Percentuale</th>
                                    <th>Visualizzazione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stati = [
                                    'pending' => ['⏳ In Attesa', '#ff9800'],
                                    'awaiting_payment' => ['💳 Da Pagare', '#9c27b0'],
                                    'processing' => ['🔄 In Lavorazione', '#2196f3'],
                                    'completed' => ['✅ Completate', '#4caf50'],
                                    'cancelled' => ['❌ Annullate', '#f44336']
                                ];
                                
                                foreach ($stati as $stato => $info):
                                    $count = $stats[$stato];
                                    $perc = $stats['totale'] > 0 ? round(($count / $stats['totale']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo $info[0]; ?></strong></td>
                                    <td><?php echo $count; ?></td>
                                    <td><?php echo $perc; ?>%</td>
                                    <td>
                                        <div style="background: #f0f0f0; height: 20px; border-radius: 10px; overflow: hidden;">
                                            <div style="background: <?php echo $info[1]; ?>; width: <?php echo $perc; ?>%; height: 100%;"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Metriche Chiave -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>🎯 Metriche Chiave</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table" style="margin: 0;">
                            <tr>
                                <th>👥 Utenti Unici:</th>
                                <td><strong><?php echo $stats['utenti_unici_count']; ?></strong></td>
                            </tr>
                            <tr>
                                <th>⏱️ Tempo Medio:</th>
                                <td><strong><?php echo $stats['tempo_medio_completamento']; ?> giorni</strong></td>
                            </tr>
                            <tr>
                                <th>💵 Ticket Medio:</th>
                                <td>
                                    <strong>€ <?php 
                                    echo $stats['completed'] > 0 
                                        ? number_format($stats['importo_pagato'] / $stats['completed'], 2, ',', '.') 
                                        : '0,00'; 
                                    ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <th>📅 Questo Mese:</th>
                                <td><strong><?php echo $stats_mese_corrente['totale']; ?> richieste</strong></td>
                            </tr>
                            <tr>
                                <th>💰 Entrate Mese:</th>
                                <td><strong>€ <?php echo number_format($stats_mese_corrente['importo'], 2, ',', '.'); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Top 5 Servizi Popolari -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>🏆 Top 5 Servizi Più Richiesti</h2>
                    </div>
                    <div class="inside">
                        <table class="wp-list-table widefat" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Servizio</th>
                                    <th>Richieste</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_servizi)): ?>
                                    <?php foreach ($top_servizi as $servizio => $count): 
                                        $perc = round(($count / $stats['totale']) * 100, 1);
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($servizio); ?></td>
                                        <td><strong><?php echo $count; ?></strong></td>
                                        <td><?php echo $perc; ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #999;">Nessun dato disponibile</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Richieste Ultime -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>🕒 Ultime 5 Richieste</h2>
                    </div>
                    <div class="inside">
                        <?php
                        $recent = new WP_Query([
                            'post_type' => 'richiesta_servizio',
                            'posts_per_page' => 5,
                            'orderby' => 'date',
                            'order' => 'DESC'
                        ]);
                        ?>
                        <table class="wp-list-table widefat" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>N. Pratica</th>
                                    <th>Servizio</th>
                                    <th>Stato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent->have_posts()): ?>
                                    <?php while ($recent->have_posts()): $recent->the_post(); 
                                        $numero_pratica = get_post_meta(get_the_ID(), 'numero_pratica', true);
                                        $servizio = get_post_meta(get_the_ID(), 'servizio', true);
                                        $stato = get_post_meta(get_the_ID(), 'stato', true);
                                        
                                        $stato_icons = [
                                            'pending' => '⏳',
                                            'awaiting_payment' => '💳',
                                            'processing' => '🔄',
                                            'completed' => '✅',
                                            'cancelled' => '❌'
                                        ];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($numero_pratica); ?></strong></td>
                                        <td><?php echo esc_html(substr($servizio, 0, 30)) . (strlen($servizio) > 30 ? '...' : ''); ?></td>
                                        <td><?php echo $stato_icons[$stato] ?? ''; ?></td>
                                    </tr>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #999;">Nessuna richiesta</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Azioni Rapide -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2>⚡ Azioni Rapide</h2>
                </div>
                <div class="inside">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi'); ?>" class="button button-primary button-large" style="height: auto; padding: 15px; text-align: center;">
                            📋 Vedi Tutte le Richieste
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=awaiting_payment'); ?>" class="button button-large" style="height: auto; padding: 15px; text-align: center; background: #9c27b0; color: white; border-color: #7b1fa2;">
                            💳 Da Pagare (<?php echo $stats['awaiting_payment']; ?>)
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=pending'); ?>" class="button button-large" style="height: auto; padding: 15px; text-align: center; background: #ff9800; color: white; border-color: #f57c00;">
                            ⏳ In Attesa (<?php echo $stats['pending']; ?>)
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=processing'); ?>" class="button button-large" style="height: auto; padding: 15px; text-align: center; background: #2196f3; color: white; border-color: #1976d2;">
                            🔄 In Lavorazione (<?php echo $stats['processing']; ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .postbox .inside {
                padding: 15px;
            }
            .form-table th {
                padding: 8px 10px;
                font-weight: 600;
            }
            .form-table td {
                padding: 8px 10px;
            }
        </style>
        <?php
    }
    
    /**
     * Lista richieste
     */
    public static function render_list() {
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $stato_filter = isset($_GET['stato']) ? sanitize_text_field($_GET['stato']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        $args = [
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => 20,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        if ($stato_filter) {
            $args['meta_query'] = [
                [
                    'key' => 'stato',
                    'value' => $stato_filter
                ]
            ];
        }
        
        if ($search) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        ?>
        <div class="wrap">
            <h1>Tutte le Richieste Servizi</h1>
            
            <form method="get" class="wecoop-filter-form">
                <input type="hidden" name="page" value="wecoop-richieste-list">
                
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Cerca per numero pratica, nome...">
                
                <select name="stato">
                    <option value="">Tutti gli stati</option>
                    <option value="pending" <?php selected($stato_filter, 'pending'); ?>>In Attesa</option>
                    <option value="awaiting_payment" <?php selected($stato_filter, 'awaiting_payment'); ?>>Da Pagare</option>
                    <option value="processing" <?php selected($stato_filter, 'processing'); ?>>In Lavorazione</option>
                    <option value="completed" <?php selected($stato_filter, 'completed'); ?>>Completata</option>
                    <option value="cancelled" <?php selected($stato_filter, 'cancelled'); ?>>Annullata</option>
                </select>
                
                <button type="submit" class="button">Filtra</button>
                <a href="?page=wecoop-richieste-list" class="button">Reset</a>
            </form>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Numero Pratica</th>
                        <th>Servizio</th>
                        <th>Categoria</th>
                        <th>Richiedente</th>
                        <th>Importo</th>
                        <th>Data</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->have_posts()) : ?>
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                            <?php self::render_table_row(get_the_ID()); ?>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8">Nessuna richiesta trovata.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php
            $total_pages = $query->max_num_pages;
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $paged,
                    'total' => $total_pages
                ]);
                echo '</div></div>';
            }
            wp_reset_postdata();
            ?>
        </div>
        
        <?php self::render_edit_modal(); ?>
        <?php
    }
    
    /**
     * Riga tabella
     */
    private static function render_table_row($post_id) {
        $numero_pratica = get_post_meta($post_id, 'numero_pratica', true);
        $servizio = get_post_meta($post_id, 'servizio', true);
        $categoria = get_post_meta($post_id, 'categoria', true);
        $stato = get_post_meta($post_id, 'stato', true) ?: 'pending';
        $importo = get_post_meta($post_id, 'importo', true);
        $user_id = get_post_meta($post_id, 'user_id', true);
        $dati_json = get_post_meta($post_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        $order_id = get_post_meta($post_id, 'wc_order_id', true);
        $payment_status = get_post_meta($post_id, 'payment_status', true);
        
        // Ottieni nome richiedente dai dati
        $nome_richiedente = $dati['nome_completo'] ?? '';
        if (!$nome_richiedente && $user_id) {
            $user = get_userdata($user_id);
            $nome_richiedente = $user ? $user->display_name : 'N/A';
        }
        
        // Link all'utente
        $user_link = '';
        if ($user_id) {
            $user_link = get_edit_user_link($user_id);
        }
        
        $stato_labels = [
            'pending' => '⏳ In Attesa',
            'awaiting_payment' => '💳 Da Pagare',
            'processing' => '🔄 In Lavorazione',
            'completed' => '✅ Completata',
            'cancelled' => '❌ Annullata'
        ];
        
        $stato_colors = [
            'pending' => '#ff9800',
            'awaiting_payment' => '#9c27b0',
            'processing' => '#2196f3',
            'completed' => '#4caf50',
            'cancelled' => '#f44336'
        ];
        ?>
        <tr>
            <td><strong><?php echo esc_html($numero_pratica); ?></strong></td>
            <td><?php echo esc_html($servizio); ?></td>
            <td><?php echo esc_html($categoria); ?></td>
            <td>
                <?php if ($user_id) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wecoop-user-detail&user_id=' . $user_id)); ?>" 
                       style="font-weight: bold; color: #2271b1;">
                        👤 <?php echo esc_html($nome_richiedente); ?>
                    </a>
                <?php else : ?>
                    <?php echo esc_html($nome_richiedente); ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($importo): ?>
                    <strong>€ <?php echo number_format($importo, 2, ',', '.'); ?></strong>
                    <?php if ($payment_status === 'paid'): ?>
                        <span style="color: green;" title="Pagato">✓</span>
                    <?php elseif ($order_id): ?>
                        <span style="color: orange;" title="In attesa di pagamento">⏳</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: #999;">—</span>
                <?php endif; ?>
            </td>
            <td><?php echo get_the_date('d/m/Y H:i'); ?></td>
            <td>
                <span style="background: <?php echo $stato_colors[$stato] ?? '#999'; ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; display: inline-block;">
                    <?php echo $stato_labels[$stato] ?? $stato; ?>
                </span>
            </td>
            <td>
                <button class="button button-small edit-richiesta" data-id="<?php echo $post_id; ?>">
                    👁️ Dettagli
                </button>
                <?php if ($importo && !$order_id): ?>
                    <button class="button button-small button-primary send-payment-request" 
                            data-id="<?php echo $post_id; ?>"
                            title="Invia richiesta di pagamento">
                        💳 Richiedi Pagamento
                    </button>
                <?php elseif ($order_id): ?>
                    <?php $order = wc_get_order($order_id); ?>
                    <?php if ($order && $order->needs_payment()): ?>
                        <button class="button button-small send-payment-request" 
                                data-id="<?php echo $post_id; ?>"
                                title="Reinvia link pagamento">
                            📧 Reinvia Link
                        </button>
                        <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" 
                           class="button button-small" 
                           target="_blank"
                           title="Copia link pagamento">
                            🔗 Link
                        </a>
                    <?php elseif ($order): ?>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" 
                           class="button button-small" 
                           target="_blank">
                            📦 Ordine #<?php echo $order_id; ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Modal modifica
     */
    private static function render_edit_modal() {
        ?>
        <div id="edit-richiesta-modal" style="display:none;">
            <div class="wecoop-modal-backdrop"></div>
            <div class="wecoop-modal-content">
                <div class="wecoop-modal-header">
                    <h2>Modifica Richiesta Servizio</h2>
                    <button class="wecoop-modal-close">&times;</button>
                </div>
                <div class="wecoop-modal-body">
                    <form id="edit-richiesta-form">
                        <input type="hidden" id="richiesta_id" name="richiesta_id">
                        
                        <div class="form-grid">
                            <div class="form-field">
                                <label>Numero Pratica</label>
                                <input type="text" id="numero_pratica" readonly>
                            </div>
                            
                            <div class="form-field">
                                <label>Utente WordPress</label>
                                <div id="user-link-container"></div>
                            </div>
                            
                            <div class="form-field">
                                <label>Servizio</label>
                                <input type="text" id="servizio" name="servizio" readonly>
                            </div>
                            
                            <div class="form-field">
                                <label>Categoria</label>
                                <input type="text" id="categoria" name="categoria" readonly>
                            </div>
                            
                            <div class="form-field">
                                <label>Stato</label>
                                <select id="stato" name="stato">
                                    <option value="pending">⏳ In Attesa</option>
                                    <option value="awaiting_payment">💳 Da Pagare</option>
                                    <option value="processing">🔄 In Lavorazione</option>
                                    <option value="completed">✅ Completata</option>
                                    <option value="cancelled">❌ Annullata</option>
                                </select>
                            </div>
                        </div>
                        
                        <h3>Dati Richiedente</h3>
                        <div id="dati-richiedente" class="form-grid"></div>
                        
                        <div class="wecoop-modal-footer">
                            <button type="button" class="button wecoop-modal-close">Annulla</button>
                            <button type="submit" class="button button-primary">Salva Modifiche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Invia richiesta di pagamento
            $(document).on('click', '.send-payment-request, .send-payment-link', function(e) {
                e.preventDefault();
                
                const richiestaId = $(this).data('id');
                const $button = $(this);
                const originalText = $button.text();
                
                if (!confirm('Confermi l\'invio della richiesta di pagamento all\'utente?')) {
                    return;
                }
                
                $button.prop('disabled', true).text('⏳ Invio...');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'send_payment_request',
                        richiesta_id: richiestaId,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ ' + response.data);
                            location.reload();
                        } else {
                            alert('❌ Errore: ' + response.data);
                            $button.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert('❌ Errore di comunicazione con il server');
                        $button.prop('disabled', false).text(originalText);
                    }
                });
            });
            
            // Cambio stato rapido (se hai ancora il select)
            $('.stato-select').on('change', function() {
                const richiestaId = $(this).data('richiesta-id');
                const nuovoStato = $(this).val();
                
                if (!confirm('Confermi il cambio di stato?')) {
                    $(this).val($(this).data('old-value'));
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'update_stato_richiesta',
                        richiesta_id: richiestaId,
                        stato: nuovoStato,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Stato aggiornato con successo!');
                            location.reload();
                        } else {
                            alert('Errore: ' + response.data);
                        }
                    }
                });
            }).each(function() {
                $(this).data('old-value', $(this).val());
            });
            
            // Apri modal modifica
            $('.edit-richiesta').on('click', function() {
                const richiestaId = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'edit_richiesta_servizio',
                        richiesta_id: richiestaId,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            $('#richiesta_id').val(richiestaId);
                            $('#numero_pratica').val(data.numero_pratica);
                            $('#servizio').val(data.servizio);
                            $('#categoria').val(data.categoria);
                            $('#stato').val(data.stato);
                            
                            // Link utente WordPress
                            if (data.user_id && data.user_edit_link) {
                                $('#user-link-container').html(
                                    '<a href="' + data.user_edit_link + '" target="_blank" class="button button-small">' +
                                    '👤 Apri profilo utente' +
                                    '</a>'
                                );
                            } else {
                                $('#user-link-container').html('<em>Nessun utente associato</em>');
                            }
                            
                            // Popola dati richiedente
                            let datiHtml = '';
                            const dati = data.dati || {};
                            
                            for (const [key, value] of Object.entries(dati)) {
                                const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                                datiHtml += `
                                    <div class="form-field">
                                        <label>${label}</label>
                                        <input type="text" name="dati[${key}]" value="${value || ''}" readonly>
                                    </div>
                                `;
                            }
                            
                            $('#dati-richiedente').html(datiHtml);
                            $('#edit-richiesta-modal').fadeIn();
                        }
                    }
                });
            });
            
            // Chiudi modal
            $('.wecoop-modal-close, .wecoop-modal-backdrop').on('click', function() {
                $('#edit-richiesta-modal').fadeOut();
            });
            
            // Salva modifiche
            $('#edit-richiesta-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: formData + '&action=save_richiesta_servizio&nonce=<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>',
                    success: function(response) {
                        if (response.success) {
                            alert('Modifiche salvate con successo!');
                            location.reload();
                        } else {
                            alert('Errore: ' + response.data);
                        }
                    }
                });
            });
            
            // Elimina richiesta
            $('.delete-richiesta').on('click', function() {
                if (!confirm('Sei sicuro di voler eliminare questa richiesta?')) {
                    return;
                }
                
                const richiestaId = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'delete_richiesta_servizio',
                        richiesta_id: richiestaId,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Richiesta eliminata con successo!');
                            location.reload();
                        } else {
                            alert('Errore: ' + response.data);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Statistiche
     */
    private static function get_statistics() {
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'total' => 0
        ];
        
        $stati = ['pending', 'processing', 'completed'];
        
        foreach ($stati as $stato) {
            $count = new WP_Query([
                'post_type' => 'richiesta_servizio',
                'meta_query' => [
                    [
                        'key' => 'stato',
                        'value' => $stato
                    ]
                ],
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            
            $stats[$stato] = $count->found_posts;
            wp_reset_postdata();
        }
        
        $total = new WP_Query([
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        $stats['total'] = $total->found_posts;
        wp_reset_postdata();
        
        return $stats;
    }
    
    /**
     * Ultime richieste
     */
    private static function render_recent_requests() {
        $query = new WP_Query([
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Numero Pratica</th>
                    <th>Servizio</th>
                    <th>Richiedente</th>
                    <th>Data</th>
                    <th>Stato</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()) : ?>
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php
                        $numero_pratica = get_post_meta(get_the_ID(), 'numero_pratica', true);
                        $servizio = get_post_meta(get_the_ID(), 'servizio', true);
                        $stato = get_post_meta(get_the_ID(), 'stato', true) ?: 'pending';
                        $dati_json = get_post_meta(get_the_ID(), 'dati', true);
                        $dati = json_decode($dati_json, true) ?: [];
                        
                        $stato_icons = [
                            'pending' => '⏳',
                            'processing' => '🔄',
                            'completed' => '✅',
                            'cancelled' => '❌'
                        ];
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($numero_pratica); ?></strong></td>
                            <td><?php echo esc_html($servizio); ?></td>
                            <td><?php echo esc_html($dati['nome_completo'] ?? 'N/A'); ?></td>
                            <td><?php echo get_the_date('d/m/Y H:i'); ?></td>
                            <td><?php echo $stato_icons[$stato]; ?> <?php echo esc_html(ucfirst($stato)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">Nessuna richiesta recente.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        wp_reset_postdata();
    }
    
    /**
     * AJAX: Carica dati richiesta
     */
    public static function ajax_edit_richiesta() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richiesta_id = absint($_POST['richiesta_id']);
        
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $categoria = get_post_meta($richiesta_id, 'categoria', true);
        $stato = get_post_meta($richiesta_id, 'stato', true);
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        $response = [
            'numero_pratica' => $numero_pratica,
            'servizio' => $servizio,
            'categoria' => $categoria,
            'stato' => $stato,
            'dati' => $dati,
            'user_id' => $user_id
        ];
        
        // Aggiungi link all'utente se esiste
        if ($user_id) {
            $response['user_edit_link'] = get_edit_user_link($user_id);
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * AJAX: Salva modifiche
     */
    public static function ajax_save_richiesta() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richiesta_id = absint($_POST['richiesta_id']);
        $stato = sanitize_text_field($_POST['stato']);
        
        update_post_meta($richiesta_id, 'stato', $stato);
        
        wp_send_json_success('Modifiche salvate con successo');
    }
    
    /**
     * AJAX: Elimina richiesta
     */
    public static function ajax_delete_richiesta() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richiesta_id = absint($_POST['richiesta_id']);
        
        if (wp_delete_post($richiesta_id, true)) {
            wp_send_json_success('Richiesta eliminata');
        } else {
            wp_send_json_error('Errore durante l\'eliminazione');
        }
    }
    
    /**
     * AJAX: Aggiorna stato
     */
    public static function ajax_update_stato() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richiesta_id = absint($_POST['richiesta_id']);
        $stato = sanitize_text_field($_POST['stato']);
        
        update_post_meta($richiesta_id, 'stato', $stato);
        
        wp_send_json_success('Stato aggiornato');
    }
    
    /**
     * AJAX: Invia richiesta di pagamento
     */
    public static function ajax_send_payment_request() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richiesta_id = absint($_POST['richiesta_id']);
        
        // Verifica importo
        $importo = get_post_meta($richiesta_id, 'importo', true);
        if (!$importo || $importo <= 0) {
            wp_send_json_error('Importo non specificato. Imposta un importo nella richiesta.');
        }
        
        // Verifica se esiste già un ordine
        $order_id = get_post_meta($richiesta_id, 'wc_order_id', true);
        
        if ($order_id) {
            // Ordine già esistente, reinvia email
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Ordine non trovato.');
            }
            
            // Reinvia email
            if (class_exists('WECOOP_Servizi_WooCommerce_Integration')) {
                WECOOP_Servizi_WooCommerce_Integration::invia_email_pagamento($richiesta_id, $order_id);
                wp_send_json_success('Email con link di pagamento reinviata!');
            } else {
                wp_send_json_error('Integrazione WooCommerce non disponibile.');
            }
        } else {
            // Crea nuovo ordine e invia email
            update_post_meta($richiesta_id, 'stato', 'awaiting_payment');
            
            if (class_exists('WECOOP_Servizi_WooCommerce_Integration')) {
                $new_order_id = WECOOP_Servizi_WooCommerce_Integration::crea_ordine_woocommerce($richiesta_id);
                
                if ($new_order_id) {
                    wp_send_json_success('Ordine creato e richiesta di pagamento inviata!');
                } else {
                    $error = get_post_meta($richiesta_id, 'payment_error', true);
                    wp_send_json_error('Errore creazione ordine: ' . $error);
                }
            } else {
                wp_send_json_error('Integrazione WooCommerce non disponibile.');
            }
        }
    }
    
    /**
     * Aggiungi row actions alla lista post standard
     */
    public static function add_row_actions($actions, $post) {
        if ($post->post_type === 'richiesta_servizio') {
            $importo = get_post_meta($post->ID, 'importo', true);
            $order_id = get_post_meta($post->ID, 'wc_order_id', true);
            
            if ($importo && !$order_id) {
                $actions['send_payment'] = sprintf(
                    '<a href="#" class="send-payment-link" data-id="%d">💳 Richiedi Pagamento</a>',
                    $post->ID
                );
            } elseif ($order_id) {
                $order = wc_get_order($order_id);
                if ($order && $order->needs_payment()) {
                    $actions['resend_payment'] = sprintf(
                        '<a href="#" class="send-payment-link" data-id="%d">📧 Reinvia Link</a>',
                        $post->ID
                    );
                }
            }
        }
        
        return $actions;
    }
    
    /**
     * Render pagina dettaglio utente
     */
    public static function render_user_detail() {
        if (!isset($_GET['user_id'])) {
            wp_die('User ID non specificato');
        }
        
        $user_id = absint($_GET['user_id']);
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_die('Utente non trovato');
        }
        
        // Ottieni dati utente
        $nome = get_user_meta($user_id, 'nome', true);
        $cognome = get_user_meta($user_id, 'cognome', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        $telefono = get_user_meta($user_id, 'telefono', true);
        $indirizzo = get_user_meta($user_id, 'indirizzo', true);
        $citta = get_user_meta($user_id, 'citta', true);
        $cap = get_user_meta($user_id, 'cap', true);
        $provincia = get_user_meta($user_id, 'provincia', true);
        $data_nascita = get_user_meta($user_id, 'data_nascita', true);
        $luogo_nascita = get_user_meta($user_id, 'luogo_nascita', true);
        $numero_tessera = get_user_meta($user_id, 'numero_tessera', true);
        
        // Statistiche richieste
        $args = [
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => 'user_id',
                'value' => $user_id
            ]]
        ];
        
        $all_richieste = new WP_Query($args);
        $total_richieste = $all_richieste->found_posts;
        
        $stats = [
            'pending' => 0,
            'awaiting_payment' => 0,
            'processing' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'totale_speso' => 0
        ];
        
        if ($all_richieste->have_posts()) {
            while ($all_richieste->have_posts()) {
                $all_richieste->the_post();
                $stato = get_post_meta(get_the_ID(), 'stato', true);
                $importo = floatval(get_post_meta(get_the_ID(), 'importo', true));
                $payment_status = get_post_meta(get_the_ID(), 'payment_status', true);
                
                if (isset($stats[$stato])) {
                    $stats[$stato]++;
                }
                
                if ($payment_status === 'paid' || $stato === 'completed') {
                    $stats['totale_speso'] += $importo;
                }
            }
            wp_reset_postdata();
        }
        
        ?>
        <div class="wrap">
            <h1>
                <span class="dashicons dashicons-admin-users" style="font-size: 32px;"></span>
                Dettaglio Utente: <?php echo esc_html($nome . ' ' . $cognome); ?>
            </h1>
            
            <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi'); ?>" class="button">
                ← Torna alle Richieste
            </a>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Dati Anagrafici -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>📋 Dati Anagrafici</h2>
                    </div>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th>Nome Completo:</th>
                                <td><strong><?php echo esc_html($nome . ' ' . $cognome); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Codice Fiscale:</th>
                                <td><?php echo esc_html($codice_fiscale ?: '—'); ?></td>
                            </tr>
                            <tr>
                                <th>Data di Nascita:</th>
                                <td><?php echo esc_html($data_nascita ?: '—'); ?></td>
                            </tr>
                            <tr>
                                <th>Luogo di Nascita:</th>
                                <td><?php echo esc_html($luogo_nascita ?: '—'); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>
                                    <a href="mailto:<?php echo esc_attr($user->user_email); ?>">
                                        <?php echo esc_html($user->user_email); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Telefono:</th>
                                <td>
                                    <?php if ($telefono): ?>
                                        <a href="tel:<?php echo esc_attr($telefono); ?>">
                                            <?php echo esc_html($telefono); ?>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Indirizzo:</th>
                                <td><?php echo esc_html($indirizzo ?: '—'); ?></td>
                            </tr>
                            <tr>
                                <th>Città:</th>
                                <td><?php echo esc_html($citta ?: '—'); ?> <?php echo esc_html($cap ? '(' . $cap . ')' : ''); ?></td>
                            </tr>
                            <tr>
                                <th>Provincia:</th>
                                <td><?php echo esc_html($provincia ?: '—'); ?></td>
                            </tr>
                            <?php if ($numero_tessera): ?>
                            <tr>
                                <th>Numero Tessera:</th>
                                <td><strong><?php echo esc_html($numero_tessera); ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>WordPress ID:</th>
                                <td>
                                    <a href="<?php echo get_edit_user_link($user_id); ?>" target="_blank">
                                        #<?php echo $user_id; ?> (Modifica)
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Registrato il:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($user->user_registered)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Statistiche -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>📊 Statistiche Richieste</h2>
                    </div>
                    <div class="inside">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                            <div style="background: #f0f0f0; padding: 15px; border-radius: 5px; text-align: center;">
                                <div style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo $total_richieste; ?></div>
                                <div style="color: #666;">Totale Richieste</div>
                            </div>
                            <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; text-align: center;">
                                <div style="font-size: 32px; font-weight: bold; color: #4caf50;">€ <?php echo number_format($stats['totale_speso'], 2, ',', '.'); ?></div>
                                <div style="color: #666;">Totale Speso</div>
                            </div>
                        </div>
                        
                        <table class="form-table">
                            <tr>
                                <th>⏳ In Attesa:</th>
                                <td><strong><?php echo $stats['pending']; ?></strong></td>
                            </tr>
                            <tr>
                                <th>💳 Da Pagare:</th>
                                <td><strong style="color: #9c27b0;"><?php echo $stats['awaiting_payment']; ?></strong></td>
                            </tr>
                            <tr>
                                <th>🔄 In Lavorazione:</th>
                                <td><strong style="color: #2196f3;"><?php echo $stats['processing']; ?></strong></td>
                            </tr>
                            <tr>
                                <th>✅ Completate:</th>
                                <td><strong style="color: #4caf50;"><?php echo $stats['completed']; ?></strong></td>
                            </tr>
                            <tr>
                                <th>❌ Annullate:</th>
                                <td><strong style="color: #f44336;"><?php echo $stats['cancelled']; ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Storico Richieste -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>📝 Storico Richieste Servizi</h2>
                </div>
                <div class="inside">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>N. Pratica</th>
                                <th>Servizio</th>
                                <th>Data</th>
                                <th>Importo</th>
                                <th>Stato</th>
                                <th>Pagamento</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $richieste_query = new WP_Query([
                                'post_type' => 'richiesta_servizio',
                                'posts_per_page' => -1,
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'meta_query' => [[
                                    'key' => 'user_id',
                                    'value' => $user_id
                                ]]
                            ]);
                            
                            if ($richieste_query->have_posts()) :
                                while ($richieste_query->have_posts()) : $richieste_query->the_post();
                                    $post_id = get_the_ID();
                                    $numero_pratica = get_post_meta($post_id, 'numero_pratica', true);
                                    $servizio = get_post_meta($post_id, 'servizio', true);
                                    $stato = get_post_meta($post_id, 'stato', true);
                                    $importo = get_post_meta($post_id, 'importo', true);
                                    $payment_status = get_post_meta($post_id, 'payment_status', true);
                                    $order_id = get_post_meta($post_id, 'wc_order_id', true);
                                    
                                    $stato_labels = [
                                        'pending' => '⏳ In Attesa',
                                        'awaiting_payment' => '💳 Da Pagare',
                                        'processing' => '🔄 In Lavorazione',
                                        'completed' => '✅ Completata',
                                        'cancelled' => '❌ Annullata'
                                    ];
                                    
                                    $stato_colors = [
                                        'pending' => '#ff9800',
                                        'awaiting_payment' => '#9c27b0',
                                        'processing' => '#2196f3',
                                        'completed' => '#4caf50',
                                        'cancelled' => '#f44336'
                                    ];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($numero_pratica); ?></strong></td>
                                        <td><?php echo esc_html($servizio); ?></td>
                                        <td><?php echo get_the_date('d/m/Y H:i'); ?></td>
                                        <td>
                                            <?php if ($importo): ?>
                                                <strong>€ <?php echo number_format($importo, 2, ',', '.'); ?></strong>
                                            <?php else: ?>
                                                <span style="color: #999;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="background: <?php echo $stato_colors[$stato] ?? '#999'; ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; display: inline-block;">
                                                <?php echo $stato_labels[$stato] ?? $stato; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($payment_status === 'paid'): ?>
                                                <span style="color: green; font-weight: bold;">✓ Pagato</span>
                                            <?php elseif ($order_id): ?>
                                                <span style="color: orange;">⏳ In attesa</span>
                                            <?php else: ?>
                                                <span style="color: #999;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo get_edit_post_link($post_id); ?>" class="button button-small">
                                                Dettagli
                                            </a>
                                            <?php if ($order_id): ?>
                                                <a href="<?php echo admin_url('post.php?post=' . $order_id . '&action=edit'); ?>" 
                                                   class="button button-small" target="_blank">
                                                    Ordine
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                                        Nessuna richiesta trovata per questo utente.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
        
        <style>
            .postbox .inside {
                padding: 20px;
            }
            .form-table th {
                width: 200px;
                padding: 10px;
                font-weight: 600;
            }
            .form-table td {
                padding: 10px;
            }
        </style>
        <?php
    }
}
