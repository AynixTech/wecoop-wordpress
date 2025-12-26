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
    public static function render_dashboard() {
        $stats = self::get_statistics();
        ?>
        <div class="wrap">
            <h1>Dashboard Richieste Servizi</h1>
            
            <div class="wecoop-stats-grid">
                <div class="wecoop-stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">In Attesa</div>
                    </div>
                </div>
                
                <div class="wecoop-stat-card processing">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['processing']; ?></div>
                        <div class="stat-label">In Lavorazione</div>
                    </div>
                </div>
                
                <div class="wecoop-stat-card completed">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['completed']; ?></div>
                        <div class="stat-label">Completate</div>
                    </div>
                </div>
                
                <div class="wecoop-stat-card total">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Totale</div>
                    </div>
                </div>
            </div>
            
            <h2>Ultime Richieste</h2>
            <?php self::render_recent_requests(); ?>
        </div>
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
