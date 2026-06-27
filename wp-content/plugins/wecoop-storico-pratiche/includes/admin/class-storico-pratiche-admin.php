<?php
/**
 * Back-office: gestione documenti storico pratiche.
 * - Pagina admin dedicata (carica documento per un cliente, elenco recenti).
 * - Sezione nella schermata di modifica utente.
 * - Handler upload / delete / download protetti da capability + nonce.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Storico_Pratiche_Admin {

    const MENU_SLUG = 'wecoop-storico-pratiche';
    const CAP = 'wecoop_storico_pratiche_manage';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_post_wecoop_pratiche_upload', [__CLASS__, 'handle_upload']);
        add_action('admin_post_wecoop_pratiche_delete', [__CLASS__, 'handle_delete']);
        add_action('admin_post_wecoop_pratiche_download', [__CLASS__, 'handle_download']);

        // Sezione nella scheda utente.
        add_action('show_user_profile', [__CLASS__, 'render_user_section']);
        add_action('edit_user_profile', [__CLASS__, 'render_user_section']);
    }

    private static function can_manage() {
        return current_user_can(self::CAP) || current_user_can('manage_options');
    }

    public static function register_menu() {
        if (!self::can_manage()) {
            return;
        }

        add_menu_page(
            'Storico Pratiche',
            'Storico Pratiche',
            self::CAP,
            self::MENU_SLUG,
            [__CLASS__, 'render_page'],
            'dashicons-portfolio',
            27
        );
    }

    /* --------------------------------------------------------------------- */
    /*  Handlers                                                             */
    /* --------------------------------------------------------------------- */

    public static function handle_upload() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        check_admin_referer('wecoop_pratiche_upload');

        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $tipo    = isset($_POST['tipo']) ? sanitize_key($_POST['tipo']) : '';
        $anno    = isset($_POST['anno']) && $_POST['anno'] !== '' ? (int) $_POST['anno'] : null;
        $titolo  = isset($_POST['titolo']) ? sanitize_text_field(wp_unslash($_POST['titolo'])) : '';
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : '';

        $tipi = wecoop_storico_pratiche_tipi();

        if (!get_userdata($user_id)) {
            self::redirect_back($redirect_to, $user_id, 'error', 'Utente non valido.');
        }
        if (!isset($tipi[$tipo])) {
            self::redirect_back($redirect_to, $user_id, 'error', 'Tipo documento non valido.');
        }
        if (empty($_FILES['documento']) || empty($_FILES['documento']['name'])) {
            self::redirect_back($redirect_to, $user_id, 'error', 'Nessun file selezionato.');
        }

        $stored = WeCoop_Storico_Pratiche_Storage::store_upload($_FILES['documento'], $user_id, $tipo);
        if (is_wp_error($stored)) {
            self::redirect_back($redirect_to, $user_id, 'error', $stored->get_error_message());
        }

        $id = WeCoop_Storico_Pratiche_Repository::insert([
            'user_id'   => $user_id,
            'tipo'      => $tipo,
            'anno'      => $anno,
            'titolo'    => $titolo !== '' ? $titolo : $tipi[$tipo] . ($anno ? ' ' . $anno : ''),
            'file_name' => $stored['file_name'],
            'file_path' => $stored['file_path'],
            'file_size' => $stored['file_size'],
            'mime_type' => $stored['mime_type'],
        ]);

        if (!$id) {
            // rollback file
            WeCoop_Storico_Pratiche_Storage::delete_file($stored['file_path']);
            self::redirect_back($redirect_to, $user_id, 'error', 'Errore nel salvataggio del documento.');
        }

        self::redirect_back($redirect_to, $user_id, 'uploaded', 'Documento caricato correttamente.');
    }

    public static function handle_delete() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }
        check_admin_referer('wecoop_pratiche_delete');

        $doc_id = isset($_POST['doc_id']) ? (int) $_POST['doc_id'] : 0;
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : '';

        $doc = WeCoop_Storico_Pratiche_Repository::get($doc_id);
        if (!$doc) {
            self::redirect_back($redirect_to, 0, 'error', 'Documento non trovato.');
        }

        WeCoop_Storico_Pratiche_Storage::delete_file($doc['file_path']);
        WeCoop_Storico_Pratiche_Repository::delete($doc_id);

        self::redirect_back($redirect_to, (int) $doc['user_id'], 'deleted', 'Documento eliminato.');
    }

    /**
     * Download lato operatore (streaming protetto da capability + nonce).
     */
    public static function handle_download() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }

        $doc_id = isset($_GET['doc_id']) ? (int) $_GET['doc_id'] : 0;
        if (!$doc_id || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'wecoop_pratiche_download_' . $doc_id)) {
            wp_die('Richiesta non valida');
        }

        $doc = WeCoop_Storico_Pratiche_Repository::get($doc_id);
        if (!$doc) {
            wp_die('Documento non trovato');
        }

        self::stream_file($doc);
    }

    /**
     * Streaming binario del file con header corretti.
     */
    public static function stream_file(array $doc) {
        $path = WeCoop_Storico_Pratiche_Storage::absolute_path($doc['file_path']);
        if (!is_file($path)) {
            wp_die('File non disponibile');
        }

        $mime = $doc['mime_type'] ?: 'application/octet-stream';
        $download_name = $doc['file_name'] ?: basename($path);

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

    private static function redirect_back($redirect_to, $user_id, $status, $message) {
        if ($redirect_to !== '') {
            $url = add_query_arg([
                'wecoop_pratiche_status' => $status,
                'wecoop_pratiche_msg'    => rawurlencode($message),
            ], $redirect_to);
        } else {
            $url = add_query_arg([
                'page'                   => self::MENU_SLUG,
                'user_id'                => $user_id,
                'wecoop_pratiche_status' => $status,
                'wecoop_pratiche_msg'    => rawurlencode($message),
            ], admin_url('admin.php'));
        }
        wp_safe_redirect($url);
        exit;
    }

    /* --------------------------------------------------------------------- */
    /*  Rendering                                                            */
    /* --------------------------------------------------------------------- */

    public static function render_page() {
        if (!self::can_manage()) {
            wp_die('Permessi insufficienti');
        }

        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        $user = $user_id ? get_userdata($user_id) : null;
        $status = isset($_GET['wecoop_pratiche_status']) ? sanitize_key($_GET['wecoop_pratiche_status']) : '';
        $msg = isset($_GET['wecoop_pratiche_msg']) ? rawurldecode((string) $_GET['wecoop_pratiche_msg']) : '';

        echo '<div class="wrap">';
        echo '<h1>Storico Pratiche</h1>';
        echo '<p>Carica i documenti (730, ISEE, ...) e associali al cliente. Il cliente li trovera\' nella sezione "Storico pratiche" dell\'app.</p>';

        if ($status === 'uploaded' || $status === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        } elseif ($status === 'error') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        }

        echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;max-width:760px;margin-top:16px;">';
        echo '<h2 style="margin-top:0;">Seleziona cliente</h2>';
        echo '<form method="get" action="' . esc_url(admin_url('admin.php')) . '">';
        echo '<input type="hidden" name="page" value="' . esc_attr(self::MENU_SLUG) . '">';
        wp_dropdown_users([
            'name' => 'user_id',
            'selected' => $user_id,
            'show_option_none' => '— Seleziona un cliente —',
            'option_none_value' => 0,
            'show' => 'display_name_with_login',
        ]);
        echo ' <button class="button button-primary">Apri scheda</button>';
        echo '</form>';
        echo '</div>';

        if ($user) {
            $self_url = add_query_arg(['page' => self::MENU_SLUG, 'user_id' => $user_id], admin_url('admin.php'));
            echo '<h2 style="margin-top:28px;">Documenti di ' . esc_html($user->display_name) . ' <small>(' . esc_html($user->user_email) . ')</small></h2>';
            self::render_upload_form($user_id, $self_url);
            self::render_documents_table($user_id, $self_url);
        } else {
            echo '<h2 style="margin-top:28px;">Documenti recenti</h2>';
            echo '<p>Totale documenti archiviati: <strong>' . esc_html((string) WeCoop_Storico_Pratiche_Repository::count_all()) . '</strong></p>';
        }

        echo '</div>';
    }

    /**
     * Sezione nella schermata edit-user.
     */
    public static function render_user_section($user) {
        if (!self::can_manage()) {
            return;
        }

        $user_id = (int) $user->ID;
        $redirect_to = get_edit_user_link($user_id);

        echo '<h2 id="wecoop-storico-pratiche">Storico Pratiche (WeCoop)</h2>';

        $status = isset($_GET['wecoop_pratiche_status']) ? sanitize_key($_GET['wecoop_pratiche_status']) : '';
        $message = isset($_GET['wecoop_pratiche_msg']) ? rawurldecode((string) $_GET['wecoop_pratiche_msg']) : '';
        if ($status === 'uploaded' || $status === 'deleted') {
            echo '<div class="notice notice-success inline"><p>' . esc_html($message) . '</p></div>';
        } elseif ($status === 'error') {
            echo '<div class="notice notice-error inline"><p>' . esc_html($message) . '</p></div>';
        }

        self::render_upload_form($user_id, $redirect_to);
        self::render_documents_table($user_id, $redirect_to);
    }

    private static function render_upload_form($user_id, $redirect_to) {
        $tipi = wecoop_storico_pratiche_tipi();
        $current_year = (int) gmdate('Y');
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px;margin:12px 0;max-width:760px;">
            <?php wp_nonce_field('wecoop_pratiche_upload'); ?>
            <input type="hidden" name="action" value="wecoop_pratiche_upload">
            <input type="hidden" name="user_id" value="<?php echo esc_attr((string) $user_id); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">

            <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;">
                <label>Tipo documento<br>
                    <select name="tipo" required>
                        <?php foreach ($tipi as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Anno<br>
                    <input type="number" name="anno" value="<?php echo esc_attr((string) $current_year); ?>" min="2000" max="2100" step="1" style="width:100px;">
                </label>
                <label>Titolo (opzionale)<br>
                    <input type="text" name="titolo" placeholder="Es. 730/2024" style="width:220px;">
                </label>
                <label>File (PDF/JPG/PNG, max 15MB)<br>
                    <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png" required>
                </label>
                <button type="submit" class="button button-primary">Carica documento</button>
            </div>
        </form>
        <?php
    }

    private static function render_documents_table($user_id, $redirect_to) {
        $docs = WeCoop_Storico_Pratiche_Repository::get_by_user($user_id);
        $tipi = wecoop_storico_pratiche_tipi();

        echo '<table class="widefat striped" style="max-width:900px;">';
        echo '<thead><tr><th>Tipo</th><th>Anno</th><th>Titolo</th><th>File</th><th>Caricato il</th><th>Azioni</th></tr></thead><tbody>';

        if (empty($docs)) {
            echo '<tr><td colspan="6">Nessun documento per questo cliente.</td></tr>';
        } else {
            foreach ($docs as $doc) {
                $download_url = wp_nonce_url(
                    add_query_arg([
                        'action' => 'wecoop_pratiche_download',
                        'doc_id' => (int) $doc['id'],
                    ], admin_url('admin-post.php')),
                    'wecoop_pratiche_download_' . (int) $doc['id']
                );

                echo '<tr>';
                echo '<td>' . esc_html($tipi[$doc['tipo']] ?? $doc['tipo']) . '</td>';
                echo '<td>' . esc_html($doc['anno'] ?: '-') . '</td>';
                echo '<td>' . esc_html($doc['titolo'] ?: '-') . '</td>';
                echo '<td>' . esc_html($doc['file_name']) . ' <small>(' . esc_html(size_format((int) $doc['file_size'])) . ')</small></td>';
                echo '<td>' . esc_html(mysql2date(get_option('date_format') . ' H:i', $doc['data_caricamento'])) . '</td>';
                echo '<td>';
                echo '<a class="button button-small" href="' . esc_url($download_url) . '">Scarica</a> ';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline;" onsubmit="return confirm(\'Eliminare definitivamente questo documento?\');">';
                wp_nonce_field('wecoop_pratiche_delete');
                echo '<input type="hidden" name="action" value="wecoop_pratiche_delete">';
                echo '<input type="hidden" name="doc_id" value="' . esc_attr((string) $doc['id']) . '">';
                echo '<input type="hidden" name="redirect_to" value="' . esc_attr($redirect_to) . '">';
                echo '<button type="submit" class="button button-small button-link-delete">Elimina</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
    }
}
