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
        add_action('wp_ajax_export_richieste_csv', [__CLASS__, 'ajax_export_csv']);
        add_action('wp_ajax_get_dashboard_data', [__CLASS__, 'ajax_get_dashboard_data']);
        add_action('wp_ajax_normalize_all_servizi', [__CLASS__, 'ajax_normalize_all_servizi']);
        add_action('wp_ajax_save_listino_prezzi', [__CLASS__, 'ajax_save_listino_prezzi']);
        add_action('wp_ajax_bulk_delete_richieste', [__CLASS__, 'ajax_bulk_delete_richieste']);
        add_action('wp_ajax_get_prezzo_listino', [__CLASS__, 'ajax_get_prezzo_listino']);
        add_action('wp_ajax_generate_receipt', [__CLASS__, 'ajax_generate_receipt']);
        add_action('wp_ajax_send_documento_unico', [__CLASS__, 'ajax_send_documento_unico']);
        
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
        
        // Mappature servizi multilingua
        add_submenu_page(
            'wecoop-richieste-servizi',
            'Mappature Servizi',
            '🌐 Servizi Multilingua',
            'manage_options',
            'wecoop-servizi-mappature',
            [__CLASS__, 'render_mappature']
        );
        
        // Listino Prezzi
        add_submenu_page(
            'wecoop-richieste-servizi',
            'Listino Prezzi',
            '💰 Listino Prezzi',
            'manage_options',
            'wecoop-servizi-listino',
            [__CLASS__, 'render_listino']
        );
        
        // Impostazioni Ricevute
        add_submenu_page(
            'wecoop-richieste-servizi',
            'Impostazioni Ricevute',
            '⚙️ Impostazioni',
            'manage_options',
            'wecoop-servizi-settings',
            [__CLASS__, 'render_settings']
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
        
        // Chart.js per i grafici
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
        
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
            'paid' => 0,
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
            'richieste_per_giorno' => [],
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
                
                // Conta anche payment_status='paid'
                if ($payment_status === 'paid') {
                    $stats['paid']++;
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
                
                // Servizi popolari (normalizzato)
                if ($servizio) {
                    $servizio_normalizzato = WECOOP_Servizi_Normalizer::normalize($servizio);
                    if (!isset($stats['servizi_popolari'][$servizio_normalizzato])) {
                        $stats['servizi_popolari'][$servizio_normalizzato] = 0;
                    }
                    $stats['servizi_popolari'][$servizio_normalizzato]++;
                }
                
                // Richieste per mese
                if (!isset($stats['richieste_per_mese'][$mese])) {
                    $stats['richieste_per_mese'][$mese] = 0;
                }
                $stats['richieste_per_mese'][$mese]++;
                
                // Richieste per giorno (per grafico temporale)
                if (!isset($stats['richieste_per_giorno'][$data_richiesta])) {
                    $stats['richieste_per_giorno'][$data_richiesta] = 0;
                }
                $stats['richieste_per_giorno'][$data_richiesta]++;
                
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
            
            <!-- Filtri Temporali & Export -->
            <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="button periodo-filter active" data-periodo="30days">📅 30 Giorni</button>
                        <button class="button periodo-filter" data-periodo="7days">📅 7 Giorni</button>
                        <button class="button periodo-filter" data-periodo="3months">📅 3 Mesi</button>
                        <button class="button periodo-filter" data-periodo="1year">📅 Anno</button>
                        <button class="button periodo-filter" data-periodo="all">📅 Tutto</button>
                        <span style="border-left: 2px solid #ddd; margin: 0 5px;"></span>
                        <input type="date" id="filter-date-from" class="regular-text" placeholder="Da" style="width: 150px;">
                        <input type="date" id="filter-date-to" class="regular-text" placeholder="A" style="width: 150px;">
                        <button class="button periodo-filter" data-periodo="custom" id="apply-custom-filter">🔍 Applica</button>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo admin_url('admin-ajax.php?action=export_richieste_csv&nonce=' . wp_create_nonce('wecoop_servizi_nonce') . '&periodo=30days'); ?>" 
                           class="button button-primary" id="export-csv">
                            📥 Esporta CSV
                        </a>
                        <button class="button" onclick="window.print()">🖨️ Stampa</button>
                    </div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                var currentPeriodo = '30days';
                var currentDateFrom = '';
                var currentDateTo = '';
                
                // Gestione filtri periodo
                $('.periodo-filter').on('click', function(e) {
                    e.preventDefault();
                    var periodo = $(this).data('periodo');
                    
                    if (periodo === 'custom') {
                        currentDateFrom = $('#filter-date-from').val();
                        currentDateTo = $('#filter-date-to').val();
                        if (!currentDateFrom || !currentDateTo) {
                            alert('Seleziona entrambe le date');
                            return;
                        }
                        currentPeriodo = 'custom';
                    } else {
                        currentPeriodo = periodo;
                        currentDateFrom = '';
                        currentDateTo = '';
                    }
                    
                    $('.periodo-filter').removeClass('active');
                    $(this).addClass('active');
                    
                    // Aggiorna export CSV link
                    var exportUrl = '<?php echo admin_url('admin-ajax.php?action=export_richieste_csv&nonce=' . wp_create_nonce('wecoop_servizi_nonce')); ?>';
                    exportUrl += '&periodo=' + currentPeriodo;
                    if (currentPeriodo === 'custom') {
                        exportUrl += '&date_from=' + currentDateFrom + '&date_to=' + currentDateTo;
                    }
                    $('#export-csv').attr('href', exportUrl);
                    
                    // Reload dashboard data via AJAX
                    loadDashboardData();
                });
                
                function loadDashboardData() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_dashboard_data',
                            nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>',
                            periodo: currentPeriodo,
                            date_from: currentDateFrom,
                            date_to: currentDateTo
                        },
                        success: function(response) {
                            if (response.success) {
                                updateDashboard(response.data);
                            }
                        }
                    });
                }
                
                function updateDashboard(data) {
                    // Aggiorna KPI cards, grafici, tabelle con i nuovi dati
                    console.log('Dashboard aggiornata:', data);
                    
                    // TODO: Aggiornare i valori dei KPI
                    // TODO: Aggiornare i grafici Chart.js
                }
            });
            </script>
            
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
            
            <!-- Grafici Interattivi Chart.js -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Andamento Temporale -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>📈 Andamento Richieste nel Tempo</h2>
                    </div>
                    <div class="inside">
                        <canvas id="chartRichiesteTemporali" height="100"></canvas>
                    </div>
                </div>
                
                <!-- Distribuzione Stati (Donut) -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>🍩 Distribuzione per Stato</h2>
                    </div>
                    <div class="inside">
                        <canvas id="chartStatiDonut" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Grafico Entrate Mensili -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2>💰 Entrate Mensili</h2>
                </div>
                <div class="inside">
                    <canvas id="chartEntrateMensili" height="80"></canvas>
                </div>
            </div>
            
            <script>
            // Aspetta che Chart.js sia caricato
            window.addEventListener('load', function() {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js non caricato!');
                    return;
                }
                
                // Prepara dati per Chart.js
                <?php
                // Ultimi 30 giorni per grafico temporale
                $giorni_labels = [];
                $giorni_counts = [];
                for ($i = 29; $i >= 0; $i--) {
                    $data = date('Y-m-d', strtotime("-$i days"));
                    $giorni_labels[] = date('d/m', strtotime($data));
                    $giorni_counts[] = isset($stats['richieste_per_giorno'][$data]) ? $stats['richieste_per_giorno'][$data] : 0;
                }
                
                // Ultimi 12 mesi per grafico entrate
                $mesi_labels = [];
                $mesi_entrate = [];
                for ($i = 11; $i >= 0; $i--) {
                    $mese = date('Y-m', strtotime("-$i months"));
                    $mesi_labels[] = date('M Y', strtotime($mese . '-01'));
                    $mesi_entrate[] = isset($stats['entrate_per_mese'][$mese]) ? $stats['entrate_per_mese'][$mese] : 0;
                }
                ?>
            
            var giorniLabels = <?php echo json_encode($giorni_labels); ?>;
            var giorniData = <?php echo json_encode($giorni_counts); ?>;
            
            var statiLabels = ['⏳ In Attesa', '💳 Da Pagare', '✅ Pagate', '🔄 In Lavorazione', '✅ Completate', '❌ Annullate'];
            var statiData = [
                <?php echo $stats['pending']; ?>,
                <?php echo $stats['awaiting_payment']; ?>,
                <?php echo $stats['paid']; ?>,
                <?php echo $stats['processing']; ?>,
                <?php echo $stats['completed']; ?>,
                <?php echo $stats['cancelled']; ?>
            ];
            var statiColors = ['#ff9800', '#9c27b0', '#4caf50', '#2196f3', '#27ae60', '#f44336'];
            
            var mesiLabels = <?php echo json_encode($mesi_labels); ?>;
            var mesiData = <?php echo json_encode($mesi_entrate); ?>;
            
            // Grafico Andamento Temporale (Linea)
            var ctxTempo = document.getElementById('chartRichiesteTemporali').getContext('2d');
            var chartTempo = new Chart(ctxTempo, {
                type: 'line',
                data: {
                    labels: giorniLabels,
                    datasets: [{
                        label: 'Richieste',
                        data: giorniData,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
            
            // Grafico Stati (Donut)
            var ctxStati = document.getElementById('chartStatiDonut').getContext('2d');
            var chartStati = new Chart(ctxStati, {
                type: 'doughnut',
                data: {
                    labels: statiLabels,
                    datasets: [{
                        data: statiData,
                        backgroundColor: statiColors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 11 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var perc = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + perc + '%)';
                                }
                            }
                        }
                    }
                }
            });
            
            // Grafico Entrate Mensili (Bar)
            var ctxEntrate = document.getElementById('chartEntrateMensili').getContext('2d');
            var chartEntrate = new Chart(ctxEntrate, {
                type: 'bar',
                data: {
                    labels: mesiLabels,
                    datasets: [{
                        label: 'Entrate (€)',
                        data: mesiData,
                        backgroundColor: 'rgba(244, 67, 54, 0.6)',
                        borderColor: '#f44336',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '€ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€ ' + value.toFixed(0);
                                }
                            }
                        }
                    }
                }
            });
            
            }); // Fine window.addEventListener('load')
            </script>
            
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
                                    'paid' => ['✅ Pagate', '#4caf50'],
                                    'processing' => ['🔄 In Lavorazione', '#2196f3'],
                                    'completed' => ['✅ Completate', '#27ae60'],
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
            
            <!-- Alert & Notifiche -->
            <?php
            // Calcola alerts
            $oggi = time();
            $richieste_scadute = new WP_Query([
                'post_type' => 'richiesta_servizio',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => 'stato',
                        'value' => 'pending'
                    ]
                ],
                'date_query' => [[
                    'before' => date('Y-m-d', strtotime('-7 days'))
                ]]
            ]);
            
            $pagamenti_ritardo = new WP_Query([
                'post_type' => 'richiesta_servizio',
                'posts_per_page' => -1,
                'meta_query' => [
                    [
                        'key' => 'stato',
                        'value' => 'awaiting_payment'
                    ]
                ],
                'date_query' => [[
                    'before' => date('Y-m-d', strtotime('-3 days'))
                ]]
            ]);
            
            // Obiettivo mensile (es. €5000)
            $obiettivo_mensile = 5000;
            $perc_obiettivo = $obiettivo_mensile > 0 ? round(($stats_mese_corrente['importo'] / $obiettivo_mensile) * 100, 1) : 0;
            $alerts_count = $richieste_scadute->found_posts + $pagamenti_ritardo->found_posts;
            if ($perc_obiettivo < 50) $alerts_count++;
            ?>
            
            <div class="postbox" style="margin: 20px 0; <?php echo $alerts_count > 0 ? 'border-left: 4px solid #ff9800;' : ''; ?>">
                <div class="postbox-header">
                    <h2>🔔 Alert & Notifiche <?php if ($alerts_count > 0) echo '<span class="count" style="background:#ff9800;color:white;border-radius:10px;padding:2px 8px;font-size:12px;margin-left:5px;">' . $alerts_count . '</span>'; ?></h2>
                </div>
                <div class="inside">
                    <?php if ($alerts_count == 0): ?>
                        <div style="text-align: center; padding: 20px; color: #4caf50;">
                            <span style="font-size: 48px;">✅</span>
                            <p style="margin: 10px 0; font-weight: bold;">Tutto OK! Nessun alert</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 10px;">
                            <?php if ($richieste_scadute->found_posts > 0): ?>
                                <div style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 12px; border-radius: 4px;">
                                    <strong>⚠️ <?php echo $richieste_scadute->found_posts; ?> richieste in attesa da oltre 7 giorni</strong>
                                    <br><small>Controlla le richieste pending più vecchie</small>
                                    <br><a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=pending'); ?>" class="button button-small" style="margin-top: 5px;">Vedi Richieste</a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($pagamenti_ritardo->found_posts > 0): ?>
                                <div style="background: #f8d7da; border-left: 4px solid #f44336; padding: 12px; border-radius: 4px;">
                                    <strong>🚨 <?php echo $pagamenti_ritardo->found_posts; ?> pagamenti in ritardo (>3 giorni)</strong>
                                    <br><small>Invia solleciti ai clienti</small>
                                    <br><a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=awaiting_payment'); ?>" class="button button-small" style="margin-top: 5px;">Vedi Pagamenti</a>
                                </div>
                            <?php endif; ?>
                            
                            <div style="background: <?php echo $perc_obiettivo >= 80 ? '#d4edda' : ($perc_obiettivo >= 50 ? '#fff3cd' : '#f8d7da'); ?>; border-left: 4px solid <?php echo $perc_obiettivo >= 80 ? '#4caf50' : ($perc_obiettivo >= 50 ? '#ff9800' : '#f44336'); ?>; padding: 12px; border-radius: 4px;">
                                <strong><?php echo $perc_obiettivo >= 80 ? '🎯' : ($perc_obiettivo >= 50 ? '📊' : '📉'); ?> Obiettivo mensile: <?php echo $perc_obiettivo; ?>%</strong>
                                <br><small>€ <?php echo number_format($stats_mese_corrente['importo'], 2, ',', '.'); ?> / € <?php echo number_format($obiettivo_mensile, 2, ',', '.'); ?></small>
                                <div style="background: #e0e0e0; height: 8px; border-radius: 4px; margin-top: 8px; overflow: hidden;">
                                    <div style="background: <?php echo $perc_obiettivo >= 80 ? '#4caf50' : ($perc_obiettivo >= 50 ? '#ff9800' : '#f44336'); ?>; width: <?php echo min($perc_obiettivo, 100); ?>%; height: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Analytics Utenti -->
            <?php
            // Top utenti per numero richieste
            global $wpdb;
            $top_users_count = $wpdb->get_results("
                SELECT meta_value as user_id, COUNT(*) as count
                FROM {$wpdb->postmeta}
                WHERE meta_key = 'user_id' AND meta_value != ''
                AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'richiesta_servizio')
                GROUP BY meta_value
                ORDER BY count DESC
                LIMIT 10
            ");
            
            // Top utenti per lifetime value
            $top_users_value = $wpdb->get_results("
                SELECT pm1.meta_value as user_id, 
                       SUM(CAST(pm2.meta_value AS DECIMAL(10,2))) as total_value,
                       COUNT(*) as count
                FROM {$wpdb->postmeta} pm1
                LEFT JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = 'importo'
                LEFT JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id AND pm3.meta_key = 'payment_status'
                WHERE pm1.meta_key = 'user_id' 
                  AND pm1.meta_value != ''
                  AND pm1.post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'richiesta_servizio')
                  AND (pm3.meta_value = 'paid' OR pm3.meta_value IS NULL)
                GROUP BY pm1.meta_value
                HAVING total_value > 0
                ORDER BY total_value DESC
                LIMIT 10
            ");
            ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Clienti Più Attivi -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>👥 Top 10 Clienti Più Attivi</h2>
                    </div>
                    <div class="inside">
                        <table class="wp-list-table widefat" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Richieste</th>
                                    <th>Categoria</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_users_count)): ?>
                                    <?php foreach ($top_users_count as $user_stat): 
                                        $user = get_userdata($user_stat->user_id);
                                        if (!$user) continue;
                                        
                                        $categoria = $user_stat->count >= 5 ? 'VIP' : ($user_stat->count >= 2 ? 'Attivo' : 'Nuovo');
                                        $badge_color = $user_stat->count >= 5 ? '#ffd700' : ($user_stat->count >= 2 ? '#4caf50' : '#2196f3');
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=wecoop-user-detail&user_id=' . $user->ID); ?>">
                                                <strong><?php echo esc_html($user->display_name); ?></strong>
                                            </a>
                                        </td>
                                        <td><?php echo $user_stat->count; ?></td>
                                        <td>
                                            <span style="background: <?php echo $badge_color; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                                                <?php echo $categoria; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #999;">Nessun dato</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Lifetime Value -->
                <div class="postbox">
                    <div class="postbox-header">
                        <h2>💎 Top 10 Lifetime Value</h2>
                    </div>
                    <div class="inside">
                        <table class="wp-list-table widefat" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Speso Totale</th>
                                    <th>Media</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($top_users_value)): ?>
                                    <?php foreach ($top_users_value as $user_stat): 
                                        $user = get_userdata($user_stat->user_id);
                                        if (!$user) continue;
                                        
                                        $media = $user_stat->count > 0 ? $user_stat->total_value / $user_stat->count : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=wecoop-user-detail&user_id=' . $user->ID); ?>">
                                                <strong><?php echo esc_html($user->display_name); ?></strong>
                                            </a>
                                        </td>
                                        <td><strong>€ <?php echo number_format($user_stat->total_value, 2, ',', '.'); ?></strong></td>
                                        <td>€ <?php echo number_format($media, 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #999;">Nessun dato</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Previsioni & Trend -->
            <?php
            // Calcola previsione entrate prossimi 3 mesi usando regressione lineare
            $ultimi_mesi = array_slice($stats['entrate_per_mese'], -6, 6, true);
            $mesi_keys = array_keys($ultimi_mesi);
            $mesi_values = array_values($ultimi_mesi);
            
            // Regressione lineare semplice
            $n = count($mesi_values);
            $slope = 0;
            $sum_y = 0;
            $forecast = [];
            
            if ($n >= 3) {
                $sum_x = 0;
                $sum_y = 0;
                $sum_xy = 0;
                $sum_xx = 0;
                
                for ($i = 0; $i < $n; $i++) {
                    $sum_x += $i;
                    $sum_y += $mesi_values[$i];
                    $sum_xy += $i * $mesi_values[$i];
                    $sum_xx += $i * $i;
                }
                
                $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_xx - $sum_x * $sum_x);
                $intercept = ($sum_y - $slope * $sum_x) / $n;
                
                for ($i = 1; $i <= 3; $i++) {
                    $next_month = date('M Y', strtotime("+$i months"));
                    $predicted = max(0, $intercept + $slope * ($n + $i - 1));
                    $forecast[$next_month] = round($predicted, 2);
                }
            }
            
            // Analisi stagionalità (media per mese dell'anno)
            $stagionalita = [];
            foreach ($stats['entrate_per_mese'] as $mese => $importo) {
                $mese_num = date('n', strtotime($mese . '-01'));
                if (!isset($stagionalita[$mese_num])) {
                    $stagionalita[$mese_num] = ['sum' => 0, 'count' => 0];
                }
                $stagionalita[$mese_num]['sum'] += $importo;
                $stagionalita[$mese_num]['count']++;
            }
            
            $mese_migliore = null;
            $max_media = 0;
            foreach ($stagionalita as $mese_num => $data) {
                $media = $data['count'] > 0 ? $data['sum'] / $data['count'] : 0;
                if ($media > $max_media) {
                    $max_media = $media;
                    $mese_migliore = $mese_num;
                }
            }
            ?>
            
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2>💹 Previsioni & Trend</h2>
                </div>
                <div class="inside">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <!-- Proiezione Entrate -->
                        <div>
                            <h3 style="margin: 0 0 15px 0; font-size: 14px; color: #666;">📊 Proiezione Prossimi 3 Mesi</h3>
                            <?php if (!empty($forecast)): ?>
                                <table class="widefat" style="margin: 0;">
                                    <tbody>
                                        <?php foreach ($forecast as $mese => $importo): ?>
                                        <tr>
                                            <td><strong><?php echo $mese; ?></strong></td>
                                            <td style="text-align: right;">€ <?php echo number_format($importo, 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr style="border-top: 2px solid #ddd;">
                                            <td><strong>Totale Previsto</strong></td>
                                            <td style="text-align: right;"><strong>€ <?php echo number_format(array_sum($forecast), 2, ',', '.'); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="color: #999; font-style: italic;">Dati insufficienti per previsione</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Trend Previsionale -->
                        <div>
                            <h3 style="margin: 0 0 15px 0; font-size: 14px; color: #666;">📈 Trend Generale</h3>
                            <?php
                            $trend_generale = $slope > 0 ? 'crescita' : 'decrescita';
                            $trend_perc = $sum_y > 0 ? abs(($slope / ($sum_y / $n)) * 100) : 0;
                            $trend_icon = $slope > 0 ? '📈' : '📉';
                            $trend_color = $slope > 0 ? '#4caf50' : '#f44336';
                            ?>
                            <div style="text-align: center; padding: 20px; background: <?php echo $trend_color; ?>; color: white; border-radius: 8px;">
                                <div style="font-size: 48px; margin-bottom: 10px;"><?php echo $trend_icon; ?></div>
                                <div style="font-size: 24px; font-weight: bold; text-transform: uppercase;"><?php echo $trend_generale; ?></div>
                                <div style="font-size: 16px; margin-top: 5px;"><?php echo round($trend_perc, 1); ?>% mensile</div>
                            </div>
                        </div>
                        
                        <!-- Stagionalità -->
                        <div>
                            <h3 style="margin: 0 0 15px 0; font-size: 14px; color: #666;">📅 Stagionalità</h3>
                            <?php if ($mese_migliore): ?>
                                <div style="text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                                    <div style="font-size: 16px; margin-bottom: 10px;">Mese Migliore</div>
                                    <div style="font-size: 32px; font-weight: bold;">
                                        <?php
                                        $mese_nome = [
                                            1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
                                            5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
                                            9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
                                        ];
                                        echo $mese_nome[$mese_migliore];
                                        ?>
                                    </div>
                                    <div style="font-size: 14px; margin-top: 5px;">Media: € <?php echo number_format($max_media, 2, ',', '.'); ?></div>
                                </div>
                            <?php else: ?>
                                <p style="color: #999; font-style: italic;">Dati insufficienti</p>
                            <?php endif; ?>
                        </div>
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
                        <a href="<?php echo admin_url('admin.php?page=wecoop-richieste-servizi&stato=paid'); ?>" class="button button-large" style="height: auto; padding: 15px; text-align: center; background: #4caf50; color: white; border-color: #388e3c;">
                            ✅ Pagato (<?php echo $stats['completed']; ?>)
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
            // Se filtro "paid", cerca per payment_status invece di stato
            if ($stato_filter === 'paid') {
                $args['meta_query'] = [
                    [
                        'key' => 'payment_status',
                        'value' => 'paid'
                    ]
                ];
            } else {
                $args['meta_query'] = [
                    [
                        'key' => 'stato',
                        'value' => $stato_filter
                    ]
                ];
            }
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
                    <option value="paid" <?php selected($stato_filter, 'paid'); ?>>✅ Pagato</option>
                    <option value="processing" <?php selected($stato_filter, 'processing'); ?>>In Lavorazione</option>
                    <option value="completed" <?php selected($stato_filter, 'completed'); ?>>Completata</option>
                    <option value="cancelled" <?php selected($stato_filter, 'cancelled'); ?>>Annullata</option>
                </select>
                
                <button type="submit" class="button">Filtra</button>
                <a href="?page=wecoop-richieste-list" class="button">Reset</a>
            </form>
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select id="bulk-action-selector-top">
                        <option value="-1">Azioni di massa</option>
                        <option value="delete">Elimina</option>
                    </select>
                    <button type="button" id="doaction" class="button action">Applica</button>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped" id="richieste-table">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </td>
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
                            <td colspan="9">Nessuna richiesta trovata.</td>
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
        <?php self::render_payment_modal(); ?>
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
        
        // Ottieni info pagamento dalla tabella wp_wecoop_pagamenti
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE richiesta_id = %d ORDER BY created_at DESC LIMIT 1",
            $post_id
        ));
        
        // Se non c'è importo, prova a prenderlo dal listino
        if (!$importo || $importo == 0) {
            $prezzi_servizi = get_option('wecoop_listino_servizi', []);
            $prezzi_categorie = get_option('wecoop_listino_categorie', []);
            
            // Cerca per servizio
            if (isset($prezzi_servizi[$servizio])) {
                $importo = floatval($prezzi_servizi[$servizio]);
            }
            // Altrimenti cerca per categoria
            elseif ($categoria && isset($prezzi_categorie[$categoria])) {
                $importo = floatval($prezzi_categorie[$categoria]);
            }
        }
        
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
            'cancelled' => '❌ Annullata',
            'paid' => '✅ Pagato'
        ];
        
        $stato_colors = [
            'pending' => '#ff9800',
            'awaiting_payment' => '#9c27b0',
            'processing' => '#2196f3',
            'completed' => '#4caf50',
            'cancelled' => '#f44336',
            'paid' => '#4caf50'
        ];
        ?>
        <tr>
            <th scope="row" class="check-column">
                <input type="checkbox" class="richiesta-checkbox" value="<?php echo $post_id; ?>">
            </th>
            <td><strong><?php echo esc_html($numero_pratica); ?></strong></td>
            <td>
                <?php 
                $servizio_normalizzato = WECOOP_Servizi_Normalizer::normalize($servizio);
                if ($servizio !== $servizio_normalizzato) {
                    echo '<span title="Originale: ' . esc_attr($servizio) . '" style="border-bottom: 1px dotted #999; cursor: help;">';
                    echo esc_html($servizio_normalizzato);
                    echo '</span>';
                } else {
                    echo esc_html($servizio);
                }
                ?>
            </td>
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
                    <?php if ($payment && in_array($payment->stato, ['paid', 'completed'])): ?>
                        <br><span style="color: #4caf50; font-weight: bold;" title="Pagato il <?php echo $payment->paid_at; ?>">
                            ✅ Pagato
                        </span>
                        <?php if ($payment->metodo_pagamento): ?>
                            <br><small style="color: #666;">via <?php echo ucfirst($payment->metodo_pagamento); ?></small>
                        <?php endif; ?>
                        <?php if ($payment->transaction_id): ?>
                            <br><small style="color: #999;" title="ID Transazione"><?php echo esc_html(substr($payment->transaction_id, 0, 20)); ?>...</small>
                        <?php endif; ?>
                    <?php elseif ($payment && $payment->stato === 'pending'): ?>
                        <br><span style="color: orange;" title="In attesa di pagamento">⏳ Da pagare</span>
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
                <?php if (!$payment || $payment->stato === 'pending'): ?>
                    <button class="button button-small button-primary open-payment-modal" 
                            data-id="<?php echo $post_id; ?>"
                            data-importo="<?php echo $importo ? esc_attr($importo) : ''; ?>"
                            data-servizio="<?php echo esc_attr($servizio); ?>"
                            data-categoria="<?php echo esc_attr($categoria); ?>"
                            title="Richiedi pagamento">
                        💳 <?php echo $payment ? 'Reinvia Richiesta' : 'Richiedi Pagamento'; ?>
                    </button>
                <?php elseif ($payment && in_array($payment->stato, ['paid', 'completed'])): ?>
                    <span style="color: #4caf50; font-weight: bold;">✅ Pagato</span>
                    <?php if (!empty($payment->receipt_url)): ?>
                        <br>
                        <a href="<?php echo esc_url($payment->receipt_url); ?>" 
                           target="_blank" 
                           class="button button-small"
                           style="margin-top: 5px;">
                            📄 Scarica Ricevuta
                        </a>
                    <?php else: ?>
                        <br>
                        <button class="button button-small generate-receipt" 
                                data-payment-id="<?php echo $payment->id; ?>"
                                style="margin-top: 5px;">
                            📄 Genera Ricevuta
                        </button>
                    <?php endif; ?>
                    <?php 
                    // Verifica se il documento unico è stato già firmato
                    $doc_firmato = get_post_meta($post_id, 'documento_unico_firmato', true);
                    ?>
                    <br>
                    <button class="button button-small button-primary send-documento-unico" 
                            data-id="<?php echo $post_id; ?>"
                            style="margin-top: 5px; background: #2196f3;"
                            title="Invia documento unico da firmare">
                        📝 <?php echo $doc_firmato === 'yes' ? 'Documento Firmato ✅' : 'Manda Documento Unico'; ?>
                    </button>
                <?php endif; ?>
                <button class="button button-small button-link-delete delete-richiesta" 
                        data-id="<?php echo $post_id; ?>"
                        title="Elimina richiesta">
                    🗑️ Elimina
                </button>
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
                        
                        <h3>📎 Documenti Allegati</h3>
                        <div id="documenti-allegati" style="margin-bottom: 20px;"></div>
                        
                        <div class="wecoop-modal-footer">
                            <button type="button" class="button wecoop-modal-close">Annulla</button>
                            <button type="submit" class="button button-primary">Salva Modifiche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Modal richiesta pagamento
     */
    private static function render_payment_modal() {
        ?>
        <div id="payment-request-modal" class="wecoop-modal" style="display:none;">
            <div class="wecoop-modal-backdrop">
                <div class="wecoop-modal-content" style="max-width: 500px;">
                    <div class="wecoop-modal-header">
                        <h2>💳 Richiesta di Pagamento</h2>
                        <button class="wecoop-modal-close">&times;</button>
                    </div>
                    <div class="wecoop-modal-body">
                        <form id="payment-request-form">
                            <input type="hidden" id="payment_richiesta_id" name="richiesta_id">
                        
                        <div class="form-field">
                            <label for="payment_importo">
                                Importo (€) <span style="color: red;">*</span>
                            </label>
                            <input type="number" 
                                   id="payment_importo" 
                                   name="importo" 
                                   step="0.01" 
                                   min="0.01" 
                                   required 
                                   placeholder="es. 50.00"
                                   style="width: 100%; padding: 8px; font-size: 16px;">
                            <p class="description">Inserisci l'importo da richiedere al cliente</p>
                        </div>
                        
                        <div class="form-field" style="margin-top: 15px;">
                            <label>
                                <input type="checkbox" id="payment_update_stato" name="update_stato" checked>
                                Cambia stato in "Da Pagare"
                            </label>
                        </div>
                        
                        <div style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 12px; margin: 15px 0; border-radius: 4px;">
                            <strong>📧 Cosa succede:</strong>
                            <ul style="margin: 8px 0 0 20px; font-size: 13px;">
                                <li>Viene creato un ordine WooCommerce</li>
                                <li>Il cliente riceve un'email con il link di pagamento</li>
                                <li>L'importo viene salvato nella richiesta</li>
                            </ul>
                        </div>
                        
                        <div class="wecoop-modal-footer">
                            <button type="button" class="button wecoop-modal-close">Annulla</button>
                            <button type="submit" class="button button-primary">
                                💳 Invia Richiesta di Pagamento
                            </button>
                        </div>
                    </form>
                </div>
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
            
            // Apri modal pagamento
            $(document).on('click', '.open-payment-modal', function(e) {
                e.preventDefault();
                
                const richiestaId = $(this).data('id');
                let importo = $(this).data('importo');
                const servizio = $(this).data('servizio');
                const categoria = $(this).data('categoria');
                
                // Se importo non è presente, prova a prenderlo dal listino via AJAX
                if (!importo || importo == 0 || importo == '') {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        async: false, // Sincrono per avere il valore subito
                        data: {
                            action: 'get_prezzo_listino',
                            nonce: '<?php echo wp_create_nonce("wecoop_servizi_nonce"); ?>',
                            servizio: servizio,
                            categoria: categoria
                        },
                        success: function(response) {
                            if (response.success && response.data.prezzo) {
                                importo = response.data.prezzo;
                            }
                        }
                    });
                }
                
                // Popola il form
                $('#payment_richiesta_id').val(richiestaId);
                $('#payment_importo').val(importo || '');
                
                // Mostra modal centrata
                $('#payment-request-modal').fadeIn(200);
                $('body').css('overflow', 'hidden'); // Previeni scroll della pagina
            });
            
            // Chiudi modal pagamento - click su X
            $(document).on('click', '#payment-request-modal .wecoop-modal-close', function(e) {
                e.preventDefault();
                $('#payment-request-modal').fadeOut(200);
                $('body').css('overflow', ''); // Ripristina scroll
            });
            
            // Chiudi modal pagamento - click su backdrop
            $(document).on('click', '#payment-request-modal .wecoop-modal-backdrop', function(e) {
                // Chiudi solo se clicchi direttamente sul backdrop, non sul contenuto
                if (e.target === this) {
                    $('#payment-request-modal').fadeOut(200);
                    $('body').css('overflow', ''); // Ripristina scroll
                }
            });
            
            // Submit form pagamento
            $('#payment-request-form').on('submit', function(e) {
                e.preventDefault();
                
                const richiestaId = $('#payment_richiesta_id').val();
                const importo = $('#payment_importo').val();
                const updateStato = $('#payment_update_stato').is(':checked');
                const $submitBtn = $(this).find('button[type="submit"]');
                const originalText = $submitBtn.text();
                
                if (!importo || parseFloat(importo) <= 0) {
                    alert('❌ Inserisci un importo valido');
                    return;
                }
                
                if (!confirm('Confermi l\'invio della richiesta di pagamento di €' + importo + '?')) {
                    return;
                }
                
                $submitBtn.prop('disabled', true).text('⏳ Invio in corso...');
                
                console.log('🔄 Invio richiesta pagamento:', {
                    richiestaId: richiestaId,
                    importo: importo,
                    updateStato: updateStato
                });
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'send_payment_request',
                        richiesta_id: richiestaId,
                        importo: importo,
                        update_stato: updateStato ? '1' : '0',
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        console.log('✅ Risposta server:', response);
                        if (response.success) {
                            alert('✅ ' + response.data);
                            $('#payment-request-modal').fadeOut(200);
                            $('body').css('overflow', ''); // Ripristina scroll
                            location.reload();
                        } else {
                            console.error('❌ Errore server:', response.data);
                            alert('❌ Errore: ' + response.data);
                            $submitBtn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Errore AJAX:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        alert('❌ Errore di comunicazione con il server. Controlla la console (F12) per dettagli.');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
            
            // Select all checkbox
            $('#cb-select-all').on('change', function() {
                $('.richiesta-checkbox').prop('checked', $(this).prop('checked'));
            });
            
            // Bulk delete action
            $('#doaction').on('click', function(e) {
                e.preventDefault();
                
                const action = $('#bulk-action-selector-top').val();
                if (action !== 'delete') {
                    return;
                }
                
                const selected = [];
                $('.richiesta-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                
                if (selected.length === 0) {
                    alert('❌ Seleziona almeno una richiesta da eliminare');
                    return;
                }
                
                if (!confirm('⚠️ SEI SICURO di voler eliminare ' + selected.length + ' richiesta/e?\n\nQuesta azione NON può essere annullata!')) {
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true).text('Eliminazione...');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'bulk_delete_richieste',
                        richieste_ids: selected,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ ' + response.data);
                            location.reload();
                        } else {
                            alert('❌ Errore: ' + response.data);
                            $btn.prop('disabled', false).text('Applica');
                        }
                    },
                    error: function() {
                        alert('❌ Errore di comunicazione con il server');
                        $btn.prop('disabled', false).text('Applica');
                    }
                });
            });
            
            // Single delete
            $(document).on('click', '.delete-richiesta', function(e) {
                e.preventDefault();
                
                const richiestaId = $(this).data('id');
                const $btn = $(this);
                
                if (!confirm('⚠️ SEI SICURO di voler eliminare questa richiesta?\n\nQuesta azione NON può essere annullata!')) {
                    return;
                }
                
                $btn.prop('disabled', true).text('⏳ Eliminazione...');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'bulk_delete_richieste',
                        richieste_ids: [richiestaId],
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ Richiesta eliminata con successo');
                            location.reload();
                        } else {
                            alert('❌ Errore: ' + response.data);
                            $btn.prop('disabled', false).text('🗑️ Elimina');
                        }
                    },
                    error: function() {
                        alert('❌ Errore di comunicazione con il server');
                        $btn.prop('disabled', false).text('🗑️ Elimina');
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
            
            // Genera ricevuta
            $(document).on('click', '.generate-receipt', function(e) {
                e.preventDefault();
                
                const paymentId = $(this).data('payment-id');
                const $btn = $(this);
                const originalText = $btn.text();
                
                if (!confirm('Vuoi generare la ricevuta PDF per questo pagamento?')) {
                    return;
                }
                
                $btn.prop('disabled', true).text('⏳ Generazione...');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'generate_receipt',
                        payment_id: paymentId,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ Ricevuta generata con successo!');
                            location.reload();
                        } else {
                            alert('❌ Errore: ' + response.data.message);
                            $btn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        alert('❌ Errore di comunicazione con il server. Controlla la console (F12).');
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
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
                            
                            // Traduzioni modalità consegna
                            const deliveryMethodLabels = {
                                'pickup': 'Ritiro in sede',
                                'email': 'Indirizzo email',
                                'courier': 'Corriere'
                            };
                            
                            for (const [key, value] of Object.entries(dati)) {
                                const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                                
                                // Gestione speciale per modalità consegna
                                if (key === 'modalita_consegna') {
                                    let consegnaHtml = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
                                    
                                    // Se value è un array o stringa separata da virgole
                                    const metodi = Array.isArray(value) ? value : (value || '').split(',').map(m => m.trim()).filter(Boolean);
                                    
                                    metodi.forEach(function(metodo) {
                                        const labelTradotta = deliveryMethodLabels[metodo] || metodo;
                                        consegnaHtml += `
                                            <span style="
                                                background: #e3f2fd;
                                                color: #1976d2;
                                                padding: 6px 12px;
                                                border-radius: 16px;
                                                font-size: 13px;
                                                font-weight: 500;
                                                display: inline-block;
                                            ">
                                                ${labelTradotta}
                                            </span>
                                        `;
                                    });
                                    
                                    consegnaHtml += '</div>';
                                    
                                    datiHtml += `
                                        <div class="form-field">
                                            <label>Modalità Consegna</label>
                                            ${consegnaHtml}
                                        </div>
                                    `;
                                } else {
                                    // Campo normale
                                    datiHtml += `
                                        <div class="form-field">
                                            <label>${label}</label>
                                            <input type="text" name="dati[${key}]" value="${value || ''}" readonly>
                                        </div>
                                    `;
                                }
                            }
                            
                            $('#dati-richiedente').html(datiHtml);
                            
                            // ⭐ Popola documenti allegati
                            let documentiHtml = '';
                            const documenti = data.documenti || [];
                            
                            if (documenti.length > 0) {
                                documentiHtml = '<div class="documenti-list" style="display: flex; flex-wrap: wrap; gap: 10px;">';
                                documenti.forEach(function(doc) {
                                    const icon = doc.tipo.includes('identita') || doc.tipo.includes('carta') ? '🪪' :
                                                 doc.tipo.includes('fiscale') ? '🧾' : 
                                                 doc.tipo.includes('permesso') || doc.tipo.includes('soggiorno') ? '📋' : '📄';
                                    
                                    const scadenza = doc.data_scadenza ? '<small>Scad: ' + doc.data_scadenza + '</small>' : '';
                                    
                                    documentiHtml += `
                                        <div class="documento-item" style="
                                            border: 1px solid #ddd;
                                            border-radius: 8px;
                                            padding: 12px;
                                            background: #f9f9f9;
                                            min-width: 200px;
                                            display: flex;
                                            flex-direction: column;
                                            gap: 5px;
                                        ">
                                            <div style="font-size: 24px;">${icon}</div>
                                            <strong style="font-size: 12px; text-transform: uppercase; color: #666;">
                                                ${doc.tipo.replace(/_/g, ' ')}
                                            </strong>
                                            <div style="font-size: 11px; color: #999; overflow: hidden; text-overflow: ellipsis;">
                                                ${doc.file_name}
                                            </div>
                                            ${scadenza}
                                            <a href="${doc.url}" target="_blank" class="button button-small" style="margin-top: 5px;">
                                                👁️ Visualizza
                                            </a>
                                        </div>
                                    `;
                                });
                                documentiHtml += '</div>';
                            } else {
                                documentiHtml = '<p style="color: #999; font-style: italic;">Nessun documento allegato</p>';
                            }
                            
                            $('#documenti-allegati').html(documentiHtml);
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
            
            // 🔐 GESTORE: Manda Documento Unico (Firma Digitale)
            $(document).on('click', '.send-documento-unico', function(e) {
                e.preventDefault();
                
                const richiestaId = $(this).data('id');
                const $button = $(this);
                const originalText = $button.text();
                const originalStyle = $button.attr('style');
                
                console.log('🔐 FIRMA: Click su send-documento-unico', {
                    richiestaId: richiestaId,
                    timestamp: new Date().toISOString()
                });
                
                // Conferma dell'utente
                if (!confirm('Vuoi mandare il documento unico per la firma digitale?\n\nIl cliente riceverà una notifica con il link al documento.')) {
                    console.log('❌ FIRMA: Annullato dall\'utente');
                    return;
                }
                
                // Mostra loader
                $button.prop('disabled', true)
                       .html('⏳ <span style="display: inline-block; animation: spin 1s linear infinite;">⟳</span> Generazione documento...')
                       .css('background-color', '#f0b849')
                       .css('cursor', 'not-allowed');
                
                console.log('⏳ FIRMA: Loader mostrato, invio richiesta...');
                
                // Richiesta AJAX per generare documento
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'send_documento_unico',
                        richiesta_id: richiestaId,
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    timeout: 30000, // 30 secondi timeout
                    success: function(response) {
                        console.log('✅ FIRMA: Risposta server ricevuta', response);
                        console.log('📊 FIRMA: Struttura risposta:', {
                            success: response.success,
                            data_type: typeof response.data,
                            data_keys: Object.keys(response.data || {}),
                            documento: response.data?.documento,
                            url: response.data?.documento?.url,
                            hash: response.data?.documento?.hash_sha256?.substring(0, 16)
                        });
                        
                        if (response.success) {
                            const url = response.data?.documento?.url;
                            const hash = response.data?.documento?.hash_sha256;
                            
                            console.log('✅ FIRMA: Documento generato con successo', {
                                pdf_url: url,
                                hash: hash?.substring(0, 16) + '...',
                                has_url: !!url,
                                has_hash: !!hash
                            });
                            
                            if (!url) {
                                console.error('❌ FIRMA: URL PDF è vuoto!', {
                                    documento: response.data?.documento,
                                    response_data: response.data
                                });
                            }
                            
                            // Aggiorna bottone
                            $button.html('✅ Documento Generato')
                                   .css('background-color', '#4caf50')
                                   .css('cursor', 'default');
                            
                            alert('✅ Documento generato con successo!\n\nIl cliente potrà ora firmare il documento tramite l\'app.');
                            
                            // Mostra il link al PDF in console
                            if (url) {
                                console.log('📄 FIRMA: Link al PDF:', url);
                            }
                            
                            // Ricarica la pagina dopo 2 secondi
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            console.error('❌ FIRMA: Errore nella risposta', response.data);
                            
                            $button.prop('disabled', false)
                                   .text(originalText)
                                   .attr('style', originalStyle);
                            
                            alert('❌ Errore: ' + (response.data || 'Errore sconosciuto'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ FIRMA: Errore AJAX completamente', {
                            status: status,
                            error: error,
                            statusCode: xhr.status,
                            responseText: xhr.responseText,
                            readyState: xhr.readyState
                        });
                        
                        // Log della risposta grezzo per debugging
                        if (xhr.responseText) {
                            console.error('📜 FIRMA: Response Body:', xhr.responseText.substring(0, 500));
                        }
                        
                        $button.prop('disabled', false)
                               .text(originalText)
                               .attr('style', originalStyle);
                        
                        let errorMsg = 'Errore di comunicazione con il server';
                        
                        if (status === 'timeout') {
                            errorMsg = 'Timeout: il server ha impiegato troppo tempo (>30s)';
                        } else if (xhr.status === 0) {
                            errorMsg = 'Errore di connessione network';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Errore interno del server (500)';
                        } else if (xhr.status === 403) {
                            errorMsg = 'Accesso negato - Non hai permessi sufficienti';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Endpoint non trovato';
                        }
                        
                        alert('❌ ' + errorMsg + '\n\nApri la console (F12) per dettagli completi');
                    }
                });
            });
            
            // CSS per animazione loader
            $('<style>')
                .text(`
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                `)
                .appendTo('head');
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
        
        // ⭐ Aggiungi documenti allegati
        $documenti_allegati = get_post_meta($richiesta_id, 'documenti_allegati', true);
        $response['documenti'] = [];
        
        if (!empty($documenti_allegati)) {
            foreach ($documenti_allegati as $doc) {
                $response['documenti'][] = [
                    'tipo' => $doc['tipo'] ?? 'altro',
                    'attachment_id' => $doc['attachment_id'] ?? 0,
                    'file_name' => $doc['file_name'] ?? '',
                    'url' => $doc['url'] ?? '',
                    'data_scadenza' => $doc['data_scadenza'] ?? ''
                ];
            }
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
        try {
            error_log('🔄 PAYMENT: Inizio ajax_send_payment_request');
            error_log('🔄 PAYMENT: POST data: ' . print_r($_POST, true));
            
            check_ajax_referer('wecoop_servizi_nonce', 'nonce');
            error_log('✅ PAYMENT: Nonce verificato');
        
        if (!current_user_can('manage_options')) {
            error_log('❌ PAYMENT: Permessi insufficienti');
            wp_send_json_error('Permessi insufficienti');
        }
        error_log('✅ PAYMENT: Permessi verificati');
        
        // Verifica che il sistema di pagamento sia attivo
        if (!class_exists('WeCoop_Payment_System')) {
            error_log('❌ PAYMENT: WeCoop_Payment_System non trovato');
            wp_send_json_error('Sistema di pagamento non disponibile.');
        }
        error_log('✅ PAYMENT: Sistema di pagamento attivo');
        
        $richiesta_id = absint($_POST['richiesta_id']);
        $importo_input = isset($_POST['importo']) ? floatval($_POST['importo']) : 0;
        $update_stato = isset($_POST['update_stato']) && $_POST['update_stato'] === '1';
        
        error_log('📊 PAYMENT: Richiesta ID: ' . $richiesta_id);
        error_log('📊 PAYMENT: Importo input: ' . $importo_input);
        error_log('📊 PAYMENT: Update stato: ' . ($update_stato ? 'SI' : 'NO'));
        
        // Verifica che la richiesta esista
        $richiesta = get_post($richiesta_id);
        if (!$richiesta) {
            error_log('❌ PAYMENT: Richiesta non trovata');
            wp_send_json_error('Richiesta non trovata.');
        }
        
        // Ottieni user_id dalla richiesta
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        if (!$user_id) {
            error_log('❌ PAYMENT: user_id non trovato nei metadati');
            wp_send_json_error('User ID non trovato nella richiesta.');
        }
        error_log('✅ PAYMENT: User ID: ' . $user_id);
        
        // Se viene fornito un importo dal modal, aggiornalo
        if ($importo_input > 0) {
            update_post_meta($richiesta_id, 'importo', $importo_input);
            $importo = $importo_input;
            error_log('✅ PAYMENT: Importo aggiornato a: ' . $importo);
        } else {
            // Altrimenti usa quello esistente
            $importo = get_post_meta($richiesta_id, 'importo', true);
            error_log('📋 PAYMENT: Importo esistente: ' . $importo);
        }
        
        // Verifica importo
        if (!$importo || $importo <= 0) {
            error_log('❌ PAYMENT: Importo non valido: ' . $importo);
            wp_send_json_error('Importo non specificato. Inserisci un importo valido.');
        }
        error_log('✅ PAYMENT: Importo valido: ' . $importo);
        
        // Verifica se esiste già un pagamento
        $existing_payment = WeCoop_Payment_System::get_payment_by_richiesta($richiesta_id);
        error_log('🔍 PAYMENT: Pagamento esistente: ' . ($existing_payment ? 'ID #' . $existing_payment->id : 'NESSUNO'));
        
        if ($existing_payment) {
            // Pagamento già esistente, reinvia email
            error_log('📧 PAYMENT: Reinvio email per pagamento esistente #' . $existing_payment->id);
            
            // Aggiorna importo se cambiato
            if ($importo != $existing_payment->importo) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'wecoop_pagamenti';
                $wpdb->update(
                    $table_name,
                    ['importo' => $importo, 'updated_at' => current_time('mysql')],
                    ['id' => $existing_payment->id],
                    ['%f', '%s'],
                    ['%d']
                );
                error_log('✅ PAYMENT: Importo aggiornato da €' . $existing_payment->importo . ' a €' . $importo);
            }
            
            WeCoop_Payment_System::send_payment_email($richiesta_id, $existing_payment->id);
            error_log('✅ PAYMENT: Email inviata con successo');
            wp_send_json_success('Email con richiesta di pagamento reinviata! (€' . number_format($importo, 2) . ')');
        } else {
            error_log('🆕 PAYMENT: Creazione nuovo pagamento');
            
            // Cambia stato se richiesto
            if ($update_stato) {
                error_log('📝 PAYMENT: Aggiornamento stato a awaiting_payment');
                update_post_meta($richiesta_id, 'stato', 'awaiting_payment');
            }
            
            // Assicurati che user_id e importo siano nei post_meta
            update_post_meta($richiesta_id, 'user_id', $user_id);
            update_post_meta($richiesta_id, 'importo', $importo);
            
            // Crea nuovo pagamento
            error_log('🏗️ PAYMENT: Chiamata create_payment...');
            try {
                $payment_id = WeCoop_Payment_System::create_payment($richiesta_id);
                error_log('🔍 PAYMENT: Risultato creazione: ' . ($payment_id ? $payment_id : 'FALSE'));
                
                if ($payment_id) {
                    error_log('✅ PAYMENT: Pagamento creato con successo #' . $payment_id);
                    wp_send_json_success('Pagamento creato e richiesta inviata! (€' . number_format($importo, 2) . ')');
                } else {
                    error_log('❌ PAYMENT: Errore creazione pagamento');
                    wp_send_json_error('Errore durante la creazione del pagamento. Verifica che user_id e importo siano validi.');
                }
            } catch (Exception $e) {
                error_log('❌ PAYMENT: Exception durante creazione: ' . $e->getMessage());
                error_log('❌ PAYMENT: Stack trace: ' . $e->getTraceAsString());
                wp_send_json_error('Errore durante creazione pagamento: ' . $e->getMessage());
            }
        }
        } catch (Throwable $e) {
            error_log('❌ PAYMENT: ERRORE FATALE in ajax_send_payment_request');
            error_log('❌ PAYMENT: Messaggio: ' . $e->getMessage());
            error_log('❌ PAYMENT: File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('❌ PAYMENT: Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Errore interno del server: ' . $e->getMessage());
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
    
    /**
     * AJAX: Export CSV
     */
    public static function ajax_export_csv() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti');
        }
        
        $periodo = isset($_GET['periodo']) ? sanitize_text_field($_GET['periodo']) : 'all';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        
        $args = [
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Filtra per periodo
        if ($periodo !== 'all' && $periodo !== 'custom') {
            $days_map = [
                '7days' => 7,
                '30days' => 30,
                '3months' => 90,
                '1year' => 365
            ];
            
            if (isset($days_map[$periodo])) {
                $args['date_query'] = [[
                    'after' => date('Y-m-d', strtotime('-' . $days_map[$periodo] . ' days'))
                ]];
            }
        } elseif ($periodo === 'custom' && $date_from && $date_to) {
            $args['date_query'] = [[
                'after' => $date_from,
                'before' => $date_to,
                'inclusive' => true
            ]];
        }
        
        $query = new WP_Query($args);
        
        // Headers CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=richieste-servizi-' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // BOM per UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header colonne
        fputcsv($output, [
            'N. Pratica',
            'Servizio',
            'Categoria',
            'Nome Richiedente',
            'Email',
            'Telefono',
            'Data Richiesta',
            'Stato',
            'Importo (€)',
            'Pagamento',
            'Data Pagamento',
            'User ID'
        ], ';');
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $numero_pratica = get_post_meta($post_id, 'numero_pratica', true);
                $servizio = get_post_meta($post_id, 'servizio', true);
                $categoria = get_post_meta($post_id, 'categoria', true);
                $stato = get_post_meta($post_id, 'stato', true);
                $importo = get_post_meta($post_id, 'importo', true);
                $payment_status = get_post_meta($post_id, 'payment_status', true);
                $payment_paid_at = get_post_meta($post_id, 'payment_paid_at', true);
                $user_id = get_post_meta($post_id, 'user_id', true);
                
                $dati = json_decode(get_post_meta($post_id, 'dati', true), true) ?: [];
                
                $nome = $dati['nome_completo'] ?? '';
                $email = $dati['email'] ?? '';
                $telefono = $dati['telefono'] ?? '';
                
                if (!$nome && $user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $nome = $user->display_name;
                        $email = $user->user_email;
                    }
                }
                
                $stato_labels = [
                    'pending' => 'In Attesa',
                    'awaiting_payment' => 'Da Pagare',
                    'processing' => 'In Lavorazione',
                    'completed' => 'Completata',
                    'cancelled' => 'Annullata'
                ];
                
                $payment_labels = [
                    'paid' => 'Pagato',
                    'pending' => 'In attesa',
                    'failed' => 'Fallito',
                    'refunded' => 'Rimborsato'
                ];
                
                fputcsv($output, [
                    $numero_pratica,
                    $servizio,
                    $categoria,
                    $nome,
                    $email,
                    $telefono,
                    get_the_date('d/m/Y H:i'),
                    $stato_labels[$stato] ?? $stato,
                    $importo ? number_format($importo, 2, ',', '') : '',
                    $payment_labels[$payment_status] ?? '-',
                    $payment_paid_at ? date('d/m/Y H:i', strtotime($payment_paid_at)) : '',
                    $user_id
                ], ';');
            }
        }
        
        fclose($output);
        wp_reset_postdata();
        exit;
    }
    
    /**
     * AJAX: Get dashboard data per periodo
     */
    public static function ajax_get_dashboard_data() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $periodo = isset($_POST['periodo']) ? sanitize_text_field($_POST['periodo']) : '30days';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        
        $data = self::get_dashboard_stats($periodo, $date_from, $date_to);
        
        wp_send_json_success($data);
    }
    
    /**
     * Ottieni statistiche dashboard per periodo
     */
    private static function get_dashboard_stats($periodo = '30days', $date_from = '', $date_to = '') {
        $args = [
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        // Filtra per periodo
        if ($periodo !== 'all' && $periodo !== 'custom') {
            $days_map = [
                '7days' => 7,
                '30days' => 30,
                '3months' => 90,
                '1year' => 365
            ];
            
            if (isset($days_map[$periodo])) {
                $args['date_query'] = [[
                    'after' => date('Y-m-d', strtotime('-' . $days_map[$periodo] . ' days'))
                ]];
            }
        } elseif ($periodo === 'custom' && $date_from && $date_to) {
            $args['date_query'] = [[
                'after' => $date_from,
                'before' => $date_to,
                'inclusive' => true
            ]];
        }
        
        $query = new WP_Query($args);
        
        $stats = [
            'totale' => 0,
            'stati' => [],
            'importo_totale' => 0,
            'importo_pagato' => 0,
            'importo_attesa' => 0,
            'richieste_per_giorno' => [],
            'entrate_per_giorno' => [],
            'top_clienti' => [],
            'lifetime_values' => []
        ];
        
        $user_stats = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $stato = get_post_meta($post_id, 'stato', true);
                $importo = floatval(get_post_meta($post_id, 'importo', true));
                $payment_status = get_post_meta($post_id, 'payment_status', true);
                $user_id = get_post_meta($post_id, 'user_id', true);
                $data = get_the_date('Y-m-d');
                
                $stats['totale']++;
                
                if (!isset($stats['stati'][$stato])) {
                    $stats['stati'][$stato] = 0;
                }
                $stats['stati'][$stato]++;
                
                if ($importo > 0) {
                    $stats['importo_totale'] += $importo;
                    
                    if ($payment_status === 'paid' || $stato === 'completed') {
                        $stats['importo_pagato'] += $importo;
                        
                        if (!isset($stats['entrate_per_giorno'][$data])) {
                            $stats['entrate_per_giorno'][$data] = 0;
                        }
                        $stats['entrate_per_giorno'][$data] += $importo;
                    } elseif ($stato === 'awaiting_payment') {
                        $stats['importo_attesa'] += $importo;
                    }
                }
                
                if (!isset($stats['richieste_per_giorno'][$data])) {
                    $stats['richieste_per_giorno'][$data] = 0;
                }
                $stats['richieste_per_giorno'][$data]++;
                
                // Stats per utente
                if ($user_id) {
                    if (!isset($user_stats[$user_id])) {
                        $user = get_userdata($user_id);
                        $user_stats[$user_id] = [
                            'nome' => $user ? $user->display_name : 'User #' . $user_id,
                            'richieste' => 0,
                            'importo_totale' => 0
                        ];
                    }
                    
                    $user_stats[$user_id]['richieste']++;
                    if ($payment_status === 'paid' || $stato === 'completed') {
                        $user_stats[$user_id]['importo_totale'] += $importo;
                    }
                }
            }
            wp_reset_postdata();
        }
        
        // Top 10 clienti
        uasort($user_stats, function($a, $b) {
            return $b['richieste'] - $a['richieste'];
        });
        $stats['top_clienti'] = array_slice($user_stats, 0, 10, true);
        
        // Lifetime values
        uasort($user_stats, function($a, $b) {
            return $b['importo_totale'] - $a['importo_totale'];
        });
        $stats['lifetime_values'] = array_slice($user_stats, 0, 10, true);
        
        return $stats;
    }
    
    /**
     * Render pagina mappature servizi multilingua
     */
    public static function render_mappature() {
        // Salva nuova mappatura
        if (isset($_POST['add_mapping']) && check_admin_referer('wecoop_add_mapping')) {
            $variante = sanitize_text_field($_POST['variante']);
            $canonico = sanitize_text_field($_POST['canonico']);
            
            if (!empty($variante) && !empty($canonico)) {
                $custom_mappings = get_option('wecoop_servizi_custom_mappings', []);
                $custom_mappings[$variante] = $canonico;
                update_option('wecoop_servizi_custom_mappings', $custom_mappings);
                
                // Aggiungi anche al normalizer runtime
                WECOOP_Servizi_Normalizer::add_mapping($variante, $canonico);
                
                echo '<div class="notice notice-success"><p>✅ Mappatura aggiunta con successo!</p></div>';
            }
        }
        
        // Elimina mappatura
        if (isset($_GET['delete_mapping']) && check_admin_referer('wecoop_delete_mapping_' . $_GET['delete_mapping'], 'nonce')) {
            $variante = sanitize_text_field($_GET['delete_mapping']);
            $custom_mappings = get_option('wecoop_servizi_custom_mappings', []);
            
            if (isset($custom_mappings[$variante])) {
                unset($custom_mappings[$variante]);
                update_option('wecoop_servizi_custom_mappings', $custom_mappings);
                echo '<div class="notice notice-success"><p>✅ Mappatura eliminata!</p></div>';
            }
        }
        
        // Carica mappature custom
        $custom_mappings = get_option('wecoop_servizi_custom_mappings', []);
        
        // Carica mappature predefinite
        $predefined_mappings = WECOOP_Servizi_Normalizer::get_map();
        
        // Raggruppa per servizio canonico
        $grouped = [];
        foreach (array_merge($predefined_mappings, $custom_mappings) as $variante => $canonico) {
            if (!isset($grouped[$canonico])) {
                $grouped[$canonico] = [];
            }
            $grouped[$canonico][] = [
                'variante' => $variante,
                'is_custom' => isset($custom_mappings[$variante])
            ];
        }
        ksort($grouped);
        
        // Ottieni servizi unici dalle richieste
        global $wpdb;
        $servizi_usati = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = 'servizio' 
            AND meta_value != ''
            ORDER BY meta_value
        ");
        
        ?>
        <div class="wrap">
            <h1>🌐 Gestione Servizi Multilingua</h1>
            <p class="description">
                Questa pagina permette di mappare servizi con lo stesso significato ma in lingue diverse (es. "Permesso di Soggiorno" e "Permiso de Residencia").
                <br>Le statistiche della dashboard raggrupperanno automaticamente i servizi mappati.
            </p>
            
            <!-- Tool Migrazione -->
            <div class="notice notice-info" style="margin: 20px 0; padding: 15px; position: relative;">
                <h3 style="margin-top: 0;">🔄 Normalizza Richieste Esistenti</h3>
                <p>
                    Hai richieste create prima dell'implementazione delle chiavi standard? 
                    Usa questo strumento per normalizzare automaticamente tutti i servizi e categorie esistenti.
                </p>
                <p>
                    <strong>Cosa fa:</strong> Analizza tutte le richieste nel database e aggiorna i campi <code>servizio</code> e <code>categoria</code> 
                    con i nomi italiani normalizzati (es. "Permiso de Residencia" → "Permesso di Soggiorno").
                </p>
                <button type="button" id="normalize-all-btn" class="button button-primary button-large">
                    🔄 Normalizza Tutte le Richieste
                </button>
                <span id="normalize-status" style="margin-left: 15px; font-weight: bold;"></span>
                
                <div id="normalize-progress" style="margin-top: 15px; display: none;">
                    <div style="background: #f0f0f0; height: 30px; border-radius: 15px; overflow: hidden; position: relative;">
                        <div id="normalize-progress-bar" style="background: linear-gradient(90deg, #2271b1, #135e96); height: 100%; width: 0%; transition: width 0.3s;"></div>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #333; font-weight: bold; font-size: 12px;" id="normalize-progress-text">0%</div>
                    </div>
                </div>
                
                <div id="normalize-results" style="margin-top: 15px; display: none;"></div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#normalize-all-btn').on('click', function() {
                    if (!confirm('Vuoi normalizzare tutte le richieste esistenti?\n\nQuesta operazione aggiornerà i campi servizio e categoria di tutte le richieste nel database.\n\nÈ sicuro e reversibile, ma potrebbe richiedere alcuni minuti.')) {
                        return;
                    }
                    
                    var $btn = $(this);
                    var $status = $('#normalize-status');
                    var $progress = $('#normalize-progress');
                    var $progressBar = $('#normalize-progress-bar');
                    var $progressText = $('#normalize-progress-text');
                    var $results = $('#normalize-results');
                    
                    $btn.prop('disabled', true).text('⏳ Normalizzazione in corso...');
                    $status.html('<span style="color: #f0b849;">⏳ Elaborazione...</span>');
                    $progress.show();
                    $results.hide().html('');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'normalize_all_servizi',
                            nonce: '<?php echo wp_create_nonce('wecoop_normalize_servizi'); ?>'
                        },
                        success: function(response) {
                            $btn.prop('disabled', false).text('🔄 Normalizza Tutte le Richieste');
                            
                            if (response.success) {
                                $progressBar.css('width', '100%');
                                $progressText.text('100%');
                                
                                setTimeout(function() {
                                    $status.html('<span style="color: #00a32a;">✅ Completato!</span>');
                                    
                                    var html = '<div class="notice notice-success" style="padding: 10px; margin: 0;">';
                                    html += '<h4 style="margin: 5px 0;">✅ Normalizzazione completata con successo!</h4>';
                                    html += '<ul style="margin: 10px 0; padding-left: 20px;">';
                                    html += '<li><strong>Richieste analizzate:</strong> ' + response.data.total + '</li>';
                                    html += '<li><strong>Richieste aggiornate:</strong> ' + response.data.updated + '</li>';
                                    html += '<li><strong>Già normalizzate:</strong> ' + response.data.skipped + '</li>';
                                    html += '</ul>';
                                    
                                    if (response.data.details && response.data.details.length > 0) {
                                        html += '<details style="margin-top: 10px;">';
                                        html += '<summary style="cursor: pointer; font-weight: bold;">📋 Dettagli modifiche</summary>';
                                        html += '<table class="widefat" style="margin-top: 10px; font-size: 12px;">';
                                        html += '<thead><tr><th>ID</th><th>Prima</th><th>Dopo</th></tr></thead><tbody>';
                                        response.data.details.forEach(function(detail) {
                                            html += '<tr>';
                                            html += '<td>' + detail.id + '</td>';
                                            html += '<td><code>' + detail.before + '</code></td>';
                                            html += '<td><code>' + detail.after + '</code></td>';
                                            html += '</tr>';
                                        });
                                        html += '</tbody></table>';
                                        html += '</details>';
                                    }
                                    
                                    html += '</div>';
                                    
                                    $results.html(html).slideDown();
                                }, 300);
                            } else {
                                $status.html('<span style="color: #d63638;">❌ Errore</span>');
                                $results.html('<div class="notice notice-error"><p>' + (response.data || 'Errore sconosciuto') + '</p></div>').slideDown();
                            }
                        },
                        error: function() {
                            $btn.prop('disabled', false).text('🔄 Normalizza Tutte le Richieste');
                            $status.html('<span style="color: #d63638;">❌ Errore di rete</span>');
                            $results.html('<div class="notice notice-error"><p>Errore di connessione. Riprova.</p></div>').slideDown();
                        }
                    });
                });
            });
            </script>
            
            <!-- Aggiungi nuova mappatura -->
            <div class="postbox" style="margin: 20px 0;">
                <div class="postbox-header">
                    <h2>➕ Aggiungi Nuova Mappatura</h2>
                </div>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('wecoop_add_mapping'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="variante">Nome Variante <span style="color: red;">*</span></label>
                                </th>
                                <td>
                                    <input type="text" name="variante" id="variante" class="regular-text" required 
                                           list="servizi-esistenti" placeholder="es. Permiso de Residencia">
                                    <datalist id="servizi-esistenti">
                                        <?php foreach ($servizi_usati as $servizio): ?>
                                            <option value="<?php echo esc_attr($servizio); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <p class="description">Il nome del servizio in un'altra lingua o variante</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="canonico">Servizio Canonico <span style="color: red;">*</span></label>
                                </th>
                                <td>
                                    <input type="text" name="canonico" id="canonico" class="regular-text" required
                                           list="servizi-canonici" placeholder="es. Permesso di Soggiorno">
                                    <datalist id="servizi-canonici">
                                        <?php foreach (WECOOP_Servizi_Normalizer::get_canonical_services() as $canonico): ?>
                                            <option value="<?php echo esc_attr($canonico); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <p class="description">Il nome "principale" del servizio (solitamente in italiano)</p>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" name="add_mapping" class="button button-primary">
                                ➕ Aggiungi Mappatura
                            </button>
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Statistiche -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">📚 Servizi Canonici</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;"><?php echo count($grouped); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Servizi principali</div>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">🌍 Varianti Totali</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;"><?php echo count($predefined_mappings) + count($custom_mappings); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Traduzioni e varianti</div>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="font-size: 14px; opacity: 0.9;">⚙️ Mappature Custom</div>
                    <div style="font-size: 36px; font-weight: bold; margin: 10px 0;"><?php echo count($custom_mappings); ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">Aggiunte manualmente</div>
                </div>
            </div>
            
            <!-- Lista mappature raggruppate -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📋 Mappature Esistenti</h2>
                </div>
                <div class="inside">
                    <?php foreach ($grouped as $canonico => $varianti): ?>
                        <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2271b1; border-radius: 4px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #2271b1;">
                                🎯 <?php echo esc_html($canonico); ?>
                                <span style="background: #2271b1; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 10px;">
                                    <?php echo count($varianti); ?> varianti
                                </span>
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php foreach ($varianti as $item): ?>
                                    <div style="background: white; padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; display: flex; align-items: center; gap: 8px;">
                                        <span><?php echo esc_html($item['variante']); ?></span>
                                        <?php if ($item['is_custom']): ?>
                                            <span style="background: #00a32a; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">CUSTOM</span>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wecoop-servizi-mappature&delete_mapping=' . urlencode($item['variante'])), 'wecoop_delete_mapping_' . $item['variante'], 'nonce'); ?>" 
                                               onclick="return confirm('Eliminare questa mappatura?');"
                                               style="color: #d63638; text-decoration: none; font-size: 16px;" 
                                               title="Elimina">❌</a>
                                        <?php else: ?>
                                            <span style="background: #999; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">PREDEFINITA</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Servizi non mappati -->
            <?php
            $servizi_non_mappati = [];
            foreach ($servizi_usati as $servizio) {
                $normalizzato = WECOOP_Servizi_Normalizer::normalize($servizio);
                if ($normalizzato === $servizio && !isset($grouped[$servizio])) {
                    $servizi_non_mappati[] = $servizio;
                }
            }
            
            if (!empty($servizi_non_mappati)):
            ?>
            <div class="postbox" style="border-left: 4px solid #ff9800;">
                <div class="postbox-header">
                    <h2>⚠️ Servizi Non Mappati (<?php echo count($servizi_non_mappati); ?>)</h2>
                </div>
                <div class="inside">
                    <p class="description">Questi servizi non hanno una mappatura. Se sono in lingue diverse, aggiungili sopra.</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;">
                        <?php foreach ($servizi_non_mappati as $servizio): ?>
                            <span style="background: #fff3cd; color: #856404; padding: 8px 12px; border-radius: 6px; border: 1px solid #ffc107;">
                                <?php echo esc_html($servizio); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * AJAX: Normalizza tutti i servizi esistenti
     */
    public static function ajax_normalize_all_servizi() {
        check_ajax_referer('wecoop_normalize_servizi', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        global $wpdb;
        
        // Ottieni tutte le richieste
        $richieste = $wpdb->get_results("
            SELECT post_id, meta_key, meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key IN ('servizio', 'categoria') 
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'richiesta_servizio'
            )
            ORDER BY post_id
        ");
        
        $stats = [
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'details' => []
        ];
        
        $processed_posts = [];
        
        foreach ($richieste as $row) {
            if (!in_array($row->post_id, $processed_posts)) {
                $processed_posts[] = $row->post_id;
                $stats['total']++;
            }
            
            $original = $row->meta_value;
            $tipo = $row->meta_key === 'servizio' ? 'servizio' : 'categoria';
            $normalized = WECOOP_Servizi_Normalizer::normalize($original, $tipo);
            
            // Aggiorna solo se diverso
            if ($normalized !== $original) {
                update_post_meta($row->post_id, $row->meta_key, $normalized);
                $stats['updated']++;
                
                // Salva dettagli (max 50 per non sovraccaricare)
                if (count($stats['details']) < 50) {
                    $stats['details'][] = [
                        'id' => $row->post_id,
                        'field' => $row->meta_key,
                        'before' => $original,
                        'after' => $normalized
                    ];
                }
            } else {
                $stats['skipped']++;
            }
        }
        
        // Log risultati
        error_log('WeCoop: Normalizzazione servizi completata - ' . json_encode($stats));
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX Bulk delete richieste
     */
    public static function ajax_bulk_delete_richieste() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $richieste_ids = isset($_POST['richieste_ids']) ? array_map('absint', $_POST['richieste_ids']) : [];
        
        if (empty($richieste_ids)) {
            wp_send_json_error('Nessuna richiesta selezionata');
        }
        
        $deleted = 0;
        $errors = [];
        
        foreach ($richieste_ids as $richiesta_id) {
            // Verifica che sia effettivamente una richiesta servizio
            if (get_post_type($richiesta_id) !== 'richiesta_servizio') {
                $errors[] = "ID $richiesta_id non è una richiesta valida";
                continue;
            }
            
            // Elimina ordine WooCommerce associato se esiste
            $order_id = get_post_meta($richiesta_id, 'wc_order_id', true);
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $order->delete(true); // true = force delete
                }
            }
            
            // Elimina la richiesta
            $result = wp_delete_post($richiesta_id, true); // true = force delete, skip trash
            
            if ($result) {
                $deleted++;
            } else {
                $errors[] = "Impossibile eliminare richiesta #$richiesta_id";
            }
        }
        
        if ($deleted > 0) {
            $message = "Eliminate $deleted richiesta/e con successo";
            if (!empty($errors)) {
                $message .= ". Errori: " . implode(', ', $errors);
            }
            wp_send_json_success($message);
        } else {
            wp_send_json_error('Nessuna richiesta eliminata. Errori: ' . implode(', ', $errors));
        }
    }
    
    /**
     * Render pagina Listino Prezzi
     */
    public static function render_listino() {
        // Ottieni normalizer per la lista dei servizi
        $servizi_standard = WECOOP_Servizi_Normalizer::get_servizi_standard();
        $categorie_standard = WECOOP_Servizi_Normalizer::get_categorie_standard();
        
        // Carica prezzi salvati
        $prezzi_servizi = get_option('wecoop_listino_servizi', []);
        $prezzi_categorie = get_option('wecoop_listino_categorie', []);
        
        ?>
        <div class="wrap">
            <h1>💰 Listino Prezzi Servizi</h1>
            
            <p class="description">
                Configura i prezzi standard per ogni servizio e categoria. Questi valori verranno utilizzati 
                come suggerimento quando crei una richiesta di pagamento.
            </p>
            
            <form id="listino-prezzi-form">
                <h2>Servizi Principali</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Servizio</th>
                            <th style="width: 30%;">Codice</th>
                            <th style="width: 20%;">Prezzo (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servizi_standard as $key => $nome): ?>
                            <tr>
                                <td><strong><?php echo esc_html($nome); ?></strong></td>
                                <td><code><?php echo esc_html($key); ?></code></td>
                                <td>
                                    <input type="number" 
                                           name="servizio[<?php echo esc_attr($key); ?>]" 
                                           value="<?php echo esc_attr($prezzi_servizi[$key] ?? ''); ?>" 
                                           step="0.01" 
                                           min="0" 
                                           placeholder="0.00"
                                           style="width: 100px;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h2 style="margin-top: 30px;">Categorie Specifiche</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Categoria</th>
                            <th style="width: 30%;">Codice</th>
                            <th style="width: 20%;">Prezzo (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorie_standard as $key => $nome): ?>
                            <tr>
                                <td><?php echo esc_html($nome); ?></td>
                                <td><code><?php echo esc_html($key); ?></code></td>
                                <td>
                                    <input type="number" 
                                           name="categoria[<?php echo esc_attr($key); ?>]" 
                                           value="<?php echo esc_attr($prezzi_categorie[$key] ?? ''); ?>" 
                                           step="0.01" 
                                           min="0" 
                                           placeholder="0.00"
                                           style="width: 100px;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        💾 Salva Listino Prezzi
                    </button>
                </p>
            </form>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0; border-radius: 4px;">
                <strong>ℹ️ Come funziona:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    <li>I prezzi qui configurati sono solo <strong>suggerimenti</strong></li>
                    <li>Quando richiedi un pagamento, il sistema cercherà prima il prezzo della categoria specifica</li>
                    <li>Se non trova la categoria, utilizzerà il prezzo del servizio principale</li>
                    <li>Puoi sempre modificare l'importo nella modale di pagamento</li>
                    <li>Lascia vuoto per non avere suggerimenti</li>
                </ul>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#listino-prezzi-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                const originalText = $btn.text();
                
                $btn.prop('disabled', true).text('💾 Salvataggio...');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'save_listino_prezzi',
                        form_data: $form.serialize(),
                        nonce: '<?php echo wp_create_nonce('wecoop_servizi_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('✅ ' + response.data);
                            $btn.prop('disabled', false).text(originalText);
                        } else {
                            alert('❌ Errore: ' + response.data);
                            $btn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert('❌ Errore di comunicazione con il server');
                        $btn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX salva listino prezzi
     */
    public static function ajax_save_listino_prezzi() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        parse_str($_POST['form_data'], $data);
        
        $servizi = [];
        $categorie = [];
        
        if (isset($data['servizio'])) {
            foreach ($data['servizio'] as $key => $prezzo) {
                if ($prezzo !== '' && floatval($prezzo) >= 0) {
                    $servizi[$key] = floatval($prezzo);
                }
            }
        }
        
        if (isset($data['categoria'])) {
            foreach ($data['categoria'] as $key => $prezzo) {
                if ($prezzo !== '' && floatval($prezzo) >= 0) {
                    $categorie[$key] = floatval($prezzo);
                }
            }
        }
        
        update_option('wecoop_listino_servizi', $servizi);
        update_option('wecoop_listino_categorie', $categorie);
        
        wp_send_json_success('Listino prezzi salvato con successo!');
    }
    
    /**
     * AJAX: Ottieni prezzo dal listino
     */
    public static function ajax_get_prezzo_listino() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $servizio = sanitize_text_field($_POST['servizio']);
        $categoria = sanitize_text_field($_POST['categoria']);
        
        $prezzi_servizi = get_option('wecoop_listino_servizi', []);
        $prezzi_categorie = get_option('wecoop_listino_categorie', []);
        
        $prezzo = null;
        
        // Cerca per servizio
        if (isset($prezzi_servizi[$servizio])) {
            $prezzo = floatval($prezzi_servizi[$servizio]);
        }
        // Altrimenti cerca per categoria
        elseif ($categoria && isset($prezzi_categorie[$categoria])) {
            $prezzo = floatval($prezzi_categorie[$categoria]);
        }
        
        if ($prezzo && $prezzo > 0) {
            wp_send_json_success(['prezzo' => $prezzo]);
        } else {
            wp_send_json_error('Prezzo non trovato nel listino');
        }
    }
    
    /**
     * Pagina impostazioni ricevute
     */
    public static function render_settings() {
        // Salva impostazioni
        if (isset($_POST['wecoop_save_settings'])) {
            check_admin_referer('wecoop_settings_nonce');
            
            update_option('wecoop_nome_associazione', sanitize_text_field($_POST['nome_associazione']));
            update_option('wecoop_rappresentante_legale', sanitize_text_field($_POST['rappresentante_legale']));
            update_option('wecoop_data_runts', sanitize_text_field($_POST['data_runts']));
            
            echo '<div class="notice notice-success"><p>✅ Impostazioni salvate con successo!</p></div>';
        }
        
        $nome_associazione = get_option('wecoop_nome_associazione', 'WeCoop APS');
        $rappresentante_legale = get_option('wecoop_rappresentante_legale', '');
        $data_runts = get_option('wecoop_data_runts', '');
        ?>
        <div class="wrap">
            <h1>⚙️ Impostazioni Ricevute PDF</h1>
            <p>Configura i dati dell'associazione che verranno utilizzati nelle ricevute per erogazioni liberali.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('wecoop_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="nome_associazione">Nome Associazione</label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="nome_associazione" 
                                   id="nome_associazione" 
                                   value="<?php echo esc_attr($nome_associazione); ?>" 
                                   class="regular-text"
                                   required>
                            <p class="description">Es: WeCoop APS, Cooperativa WeWork ETS, ecc.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="rappresentante_legale">Rappresentante Legale</label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="rappresentante_legale" 
                                   id="rappresentante_legale" 
                                   value="<?php echo esc_attr($rappresentante_legale); ?>" 
                                   class="regular-text"
                                   required>
                            <p class="description">Nome e cognome del legale rappresentante che firma le ricevute</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="data_runts">Data Iscrizione RUNTS</label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="data_runts" 
                                   id="data_runts" 
                                   value="<?php echo esc_attr($data_runts); ?>" 
                                   class="regular-text"
                                   placeholder="gg/mm/aaaa"
                                   required>
                            <p class="description">Data di iscrizione nel Registro Unico Nazionale del Terzo Settore (formato: gg/mm/aaaa)</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="wecoop_save_settings" class="button button-primary">
                        💾 Salva Impostazioni
                    </button>
                </p>
            </form>
            
            <hr>
            
            <h2>📄 Anteprima Ricevuta</h2>
            <p>Le ricevute vengono generate automaticamente dopo il pagamento e salvate in <code>/wp-content/uploads/ricevute/</code></p>
            
            <div class="postbox" style="margin-top: 20px;">
                <div class="inside" style="padding: 20px;">
                    <h3>Informazioni Ricevuta</h3>
                    <ul>
                        <li><strong>Formato:</strong> PDF (A4)</li>
                        <li><strong>Generazione:</strong> Automatica dopo pagamento completato</li>
                        <li><strong>Libreria:</strong> mPDF (fornita da Complianz GDPR)</li>
                        <li><strong>Nomenclatura:</strong> Ricevuta_{payment_id}_{anno}.pdf</li>
                        <li><strong>Conforme a:</strong> D.Lgs. 117/2017 (Codice del Terzo Settore)</li>
                    </ul>
                    
                    <h3 style="margin-top: 20px;">Detraibilità/Deducibilità</h3>
                    <ul>
                        <li><strong>Persone fisiche:</strong> Detraibile 30% fino a €30.000 o Deducibile 10% del reddito</li>
                        <li><strong>Enti/Aziende:</strong> Deducibile 10% del reddito</li>
                        <li><strong>Requisito:</strong> Pagamento tracciabile (carta, bonifico, ecc.)</li>
                        <li><strong>Esenzione:</strong> Esente da imposta di bollo (art. 82 co. 5 D.Lgs. 117/2017)</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Genera ricevuta per pagamento completato
     */
    public static function ajax_generate_receipt() {
        check_ajax_referer('wecoop_servizi_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $payment_id = intval($_POST['payment_id']);
        
        if (!$payment_id) {
            wp_send_json_error(['message' => 'ID pagamento non valido']);
        }
        
        // Verifica che il pagamento esista ed sia completato
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $payment_id
        ));
        
        if (!$payment) {
            wp_send_json_error(['message' => 'Pagamento non trovato']);
        }
        
        if (!in_array($payment->stato, ['paid', 'completed'])) {
            wp_send_json_error(['message' => 'Il pagamento non è ancora completato (stato: ' . $payment->stato . ')']);
        }
        
        // Genera la ricevuta
        error_log("[WECOOP] Inizio generazione ricevuta per payment_id: $payment_id");
        $result = WeCoop_Ricevuta_PDF::generate_ricevuta($payment_id);
        error_log("[WECOOP] Risultato generazione: " . json_encode($result));
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Ricevuta generata con successo',
                'receipt_url' => $result['receipt_url']
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * AJAX: Invia/Genera Documento Unico per firma digitale (v1.1 PDF)
     */
    public static function ajax_send_documento_unico() {
        try {
            error_log('🔐 FIRMA: Inizio ajax_send_documento_unico');
            
            check_ajax_referer('wecoop_servizi_nonce', 'nonce');
            error_log('✅ FIRMA: Nonce verificato');
            
            if (!current_user_can('manage_options')) {
                error_log('❌ FIRMA: Permessi insufficienti');
                wp_send_json_error('Permessi insufficienti');
            }
            error_log('✅ FIRMA: Permessi verificati');
            
            // Ottieni richiesta_id
            $richiesta_id = absint($_POST['richiesta_id'] ?? 0);
            error_log("📊 FIRMA: Richiesta ID: $richiesta_id");
            
            if (!$richiesta_id) {
                error_log('❌ FIRMA: Richiesta ID non valido');
                wp_send_json_error('Richiesta ID non specificato');
            }
            
            // Verifica che la richiesta esista e sia pagata
            $richiesta = get_post($richiesta_id);
            if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
                error_log("❌ FIRMA: Richiesta non trovata o tipo non valido");
                wp_send_json_error('Richiesta non trovata');
            }
            error_log("✅ FIRMA: Richiesta trovata: {$richiesta->post_title}");
            
            // Verifica che sia pagata
            $payment_status = get_post_meta($richiesta_id, 'payment_status', true);
            $stato = get_post_meta($richiesta_id, 'stato', true);
            
            error_log("📋 FIRMA: Payment status: $payment_status, Stato: $stato");
            
            if ($payment_status !== 'paid' && $stato !== 'completed') {
                error_log("❌ FIRMA: Richiesta non pagata (payment_status: $payment_status, stato: $stato)");
                wp_send_json_error('La richiesta non è stata ancora pagata. Stato: ' . $payment_status);
            }
            error_log('✅ FIRMA: Richiesta pagata');
            
            // Verifica che la classe PDF esista
            if (!class_exists('WECOOP_Documento_Unico_PDF')) {
                error_log('❌ FIRMA: Classe WECOOP_Documento_Unico_PDF non trovata');
                wp_send_json_error('Classe generazione PDF non disponibile. Contatta l\'amministratore.');
            }
            error_log('✅ FIRMA: Classe WECOOP_Documento_Unico_PDF disponibile');
            
            // Ottieni user_id
            $user_id = get_post_meta($richiesta_id, 'user_id', true);
            error_log("📄 FIRMA: User ID: $user_id");
            
            if (!$user_id) {
                error_log('❌ FIRMA: User ID non trovato');
                wp_send_json_error('User ID non trovato nella richiesta');
            }
            
            // Genera documento PDF
            error_log('🔨 FIRMA: Inizio generazione PDF...');
            $result = WECOOP_Documento_Unico_PDF::generate_documento_unico($richiesta_id, $user_id);
            
            error_log('📊 FIRMA: Risultato generazione PDF: ' . json_encode([
                'success' => isset($result['success']) ? $result['success'] : false,
                'has_doc' => isset($result['documento']),
                'url' => $result['documento']['url'] ?? 'N/A',
                'hash' => isset($result['documento']['hash_sha256']) ? substr($result['documento']['hash_sha256'], 0, 16) . '...' : 'N/A'
            ]));
            
            if (!isset($result['success']) || !$result['success']) {
                error_log('❌ FIRMA: Generazione PDF fallita: ' . json_encode($result));
                wp_send_json_error('Errore durante la generazione del PDF: ' . ($result['message'] ?? 'Errore sconosciuto'));
            }
            
            // Estrai dati dal nidificato
            $doc_data = $result['documento'] ?? [];
            $url = $doc_data['url'] ?? '';
            $hash = $doc_data['hash_sha256'] ?? '';
            $contenuto = $doc_data['contenuto_testo'] ?? '';
            $nome = $doc_data['nome'] ?? 'documento_unico.pdf';
            
            error_log('✅ FIRMA: PDF generato con successo');
            error_log('📄 FIRMA: URL PDF: ' . $url);
            error_log('🔐 FIRMA: Hash SHA-256: ' . substr($hash, 0, 32) . '...');
            
            // Aggiorna metadata della richiesta
            update_post_meta($richiesta_id, 'documento_unico_generato', 'yes');
            update_post_meta($richiesta_id, 'documento_unico_url', $url);
            update_post_meta($richiesta_id, 'documento_unico_hash', $hash);
            update_post_meta($richiesta_id, 'documento_unico_generato_il', current_time('mysql'));
            
            error_log('✅ FIRMA: Metadata salvati');
            
            // Risposta di successo
            wp_send_json_success([
                'message' => 'Documento generato con successo',
                'documento' => [
                    'url' => $url,
                    'contenuto_testo' => $contenuto,
                    'hash_sha256' => $hash,
                    'nome' => $nome
                ]
            ]);
            
        } catch (Throwable $e) {
            error_log('❌ FIRMA: ERRORE FATALE in ajax_send_documento_unico');
            error_log('❌ FIRMA: Messaggio: ' . $e->getMessage());
            error_log('❌ FIRMA: File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('❌ FIRMA: Stack trace: ' . $e->getTraceAsString());
            
            wp_send_json_error('Errore interno: ' . $e->getMessage());
        }
    }
}
