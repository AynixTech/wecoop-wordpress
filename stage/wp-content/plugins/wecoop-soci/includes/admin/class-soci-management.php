<?php
/**
 * Gestione Admin Soci
 * 
 * @package WeCoop_Soci
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Soci_Management {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menus']);
        add_action('wp_ajax_edit_socio', [__CLASS__, 'ajax_edit_socio']);
        add_action('wp_ajax_delete_socio', [__CLASS__, 'ajax_delete_socio']);
        add_action('wp_ajax_save_socio', [__CLASS__, 'ajax_save_socio']);
    }
    
    /**
     * Aggiungi menu admin
     */
    public static function add_admin_menus() {
        // Menu principale
        add_menu_page(
            'Gestione Soci',
            'Soci',
            'manage_options',
            'wecoop-soci',
            [__CLASS__, 'render_dashboard_page'],
            'dashicons-groups',
            30
        );
        
        // Sottomenu: Dashboard
        add_submenu_page(
            'wecoop-soci',
            'Dashboard Soci',
            'Dashboard',
            'manage_options',
            'wecoop-soci',
            [__CLASS__, 'render_dashboard_page']
        );
        
        // Sottomenu: Richieste
        add_submenu_page(
            'wecoop-soci',
            'Richieste Adesione',
            'Richieste',
            'manage_options',
            'edit.php?post_type=richiesta_socio'
        );
        
        // Sottomenu: Tutti i Soci
        add_submenu_page(
            'wecoop-soci',
            'Elenco Soci',
            'Tutti i Soci',
            'manage_options',
            'wecoop-soci-list',
            [__CLASS__, 'render_soci_list_page']
        );
    }
    
    /**
     * Dashboard principale
     */
    public static function render_dashboard_page() {
        // Statistiche
        $totale_soci = count(get_users(['role' => 'socio']));
        $richieste_pending = wp_count_posts('richiesta_socio')->pending ?? 0;
        $richieste_approved = wp_count_posts('richiesta_socio')->approved ?? 0;
        $richieste_rejected = wp_count_posts('richiesta_socio')->rejected ?? 0;
        
        ?>
        <div class="wrap">
            <h1>Gestione Soci WeCoop</h1>
            
            <div class="wecoop-soci-dashboard">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3><?php echo $totale_soci; ?></h3>
                        <p>Soci Totali</p>
                    </div>
                    
                    <div class="stat-card pending">
                        <h3><?php echo $richieste_pending; ?></h3>
                        <p>Richieste in Attesa</p>
                    </div>
                    
                    <div class="stat-card approved">
                        <h3><?php echo $richieste_approved; ?></h3>
                        <p>Richieste Approvate</p>
                    </div>
                    
                    <div class="stat-card rejected">
                        <h3><?php echo $richieste_rejected; ?></h3>
                        <p>Richieste Rifiutate</p>
                    </div>
                </div>
                
                <div class="dashboard-actions">
                    <a href="<?php echo admin_url('edit.php?post_type=richiesta_socio'); ?>" class="button button-primary button-large">
                        Gestisci Richieste
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wecoop-soci-list'); ?>" class="button button-secondary button-large">
                        Vedi Tutti i Soci
                    </a>
                </div>
            </div>
            
            <style>
                .wecoop-soci-dashboard { margin-top: 20px; }
                .dashboard-stats { 
                    display: grid; 
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
                    gap: 20px; 
                    margin-bottom: 30px;
                }
                .stat-card {
                    background: white;
                    padding: 20px;
                    border-left: 4px solid #2271b1;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .stat-card.pending { border-left-color: #f0ad4e; }
                .stat-card.approved { border-left-color: #5cb85c; }
                .stat-card.rejected { border-left-color: #d9534f; }
                .stat-card h3 { margin: 0 0 5px; font-size: 32px; }
                .stat-card p { margin: 0; color: #666; }
                .dashboard-actions { display: flex; gap: 10px; }
            </style>
        </div>
        <?php
    }
    
    /**
     * Lista soci
     */
    public static function render_soci_list_page() {
        $soci = get_users(['role' => 'socio']);
        
        ?>
        <div class="wrap">
            <h1>Elenco Soci</h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Numero Tessera</th>
                        <th>Data Iscrizione</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($soci)): ?>
                        <tr>
                            <td colspan="6">Nessun socio trovato.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($soci as $socio): 
                            $tessera = get_user_meta($socio->ID, 'numero_tessera', true);
                            $stato = get_user_meta($socio->ID, 'stato_socio', true) ?: 'attivo';
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($socio->display_name); ?></strong></td>
                            <td><?php echo esc_html($socio->user_email); ?></td>
                            <td><?php echo esc_html($tessera ?: '-'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($socio->user_registered)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($stato); ?>">
                                    <?php echo ucfirst($stato); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small edit-socio" data-user-id="<?php echo $socio->ID; ?>">
                                    Modifica
                                </button>
                                <button class="button button-small button-link-delete delete-socio" data-user-id="<?php echo $socio->ID; ?>" data-user-name="<?php echo esc_attr($socio->display_name); ?>">
                                    Elimina
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <style>
                .status-badge {
                    padding: 3px 8px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: bold;
                }
                .status-attivo { background: #d4edda; color: #155724; }
                .status-sospeso { background: #fff3cd; color: #856404; }
                .status-cessato { background: #f8d7da; color: #721c24; }
            </style>
            
            <!-- Modal Modifica Socio -->
            <div id="edit-socio-modal" style="display:none;">
                <div style="background: white; padding: 20px; max-width: 600px; margin: 50px auto; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <h2>Modifica Socio</h2>
                    <form id="edit-socio-form">
                        <input type="hidden" id="edit-user-id" name="user_id">
                        
                        <p>
                            <label><strong>Nome Completo</strong></label>
                            <input type="text" id="edit-display-name" name="display_name" class="regular-text" required>
                        </p>
                        
                        <p>
                            <label><strong>Email</strong></label>
                            <input type="email" id="edit-email" name="email" class="regular-text" required>
                        </p>
                        
                        <p>
                            <label><strong>Numero Tessera</strong></label>
                            <input type="text" id="edit-tessera" name="numero_tessera" class="regular-text">
                        </p>
                        
                        <p>
                            <label><strong>Telefono</strong></label>
                            <input type="text" id="edit-telefono" name="telefono" class="regular-text">
                        </p>
                        
                        <p>
                            <label><strong>Città</strong></label>
                            <input type="text" id="edit-citta" name="citta" class="regular-text">
                        </p>
                        
                        <p>
                            <label><strong>Stato Socio</strong></label>
                            <select id="edit-stato" name="stato_socio" class="regular-text">
                                <option value="attivo">Attivo</option>
                                <option value="sospeso">Sospeso</option>
                                <option value="cessato">Cessato</option>
                            </select>
                        </p>
                        
                        <p>
                            <button type="submit" class="button button-primary">Salva Modifiche</button>
                            <button type="button" class="button cancel-edit">Annulla</button>
                        </p>
                    </form>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Modifica socio
                $('.edit-socio').on('click', function() {
                    var userId = $(this).data('user-id');
                    
                    $.post(ajaxurl, {
                        action: 'edit_socio',
                        user_id: userId
                    }, function(response) {
                        if (response.success) {
                            var data = response.data;
                            $('#edit-user-id').val(userId);
                            $('#edit-display-name').val(data.display_name);
                            $('#edit-email').val(data.email);
                            $('#edit-tessera').val(data.numero_tessera);
                            $('#edit-telefono').val(data.telefono);
                            $('#edit-citta').val(data.citta);
                            $('#edit-stato').val(data.stato_socio);
                            
                            $('body').append('<div id="modal-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;"></div>');
                            $('#edit-socio-modal').css('z-index', '9999').show();
                        }
                    });
                });
                
                // Chiudi modal
                $('.cancel-edit, #modal-overlay').on('click', function() {
                    $('#edit-socio-modal').hide();
                    $('#modal-overlay').remove();
                });
                
                // Salva modifiche
                $('#edit-socio-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    $.post(ajaxurl, {
                        action: 'save_socio',
                        user_id: $('#edit-user-id').val(),
                        display_name: $('#edit-display-name').val(),
                        email: $('#edit-email').val(),
                        numero_tessera: $('#edit-tessera').val(),
                        telefono: $('#edit-telefono').val(),
                        citta: $('#edit-citta').val(),
                        stato_socio: $('#edit-stato').val()
                    }, function(response) {
                        if (response.success) {
                            alert('Socio aggiornato con successo!');
                            location.reload();
                        } else {
                            alert('Errore: ' + response.data.message);
                        }
                    });
                });
                
                // Elimina socio
                $('.delete-socio').on('click', function() {
                    var userId = $(this).data('user-id');
                    var userName = $(this).data('user-name');
                    
                    if (confirm('Sei sicuro di voler eliminare il socio "' + userName + '"? Questa azione è irreversibile.')) {
                        $.post(ajaxurl, {
                            action: 'delete_socio',
                            user_id: userId
                        }, function(response) {
                            if (response.success) {
                                alert('Socio eliminato con successo!');
                                location.reload();
                            } else {
                                alert('Errore: ' + response.data.message);
                            }
                        });
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * AJAX: Carica dati socio per modifica
     */
    public static function ajax_edit_socio() {
        check_ajax_referer('wp_ajax', false, false);
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => 'Utente non trovato']);
        }
        
        wp_send_json_success([
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'numero_tessera' => get_user_meta($user_id, 'numero_tessera', true),
            'telefono' => get_user_meta($user_id, 'telefono', true),
            'citta' => get_user_meta($user_id, 'citta', true),
            'stato_socio' => get_user_meta($user_id, 'stato_socio', true) ?: 'attivo'
        ]);
    }
    
    /**
     * AJAX: Salva modifiche socio
     */
    public static function ajax_save_socio() {
        check_ajax_referer('wp_ajax', false, false);
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_userdata($user_id);
        
        if (!$user) {
            wp_send_json_error(['message' => 'Utente non trovato']);
        }
        
        // Aggiorna dati utente
        wp_update_user([
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_email' => sanitize_email($_POST['email'])
        ]);
        
        // Aggiorna meta
        update_user_meta($user_id, 'numero_tessera', sanitize_text_field($_POST['numero_tessera']));
        update_user_meta($user_id, 'telefono', sanitize_text_field($_POST['telefono']));
        update_user_meta($user_id, 'citta', sanitize_text_field($_POST['citta']));
        update_user_meta($user_id, 'stato_socio', sanitize_text_field($_POST['stato_socio']));
        
        wp_send_json_success(['message' => 'Socio aggiornato']);
    }
    
    /**
     * AJAX: Elimina socio
     */
    public static function ajax_delete_socio() {
        check_ajax_referer('wp_ajax', false, false);
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $user_id = intval($_POST['user_id']);
        
        if (!$user_id || $user_id === get_current_user_id()) {
            wp_send_json_error(['message' => 'Non puoi eliminare te stesso']);
        }
        
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        
        if (wp_delete_user($user_id)) {
            wp_send_json_success(['message' => 'Socio eliminato']);
        } else {
            wp_send_json_error(['message' => 'Impossibile eliminare il socio']);
        }
    }
}
