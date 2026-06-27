<?php
/**
 * Plugin Name: WeCoop Storico Pratiche
 * Plugin URI: https://www.wecoop.org
 * Description: Archivio documentale per cliente (730, ISEE, ...). Gli operatori caricano i documenti dal back-office; il cliente li consulta e scarica dall'app tramite REST API protette.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-storico-pratiche
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WECOOP_STORICO_PRATICHE_VERSION', '1.0.0');
define('WECOOP_STORICO_PRATICHE_FILE', __FILE__);
define('WECOOP_STORICO_PRATICHE_DIR', plugin_dir_path(__FILE__));
define('WECOOP_STORICO_PRATICHE_URL', plugin_dir_url(__FILE__));

require_once WECOOP_STORICO_PRATICHE_DIR . 'includes/class-storico-pratiche-repository.php';
require_once WECOOP_STORICO_PRATICHE_DIR . 'includes/class-storico-pratiche-storage.php';
require_once WECOOP_STORICO_PRATICHE_DIR . 'includes/admin/class-storico-pratiche-admin.php';
require_once WECOOP_STORICO_PRATICHE_DIR . 'includes/api/class-storico-pratiche-endpoint.php';

/**
 * Tipi di documento supportati (estendibile in futuro).
 */
function wecoop_storico_pratiche_tipi() {
    return apply_filters('wecoop_storico_pratiche_tipi', [
        '730'  => 'Modello 730',
        'isee' => 'ISEE',
    ]);
}

/**
 * Attivazione: crea la tabella e la cartella protetta.
 */
function wecoop_storico_pratiche_activate() {
    WeCoop_Storico_Pratiche_Repository::install();
    WeCoop_Storico_Pratiche_Storage::ensure_protected_dir();

    foreach (['administrator', 'operator'] as $role_name) {
        $role = get_role($role_name);
        if ($role && !$role->has_cap('wecoop_storico_pratiche_manage')) {
            $role->add_cap('wecoop_storico_pratiche_manage');
        }
    }
}
register_activation_hook(__FILE__, 'wecoop_storico_pratiche_activate');

/**
 * Bootstrap.
 */
add_action('plugins_loaded', static function () {
    // Garantisce capability anche su installazioni gia' attive.
    add_action('init', static function () {
        foreach (['administrator', 'operator'] as $role_name) {
            $role = get_role($role_name);
            if ($role && !$role->has_cap('wecoop_storico_pratiche_manage')) {
                $role->add_cap('wecoop_storico_pratiche_manage');
            }
        }
    });

    // Garantisce lo schema aggiornato (in caso di upgrade senza riattivazione).
    WeCoop_Storico_Pratiche_Repository::maybe_upgrade();

    WeCoop_Storico_Pratiche_Admin::init();
    WeCoop_Storico_Pratiche_Endpoint::init();
});
