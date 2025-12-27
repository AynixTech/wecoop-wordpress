<?php
/**
 * Migrazione: Aggiungi colonna receipt_url
 * 
 * Esegui questo file una volta per aggiungere la colonna receipt_url alla tabella wp_wecoop_pagamenti
 */

require_once(__DIR__ . '/../../../wp-load.php');

global $wpdb;

$table_name = $wpdb->prefix . 'wecoop_pagamenti';

// Verifica se la colonna esiste già
$column_exists = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = %s 
        AND TABLE_NAME = %s 
        AND COLUMN_NAME = 'receipt_url'",
        DB_NAME,
        $table_name
    )
);

if (empty($column_exists)) {
    $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `receipt_url` VARCHAR(500) NULL AFTER `transaction_id`";
    $result = $wpdb->query($sql);
    
    if ($result === false) {
        echo "❌ Errore: " . $wpdb->last_error . "\n";
    } else {
        echo "✅ Colonna receipt_url aggiunta con successo!\n";
    }
} else {
    echo "ℹ️  Colonna receipt_url già presente\n";
}

echo "\nCompletato!\n";
