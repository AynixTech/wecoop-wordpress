<?php
/**
 * Test Debug Ordini WooCommerce
 * URL: https://www.wecoop.org/test-order-debug.php
 */

// Carica WordPress
require_once(__DIR__ . '/wp-load.php');

// Solo admin
if (!current_user_can('manage_options')) {
    die('Accesso negato');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Debug Ordini WooCommerce</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .section { background: #252526; padding: 15px; margin: 10px 0; border-left: 3px solid #007acc; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        h2 { color: #569cd6; }
        pre { background: #1e1e1e; padding: 10px; overflow-x: auto; }
        .btn { 
            display: inline-block;
            padding: 10px 20px; 
            background: #007acc; 
            color: white; 
            text-decoration: none; 
            margin: 10px 5px;
            border-radius: 3px;
        }
        .btn:hover { background: #005a9e; }
    </style>
</head>
<body>
    <h1>🔍 Test Debug Ordini WooCommerce - WeCoop Servizi</h1>
    
    <?php
    
    // Test 1: Verifica WooCommerce
    echo '<div class="section">';
    echo '<h2>1️⃣ Verifica WooCommerce</h2>';
    if (class_exists('WooCommerce')) {
        echo '<p class="success">✅ WooCommerce è attivo</p>';
        global $woocommerce;
        echo '<p>Versione: ' . $woocommerce->version . '</p>';
    } else {
        echo '<p class="error">❌ WooCommerce NON è attivo</p>';
    }
    echo '</div>';
    
    // Test 2: Verifica tabelle database
    echo '<div class="section">';
    echo '<h2>2️⃣ Verifica Tabelle Database</h2>';
    global $wpdb;
    
    $tables_to_check = [
        'woocommerce_order_items',
        'woocommerce_order_itemmeta',
        'posts',
    ];
    
    foreach ($tables_to_check as $table) {
        $table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo '<p class="success">✅ ' . $table_name . ' (' . $count . ' record)</p>';
        } else {
            echo '<p class="error">❌ ' . $table_name . ' NON ESISTE</p>';
        }
    }
    echo '</div>';
    
    // Test 3: Ultimi ordini
    echo '<div class="section">';
    echo '<h2>3️⃣ Ultimi 5 Ordini WooCommerce</h2>';
    
    $orders = wc_get_orders([
        'limit' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    
    if (empty($orders)) {
        echo '<p class="warning">⚠️ Nessun ordine trovato</p>';
    } else {
        foreach ($orders as $order) {
            echo '<div style="border-left: 3px solid #4ec9b0; padding-left: 10px; margin: 10px 0;">';
            echo '<p><strong>Ordine #' . $order->get_id() . '</strong> - ' . $order->get_status() . '</p>';
            echo '<p>Data: ' . $order->get_date_created()->format('d/m/Y H:i') . '</p>';
            echo '<p>Totale: ' . $order->get_total() . ' €</p>';
            echo '<p>Items: ' . count($order->get_items()) . '</p>';
            
            $items = $order->get_items();
            if (!empty($items)) {
                echo '<ul>';
                foreach ($items as $item) {
                    echo '<li>' . $item->get_name() . ' - ' . $item->get_total() . ' €</li>';
                }
                echo '</ul>';
            } else {
                echo '<p class="error">❌ NESSUN ITEM in questo ordine!</p>';
            }
            
            $created_via = $order->get_meta('_created_via');
            if ($created_via === 'wecoop_servizi') {
                echo '<p class="success">✅ Creato da WeCoop Servizi</p>';
                $richiesta_id = $order->get_meta('_wecoop_richiesta_id');
                echo '<p>Richiesta ID: ' . $richiesta_id . '</p>';
            }
            
            echo '<p><a href="' . $order->get_checkout_payment_url(true) . '" class="btn" target="_blank">🔗 Link Pagamento</a></p>';
            echo '</div>';
        }
    }
    echo '</div>';
    
    // Test 4: Items database diretti
    echo '<div class="section">';
    echo '<h2>4️⃣ Items Database (Ultimi 10)</h2>';
    
    $items_db = $wpdb->get_results("
        SELECT oi.order_item_id, oi.order_id, oi.order_item_name, oi.order_item_type,
               (SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = oi.order_item_id AND meta_key = '_line_total' LIMIT 1) as total
        FROM {$wpdb->prefix}woocommerce_order_items oi
        ORDER BY oi.order_item_id DESC
        LIMIT 10
    ");
    
    if (empty($items_db)) {
        echo '<p class="warning">⚠️ Nessun item trovato nel database</p>';
    } else {
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background: #007acc; color: white;">';
        echo '<th style="padding: 8px; text-align: left;">Item ID</th>';
        echo '<th style="padding: 8px; text-align: left;">Order ID</th>';
        echo '<th style="padding: 8px; text-align: left;">Nome</th>';
        echo '<th style="padding: 8px; text-align: left;">Tipo</th>';
        echo '<th style="padding: 8px; text-align: left;">Totale</th>';
        echo '</tr>';
        foreach ($items_db as $item) {
            echo '<tr style="border-bottom: 1px solid #444;">';
            echo '<td style="padding: 8px;">' . $item->order_item_id . '</td>';
            echo '<td style="padding: 8px;">' . $item->order_id . '</td>';
            echo '<td style="padding: 8px;">' . $item->order_item_name . '</td>';
            echo '<td style="padding: 8px;">' . $item->order_item_type . '</td>';
            echo '<td style="padding: 8px;">' . ($item->total ?? 'N/A') . ' €</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    echo '</div>';
    
    // Test 5: Crea ordine test
    if (isset($_GET['create_test'])) {
        echo '<div class="section">';
        echo '<h2>5️⃣ Test Creazione Ordine</h2>';
        
        try {
            // Crea ordine test
            $test_order = wc_create_order([
                'customer_id' => get_current_user_id(),
                'status' => 'pending'
            ]);
            
            if (is_wp_error($test_order)) {
                throw new Exception($test_order->get_error_message());
            }
            
            echo '<p class="success">✅ Ordine creato: #' . $test_order->get_id() . '</p>';
            
            // Aggiungi item manualmente
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_order_items',
                [
                    'order_item_name' => 'Test Servizio - Pratica TEST123',
                    'order_item_type' => 'line_item',
                    'order_id' => $test_order->get_id()
                ],
                ['%s', '%s', '%d']
            );
            
            $item_id = $wpdb->insert_id;
            echo '<p class="success">✅ Item creato: #' . $item_id . '</p>';
            
            if ($wpdb->last_error) {
                echo '<p class="error">❌ Errore DB: ' . $wpdb->last_error . '</p>';
            }
            
            // Aggiungi meta
            wc_add_order_item_meta($item_id, '_qty', 1);
            wc_add_order_item_meta($item_id, '_line_subtotal', 15.00);
            wc_add_order_item_meta($item_id, '_line_total', 15.00);
            wc_add_order_item_meta($item_id, '_line_subtotal_tax', 0);
            wc_add_order_item_meta($item_id, '_line_tax', 0);
            
            echo '<p class="success">✅ Meta dati aggiunti</p>';
            
            // Imposta totale
            $test_order->set_total(15.00);
            $test_order->save();
            
            // Ricarica e verifica
            $test_order = wc_get_order($test_order->get_id());
            $items_count = count($test_order->get_items());
            
            echo '<p><strong>Verifica:</strong></p>';
            echo '<p>Items count: ' . $items_count . '</p>';
            echo '<p>Totale: ' . $test_order->get_total() . ' €</p>';
            
            if ($items_count > 0) {
                echo '<p class="success">✅✅✅ ORDINE TEST CREATO CON SUCCESSO!</p>';
                echo '<p><a href="' . $test_order->get_checkout_payment_url(true) . '" class="btn" target="_blank">🔗 Vai al Pagamento</a></p>';
            } else {
                echo '<p class="error">❌ Nessun item trovato dopo il reload</p>';
            }
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Errore: ' . $e->getMessage() . '</p>';
        }
        
        echo '</div>';
    }
    
    ?>
    
    <div class="section">
        <h2>🧪 Azioni Test</h2>
        <a href="?create_test=1" class="btn">🚀 Crea Ordine di Test</a>
        <a href="?" class="btn">🔄 Ricarica Pagina</a>
        <a href="/wp-admin/admin.php?page=wecoop-servizi" class="btn">📋 Gestione Servizi</a>
    </div>
    
    <p style="margin-top: 30px; color: #858585; font-size: 12px;">
        File: /test-order-debug.php | <?php echo date('d/m/Y H:i:s'); ?>
    </p>
</body>
</html>
