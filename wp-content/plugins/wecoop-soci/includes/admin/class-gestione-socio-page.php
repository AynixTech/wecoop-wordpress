<?php
/**
 * Pagina Admin - Gestione Socio
 * Completa profilo e approva come socio
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Gestione_Socio_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_wecoop_completa_profilo', [$this, 'handle_completa_profilo']);
        add_action('admin_post_wecoop_approva_socio', [$this, 'handle_approva_socio']);
        add_action('admin_post_wecoop_revoca_socio', [$this, 'handle_revoca_socio']);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'users.php',
            'Gestione Socio',
            'Gestione Socio',
            'manage_options',
            'wecoop-gestione-socio',
            [$this, 'render_page']
        );
    }
    
    public function handle_completa_profilo() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_completa_profilo');
        
        $user_id = intval($_POST['user_id']);
        
        // Aggiorna profilo utente
        $update_data = [
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'display_name' => sanitize_text_field($_POST['display_name'])
        ];
        
        $result = wp_update_user($update_data);
        
        if (!is_wp_error($result)) {
            // Aggiorna user meta
            update_user_meta($user_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
            update_user_meta($user_id, 'citta', sanitize_text_field($_POST['citta']));
            update_user_meta($user_id, 'cap', sanitize_text_field($_POST['cap']));
            update_user_meta($user_id, 'provincia', strtoupper(sanitize_text_field($_POST['provincia'])));
            update_user_meta($user_id, 'codice_fiscale', strtoupper(sanitize_text_field($_POST['codice_fiscale'])));
            update_user_meta($user_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
            update_user_meta($user_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
            update_user_meta($user_id, 'profilo_completo', true);
            
            // Aggiorna anche i meta nel post richiesta_socio
            $richiesta = get_posts([
                'post_type' => 'richiesta_socio',
                'meta_key' => 'user_id_socio',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ]);
            
            if (!empty($richiesta)) {
                $post_id = $richiesta[0]->ID;
                update_post_meta($post_id, 'email', sanitize_email($_POST['email']));
                update_post_meta($post_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
                update_post_meta($post_id, 'citta', sanitize_text_field($_POST['citta']));
                update_post_meta($post_id, 'cap', sanitize_text_field($_POST['cap']));
                update_post_meta($post_id, 'provincia', strtoupper(sanitize_text_field($_POST['provincia'])));
                update_post_meta($post_id, 'codice_fiscale', strtoupper(sanitize_text_field($_POST['codice_fiscale'])));
                update_post_meta($post_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
                update_post_meta($post_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
                update_post_meta($post_id, 'profilo_completo', true);
            }
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-gestione-socio',
                'user_id' => $user_id,
                'message' => 'profilo_salvato'
            ], admin_url('users.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-gestione-socio',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode($result->get_error_message())
            ], admin_url('users.php')));
        }
        exit;
    }
    
    public function handle_approva_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_approva_socio');
        
        $user_id = intval($_POST['user_id']);
        
        // Trova richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            $post_id = $richiesta[0]->ID;
            
            // Aggiorna status
            update_post_meta($post_id, 'is_socio', true);
            update_post_meta($post_id, 'data_approvazione', current_time('mysql'));
            update_user_meta($user_id, 'is_socio', true);
            
            // Cambia ruolo a 'socio'
            $user = new WP_User($user_id);
            if (!get_role('socio')) {
                // Crea ruolo socio se non esiste
                add_role('socio', 'Socio', get_role('subscriber')->capabilities);
            }
            $user->set_role('socio');
            
            // Cambia status post
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish'
            ]);
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-gestione-socio',
                'user_id' => $user_id,
                'message' => 'socio_approvato'
            ], admin_url('users.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-gestione-socio',
                'user_id' => $user_id,
                'message' => 'errore_richiesta'
            ], admin_url('users.php')));
        }
        exit;
    }
    
    public function handle_revoca_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_revoca_socio');
        
        $user_id = intval($_POST['user_id']);
        
        update_user_meta($user_id, 'is_socio', false);
        
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            update_post_meta($richiesta[0]->ID, 'is_socio', false);
            update_post_meta($richiesta[0]->ID, 'data_revoca', current_time('mysql'));
        }
        
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-gestione-socio',
            'user_id' => $user_id,
            'message' => 'socio_revocato'
        ], admin_url('users.php')));
        exit;
    }
    
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        if (!$user_id) {
            echo '<div class="wrap">';
            echo '<h1>Gestione Socio</h1>';
            echo '<div class="notice notice-error"><p>ID utente non specificato. Seleziona un utente dalla <a href="' . admin_url('users.php') . '">lista utenti</a>.</p></div>';
            echo '</div>';
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            echo '<div class="wrap">';
            echo '<h1>Gestione Socio</h1>';
            echo '<div class="notice notice-error"><p>Utente non trovato.</p></div>';
            echo '</div>';
            return;
        }
        
        // Recupera dati utente
        $is_socio = get_user_meta($user_id, 'is_socio', true);
        $profilo_completo = get_user_meta($user_id, 'profilo_completo', true);
        $telefono_completo = get_user_meta($user_id, 'telefono_completo', true);
        $indirizzo = get_user_meta($user_id, 'indirizzo', true);
        $citta = get_user_meta($user_id, 'citta', true);
        $cap = get_user_meta($user_id, 'cap', true);
        $provincia = get_user_meta($user_id, 'provincia', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        $data_nascita = get_user_meta($user_id, 'data_nascita', true);
        $luogo_nascita = get_user_meta($user_id, 'luogo_nascita', true);
        
        // Recupera richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        $numero_pratica = '';
        $data_registrazione = $user->user_registered;
        if (!empty($richiesta)) {
            $numero_pratica = get_post_meta($richiesta[0]->ID, 'numero_pratica', true);
            $data_registrazione = $richiesta[0]->post_date;
        }
        
        // Messaggi
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-admin-users" style="font-size: 32px; width: 32px; height: 32px;"></span>
                Gestione Socio: <?php echo esc_html($user->display_name); ?>
            </h1>
            
            <?php if ($message === 'profilo_salvato'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>✅ Profilo salvato con successo!</strong></p>
                </div>
            <?php elseif ($message === 'socio_approvato'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>🎉 Utente approvato come SOCIO con successo!</strong></p>
                </div>
            <?php elseif ($message === 'socio_revocato'): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong>⚠️ Socio revocato. Utente tornato a subscriber.</strong></p>
                </div>
            <?php elseif ($message === 'errore'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>❌ Errore:</strong> <?php echo esc_html(urldecode($_GET['error_msg'])); ?></p>
                </div>
            <?php elseif ($message === 'errore_richiesta'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>❌ Richiesta socio non trovata per questo utente.</strong></p>
                </div>
            <?php endif; ?>
            
            <hr class="wp-header-end">
            
            <style>
                .wecoop-card {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                    margin: 20px 0;
                    padding: 20px;
                }
                .wecoop-card h2 {
                    margin-top: 0;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #ddd;
                }
                .wecoop-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    margin-left: 10px;
                }
                .wecoop-badge.socio { background: #46b450; color: white; }
                .wecoop-badge.subscriber { background: #f56e28; color: white; }
                .wecoop-badge.completo { background: #00a0d2; color: white; }
                .wecoop-badge.incompleto { background: #dc3232; color: white; }
                .wecoop-info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 15px;
                    margin: 15px 0;
                }
                .wecoop-info-item {
                    padding: 10px;
                    background: #f9f9f9;
                    border-left: 3px solid #00a0d2;
                }
                .wecoop-info-label {
                    font-size: 11px;
                    color: #666;
                    text-transform: uppercase;
                    font-weight: 600;
                    margin-bottom: 3px;
                }
                .wecoop-info-value {
                    font-size: 14px;
                    color: #23282d;
                    font-weight: 500;
                }
                .form-table th {
                    width: 200px;
                }
                .button-hero {
                    font-size: 14px !important;
                    height: 40px !important;
                    line-height: 38px !important;
                    padding: 0 20px !important;
                }
            </style>
            
            <!-- Informazioni Base -->
            <div class="wecoop-card">
                <h2>
                    📋 Informazioni Base
                    <span class="wecoop-badge <?php echo $is_socio ? 'socio' : 'subscriber'; ?>">
                        <?php echo $is_socio ? '✓ Socio' : 'Subscriber'; ?>
                    </span>
                    <span class="wecoop-badge <?php echo $profilo_completo ? 'completo' : 'incompleto'; ?>">
                        <?php echo $profilo_completo ? '✓ Completo' : '⚠ Incompleto'; ?>
                    </span>
                </h2>
                
                <div class="wecoop-info-grid">
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Username</div>
                        <div class="wecoop-info-value"><?php echo esc_html($user->user_login); ?></div>
                    </div>
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Email</div>
                        <div class="wecoop-info-value"><?php echo $user->user_email ?: '<em>Non impostata</em>'; ?></div>
                    </div>
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Telefono</div>
                        <div class="wecoop-info-value"><?php echo esc_html($telefono_completo); ?></div>
                    </div>
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Data Registrazione</div>
                        <div class="wecoop-info-value"><?php echo date('d/m/Y H:i', strtotime($data_registrazione)); ?></div>
                    </div>
                    <?php if ($numero_pratica): ?>
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Numero Pratica</div>
                        <div class="wecoop-info-value"><?php echo esc_html($numero_pratica); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="wecoop-info-item">
                        <div class="wecoop-info-label">Ruolo WordPress</div>
                        <div class="wecoop-info-value"><?php echo implode(', ', $user->roles); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Form Completa Profilo -->
            <div class="wecoop-card">
                <h2>✏️ Completa/Modifica Profilo</h2>
                
                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wecoop_completa_profilo'); ?>
                    <input type="hidden" name="action" value="wecoop_completa_profilo">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="first_name">Nome *</label></th>
                            <td><input type="text" name="first_name" id="first_name" class="regular-text" value="<?php echo esc_attr($user->first_name); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="last_name">Cognome *</label></th>
                            <td><input type="text" name="last_name" id="last_name" class="regular-text" value="<?php echo esc_attr($user->last_name); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="display_name">Nome Visualizzato *</label></th>
                            <td><input type="text" name="display_name" id="display_name" class="regular-text" value="<?php echo esc_attr($user->display_name); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="email">Email *</label></th>
                            <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($user->user_email); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="codice_fiscale">Codice Fiscale *</label></th>
                            <td>
                                <input type="text" name="codice_fiscale" id="codice_fiscale" class="regular-text" 
                                       value="<?php echo esc_attr($codice_fiscale); ?>" 
                                       pattern="[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]" 
                                       style="text-transform: uppercase;" maxlength="16" required>
                                <p class="description">Formato: RSSMRA85M01H501Z</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="data_nascita">Data di Nascita *</label></th>
                            <td><input type="date" name="data_nascita" id="data_nascita" value="<?php echo esc_attr($data_nascita); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="luogo_nascita">Luogo di Nascita *</label></th>
                            <td><input type="text" name="luogo_nascita" id="luogo_nascita" class="regular-text" value="<?php echo esc_attr($luogo_nascita); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="indirizzo">Indirizzo *</label></th>
                            <td><input type="text" name="indirizzo" id="indirizzo" class="regular-text" value="<?php echo esc_attr($indirizzo); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="citta">Città *</label></th>
                            <td><input type="text" name="citta" id="citta" class="regular-text" value="<?php echo esc_attr($citta); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="cap">CAP *</label></th>
                            <td>
                                <input type="text" name="cap" id="cap" class="regular-text" 
                                       value="<?php echo esc_attr($cap); ?>" 
                                       pattern="[0-9]{5}" maxlength="5" required>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="provincia">Provincia *</label></th>
                            <td>
                                <input type="text" name="provincia" id="provincia" class="regular-text" 
                                       value="<?php echo esc_attr($provincia); ?>" 
                                       maxlength="2" style="text-transform: uppercase;" pattern="[A-Z]{2}" required>
                                <p class="description">Due lettere (es: RM, MI, NA)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero">
                            <span class="dashicons dashicons-yes" style="margin-top: 8px;"></span>
                            Salva Profilo
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- Gestione Status Socio -->
            <div class="wecoop-card">
                <h2>🎯 Gestione Status Socio</h2>
                
                <?php if (!$is_socio): ?>
                    <p style="margin: 15px 0;">
                        Questo utente non è ancora socio. Completa il profilo e clicca il bottone qui sotto per approvarlo come socio.
                    </p>
                    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" 
                          onsubmit="return confirm('Confermi di voler approvare questo utente come SOCIO?');">
                        <?php wp_nonce_field('wecoop_approva_socio'); ?>
                        <input type="hidden" name="action" value="wecoop_approva_socio">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <button type="submit" class="button button-primary button-hero" 
                                <?php echo !$profilo_completo ? 'disabled title="Completa prima il profilo"' : ''; ?>>
                            <span class="dashicons dashicons-yes-alt" style="margin-top: 8px;"></span>
                            Approva come SOCIO
                        </button>
                    </form>
                <?php else: ?>
                    <p style="margin: 15px 0; color: #46b450; font-weight: 600;">
                        ✓ Questo utente è un SOCIO attivo
                    </p>
                    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" 
                          onsubmit="return confirm('Confermi di voler REVOCARE lo status di socio?');">
                        <?php wp_nonce_field('wecoop_revoca_socio'); ?>
                        <input type="hidden" name="action" value="wecoop_revoca_socio">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <button type="submit" class="button button-secondary button-hero">
                            <span class="dashicons dashicons-dismiss" style="margin-top: 8px;"></span>
                            Revoca Socio
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

// Inizializza
new WeCoop_Gestione_Socio_Page();
