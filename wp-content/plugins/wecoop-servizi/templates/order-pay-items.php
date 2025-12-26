<?php
/**
 * Template override per mostrare items nella pagina order-pay
 * 
 * @package WECOOP_Servizi
 */

if (!defined('ABSPATH')) exit;

// Questo file viene incluso quando si visualizza la pagina order-pay
// per assicurarsi che gli items vengano mostrati

add_action('woocommerce_order_details_before_order_table', function($order) {
    if (!$order) return;
    
    $items = $order->get_items();
    
    if (empty($items)) {
        echo '<div class="woocommerce-notice woocommerce-notice--error">Nessun articolo trovato in questo ordine.</div>';
        return;
    }
    
    echo '<h2>Dettagli dell\'ordine</h2>';
    echo '<table class="shop_table order_details">';
    echo '<thead><tr>';
    echo '<th class="product-name">Prodotto</th>';
    echo '<th class="product-total">Totale</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($items as $item_id => $item) {
        echo '<tr class="order_item">';
        echo '<td class="product-name">';
        echo '<strong>' . esc_html($item->get_name()) . '</strong>';
        echo ' <strong class="product-quantity">× ' . esc_html($item->get_quantity()) . '</strong>';
        echo '</td>';
        echo '<td class="product-total">';
        echo wc_price($item->get_total());
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr>';
    echo '<th scope="row">Totale:</th>';
    echo '<td>' . wc_price($order->get_total()) . '</td>';
    echo '</tr>';
    echo '</tfoot>';
    echo '</table>';
}, 5);
