<?php
/**
 * Custom Post Type: Richiesta Supporto
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Richiesta_Supporto_CPT {
    
    public function register() {
        register_post_type('richiesta_supporto', [
            'labels' => [
                'name' => 'Richieste Supporto',
                'singular_name' => 'Richiesta Supporto',
                'add_new' => 'Nuova Richiesta',
                'add_new_item' => 'Nuova Richiesta Supporto',
                'edit_item' => 'Modifica Richiesta',
                'view_item' => 'Visualizza Richiesta',
                'search_items' => 'Cerca Richieste',
                'not_found' => 'Nessuna richiesta trovata',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-sos',
            'menu_position' => 25,
            'supports' => ['title', 'editor'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'has_archive' => false,
            'rewrite' => false,
            'show_in_rest' => false,
        ]);
        
        // Aggiungi metabox
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_richiesta_supporto', [$this, 'save_meta'], 10, 2);
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'richiesta_supporto_details',
            'Dettagli Richiesta',
            [$this, 'render_details_metabox'],
            'richiesta_supporto',
            'normal',
            'high'
        );
        
        add_meta_box(
            'richiesta_supporto_actions',
            'Azioni Rapide',
            [$this, 'render_actions_metabox'],
            'richiesta_supporto',
            'side',
            'high'
        );
    }
    
    public function render_details_metabox($post) {
        wp_nonce_field('richiesta_supporto_meta', 'richiesta_supporto_nonce');
        
        $user_id = get_post_meta($post->ID, 'user_id', true);
        $service_name = get_post_meta($post->ID, 'service_name', true);
        $service_category = get_post_meta($post->ID, 'service_category', true);
        $current_screen = get_post_meta($post->ID, 'current_screen', true);
        $user_email = get_post_meta($post->ID, 'user_email', true);
        $user_name = get_post_meta($post->ID, 'user_name', true);
        $user_phone = get_post_meta($post->ID, 'user_phone', true);
        $tipo_richiesta = get_post_meta($post->ID, 'tipo_richiesta', true);
        $priorita = get_post_meta($post->ID, 'priorita', true);
        $status = get_post_meta($post->ID, 'status', true) ?: 'aperta';
        $timestamp = get_post_meta($post->ID, 'timestamp', true);
        
        ?>
        <style>
            .wecoop-meta-grid {
                display: grid;
                grid-template-columns: 200px 1fr;
                gap: 15px;
                margin: 15px 0;
            }
            .wecoop-meta-label {
                font-weight: 600;
                color: #23282d;
            }
            .wecoop-meta-value {
                color: #50575e;
            }
            .wecoop-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .wecoop-badge.alta { background: #dc3232; color: white; }
            .wecoop-badge.media { background: #f56e28; color: white; }
            .wecoop-badge.bassa { background: #46b450; color: white; }
            .wecoop-badge.aperta { background: #00a0d2; color: white; }
            .wecoop-badge.in_lavorazione { background: #f56e28; color: white; }
            .wecoop-badge.risolta { background: #46b450; color: white; }
            .wecoop-badge.chiusa { background: #72777c; color: white; }
        </style>
        
        <div class="wecoop-meta-grid">
            <div class="wecoop-meta-label">Utente:</div>
            <div class="wecoop-meta-value">
                <?php if ($user_id): ?>
                    <a href="<?php echo get_edit_user_link($user_id); ?>" target="_blank">
                        <?php echo esc_html($user_name); ?> (ID: <?php echo $user_id; ?>)
                    </a>
                <?php else: ?>
                    <?php echo esc_html($user_name); ?>
                <?php endif; ?>
            </div>
            
            <div class="wecoop-meta-label">Email:</div>
            <div class="wecoop-meta-value">
                <a href="mailto:<?php echo esc_attr($user_email); ?>">
                    <?php echo esc_html($user_email); ?>
                </a>
            </div>
            
            <div class="wecoop-meta-label">Telefono:</div>
            <div class="wecoop-meta-value">
                <a href="tel:<?php echo esc_attr($user_phone); ?>">
                    <?php echo esc_html($user_phone); ?>
                </a>
            </div>
            
            <div class="wecoop-meta-label">Servizio:</div>
            <div class="wecoop-meta-value">
                <strong><?php echo esc_html($service_name); ?></strong>
                <br><small style="color: #72777c;"><?php echo esc_html($service_category); ?></small>
            </div>
            
            <div class="wecoop-meta-label">Schermata:</div>
            <div class="wecoop-meta-value"><?php echo esc_html($current_screen); ?></div>
            
            <div class="wecoop-meta-label">Tipo Richiesta:</div>
            <div class="wecoop-meta-value"><?php echo esc_html($tipo_richiesta); ?></div>
            
            <div class="wecoop-meta-label">Priorità:</div>
            <div class="wecoop-meta-value">
                <span class="wecoop-badge <?php echo esc_attr($priorita); ?>">
                    <?php echo esc_html(strtoupper($priorita)); ?>
                </span>
            </div>
            
            <div class="wecoop-meta-label">Status:</div>
            <div class="wecoop-meta-value">
                <select name="status" id="status" style="width: 200px;">
                    <option value="aperta" <?php selected($status, 'aperta'); ?>>🔵 Aperta</option>
                    <option value="in_lavorazione" <?php selected($status, 'in_lavorazione'); ?>>🟡 In Lavorazione</option>
                    <option value="risolta" <?php selected($status, 'risolta'); ?>>🟢 Risolta</option>
                    <option value="chiusa" <?php selected($status, 'chiusa'); ?>>⚫ Chiusa</option>
                </select>
            </div>
            
            <div class="wecoop-meta-label">Data/Ora:</div>
            <div class="wecoop-meta-value">
                <?php 
                if ($timestamp) {
                    $date = new DateTime($timestamp);
                    echo $date->format('d/m/Y H:i:s');
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    public function render_actions_metabox($post) {
        $user_phone = get_post_meta($post->ID, 'user_phone', true);
        $user_name = get_post_meta($post->ID, 'user_name', true);
        $service_name = get_post_meta($post->ID, 'service_name', true);
        
        // Rimuovi spazi e caratteri speciali dal numero
        $whatsapp_number = preg_replace('/[^0-9]/', '', $user_phone);
        
        // Messaggio predefinito
        $message = "Ciao {$user_name}, ti contattiamo riguardo la tua richiesta di supporto per il servizio {$service_name}.";
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($message);
        
        ?>
        <div style="padding: 10px 0;">
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" class="button button-primary button-large" style="width: 100%; text-align: center; margin-bottom: 10px;">
                <span class="dashicons dashicons-whatsapp" style="margin-top: 3px;"></span>
                Apri WhatsApp
            </a>
            
            <a href="mailto:<?php echo esc_attr(get_post_meta($post->ID, 'user_email', true)); ?>" class="button button-secondary button-large" style="width: 100%; text-align: center; margin-bottom: 10px;">
                <span class="dashicons dashicons-email" style="margin-top: 3px;"></span>
                Invia Email
            </a>
            
            <a href="tel:<?php echo esc_attr($user_phone); ?>" class="button button-secondary button-large" style="width: 100%; text-align: center;">
                <span class="dashicons dashicons-phone" style="margin-top: 3px;"></span>
                Chiama
            </a>
        </div>
        
        <hr>
        
        <div style="padding: 10px 0;">
            <p><strong>Info Rapide:</strong></p>
            <p style="margin: 5px 0; font-size: 12px;">
                <strong>Telefono:</strong><br>
                <code><?php echo esc_html($user_phone); ?></code>
            </p>
            <p style="margin: 5px 0; font-size: 12px;">
                <strong>Creata:</strong><br>
                <?php echo get_the_date('d/m/Y H:i', $post->ID); ?>
            </p>
        </div>
        <?php
    }
    
    public function save_meta($post_id, $post) {
        // Verifica nonce
        if (!isset($_POST['richiesta_supporto_nonce']) || 
            !wp_verify_nonce($_POST['richiesta_supporto_nonce'], 'richiesta_supporto_meta')) {
            return;
        }
        
        // Verifica permessi
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Salva status
        if (isset($_POST['status'])) {
            update_post_meta($post_id, 'status', sanitize_text_field($_POST['status']));
        }
    }
}
