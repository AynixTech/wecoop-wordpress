<?php
/**
 * REST API: Storico Pratiche (app Flutter)
 *
 * Base URL: /wp-json/wecoop/v1
 *  - GET  /pratiche/me                                Elenco documenti del cliente loggato
 *  - GET  /pratiche/me/documento/(?P<id>\d+)/download  Download protetto del documento
 *
 * Auth: JWT (Bearer) gia' risolto da determine_current_user; usiamo is_user_logged_in().
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Storico_Pratiche_Endpoint {

    const NS = 'wecoop/v1';

    public static function init() {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
            @ini_set('display_errors', 0);
            @error_reporting(E_ERROR | E_PARSE);
        }
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route(self::NS, '/pratiche/me', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_my_documents'],
            'permission_callback' => [__CLASS__, 'require_login'],
            'args'                => [
                'tipo' => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ],
        ]);

        register_rest_route(self::NS, '/pratiche/me/documento/(?P<id>\d+)/download', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'download_my_document'],
            'permission_callback' => [__CLASS__, 'require_login'],
            'args'                => [
                'id' => [
                    'required' => true,
                    'type'     => 'integer',
                ],
            ],
        ]);
    }

    public static function require_login() {
        if (is_user_logged_in() && get_current_user_id() > 0) {
            return true;
        }
        return new WP_Error('not_authenticated', 'Autenticazione richiesta.', ['status' => 401]);
    }

    /**
     * GET /pratiche/me — elenco documenti del cliente loggato.
     */
    public static function get_my_documents($request) {
        $user_id = get_current_user_id();
        $tipo = (string) $request->get_param('tipo');

        $rows = WeCoop_Storico_Pratiche_Repository::get_by_user($user_id, $tipo);
        $tipi = wecoop_storico_pratiche_tipi();

        $data = array_map(function ($row) use ($tipi) {
            return [
                'id'               => (int) $row['id'],
                'tipo'             => $row['tipo'],
                'tipo_label'       => $tipi[$row['tipo']] ?? $row['tipo'],
                'anno'             => $row['anno'] !== null ? (int) $row['anno'] : null,
                'titolo'           => $row['titolo'] ?: ($tipi[$row['tipo']] ?? $row['tipo']),
                'file_name'        => $row['file_name'],
                'file_size'        => (int) $row['file_size'],
                'mime_type'        => $row['mime_type'],
                'data_caricamento' => mysql_to_rfc3339($row['data_caricamento']),
                'download_url'     => rest_url(self::NS . '/pratiche/me/documento/' . (int) $row['id'] . '/download'),
            ];
        }, $rows);

        return rest_ensure_response([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /pratiche/me/documento/{id}/download — streaming protetto con ownership check.
     */
    public static function download_my_document($request) {
        $user_id = get_current_user_id();
        $doc_id = (int) $request->get_param('id');

        $doc = WeCoop_Storico_Pratiche_Repository::get($doc_id);
        if (!$doc) {
            return new WP_Error('not_found', 'Documento non trovato.', ['status' => 404]);
        }

        // Ownership: solo il proprietario o un admin.
        if ((int) $doc['user_id'] !== (int) $user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non sei autorizzato a scaricare questo documento.', ['status' => 403]);
        }

        $path = WeCoop_Storico_Pratiche_Storage::absolute_path($doc['file_path']);
        if (!is_file($path)) {
            return new WP_Error('file_missing', 'File non disponibile.', ['status' => 404]);
        }

        $mime = $doc['mime_type'] ?: 'application/octet-stream';
        $download_name = $doc['file_name'] ?: basename($path);

        // NB: per i binari NON usare WP_REST_Response (corrompe i byte).
        nocache_headers();
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($download_name) . '"');
        header('Content-Length: ' . filesize($path));

        if (ob_get_level()) {
            @ob_end_clean();
        }
        readfile($path);
        exit;
    }
}
