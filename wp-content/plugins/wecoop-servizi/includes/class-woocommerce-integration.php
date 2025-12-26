<?php
/**
 * Integrazione WooCommerce per Richieste Servizi
 * 
 * Gestisce creazione ordini e pagamenti per le richieste servizi
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_WooCommerce_Integration {
    
    /**
     * Inizializza
     */
    public static function init() {
        // Hook quando salvi una richiesta servizio
        add_action('save_post_richiesta_servizio', [__CLASS__, 'on_richiesta_save'], 20, 3);
        
        // Hook quando lo stato dell'ordine WooCommerce cambia
        add_action('woocommerce_order_status_changed', [__CLASS__, 'on_order_status_change'], 10, 4);
        
        // Aggiungi meta box per vedere ordine collegato
        add_action('add_meta_boxes', [__CLASS__, 'add_order_metabox']);
    }
    
    /**
     * Quando salvi una richiesta servizio e cambi lo stato a "awaiting_payment"
     */
    public static function on_richiesta_save($post_id, $post, $update) {
        // Previeni loop infiniti
        if (defined('WECOOP_SERVIZI_UPDATING')) return;
        
        if (!$update) return; // Solo su update, non su nuovo post
        if (wp_is_post_revision($post_id)) return;
        
        $stato = get_post_meta($post_id, 'stato', true);
        $order_id = get_post_meta($post_id, 'wc_order_id', true);
        
        // Se stato = awaiting_payment e non c'è ancora un ordine
        if ($stato === 'awaiting_payment' && !$order_id) {
            self::crea_ordine_woocommerce($post_id);
        }
    }
    
    /**
     * Crea ordine WooCommerce per la richiesta servizio
     */
    public static function crea_ordine_woocommerce($richiesta_id) {
        if (!class_exists('WooCommerce')) {
            error_log('[WECOOP SERVIZI] WooCommerce non è attivo!');
            return false;
        }
        
        $richiesta = get_post($richiesta_id);
        if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
            return false;
        }
        
        // Dati richiesta
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        if (!$importo || $importo <= 0) {
            error_log('[WECOOP SERVIZI] Impossibile creare ordine: importo non valido');
            update_post_meta($richiesta_id, 'payment_error', 'Importo non specificato');
            return false;
        }
        
        if (!$user_id) {
            error_log('[WECOOP SERVIZI] Impossibile creare ordine: user_id mancante');
            update_post_meta($richiesta_id, 'payment_error', 'Utente non trovato');
            return false;
        }
        
        try {
            // Crea ordine WooCommerce
            $order = wc_create_order([
                'customer_id' => $user_id,
                'status' => 'pending'
            ]);
            
            if (is_wp_error($order)) {
                throw new Exception($order->get_error_message());
            }
            
            // Aggiungi prodotto virtuale come line item
            $item = new WC_Order_Item_Product();
            $item->set_name($servizio . ' - Pratica ' . $numero_pratica);
            $item->set_quantity(1);
            $item->set_subtotal($importo);
            $item->set_total($importo);
            
            // Meta dati personalizzati
            $item->add_meta_data('_richiesta_servizio_id', $richiesta_id, true);
            $item->add_meta_data('_numero_pratica', $numero_pratica, true);
            $item->add_meta_data('_tipo_servizio', $servizio, true);
            
            $order->add_item($item);
            $order->calculate_totals();
            
            // Aggiungi note
            $order->add_order_note(sprintf(
                'Ordine creato automaticamente per richiesta servizio #%s (%s)',
                $numero_pratica,
                $servizio
            ));
            
            // Salva l'ordine
            $order->save();
            
            // Collega ordine alla richiesta
            update_post_meta($richiesta_id, 'wc_order_id', $order->get_id());
            update_post_meta($richiesta_id, 'payment_status', 'pending');
            update_post_meta($richiesta_id, 'payment_created_at', current_time('mysql'));
            
            // Salva link richiesta nell'ordine
            update_post_meta($order->get_id(), '_richiesta_servizio_id', $richiesta_id);
            
            error_log(sprintf(
                '[WECOOP SERVIZI] Ordine WC #%d creato per richiesta #%s (€%s)',
                $order->get_id(),
                $numero_pratica,
                number_format($importo, 2)
            ));
            
            // Invia email con link pagamento
            self::invia_email_pagamento($richiesta_id, $order->get_id());
            
            return $order->get_id();
            
        } catch (Exception $e) {
            error_log('[WECOOP SERVIZI] Errore creazione ordine: ' . $e->getMessage());
            update_post_meta($richiesta_id, 'payment_error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invia email con link pagamento
     */
    public static function invia_email_pagamento($richiesta_id, $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $user = get_userdata($user_id);
        if (!$user) return;
        
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        
        // URL pagamento
        $payment_url = $order->get_checkout_payment_url();
        
        // Invia email multilingua se disponibile
        if (class_exists('WeCoop_Multilingual_Email')) {
            WeCoop_Multilingual_Email::send(
                $user->user_email,
                'service_payment_required',
                [
                    'nome' => $user->display_name,
                    'servizio' => $servizio,
                    'numero_pratica' => $numero_pratica,
                    'importo' => number_format($importo, 2, ',', '.') . ' €',
                    'payment_url' => $payment_url,
                    'button_url' => $payment_url,
                    'button_text' => 'Paga Ora'
                ],
                $user_id
            );
            
            error_log("[WECOOP SERVIZI] Email pagamento inviata a {$user->user_email}");
        } else {
            // Fallback: email standard WordPress
            $subject = 'Richiesta di Pagamento - Pratica ' . $numero_pratica;
            $message = sprintf(
                "Ciao %s,\n\n" .
                "La tua richiesta di servizio è stata presa in carico:\n\n" .
                "Servizio: %s\n" .
                "Numero Pratica: %s\n" .
                "Importo: €%s\n\n" .
                "Per procedere, completa il pagamento cliccando qui:\n%s\n\n" .
                "Grazie,\nIl Team WeCoop",
                $user->display_name,
                $servizio,
                $numero_pratica,
                number_format($importo, 2, ',', '.'),
                $payment_url
            );
            
            wp_mail($user->user_email, $subject, $message);
        }
    }
    
    /**
     * Quando lo stato dell'ordine WooCommerce cambia
     */
    public static function on_order_status_change($order_id, $old_status, $new_status, $order) {
        // Previeni loop
        if (defined('WECOOP_SERVIZI_UPDATING')) return;
        
        // Trova richiesta collegata
        $richiesta_id = get_post_meta($order_id, '_richiesta_servizio_id', true);
        if (!$richiesta_id) return;
        
        define('WECOOP_SERVIZI_UPDATING', true);
        
        // Sincronizza stato
        switch ($new_status) {
            case 'completed':
            case 'processing':
                // Pagamento ricevuto
                update_post_meta($richiesta_id, 'payment_status', 'paid');
                update_post_meta($richiesta_id, 'payment_paid_at', current_time('mysql'));
                update_post_meta($richiesta_id, 'stato', 'processing');
                
                error_log("[WECOOP SERVIZI] Pagamento ricevuto per richiesta #{$richiesta_id}");
                break;
                
            case 'failed':
            case 'cancelled':
                update_post_meta($richiesta_id, 'payment_status', 'failed');
                update_post_meta($richiesta_id, 'stato', 'pending');
                
                error_log("[WECOOP SERVIZI] Pagamento fallito per richiesta #{$richiesta_id}");
                break;
                
            case 'refunded':
                update_post_meta($richiesta_id, 'payment_status', 'refunded');
                
                error_log("[WECOOP SERVIZI] Pagamento rimborsato per richiesta #{$richiesta_id}");
                break;
        }
    }
    
    /**
     * Metabox per mostrare ordine collegato
     */
    public static function add_order_metabox() {
        add_meta_box(
            'richiesta_servizio_payment',
            'Pagamento',
            [__CLASS__, 'render_payment_metabox'],
            'richiesta_servizio',
            'side',
            'high'
        );
    }
    
    /**
     * Render metabox pagamento
     */
    public static function render_payment_metabox($post) {
        $order_id = get_post_meta($post->ID, 'wc_order_id', true);
        $payment_status = get_post_meta($post->ID, 'payment_status', true);
        $importo = get_post_meta($post->ID, 'importo', true);
        $payment_error = get_post_meta($post->ID, 'payment_error', true);
        
        ?>
        <div style="padding: 10px 0;">
            <p><strong>Importo Servizio:</strong></p>
            <input type="number" name="importo" step="0.01" min="0" 
                   value="<?php echo esc_attr($importo); ?>" 
                   style="width: 100%; margin-bottom: 10px;"
                   placeholder="0.00">
            <p class="description">Inserisci l'importo per abilitare il pagamento</p>
            
            <?php if ($order_id): 
                $order = wc_get_order($order_id);
                if ($order):
            ?>
                <hr style="margin: 15px 0;">
                <p><strong>Ordine WooCommerce:</strong></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" 
                       target="_blank">
                        Ordine #<?php echo $order_id; ?>
                    </a>
                </p>
                <p><strong>Stato Ordine:</strong> 
                    <span style="color: <?php echo $order->get_status() === 'completed' ? 'green' : 'orange'; ?>;">
                        <?php echo wc_get_order_status_name($order->get_status()); ?>
                    </span>
                </p>
                <p><strong>Importo:</strong> <?php echo $order->get_formatted_order_total(); ?></p>
                <p>
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" 
                       class="button button-primary" target="_blank">
                        Link Pagamento
                    </a>
                </p>
                <?php endif; ?>
            <?php elseif ($payment_status === 'pending' || $importo > 0): ?>
                <p><em>L'ordine verrà creato quando salvi con stato "Da pagare"</em></p>
            <?php endif; ?>
            
            <?php if ($payment_error): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">
                    <strong>Errore:</strong> <?php echo esc_html($payment_error); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
