<?php
/**
 * Plugin Name: WeCoop Annunci
 * Description: Sistema di annunci per la comunità WeCoop. Concerti, ristoranti, eventi, servizi e molto altro.
 * Version: 1.0.0
 * Author: WeCoop
 * Text Domain: wecoop-annunci
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WECOOP_ANNUNCI_VERSION', '1.0.0' );
define( 'WECOOP_ANNUNCI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WECOOP_ANNUNCI_URL', plugin_dir_url( __FILE__ ) );

// Free tier: 3 giorni gratis, poi 1€/giorno
define( 'WECOOP_ANNUNCI_FREE_DAYS', 3 );
define( 'WECOOP_ANNUNCI_PRICE_PER_DAY', 1.00 );

require_once WECOOP_ANNUNCI_PATH . 'includes/class-annuncio-cpt.php';
require_once WECOOP_ANNUNCI_PATH . 'includes/class-annuncio-metabox.php';
require_once WECOOP_ANNUNCI_PATH . 'includes/class-annuncio-rest-api.php';
require_once WECOOP_ANNUNCI_PATH . 'includes/class-annuncio-publication.php';

new WECOOP_Annuncio_CPT();
new WECOOP_Annuncio_Metabox();
new WECOOP_Annuncio_REST_API();
new WECOOP_Annuncio_Publication();

// Attivazione plugin: crea tabella pagamenti
register_activation_hook( __FILE__, 'wecoop_annunci_activate' );
function wecoop_annunci_activate() {
    global $wpdb;
    $table = $wpdb->prefix . 'annunci_pagamenti';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        giorni_aggiuntivi INT NOT NULL DEFAULT 0,
        importo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        metodo VARCHAR(50) DEFAULT 'stripe',
        stato VARCHAR(20) DEFAULT 'pending',
        transaction_id VARCHAR(255) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id),
        KEY user_id (user_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
