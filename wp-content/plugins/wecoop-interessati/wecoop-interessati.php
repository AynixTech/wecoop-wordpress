<?php
/**
 * Plugin Name: WeCoop Interessati
 * Plugin URI: https://www.wecoop.org
 * Description: Traccia gli utenti interessati a corsi, opportunita ed eventi e fornisce contatori via REST API.
 * Version: 1.0.0
 * Author: WeCoop Team
 * Author URI: https://www.wecoop.org
 * Text Domain: wecoop-interessati
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Interessati_Plugin {
    private const TABLE_SUFFIX = 'wecoop_interessati';

    public static function init(): void {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
        add_action('admin_menu', [__CLASS__, 'register_admin_page']);
    }

    public static function activate(): void {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            item_key VARCHAR(191) NOT NULL,
            item_title VARCHAR(255) NOT NULL DEFAULT '',
            item_type VARCHAR(50) NOT NULL DEFAULT 'corso',
            source VARCHAR(50) NOT NULL DEFAULT 'app',
            identity_key VARCHAR(191) NOT NULL,
            user_id BIGINT(20) UNSIGNED NULL,
            interested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY uniq_item_identity (item_key, identity_key),
            KEY idx_item_key (item_key),
            KEY idx_item_type (item_type),
            KEY idx_interested_at (interested_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function register_routes(): void {
        register_rest_route('wecoop/v1', '/interessati/register', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [__CLASS__, 'register_interest'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('wecoop/v1', '/interessati/count', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [__CLASS__, 'get_interest_count'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function register_admin_page(): void {
        add_menu_page(
            'WeCoop Interessati',
            'WeCoop Interessati',
            'manage_options',
            'wecoop-interessati',
            [__CLASS__, 'render_admin_page'],
            'dashicons-groups',
            58
        );
    }

    public static function render_admin_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;

        $rows = $wpdb->get_results(
            "SELECT item_key, item_title, item_type, COUNT(*) AS total_interessati, MAX(updated_at) AS last_update
             FROM {$table_name}
             GROUP BY item_key, item_title, item_type
             ORDER BY total_interessati DESC, last_update DESC
             LIMIT 500",
            ARRAY_A
        );

        echo '<div class="wrap">';
        echo '<h1>WeCoop Interessati</h1>';
        echo '<p>Contatore interessati per corsi, opportunita ed eventi.</p>';

        if (empty($rows)) {
            echo '<p>Nessun interesse registrato al momento.</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>Elemento</th>';
        echo '<th>Tipo</th>';
        echo '<th>Chiave</th>';
        echo '<th>Interessati</th>';
        echo '<th>Ultimo aggiornamento</th>';
        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['item_title']) . '</td>';
            echo '<td>' . esc_html($row['item_type']) . '</td>';
            echo '<td><code>' . esc_html($row['item_key']) . '</code></td>';
            echo '<td><strong>' . esc_html((string) $row['total_interessati']) . '</strong></td>';
            echo '<td>' . esc_html($row['last_update']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    public static function register_interest(WP_REST_Request $request) {
        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = $request->get_params();
        }

        $item_key = sanitize_title((string) ($params['item_key'] ?? ''));
        $item_title = sanitize_text_field((string) ($params['item_title'] ?? ''));
        $item_type = sanitize_text_field((string) ($params['item_type'] ?? 'corso'));
        $source = sanitize_text_field((string) ($params['source'] ?? 'app'));

        if ($item_key === '') {
            return new WP_Error('invalid_item_key', 'item_key obbligatorio', ['status' => 400]);
        }

        $user_id = get_current_user_id();
        $identity_key = self::resolve_identity_key($user_id, $request);

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;

        $existing_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE item_key = %s AND identity_key = %s LIMIT 1",
                $item_key,
                $identity_key
            )
        );

        $already_registered = !empty($existing_id);

        if ($already_registered) {
            $wpdb->update(
                $table_name,
                [
                    'item_title' => $item_title,
                    'item_type' => $item_type,
                    'source' => $source,
                    'user_id' => $user_id > 0 ? $user_id : null,
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => (int) $existing_id],
                ['%s', '%s', '%s', '%d', '%s'],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $table_name,
                [
                    'item_key' => $item_key,
                    'item_title' => $item_title,
                    'item_type' => $item_type,
                    'source' => $source,
                    'identity_key' => $identity_key,
                    'user_id' => $user_id > 0 ? $user_id : null,
                    'interested_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ],
                ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
            );
        }

        $count = self::count_by_item_key($item_key);

        return new WP_REST_Response([
            'success' => true,
            'item_key' => $item_key,
            'already_registered' => $already_registered,
            'count' => $count,
        ], 200);
    }

    public static function get_interest_count(WP_REST_Request $request) {
        $item_key = sanitize_title((string) $request->get_param('item_key'));

        if ($item_key === '') {
            return new WP_Error('invalid_item_key', 'item_key obbligatorio', ['status' => 400]);
        }

        return new WP_REST_Response([
            'success' => true,
            'item_key' => $item_key,
            'count' => self::count_by_item_key($item_key),
        ], 200);
    }

    private static function count_by_item_key(string $item_key): int {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE item_key = %s",
                $item_key
            )
        );
    }

    private static function resolve_identity_key(int $user_id, WP_REST_Request $request): string {
        if ($user_id > 0) {
            return 'user:' . $user_id;
        }

        $forwarded_for = (string) $request->get_header('x_forwarded_for');
        $ip = '';
        if ($forwarded_for !== '') {
            $parts = explode(',', $forwarded_for);
            $ip = trim((string) ($parts[0] ?? ''));
        }

        if ($ip === '') {
            $ip = sanitize_text_field((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
        }

        $user_agent = sanitize_text_field((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown-agent'));
        return 'anon:' . md5($ip . '|' . $user_agent);
    }
}

WeCoop_Interessati_Plugin::init();
