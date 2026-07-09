<?php
/**
 * Plugin Name: WeCoop API Key Auth
 * Plugin URI: https://www.wecoop.org
 * Description: Autenticazione REST tramite API Key per client esterni (es. HubWeCoop). Gestione di chiavi per azienda con attivazione/disattivazione. Non modifica i plugin wecoop-* esistenti.
 * Version: 1.0.0
 * Author: AynixTech / WeCoop
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-apikey-auth
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WECOOP_APIKEY_VERSION', '1.0.0' );
define( 'WECOOP_APIKEY_FILE', __FILE__ );
define( 'WECOOP_APIKEY_DIR', plugin_dir_path( __FILE__ ) );
define( 'WECOOP_APIKEY_INCLUDES', WECOOP_APIKEY_DIR . 'includes/' );

require_once WECOOP_APIKEY_INCLUDES . 'class-apikey-store.php';
require_once WECOOP_APIKEY_INCLUDES . 'class-apikey-auth.php';
require_once WECOOP_APIKEY_INCLUDES . 'class-apikey-admin.php';

/**
 * Autenticazione: si aggancia molto presto per intercettare le richieste REST.
 */
WeCoop_ApiKey_Auth::init();

/**
 * Amministrazione: pagina di gestione chiavi.
 */
if ( is_admin() ) {
    WeCoop_ApiKey_Admin::init();
}

register_activation_hook( __FILE__, array( 'WeCoop_ApiKey_Store', 'on_activation' ) );
