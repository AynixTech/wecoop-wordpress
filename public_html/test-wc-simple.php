<?php
/**
 * Test Semplice WooCommerce
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/wp-load.php');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test WooCommerce Simple</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test WooCommerce</h1>
    
    <?php
    
    echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
    echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
    
    if (class_exists('WooCommerce')) {
        echo '<p class="ok">✅ WooCommerce ATTIVO</p>';
        echo '<p>WooCommerce Version: ' . WC()->version . '</p>';
        
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        echo '<p>Payment Gateways: ' . count($gateways) . '</p>';
        
        if (!empty($gateways)) {
            echo '<ul>';
            foreach ($gateways as $gateway) {
                echo '<li>' . $gateway->get_title() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="error">❌ NESSUN METODO DI PAGAMENTO CONFIGURATO!</p>';
            echo '<p>Vai su WooCommerce → Impostazioni → Pagamenti e abilita almeno un metodo</p>';
        }
        
        // Test ordine più recente
        $orders = wc_get_orders(['limit' => 1]);
        if (!empty($orders)) {
            $order = $orders[0];
            echo '<h2>Ultimo ordine: #' . $order->get_id() . '</h2>';
            echo '<p>Totale: ' . $order->get_total() . ' €</p>';
            echo '<p>Items: ' . count($order->get_items()) . '</p>';
            
            $items = $order->get_items();
            if (!empty($items)) {
                echo '<ul>';
                foreach ($items as $item) {
                    echo '<li>' . $item->get_name() . ' - ' . $item->get_total() . ' €</li>';
                }
                echo '</ul>';
            }
            
            echo '<p><a href="' . $order->get_checkout_payment_url(true) . '">Link Pagamento</a></p>';
        }
        
    } else {
        echo '<p class="error">❌ WooCommerce NON ATTIVO</p>';
    }
    
    ?>
</body>
</html>
