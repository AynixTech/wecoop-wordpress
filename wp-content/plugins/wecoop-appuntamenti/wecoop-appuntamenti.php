<?php
/**
 * Plugin Name: WECOOP Appuntamenti
 * Description: Prenotazione appuntamenti fisici (stile Calendly) collegati alle richieste di servizio. L'operatore propone slot, l'utente sceglie giorno/ora dall'app.
 * Version: 1.0.0
 * Author: WECOOP
 * Text Domain: wecoop-appuntamenti
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

define('WECOOP_APPUNTAMENTI_VERSION', '1.0.0');
define('WECOOP_APPUNTAMENTI_PATH', plugin_dir_path(__FILE__));
define('WECOOP_APPUNTAMENTI_URL', plugin_dir_url(__FILE__));

require_once WECOOP_APPUNTAMENTI_PATH . 'includes/class-appuntamenti-repository.php';
require_once WECOOP_APPUNTAMENTI_PATH . 'includes/api/class-appuntamenti-endpoint.php';
require_once WECOOP_APPUNTAMENTI_PATH . 'includes/class-appuntamenti-notifications.php';

if (is_admin()) {
    require_once WECOOP_APPUNTAMENTI_PATH . 'includes/admin/class-appuntamenti-admin.php';
}

/**
 * Bootstrap del plugin.
 */
class WECOOP_Appuntamenti_Plugin {

    /**
     * Capability richiesta per gestire gli appuntamenti (operatore/admin).
     */
    const CAPABILITY = 'wecoop_appuntamenti_manage';

    /**
     * Nuovi stati richiesta introdotti dalla feature appuntamenti.
     */
    const STATO_AWAITING = 'awaiting_appointment';
    const STATO_CONFIRMED = 'appointment_confirmed';

    public static function init() {
        // Assicura schema DB aggiornato.
        add_action('plugins_loaded', ['WeCoop_Appuntamenti_Repository', 'maybe_upgrade']);

        // Consente agli operatori (wecoop_appuntamenti_manage) di aprire/salvare
        // l'editor del CPT richiesta_servizio, senza dare accesso agli altri post.
        add_filter('map_meta_cap', [__CLASS__, 'map_richiesta_cap'], 10, 4);

        // Endpoint REST.
        WECOOP_Appuntamenti_Endpoint::init();

        // Notifiche push + email.
        WeCoop_Appuntamenti_Notifications::init();

        // Back-office operatore.
        if (is_admin() && class_exists('WeCoop_Appuntamenti_Admin')) {
            WeCoop_Appuntamenti_Admin::init();
        }
    }

    /**
     * Attivazione plugin: crea tabelle, ruolo operatore e capability.
     */
    public static function on_activation() {
        WeCoop_Appuntamenti_Repository::install();
        self::ensure_operator_role();
        self::add_capabilities();

        // Valore di default per il limite di riprogrammazione (in ore).
        if (get_option('wecoop_appuntamenti_reschedule_limit_hours', null) === null) {
            update_option('wecoop_appuntamenti_reschedule_limit_hours', 24);
        }
    }

    /**
     * Crea il ruolo 'operator' se non esiste (clonando le capability di subscriber).
     * I plugin wecoop assumono l'esistenza del ruolo ma non lo creano.
     */
    public static function ensure_operator_role() {
        if (get_role('operator')) {
            return;
        }
        $subscriber = get_role('subscriber');
        $caps = $subscriber ? $subscriber->capabilities : ['read' => true];
        add_role('operator', 'Operatore WECOOP', $caps);
    }

    /**
     * Aggiunge la capability di gestione appuntamenti ai ruoli operator e administrator.
     */
    public static function add_capabilities() {
        foreach (['administrator', 'operator'] as $role_name) {
            $role = get_role($role_name);
            if ($role && !$role->has_cap(self::CAPABILITY)) {
                $role->add_cap(self::CAPABILITY);
            }
        }
    }

    /**
     * Timezone del sito (per interpretare/formattare gli orari degli slot).
     */
    public static function timezone() {
        return wp_timezone();
    }

    /**
     * Concede a chi ha la capability appuntamenti il permesso di
     * modificare/leggere i post del CPT richiesta_servizio (che usa
     * capability_type 'post'), senza esporre gli altri contenuti.
     */
    public static function map_richiesta_cap($caps, $cap, $user_id, $args) {
        $target_caps = [
            'edit_post', 'edit_posts', 'edit_others_posts', 'edit_published_posts',
            'read_post', 'publish_posts',
        ];
        if (!in_array($cap, $target_caps, true)) {
            return $caps;
        }

        // Gli admin passano dai controlli standard.
        if (user_can($user_id, 'manage_options')) {
            return $caps;
        }
        if (!user_can($user_id, self::CAPABILITY)) {
            return $caps;
        }

        // Se il cap riguarda uno specifico post, deve essere una richiesta_servizio.
        if (!empty($args[0])) {
            $post = get_post((int) $args[0]);
            if (!$post || $post->post_type !== 'richiesta_servizio') {
                return $caps;
            }
        }

        // Concedi il permesso mappandolo alla capability appuntamenti.
        return [self::CAPABILITY];
    }
}

register_activation_hook(__FILE__, ['WECOOP_Appuntamenti_Plugin', 'on_activation']);

WECOOP_Appuntamenti_Plugin::init();
