<?php
/**
 * Admin UI: Push Notifications Management
 * 
 * Interfaccia amministrativa con 4 tabs:
 * - Send: Invio push immediate
 * - Schedule: Programmazione push
 * - Logs: Storico invii con paginazione
 * - Settings: Configurazione FCM (Server Key + Service Account JSON)
 * 
 * @package WECOOP_Notifications
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Push_Notifications_Admin {
    
    /**
     * Inizializza hooks
     */
    public static function init() {
        add_action('admin_post_wecoop_send_push', [__CLASS__, 'handle_send_push']);
        add_action('admin_post_wecoop_schedule_push', [__CLASS__, 'handle_schedule_push']);
        add_action('admin_post_wecoop_save_fcm_settings', [__CLASS__, 'handle_save_settings']);
        add_action('wp_ajax_wecoop_delete_push_log', [__CLASS__, 'ajax_delete_log']);
        add_action('wp_ajax_wecoop_test_fcm_connection', [__CLASS__, 'ajax_test_fcm']);
    }
    
    /**
     * Render pagina amministrativa principale
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }
        
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'send';
        
        ?>
        <div class="wrap">
            <h1>
                <span class="dashicons dashicons-megaphone" style="font-size: 32px; margin-right: 10px;"></span>
                Push Notifications
            </h1>
            
            <?php $this->render_notices(); ?>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=wecoop-push-notifications&tab=send" class="nav-tab <?php echo $active_tab === 'send' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-email-alt"></span> Invia
                </a>
                <a href="?page=wecoop-push-notifications&tab=schedule" class="nav-tab <?php echo $active_tab === 'schedule' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-clock"></span> Programma
                </a>
                <a href="?page=wecoop-push-notifications&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-list-view"></span> Log
                </a>
                <a href="?page=wecoop-push-notifications&tab=debug" class="nav-tab <?php echo $active_tab === 'debug' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-media-code"></span> Debug
                </a>
                <a href="?page=wecoop-push-notifications&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span> Impostazioni
                </a>
            </h2>
            
            <div class="tab-content" style="margin-top: 20px;">
                <?php
                switch ($active_tab) {
                    case 'send':
                        $this->render_send_tab();
                        break;
                    case 'schedule':
                        $this->render_schedule_tab();
                        break;
                    case 'logs':
                        $this->render_logs_tab();
                        break;
                    case 'debug':
                        $this->render_debug_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                }
                ?>
            </div>
        </div>
        
        <style>
            .wecoop-push-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .wecoop-push-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
            .form-table th {
                width: 200px;
            }
            .wecoop-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .wecoop-stat-card {
                background: #f0f6fc;
                border-left: 4px solid #2271b1;
                padding: 15px;
                border-radius: 4px;
            }
            .wecoop-stat-card h3 {
                margin: 0 0 5px 0;
                font-size: 28px;
                color: #2271b1;
            }
            .wecoop-stat-card p {
                margin: 0;
                color: #646970;
                font-size: 13px;
            }
            .wecoop-log-table {
                width: 100%;
                border-collapse: collapse;
            }
            .wecoop-log-table th,
            .wecoop-log-table td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            .wecoop-log-table th {
                background: #f6f7f7;
                font-weight: 600;
            }
            .status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .status-sent { background: #d4edda; color: #155724; }
            .status-failed { background: #f8d7da; color: #721c24; }
            .status-scheduled { background: #d1ecf1; color: #0c5460; }
            .status-pending { background: #fff3cd; color: #856404; }
        </style>
        <?php
    }
    
    /**
     * Render notifiche admin
     */
    private function render_notices() {
        if (isset($_GET['success'])) {
            $message = sanitize_text_field($_GET['message'] ?? 'Operazione completata con successo');
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        
        if (isset($_GET['error'])) {
            $message = sanitize_text_field($_GET['message'] ?? 'Si è verificato un errore');
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }
    
    /**
     * Tab: Invia Push Immediata
     */
    private function render_send_tab() {
        ?>
        <div class="wecoop-push-card">
            <h2>Invia Notifica Push</h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('wecoop_send_push', 'wecoop_push_nonce'); ?>
                <input type="hidden" name="action" value="wecoop_send_push">
                
                <table class="form-table">
                    <tr>
                        <th><label for="recipient_type">Destinatari *</label></th>
                        <td>
                            <select name="recipient_type" id="recipient_type" class="regular-text" required>
                                <option value="all">Tutti gli utenti</option>
                                <option value="role">Per ruolo</option>
                                <option value="single">Utente singolo</option>
                            </select>
                            
                            <div id="role_select" style="display: none; margin-top: 10px;">
                                <select name="recipient_role" class="regular-text">
                                    <option value="">-- Seleziona ruolo --</option>
                                    <?php
                                    $roles = wp_roles()->get_names();
                                    foreach ($roles as $role_key => $role_name) {
                                        echo '<option value="' . esc_attr($role_key) . '">' . esc_html($role_name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div id="user_select" style="display: none; margin-top: 10px;">
                                <select name="recipient_user" class="regular-text">
                                    <option value="">-- Seleziona utente --</option>
                                    <?php
                                    $users = get_users(['number' => 200]);
                                    foreach ($users as $user) {
                                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (ID: ' . esc_html($user->ID) . ' - ' . esc_html($user->user_email) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_title">Titolo *</label></th>
                        <td>
                            <input type="text" name="push_title" id="push_title" class="regular-text" maxlength="100" required>
                            <p class="description">Massimo 100 caratteri</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_body">Messaggio *</label></th>
                        <td>
                            <textarea name="push_body" id="push_body" rows="4" class="large-text" maxlength="255" required></textarea>
                            <p class="description">Massimo 255 caratteri</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_url">URL di destinazione</label></th>
                        <td>
                            <input type="url" name="push_url" id="push_url" class="regular-text" placeholder="https://">
                            <p class="description">Opzionale: URL da aprire al tap della notifica</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_icon">Icona</label></th>
                        <td>
                            <select name="push_icon" id="push_icon">
                                <option value="">Default</option>
                                <option value="info">ℹ️ Info</option>
                                <option value="warning">⚠️ Avviso</option>
                                <option value="success">✅ Successo</option>
                                <option value="event">📅 Evento</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Invia Notifica', 'primary', 'submit', false); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#recipient_type').on('change', function() {
                const type = $(this).val();
                $('#role_select, #user_select').hide();
                
                if (type === 'role') {
                    $('#role_select').show();
                } else if (type === 'single') {
                    $('#user_select').show();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Tab: Programma Push
     */
    private function render_schedule_tab() {
        ?>
        <div class="wecoop-push-card">
            <h2>Programma Notifica Push</h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('wecoop_schedule_push', 'wecoop_push_nonce'); ?>
                <input type="hidden" name="action" value="wecoop_schedule_push">
                
                <table class="form-table">
                    <tr>
                        <th><label for="schedule_date">Data e Ora *</label></th>
                        <td>
                            <input type="datetime-local" name="schedule_datetime" id="schedule_date" class="regular-text" required>
                            <p class="description">Seleziona quando inviare la notifica</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="recipient_type_schedule">Destinatari *</label></th>
                        <td>
                            <select name="recipient_type" id="recipient_type_schedule" class="regular-text" required>
                                <option value="all">Tutti gli utenti</option>
                                <option value="role">Per ruolo</option>
                            </select>
                            
                            <div id="role_select_schedule" style="display: none; margin-top: 10px;">
                                <select name="recipient_role" class="regular-text">
                                    <option value="">-- Seleziona ruolo --</option>
                                    <?php
                                    $roles = wp_roles()->get_names();
                                    foreach ($roles as $role_key => $role_name) {
                                        echo '<option value="' . esc_attr($role_key) . '">' . esc_html($role_name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_title_schedule">Titolo *</label></th>
                        <td>
                            <input type="text" name="push_title" id="push_title_schedule" class="regular-text" maxlength="100" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="push_body_schedule">Messaggio *</label></th>
                        <td>
                            <textarea name="push_body" id="push_body_schedule" rows="4" class="large-text" maxlength="255" required></textarea>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Programma Notifica', 'primary', 'submit', false); ?>
            </form>
        </div>
        
        <?php $this->render_scheduled_notifications(); ?>
        
        <script>
        jQuery(document).ready(function($) {
            $('#recipient_type_schedule').on('change', function() {
                if ($(this).val() === 'role') {
                    $('#role_select_schedule').show();
                } else {
                    $('#role_select_schedule').hide();
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render lista notifiche programmate
     */
    private function render_scheduled_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_push_logs';
        
        $scheduled = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE status = 'scheduled' ORDER BY scheduled_for ASC LIMIT 20"
        );
        
        if (empty($scheduled)) {
            return;
        }
        
        ?>
        <div class="wecoop-push-card">
            <h2>Notifiche Programmate</h2>
            
            <table class="wecoop-log-table">
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Destinatari</th>
                        <th>Programmata per</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduled as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->title); ?></td>
                        <td><?php echo esc_html($item->recipient_type); ?></td>
                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($item->scheduled_for))); ?></td>
                        <td>
                            <button type="button" class="button button-small wecoop-delete-scheduled" data-id="<?php echo esc_attr($item->id); ?>">
                                Annulla
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Tab: Log Notifiche
     */
    private function render_logs_tab() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_push_logs';
        
        // Paginazione
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Filtri
        $status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : 'all';
        
        $where = '1=1';
        if ($status_filter !== 'all') {
            $where .= $wpdb->prepare(' AND status = %s', $status_filter);
        }
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where}");
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
        
        // Stats
        $stats = [
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}"),
            'sent' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'sent'"),
            'failed' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'failed'"),
            'scheduled' => $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'scheduled'"),
            'users_with_token' => $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = 'fcm_token'")
        ];
        
        ?>
        <div class="wecoop-stats-grid">
            <div class="wecoop-stat-card">
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p>Totale Invii</p>
            </div>
            <div class="wecoop-stat-card">
                <h3><?php echo number_format($stats['sent']); ?></h3>
                <p>Inviate con Successo</p>
            </div>
            <div class="wecoop-stat-card">
                <h3><?php echo number_format($stats['failed']); ?></h3>
                <p>Fallite</p>
            </div>
            <div class="wecoop-stat-card">
                <h3><?php echo number_format($stats['scheduled']); ?></h3>
                <p>Programmate</p>
            </div>
            <div class="wecoop-stat-card">
                <h3><?php echo number_format($stats['users_with_token']); ?></h3>
                <p>Utenti con Token</p>
            </div>
        </div>
        
        <div class="wecoop-push-card">
            <h2>Storico Notifiche</h2>
            
            <div style="margin-bottom: 15px;">
                <form method="get">
                    <input type="hidden" name="page" value="wecoop-push-notifications">
                    <input type="hidden" name="tab" value="logs">
                    
                    <select name="status_filter" onchange="this.form.submit()">
                        <option value="all" <?php selected($status_filter, 'all'); ?>>Tutti gli stati</option>
                        <option value="sent" <?php selected($status_filter, 'sent'); ?>>Inviate</option>
                        <option value="failed" <?php selected($status_filter, 'failed'); ?>>Fallite</option>
                        <option value="scheduled" <?php selected($status_filter, 'scheduled'); ?>>Programmate</option>
                        <option value="pending" <?php selected($status_filter, 'pending'); ?>>In Attesa</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($logs)): ?>
                <p>Nessun log trovato.</p>
            <?php else: ?>
                <table class="wecoop-log-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titolo</th>
                            <th>Destinatari</th>
                            <th>Stato</th>
                            <th>Data</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->id); ?></td>
                            <td>
                                <strong><?php echo esc_html($log->title); ?></strong><br>
                                <small><?php echo esc_html(wp_trim_words($log->body, 10)); ?></small>
                            </td>
                            <td><?php echo esc_html($log->recipient_type); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                                    <?php echo esc_html($log->status); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                if ($log->sent_at) {
                                    echo esc_html(date('d/m/Y H:i', strtotime($log->sent_at)));
                                } elseif ($log->scheduled_for) {
                                    echo '📅 ' . esc_html(date('d/m/Y H:i', strtotime($log->scheduled_for)));
                                } else {
                                    echo esc_html(date('d/m/Y H:i', strtotime($log->created_at)));
                                }
                                ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small wecoop-delete-log" data-id="<?php echo esc_attr($log->id); ?>">
                                    Elimina
                                </button>
                                <?php if ($log->error_message): ?>
                                <button type="button" class="button button-small" onclick="alert('<?php echo esc_js($log->error_message); ?>')">
                                    Errore
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                // Paginazione
                $total_pages = ceil($total / $per_page);
                if ($total_pages > 1) {
                    echo '<div class="tablenav"><div class="tablenav-pages">';
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => $current_page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;'
                    ]);
                    echo '</div></div>';
                }
                ?>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.wecoop-delete-log, .wecoop-delete-scheduled').on('click', function() {
                if (!confirm('Sei sicuro di voler eliminare questo log?')) {
                    return;
                }
                
                const logId = $(this).data('id');
                const $row = $(this).closest('tr');
                
                $.ajax({
                    url: wecoopNotifications.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wecoop_delete_push_log',
                        nonce: wecoopNotifications.nonce,
                        log_id: logId
                    },
                    success: function(response) {
                        if (response.success) {
                            $row.fadeOut(300, function() { $(this).remove(); });
                        } else {
                            alert('Errore durante l\'eliminazione');
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Tab: Debug
     */
    private function render_debug_tab() {
        global $wpdb;
        
        $table_tokens = $wpdb->prefix . 'wecoop_push_tokens';
        $table_logs = $wpdb->prefix . 'wecoop_push_logs';
        
        // Statistiche
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        $users_with_tokens = $wpdb->get_var("SELECT COUNT(*) FROM {$table_tokens}");
        $failed_logs = $wpdb->get_results("
            SELECT * FROM {$table_logs} 
            WHERE status = 'failed' 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        // Configurazione FCM
        $service_account_json = get_option('wecoop_fcm_service_account_json', '');
        $service_account = json_decode($service_account_json, true);
        
        ?>
        <div class="wecoop-push-card">
            <h2>🔍 Informazioni Debug</h2>
            
            <h3>📊 Statistiche</h3>
            <table class="widefat">
                <tr>
                    <th style="width: 300px;">Totale Utenti Registrati</th>
                    <td><?php echo number_format($total_users); ?></td>
                </tr>
                <tr>
                    <th>Utenti con Token FCM</th>
                    <td>
                        <?php echo number_format($users_with_tokens); ?>
                        <?php if ($users_with_tokens == 0): ?>
                            <span style="color: #d63638;">⚠️ Nessun utente ha token FCM salvato!</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Copertura App</th>
                    <td>
                        <?php 
                        $percentage = $total_users > 0 ? ($users_with_tokens / $total_users * 100) : 0;
                        echo number_format($percentage, 1) . '%';
                        ?>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px;">⚙️ Configurazione FCM</h3>
            <table class="widefat">
                <tr>
                    <th style="width: 300px;">Service Account Configurato</th>
                    <td>
                        <?php if (!empty($service_account)): ?>
                            ✅ Sì
                        <?php else: ?>
                            ❌ No - <a href="?page=wecoop-push-notifications&tab=settings">Configura ora</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($service_account)): ?>
                <tr>
                    <th>Project ID</th>
                    <td><code><?php echo esc_html($service_account['project_id'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <th>Client Email</th>
                    <td><code><?php echo esc_html($service_account['client_email'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <th>Private Key</th>
                    <td>
                        <?php if (isset($service_account['private_key'])): ?>
                            ✅ Presente (<?php echo strlen($service_account['private_key']); ?> caratteri)
                        <?php else: ?>
                            ❌ Mancante
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
            
            <h3 style="margin-top: 30px;">❌ Ultimi Errori</h3>
            <?php if (empty($failed_logs)): ?>
                <p style="color: #46b450;">✅ Nessun errore recente</p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Titolo</th>
                            <th>User ID</th>
                            <th>Errore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failed_logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->created_at); ?></td>
                            <td><?php echo esc_html($log->title); ?></td>
                            <td><?php echo esc_html($log->user_id ?: 'N/A'); ?></td>
                            <td style="color: #d63638;">
                                <code><?php echo esc_html($log->error_message ?: 'Errore sconosciuto'); ?></code>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <h3 style="margin-top: 30px;">🔑 Ultimi Token Registrati</h3>
            <?php
            $recent_tokens = $wpdb->get_results("
                SELECT 
                    t.*,
                    u.user_login,
                    u.user_email
                FROM {$table_tokens} t
                LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
                ORDER BY t.updated_at DESC
                LIMIT 5
            ");
            ?>
            
            <?php if (empty($recent_tokens)): ?>
                <p style="color: #d63638;">
                    ⚠️ Nessun token FCM trovato!<br><br>
                    <strong>Possibili cause:</strong><br>
                    1. L'app Flutter non sta chiamando <code>POST /wp-json/push/v1/token</code> dopo il login<br>
                    2. Endpoint API non funzionante - <a href="<?php echo home_url('/wp-json/push/v1/'); ?>" target="_blank">Testa API</a><br>
                    3. JWT token non valido nell'app
                </p>
            <?php else: ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Utente</th>
                            <th>Email</th>
                            <th>Device</th>
                            <th>Token (primi 30 char)</th>
                            <th>Ultimo Aggiornamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_tokens as $token): ?>
                        <tr>
                            <td><?php echo esc_html($token->user_login); ?></td>
                            <td><?php echo esc_html($token->user_email); ?></td>
                            <td><?php echo esc_html($token->device_info ?: 'N/A'); ?></td>
                            <td><code><?php echo esc_html(substr($token->token, 0, 30)) . '...'; ?></code></td>
                            <td><?php echo esc_html($token->updated_at); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <h3 style="margin-top: 30px;">🧪 Test Rapido</h3>
            <p>
                <a href="?page=wecoop-push-notifications&tab=settings" class="button">
                    Testa Connessione FCM
                </a>
                
                <a href="<?php echo home_url('/wp-json/push/v1/'); ?>" class="button" target="_blank">
                    Verifica Endpoint API
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Tab: Impostazioni FCM
     */
    private function render_settings_tab() {
        $service_account = get_option('wecoop_fcm_service_account_json', '');
        
        ?>
        <div class="wecoop-push-card">
            <h2>Configurazione Firebase Cloud Messaging (FCM v1 API)</h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('wecoop_save_fcm_settings', 'wecoop_settings_nonce'); ?>
                <input type="hidden" name="action" value="wecoop_save_fcm_settings">
                
                <table class="form-table">
                    <tr>
                        <th><label for="fcm_service_account">Service Account JSON *</label></th>
                        <td>
                            <textarea name="fcm_service_account_json" id="fcm_service_account" rows="10" class="large-text code" placeholder='{"type": "service_account", ...}' required><?php echo esc_textarea($service_account); ?></textarea>
                            <p class="description">
                                <strong>⚠️ Nota:</strong> L'API Legacy (Server Key) è stata deprecata da Google.<br>
                                <strong>Come ottenere il Service Account JSON:</strong><br>
                                1. Vai su <a href="https://console.firebase.google.com" target="_blank">Firebase Console</a> → Seleziona progetto<br>
                                2. Clicca ingranaggio ⚙️ → <strong>Project Settings</strong> → Tab <strong>Service Accounts</strong><br>
                                3. Clicca <strong>"Generate new private key"</strong> → Conferma<br>
                                4. Apri il file <code>.json</code> scaricato e copia <strong>tutto</strong> il contenuto qui sopra<br>
                                5. Salva le impostazioni
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Salva Impostazioni'); ?>
            </form>
        </div>
        
        <div class="wecoop-push-card">
            <h2>Test Connessione FCM</h2>
            <p>Verifica che la configurazione FCM sia corretta.</p>
            
            <button type="button" id="test-fcm-btn" class="button">
                Testa Connessione
            </button>
            
            <div id="test-fcm-result" style="margin-top: 15px;"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-fcm-btn').on('click', function() {
                const $btn = $(this);
                const $result = $('#test-fcm-result');
                
                $btn.prop('disabled', true).text('Test in corso...');
                $result.html('<p>⏳ Verifica configurazione FCM...</p>');
                
                $.ajax({
                    url: wecoopNotifications.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wecoop_test_fcm_connection',
                        nonce: wecoopNotifications.nonce
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).text('Testa Connessione');
                        
                        if (response.success) {
                            $result.html('<div class="notice notice-success inline"><p>✅ ' + response.data.message + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error inline"><p>❌ ' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).text('Testa Connessione');
                        $result.html('<div class="notice notice-error inline"><p>❌ Errore di connessione</p></div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handler: Invia push immediata
     */
    public static function handle_send_push() {
        if (!current_user_can('manage_options')) {
            wp_die('Non hai i permessi necessari');
        }
        
        if (!isset($_POST['wecoop_push_nonce']) || !wp_verify_nonce($_POST['wecoop_push_nonce'], 'wecoop_send_push')) {
            wp_die('Verifica di sicurezza fallita');
        }
        
        $recipient_type = sanitize_text_field($_POST['recipient_type']);
        $title = sanitize_text_field($_POST['push_title']);
        $body = sanitize_textarea_field($_POST['push_body']);
        $url = sanitize_url($_POST['push_url'] ?? '');
        $icon = sanitize_text_field($_POST['push_icon'] ?? '');
        
        $data = [];
        if (!empty($url)) {
            $data['url'] = $url;
        }
        if (!empty($icon)) {
            $data['icon'] = $icon;
        }
        
        // Determina destinatari
        $user_ids = [];
        $recipient_value = '';
        
        switch ($recipient_type) {
            case 'all':
                $user_ids = get_users(['fields' => 'ID']);
                break;
                
            case 'role':
                $role = sanitize_text_field($_POST['recipient_role']);
                $user_ids = get_users(['role' => $role, 'fields' => 'ID']);
                $recipient_value = $role;
                break;
                
            case 'single':
                $user_id = intval($_POST['recipient_user']);
                $user_ids = [$user_id];
                $recipient_value = $user_id;
                break;
        }
        
        if (empty($user_ids)) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'send',
                'error' => 1,
                'message' => 'Nessun destinatario trovato'
            ], admin_url('admin.php')));
            exit;
        }
        
        // Invia notifica
        $result = WECOOP_Push_Integrations::send_push_notification($user_ids, $title, $body, $data);
        
        // Prepara messaggio dettagliato
        $message = sprintf(
            'Inviate %d notifiche, %d fallite',
            $result['sent_count'] ?? 0,
            $result['failed_count'] ?? 0
        );
        
        // Aggiungi dettagli errori se presenti
        if (!empty($result['errors'])) {
            $message .= ' | Errori: ' . implode('; ', array_slice($result['errors'], 0, 3));
        }
        
        if ($result['success']) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'send',
                'success' => 1,
                'message' => $message
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'send',
                'error' => 1,
                'message' => $message
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    /**
     * Handler: Programma push
     */
    public static function handle_schedule_push() {
        if (!current_user_can('manage_options')) {
            wp_die('Non hai i permessi necessari');
        }
        
        if (!isset($_POST['wecoop_push_nonce']) || !wp_verify_nonce($_POST['wecoop_push_nonce'], 'wecoop_schedule_push')) {
            wp_die('Verifica di sicurezza fallita');
        }
        
        $datetime = sanitize_text_field($_POST['schedule_datetime']);
        $recipient_type = sanitize_text_field($_POST['recipient_type']);
        $title = sanitize_text_field($_POST['push_title']);
        $body = sanitize_textarea_field($_POST['push_body']);
        
        $scheduled_time = strtotime($datetime);
        
        if ($scheduled_time <= time()) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'schedule',
                'error' => 1,
                'message' => 'La data deve essere futura'
            ], admin_url('admin.php')));
            exit;
        }
        
        // Determina destinatari
        $user_ids = [];
        if ($recipient_type === 'all') {
            $user_ids = get_users(['fields' => 'ID']);
        } else {
            $role = sanitize_text_field($_POST['recipient_role']);
            $user_ids = get_users(['role' => $role, 'fields' => 'ID']);
        }
        
        // Schedula evento
        $args = [
            'user_ids' => $user_ids,
            'title' => $title,
            'body' => $body,
            'data' => []
        ];
        
        wp_schedule_single_event($scheduled_time, 'wecoop_send_scheduled_push', [$args]);
        
        // Log
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'wecoop_push_logs', [
            'recipient_type' => $recipient_type,
            'title' => $title,
            'body' => $body,
            'status' => 'scheduled',
            'scheduled_for' => date('Y-m-d H:i:s', $scheduled_time),
            'created_at' => current_time('mysql')
        ]);
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-push-notifications',
            'tab' => 'schedule',
            'success' => 1,
            'message' => 'Notifica programmata con successo'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Handler: Salva impostazioni FCM
     */
    public static function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Non hai i permessi necessari');
        }
        
        if (!isset($_POST['wecoop_settings_nonce']) || !wp_verify_nonce($_POST['wecoop_settings_nonce'], 'wecoop_save_fcm_settings')) {
            wp_die('Verifica di sicurezza fallita');
        }
        
        $service_account = wp_unslash($_POST['fcm_service_account_json']);
        
        // Valida JSON
        if (empty($service_account)) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'settings',
                'error' => 1,
                'message' => 'Service Account JSON è obbligatorio'
            ], admin_url('admin.php')));
            exit;
        }
        
        $decoded = json_decode($service_account, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'settings',
                'error' => 1,
                'message' => 'Service Account JSON non valido: ' . json_last_error_msg()
            ], admin_url('admin.php')));
            exit;
        }
        
        // Verifica campi obbligatori
        if (!isset($decoded['project_id'], $decoded['private_key'], $decoded['client_email'])) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-push-notifications',
                'tab' => 'settings',
                'error' => 1,
                'message' => 'Service Account JSON incompleto. Verifica che contenga project_id, private_key e client_email'
            ], admin_url('admin.php')));
            exit;
        }
        
        update_option('wecoop_fcm_service_account_json', $service_account);
        delete_option('wecoop_fcm_server_key'); // Rimuovi vecchia configurazione
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-push-notifications',
            'tab' => 'settings',
            'success' => 1,
            'message' => 'Impostazioni salvate con successo (Project: ' . $decoded['project_id'] . ')'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * AJAX: Elimina log
     */
    public static function ajax_delete_log() {
        check_ajax_referer('wecoop_push_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $log_id = intval($_POST['log_id']);
        
        global $wpdb;
        $deleted = $wpdb->delete($wpdb->prefix . 'wecoop_push_logs', ['id' => $log_id], ['%d']);
        
        if ($deleted) {
            wp_send_json_success(['message' => 'Log eliminato']);
        } else {
            wp_send_json_error(['message' => 'Errore eliminazione']);
        }
    }
    
    /**
     * AJAX: Test connessione FCM
     */
    public static function ajax_test_fcm() {
        check_ajax_referer('wecoop_push_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Non hai i permessi necessari']);
        }
        
        $service_account_json = get_option('wecoop_fcm_service_account_json', '');
        
        if (empty($service_account_json)) {
            wp_send_json_error(['message' => 'Nessuna configurazione FCM trovata. Salva prima il Service Account JSON.']);
        }
        
        $service_account = json_decode($service_account_json, true);
        
        if (!$service_account || !isset($service_account['project_id'], $service_account['client_email'], $service_account['private_key'])) {
            wp_send_json_error(['message' => 'Service Account JSON non valido. Verifica che contenga project_id, client_email e private_key.']);
        }
        
        // Testa OAuth2
        $test_result = self::test_fcm_oauth($service_account);
        
        if ($test_result['success']) {
            wp_send_json_success(['message' => '✅ Service Account configurato correttamente (Project: ' . $service_account['project_id'] . ')']);
        } else {
            wp_send_json_error(['message' => 'Errore Service Account: ' . $test_result['error']]);
        }
    }
    
    /**
     * Test OAuth2 FCM v1
     */
    private static function test_fcm_oauth($service_account) {
        // Crea JWT
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'iss' => $service_account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];
        
        // Encode header e payload
        $header_encoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payload_encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        
        $signature_input = $header_encoded . '.' . $payload_encoded;
        
        // Firma con private key
        if (!openssl_sign($signature_input, $signature, $service_account['private_key'], OPENSSL_ALGO_SHA256)) {
            return ['success' => false, 'error' => 'Impossibile firmare JWT con la private key'];
        }
        
        $signature_encoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwt = $signature_input . '.' . $signature_encoded;
        
        // Richiedi access token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ],
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['access_token'])) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => $body['error_description'] ?? 'Token non ricevuto'];
    }
    
    /**
     * Test Legacy API
     */
    private static function test_fcm_legacy($server_key) {
        // Invia richiesta di test (senza token reale, solo per verificare auth)
        $response = wp_remote_post('https://fcm.googleapis.com/fcm/send', [
            'headers' => [
                'Authorization' => 'key=' . $server_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'to' => 'test_token',
                'notification' => [
                    'title' => 'Test',
                    'body' => 'Test'
                ]
            ]),
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        // 200 = ok ma token non valido (normale per test)
        // 401 = auth failed
        if ($code === 200) {
            return ['success' => true];
        }
        
        if ($code === 401) {
            return ['success' => false, 'error' => 'Server Key non valida'];
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return ['success' => false, 'error' => $body['error'] ?? "HTTP $code"];
    }
}
