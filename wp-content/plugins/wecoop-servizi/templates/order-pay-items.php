<?php
/**
 * Template override per mostrare items nella pagina order-pay
 * 
 * @package WECOOP_Servizi
 */

if (!defined('ABSPATH')) exit;

error_log('=== ORDER PAY TEMPLATE CARICATO ===');

// Forza visualizzazione form pagamento
add_action('woocommerce_before_pay_action', function($order) {
    error_log('[ORDER PAY] Hook woocommerce_before_pay_action chiamato');
}, 5);

// Forza rendering del form di pagamento
add_action('woocommerce_review_order_before_payment', function() {
    error_log('[ORDER PAY] Hook woocommerce_review_order_before_payment - Form pagamento in arrivo');
    
    // Aggiungi CSS inline per forzare visualizzazione su tutti i device
    echo '<style>
        #payment, .woocommerce-checkout-payment, #order_review {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: auto !important;
            overflow: visible !important;
        }
        
        .payment_methods {
            display: block !important;
            visibility: visible !important;
        }
        
        .wc_payment_method {
            display: list-item !important;
            visibility: visible !important;
        }
        
        @media (max-width: 768px) {
            #payment, .woocommerce-checkout-payment, #order_review {
                display: block !important;
            }
        }
    </style>';
}, 1);

add_action('woocommerce_review_order_after_payment', function() {
    error_log('[ORDER PAY] Hook woocommerce_review_order_after_payment - Form pagamento renderizzato');
}, 999);

// Questo file viene incluso quando si visualizza la pagina order-pay
// per assicurarsi che gli items vengano mostrati

add_action('woocommerce_order_details_before_order_table', function($order) {
    error_log('[ORDER PAY] Hook woocommerce_order_details_before_order_table chiamato');
    
    if (!$order) {
        error_log('[ORDER PAY] ERRORE: Ordine NULL');
        return;
    }
    
    error_log('[ORDER PAY] Order ID: ' . $order->get_id());
    error_log('[ORDER PAY] Order Status: ' . $order->get_status());
    error_log('[ORDER PAY] Order Total: ' . $order->get_total());
    
    $items = $order->get_items();
    
    error_log('[ORDER PAY] Items count: ' . count($items));
    
    if (empty($items)) {
        echo '<div class="woocommerce-notice woocommerce-notice--error">Nessun articolo trovato in questo ordine.</div>';
        error_log('[ORDER PAY] NESSUN ITEM TROVATO!');
        return;
    }
    
    error_log('[ORDER PAY] Rendering tabella items...');
    
    echo '<h2 style="margin-top: 20px;">Dettagli dell\'ordine</h2>';
    echo '<table class="shop_table order_details" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
    echo '<thead><tr style="background: #f8f8f8;">';
    echo '<th class="product-name" style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Prodotto</th>';
    echo '<th class="product-total" style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Totale</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    foreach ($items as $item_id => $item) {
        error_log('[ORDER PAY] Item: ' . $item->get_name() . ' - Total: ' . $item->get_total());
        echo '<tr class="order_item" style="border-bottom: 1px solid #eee;">';
        echo '<td class="product-name" style="padding: 12px;">';
        echo '<strong>' . esc_html($item->get_name()) . '</strong>';
        echo ' <strong class="product-quantity" style="color: #666;">× ' . esc_html($item->get_quantity()) . '</strong>';
        echo '</td>';
        echo '<td class="product-total" style="padding: 12px; text-align: right;">';
        echo wc_price($item->get_total());
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr style="background: #f8f8f8;">';
    echo '<th scope="row" style="padding: 12px; text-align: left; font-size: 16px;">Totale:</th>';
    echo '<td style="padding: 12px; text-align: right; font-size: 16px; font-weight: bold;">' . wc_price($order->get_total()) . '</td>';
    echo '</tr>';
    echo '</tfoot>';
    echo '</table>';
    
    error_log('[ORDER PAY] Tabella renderizzata con successo');
}, 5);

error_log('=== ORDER PAY TEMPLATE: Hook registrato ===');
