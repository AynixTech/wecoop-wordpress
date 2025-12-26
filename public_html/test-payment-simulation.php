<?php
/**
 * Test completo simulazione pagamento WooCommerce
 * URL: https://www.wecoop.org/test-payment-simulation.php
 */

require_once(__DIR__ . '/wp-load.php');

if (!current_user_can('manage_options')) {
    die('Accesso negato');
}

// Simula parametri order-pay
$_GET['key'] = 'wc_order_test123';
$_GET['pay_for_order'] = 'true';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Simulazione Pagamento</title>
    <style>
        body { font-family: -apple-system, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1; }
        .success { color: #00a32a; }
        .error { color: #d63638; }
        .warning { color: #dba617; }
        h1 { color: #1d2327; }
        h2 { color: #2271b1; border-bottom: 2px solid #2271b1; padding-bottom: 10px; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; overflow-x: auto; border-radius: 4px; }
        .btn { 
            display: inline-block;
            padding: 12px 24px; 
            background: #2271b1; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px;
            margin: 10px 5px;
        }
        .btn:hover { background: #135e96; }
        .btn-green { background: #00a32a; }
        .btn-green:hover { background: #008a20; }
        .order-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-table th, .order-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .order-table th { background: #f0f0f0; font-weight: 600; }
        .payment-methods { margin: 20px 0; }
        .payment-method { 
            padding: 15px; 
            margin: 10px 0; 
            border: 2px solid #ddd; 
            border-radius: 4px;
            cursor: pointer;
        }
        .payment-method:hover { border-color: #2271b1; background: #f0f6fc; }
        .payment-method input[type="radio"] { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Simulazione Pagamento WooCommerce</h1>
        
        <?php
        // STEP 1: Verifica WooCommerce
        echo '<div class="section">';
        echo '<h2>1️⃣ Verifica Sistema</h2>';
        
        if (!class_exists('WooCommerce')) {
            echo '<p class="error">❌ WooCommerce NON attivo</p>';
            echo '</div></div></body></html>';
            exit;
        }
        echo '<p class="success">✅ WooCommerce attivo (v' . WC()->version . ')</p>';
        
        // Verifica payment gateways
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        echo '<p class="success">✅ Payment Gateways disponibili: ' . count($available_gateways) . '</p>';
        
        if (empty($available_gateways)) {
            echo '<p class="error">❌ PROBLEMA: Nessun metodo di pagamento configurato in WooCommerce!</p>';
            echo '<p>Vai su <a href="/wp-admin/admin.php?page=wc-settings&tab=checkout">WooCommerce → Impostazioni → Pagamenti</a> e abilita almeno un metodo.</p>';
        } else {
            echo '<ul>';
            foreach ($available_gateways as $gateway) {
                echo '<li class="success">✅ ' . $gateway->get_title() . ' (' . $gateway->id . ')</li>';
            }
            echo '</ul>';
        }
        
        echo '</div>';
        
        // STEP 2: Crea ordine di test
        if (isset($_GET['create_order'])) {
            echo '<div class="section">';
            echo '<h2>2️⃣ Creazione Ordine di Test</h2>';
            
            try {
                // Crea ordine
                $order = wc_create_order([
                    'customer_id' => get_current_user_id(),
                    'status' => 'pending'
                ]);
                
                if (is_wp_error($order)) {
                    throw new Exception($order->get_error_message());
                }
                
                echo '<p class="success">✅ Ordine creato: #' . $order->get_id() . '</p>';
                
                // Ottieni prodotto virtuale
                $product_id = get_option('wecoop_servizi_virtual_product_id');
                if (!$product_id) {
                    // Crea prodotto se non esiste
                    $product = new WC_Product_Simple();
                    $product->set_name('Test Servizio Pagamento');
                    $product->set_status('private');
                    $product->set_virtual(true);
                    $product->set_price(10);
                    $product_id = $product->save();
                    update_option('wecoop_servizi_virtual_product_id', $product_id);
                }
                
                // Aggiungi item usando wpdb diretto
                global $wpdb;
                $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_order_items',
                    [
                        'order_item_name' => 'Test Servizio - Pratica TEST001',
                        'order_item_type' => 'line_item',
                        'order_id' => $order->get_id()
                    ],
                    ['%s', '%s', '%d']
                );
                
                $item_id = $wpdb->insert_id;
                
                if ($item_id) {
                    echo '<p class="success">✅ Item aggiunto: #' . $item_id . '</p>';
                    
                    // Meta item
                    wc_add_order_item_meta($item_id, '_qty', 1);
                    wc_add_order_item_meta($item_id, '_line_subtotal', 10);
                    wc_add_order_item_meta($item_id, '_line_total', 10);
                    wc_add_order_item_meta($item_id, '_line_subtotal_tax', 0);
                    wc_add_order_item_meta($item_id, '_line_tax', 0);
                    wc_add_order_item_meta($item_id, '_line_tax_data', serialize([]));
                    
                    echo '<p class="success">✅ Meta dati aggiunti</p>';
                }
                
                // Imposta dati fatturazione
                $user = wp_get_current_user();
                $order->set_billing_email($user->user_email);
                $order->set_billing_first_name($user->first_name ?: 'Test');
                $order->set_billing_last_name($user->last_name ?: 'User');
                $order->set_total(10);
                $order->save();
                
                // Verifica
                $order = wc_get_order($order->get_id());
                $items = $order->get_items();
                
                echo '<p><strong>Verifica ordine:</strong></p>';
                echo '<p>Items: ' . count($items) . '</p>';
                echo '<p>Totale: ' . $order->get_total() . ' €</p>';
                echo '<p>Status: ' . $order->get_status() . '</p>';
                echo '<p>Needs Payment: ' . ($order->needs_payment() ? 'SI' : 'NO') . '</p>';
                
                if (count($items) > 0) {
                    echo '<p class="success">✅✅✅ ORDINE VALIDO</p>';
                    $payment_url = $order->get_checkout_payment_url(true);
                    echo '<p><a href="?simulate=' . $order->get_id() . '" class="btn btn-green">📋 Simula Pagamento Ordine #' . $order->get_id() . '</a></p>';
                    echo '<p><a href="' . $payment_url . '" class="btn" target="_blank">🔗 Link Pagamento Reale</a></p>';
                } else {
                    echo '<p class="error">❌ Nessun item nell\'ordine</p>';
                }
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Errore: ' . $e->getMessage() . '</p>';
            }
            
            echo '</div>';
        }
        
        // STEP 3: Simula pagina pagamento
        if (isset($_GET['simulate'])) {
            $order_id = absint($_GET['simulate']);
            $order = wc_get_order($order_id);
            
            if (!$order) {
                echo '<div class="section"><p class="error">❌ Ordine non trovato</p></div>';
            } else {
                echo '<div class="section">';
                echo '<h2>3️⃣ Simulazione Pagina Order-Pay</h2>';
                echo '<p class="success">✅ Ordine #' . $order->get_id() . ' caricato</p>';
                echo '</div>';
                
                // Simula HTML pagina pagamento
                echo '<div style="background: white; padding: 30px; border: 1px solid #ddd; border-radius: 8px;">';
                echo '<h1>Paga per l\'ordine</h1>';
                
                // Info ordine
                echo '<ul style="list-style: none; padding: 0; background: #f9f9f9; padding: 20px; border-radius: 4px;">';
                echo '<li><strong>Numero ordine:</strong> ' . $order->get_id() . '</li>';
                echo '<li><strong>Data:</strong> ' . $order->get_date_created()->format('d F Y') . '</li>';
                echo '<li><strong>Totale:</strong> <span style="font-size: 24px; color: #00a32a;">' . wc_price($order->get_total()) . '</span></li>';
                echo '</ul>';
                
                // Dettagli ordine
                $items = $order->get_items();
                
                if (empty($items)) {
                    echo '<div style="background: #f9e5e5; color: #d63638; padding: 15px; border-radius: 4px; margin: 20px 0;">';
                    echo '❌ PROBLEMA: Nessun item nell\'ordine! Questo è il motivo per cui non vedi i metodi di pagamento.';
                    echo '</div>';
                } else {
                    echo '<h2 style="margin-top: 30px;">Dettagli dell\'ordine</h2>';
                    echo '<table class="order-table">';
                    echo '<thead><tr><th>Prodotto</th><th style="text-align: right;">Totale</th></tr></thead>';
                    echo '<tbody>';
                    foreach ($items as $item) {
                        echo '<tr>';
                        echo '<td><strong>' . $item->get_name() . '</strong> × ' . $item->get_quantity() . '</td>';
                        echo '<td style="text-align: right;">' . wc_price($item->get_total()) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '<tfoot>';
                    echo '<tr><th>Totale</th><th style="text-align: right; font-size: 18px;">' . wc_price($order->get_total()) . '</th></tr>';
                    echo '</tfoot>';
                    echo '</table>';
                }
                
                // Metodi di pagamento
                if ($order->needs_payment() && !empty($items)) {
                    echo '<h2 style="margin-top: 30px;">Metodo di pagamento</h2>';
                    
                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                    
                    if (empty($available_gateways)) {
                        echo '<div style="background: #f9e5e5; color: #d63638; padding: 15px; border-radius: 4px;">';
                        echo '❌ Nessun metodo di pagamento disponibile. Configura i gateway in WooCommerce → Impostazioni → Pagamenti';
                        echo '</div>';
                    } else {
                        echo '<form method="post" class="payment-methods">';
                        
                        foreach ($available_gateways as $gateway) {
                            echo '<div class="payment-method">';
                            echo '<input type="radio" name="payment_method" id="' . $gateway->id . '" value="' . $gateway->id . '" checked>';
                            echo '<label for="' . $gateway->id . '"><strong>' . $gateway->get_title() . '</strong></label>';
                            if ($gateway->get_description()) {
                                echo '<p style="margin: 5px 0 0 30px; color: #666; font-size: 14px;">' . $gateway->get_description() . '</p>';
                            }
                            echo '</div>';
                        }
                        
                        echo '<button type="submit" class="btn btn-green" style="width: 100%; font-size: 18px; padding: 15px; margin-top: 20px; border: none; cursor: pointer;">💳 Procedi al Pagamento</button>';
                        echo '</form>';
                    }
                } else {
                    echo '<div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0;">';
                    echo '⚠️ Ordine non pagabile: ';
                    if (!$order->needs_payment()) {
                        echo 'needs_payment() = false';
                    }
                    if (empty($items)) {
                        echo 'Nessun item nell\'ordine';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
            }
        }
        
        // Pulsanti azione
        if (!isset($_GET['create_order']) && !isset($_GET['simulate'])) {
            echo '<div class="section">';
            echo '<h2>🚀 Azioni</h2>';
            echo '<a href="?create_order=1" class="btn btn-green">1️⃣ Crea Ordine di Test</a>';
            echo '<a href="/wp-admin/admin.php?page=wc-settings&tab=checkout" class="btn">⚙️ Impostazioni Pagamenti WC</a>';
            echo '</div>';
        } else {
            echo '<div class="section">';
            echo '<a href="?" class="btn">🔄 Ricomincia Test</a>';
            echo '</div>';
        }
        ?>
        
        <p style="margin-top: 40px; color: #666; font-size: 12px; text-align: center;">
            Test Payment Simulation | <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
