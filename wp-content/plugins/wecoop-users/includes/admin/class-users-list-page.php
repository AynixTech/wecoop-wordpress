<?php
/**
 * Pagina Lista Utenti Registrati
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Users_List_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_wecoop_bulk_approve_soci', [$this, 'handle_bulk_approve']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Utenti Registrati',
            'Utenti Registrati',
            'manage_options',
            'wecoop-users-list',
            [$this, 'render_page'],
            'dashicons-groups',
            26
        );
    }
    
    public function handle_bulk_approve() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_bulk_approve');
        
        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : [];
        $approved_count = 0;
        
        foreach ($user_ids as $user_id) {
            // Approva come socio
            update_user_meta($user_id, 'is_socio', true);
            
            // Trova richiesta_socio
            $richiesta = get_posts([
                'post_type' => 'richiesta_socio',
                'meta_key' => 'user_id_socio',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ]);
            
            if (!empty($richiesta)) {
                update_post_meta($richiesta[0]->ID, 'is_socio', true);
                update_post_meta($richiesta[0]->ID, 'data_approvazione', current_time('mysql'));
                wp_update_post([
                    'ID' => $richiesta[0]->ID,
                    'post_status' => 'publish'
                ]);
            }
            
            // Cambia ruolo
            $user = new WP_User($user_id);
            if (!get_role('socio')) {
                add_role('socio', 'Socio', get_role('subscriber')->capabilities);
            }
            $user->set_role('socio');
            
            $approved_count++;
        }
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-users-list',
            'message' => 'bulk_approved',
            'count' => $approved_count
        ], admin_url('admin.php')));
        exit;
    }
    
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        // Filtri
        $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : 'all';
        $filter_socio = isset($_GET['filter_socio']) ? sanitize_text_field($_GET['filter_socio']) : 'all';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Query utenti
        $args = [
            'number' => 50,
            'orderby' => 'registered',
            'order' => 'DESC'
        ];
        
        if ($search) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }
        
        // Filtro per ruolo
        if ($filter_status !== 'all') {
            $args['role'] = $filter_status;
        }
        
        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        
        // Filtra per is_socio se necessario
        if ($filter_socio !== 'all') {
            $users = array_filter($users, function($user) use ($filter_socio) {
                $is_socio = get_user_meta($user->ID, 'is_socio', true);
                if ($filter_socio === 'socio') {
                    return $is_socio;
                } else {
                    return !$is_socio;
                }
            });
        }
        
        // Messaggi
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-groups" style="font-size: 32px; width: 32px; height: 32px;"></span>
                Utenti Registrati
            </h1>
            
            <?php if ($message === 'bulk_approved'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>✅ <?php echo intval($_GET['count']); ?> utenti approvati come soci!</strong></p>
                </div>
            <?php endif; ?>
            
            <hr class="wp-header-end">
            
            <style>
                .wecoop-filters {
                    background: #fff;
                    padding: 15px;
                    margin: 20px 0;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .wecoop-filters form {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    flex-wrap: wrap;
                }
                .wecoop-table {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .wecoop-table table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .wecoop-table th {
                    background: #f9f9f9;
                    padding: 12px;
                    text-align: left;
                    font-weight: 600;
                    border-bottom: 1px solid #ccd0d4;
                }
                .wecoop-table td {
                    padding: 12px;
                    border-bottom: 1px solid #f0f0f1;
                }
                .wecoop-table tr:hover {
                    background: #f9f9f9;
                }
                .wecoop-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                }
                .wecoop-badge.socio { background: #46b450; color: white; }
                .wecoop-badge.subscriber { background: #f56e28; color: white; }
                .wecoop-badge.completo { background: #00a0d2; color: white; }
                .wecoop-badge.incompleto { background: #dc3232; color: white; }
                .wecoop-stats {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                .wecoop-stat-card {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                    text-align: center;
                }
                .wecoop-stat-number {
                    font-size: 36px;
                    font-weight: 700;
                    color: #2271b1;
                    margin: 10px 0;
                }
                .wecoop-stat-label {
                    font-size: 13px;
                    color: #646970;
                    text-transform: uppercase;
                    font-weight: 600;
                }
            </style>
            
            <!-- Statistiche -->
            <div class="wecoop-stats">
                <?php
                $total_users = count($users);
                $total_soci = count(array_filter($users, function($u) {
                    return get_user_meta($u->ID, 'is_socio', true);
                }));
                $total_non_soci = $total_users - $total_soci;
                $profili_completi = count(array_filter($users, function($u) {
                    return get_user_meta($u->ID, 'profilo_completo', true);
                }));
                ?>
                <div class="wecoop-stat-card">
                    <div class="wecoop-stat-label">Totale Utenti</div>
                    <div class="wecoop-stat-number"><?php echo $total_users; ?></div>
                </div>
                <div class="wecoop-stat-card">
                    <div class="wecoop-stat-label">Soci Attivi</div>
                    <div class="wecoop-stat-number" style="color: #46b450;"><?php echo $total_soci; ?></div>
                </div>
                <div class="wecoop-stat-card">
                    <div class="wecoop-stat-label">Non Soci</div>
                    <div class="wecoop-stat-number" style="color: #f56e28;"><?php echo $total_non_soci; ?></div>
                </div>
                <div class="wecoop-stat-card">
                    <div class="wecoop-stat-label">Profili Completi</div>
                    <div class="wecoop-stat-number" style="color: #00a0d2;"><?php echo $profili_completi; ?></div>
                </div>
            </div>
            
            <!-- Filtri -->
            <div class="wecoop-filters">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="wecoop-users-list">
                    
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" 
                           placeholder="Cerca per nome, email, telefono..." style="width: 300px;">
                    
                    <select name="filter_socio">
                        <option value="all" <?php selected($filter_socio, 'all'); ?>>Tutti</option>
                        <option value="socio" <?php selected($filter_socio, 'socio'); ?>>Solo Soci</option>
                        <option value="non_socio" <?php selected($filter_socio, 'non_socio'); ?>>Solo Non Soci</option>
                    </select>
                    
                    <select name="filter_status">
                        <option value="all" <?php selected($filter_status, 'all'); ?>>Tutti i ruoli</option>
                        <option value="socio" <?php selected($filter_status, 'socio'); ?>>Ruolo Socio</option>
                        <option value="subscriber" <?php selected($filter_status, 'subscriber'); ?>>Ruolo Subscriber</option>
                    </select>
                    
                    <button type="submit" class="button">Filtra</button>
                    <a href="?page=wecoop-users-list" class="button">Reset</a>
                </form>
            </div>
            
            <!-- Tabella -->
            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('wecoop_bulk_approve'); ?>
                <input type="hidden" name="action" value="wecoop_bulk_approve_soci">
                
                <div class="wecoop-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="select-all"></th>
                                <th>Utente</th>
                                <th>Contatti</th>
                                <th>Data Registrazione</th>
                                <th>Status</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #646970;">
                                        Nessun utente trovato
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): 
                                    $is_socio = get_user_meta($user->ID, 'is_socio', true);
                                    $profilo_completo = get_user_meta($user->ID, 'profilo_completo', true);
                                    $telefono = get_user_meta($user->ID, 'telefono_completo', true) ?: $user->user_login;
                                    
                                    // Richiesta socio
                                    $richiesta = get_posts([
                                        'post_type' => 'richiesta_socio',
                                        'meta_key' => 'user_id_socio',
                                        'meta_value' => $user->ID,
                                        'posts_per_page' => 1
                                    ]);
                                    $numero_pratica = !empty($richiesta) ? get_post_meta($richiesta[0]->ID, 'numero_pratica', true) : '';
                                ?>
                                <tr>
                                    <td>
                                        <?php if (!$is_socio && $profilo_completo): ?>
                                            <input type="checkbox" name="user_ids[]" value="<?php echo $user->ID; ?>" class="user-checkbox">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <a href="<?php echo admin_url('admin.php?page=wecoop-user-detail&user_id=' . $user->ID); ?>">
                                                <?php echo esc_html($user->display_name); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small style="color: #646970;">
                                            ID: <?php echo $user->ID; ?>
                                            <?php if ($numero_pratica): ?>
                                                | <?php echo esc_html($numero_pratica); ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($telefono); ?></strong><br>
                                        <small>
                                            <a href="mailto:<?php echo esc_attr($user->user_email); ?>">
                                                <?php echo esc_html($user->user_email ?: 'N/A'); ?>
                                            </a>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($user->user_registered)); ?>
                                    </td>
                                    <td>
                                        <span class="wecoop-badge <?php echo $is_socio ? 'socio' : 'subscriber'; ?>">
                                            <?php echo $is_socio ? '✓ Socio' : 'Subscriber'; ?>
                                        </span>
                                        <br>
                                        <span class="wecoop-badge <?php echo $profilo_completo ? 'completo' : 'incompleto'; ?>">
                                            <?php echo $profilo_completo ? 'Completo' : 'Incompleto'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $whatsapp_number = preg_replace('/[^0-9]/', '', $telefono);
                                        $message = "Ciao {$user->display_name}, ti contattiamo da WeCoop.";
                                        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($message);
                                        ?>
                                        <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" 
                                           class="button button-small" style="background: #25D366; border-color: #25D366; color: white;">
                                            <span class="dashicons dashicons-whatsapp" style="font-size: 14px; margin-top: 2px;"></span>
                                        </a>
                                        
                                        <a href="<?php echo admin_url('admin.php?page=wecoop-user-detail&user_id=' . $user->ID); ?>" 
                                           class="button button-small">
                                            Dettagli
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($users)): ?>
                    <p style="margin-top: 15px;">
                        <button type="submit" class="button button-primary button-large" 
                                onclick="return confirm('Confermi di voler approvare come SOCI tutti gli utenti selezionati?');">
                            <span class="dashicons dashicons-yes-alt" style="margin-top: 8px;"></span>
                            Approva Soci Selezionati
                        </button>
                    </p>
                <?php endif; ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#select-all').on('change', function() {
                $('.user-checkbox').prop('checked', $(this).prop('checked'));
            });
        });
        </script>
        <?php
    }
}

new WeCoop_Users_List_Page();
