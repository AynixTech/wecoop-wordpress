<?php
/**
 * Script per creare la tabella wp_wecoop_pagamenti
 * 
 * Esegui con: wp eval-file create-payments-table.php
 */

global $wpdb;
$table_name = $wpdb->prefix . 'wecoop_pagamenti';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    richiesta_id bigint(20) NOT NULL,
    user_id bigint(20) NOT NULL,
    importo decimal(10,2) NOT NULL,
    stato varchar(50) NOT NULL DEFAULT 'pending',
    metodo_pagamento varchar(50),
    transaction_id varchar(255),
    stripe_payment_intent_id varchar(255),
    note text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    paid_at datetime,
    PRIMARY KEY  (id),
    KEY richiesta_id (richiesta_id),
    KEY user_id (user_id),
    KEY stato (stato),
    KEY transaction_id (transaction_id),
    KEY stripe_payment_intent_id (stripe_payment_intent_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

echo "✅ Tabella $table_name creata con successo!\n";

// Verifica
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
if ($table_exists) {
    echo "✅ Verifica: tabella esistente\n";
    
    // Mostra struttura
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    echo "\nStruttura tabella:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} else {
    echo "❌ Errore: tabella non trovata dopo creazione\n";
}
