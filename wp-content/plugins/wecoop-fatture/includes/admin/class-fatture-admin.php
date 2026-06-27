<?php
/**
 * Admin: Backoffice Fatture WeCoop
 *
 * Elenca tutte le fatture/ricevute generate dai pagamenti e permette di:
 * - scaricare una singola fattura (PDF)
 * - scaricare in blocco le fatture (ZIP)
 * - generare on-demand la fattura mancante per un pagamento pagato/completato
 * - esportare l'elenco in CSV
 *
 * @package WeCoop_Fatture
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Fatture_Admin {

    const NONCE_ACTION = 'wecoop_fatture_action';
    const MENU_SLUG    = 'wecoop-fatture';

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_post_wecoop_download_fattura', [__CLASS__, 'handle_download_fattura']);
        add_action('admin_post_wecoop_download_fatture_bulk', [__CLASS__, 'handle_download_fatture_bulk']);
        add_action('admin_post_wecoop_export_fatture_csv', [__CLASS__, 'handle_export_csv']);
        add_action('admin_post_wecoop_genera_fattura', [__CLASS__, 'handle_genera_fattura']);
    }

    /**
     * Menu admin
     */
    public static function add_admin_menu() {
        add_menu_page(
            'WeCoop Fatture',
            'WeCoop Fatture',
            'manage_options',
            self::MENU_SLUG,
            [__CLASS__, 'render_page'],
            'dashicons-media-spreadsheet',
            31
        );
    }

    /**
     * Nome tabella pagamenti
     */
    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'wecoop_pagamenti';
    }

    /**
     * Recupera i pagamenti che possono avere una fattura (paid/completed),
     * applicando i filtri della pagina.
     *
     * @return array
     */
    private static function get_pagamenti($filtri = []) {
        global $wpdb;
        $table = self::table();

        $where  = ["stato IN ('paid','completed')"];
        $params = [];

        if (!empty($filtri['anno'])) {
            $where[]  = "YEAR(COALESCE(paid_at, created_at)) = %d";
            $params[] = intval($filtri['anno']);
        }

        if (isset($filtri['fattura']) && $filtri['fattura'] !== '') {
            if ($filtri['fattura'] === 'si') {
                $where[] = "(receipt_url IS NOT NULL AND receipt_url <> '')";
            } elseif ($filtri['fattura'] === 'no') {
                $where[] = "(receipt_url IS NULL OR receipt_url = '')";
            }
        }

        if (!empty($filtri['search'])) {
            $like = '%' . $wpdb->esc_like($filtri['search']) . '%';
            $where[]  = "(transaction_id LIKE %s OR id LIKE %s OR richiesta_id LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY COALESCE(paid_at, created_at) DESC";

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        $rows = $wpdb->get_results($sql);
        return is_array($rows) ? $rows : [];
    }

    /**
     * Anni disponibili per il filtro.
     */
    private static function get_anni_disponibili() {
        global $wpdb;
        $table = self::table();
        $anni = $wpdb->get_col(
            "SELECT DISTINCT YEAR(COALESCE(paid_at, created_at)) AS anno
             FROM {$table}
             WHERE stato IN ('paid','completed')
             ORDER BY anno DESC"
        );
        return array_filter(array_map('intval', (array) $anni));
    }

    /**
     * Dati utile da mostrare per ogni pagamento (nome cliente, servizio, pratica).
     */
    private static function get_richiesta_info($richiesta_id) {
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $nome    = trim((string) get_user_meta($user_id, 'nome', true) . ' ' . (string) get_user_meta($user_id, 'cognome', true));
        if ($nome === '') {
            $user = $user_id ? get_userdata($user_id) : null;
            $nome = $user ? $user->display_name : '—';
        }
        return [
            'cliente'        => $nome !== '' ? $nome : '—',
            'servizio'       => get_post_meta($richiesta_id, 'servizio', true) ?: '—',
            'numero_pratica' => get_post_meta($richiesta_id, 'numero_pratica', true) ?: '—',
        ];
    }

    /**
     * Pagina principale: elenco fatture
     */
    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'wecoop-fatture'));
        }

        $filtri = [
            'anno'    => isset($_GET['f_anno']) ? sanitize_text_field(wp_unslash($_GET['f_anno'])) : '',
            'fattura' => isset($_GET['f_fattura']) ? sanitize_text_field(wp_unslash($_GET['f_fattura'])) : '',
            'search'  => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
        ];

        $pagamenti = self::get_pagamenti($filtri);
        $anni      = self::get_anni_disponibili();
        $base_url  = admin_url('admin.php?page=' . self::MENU_SLUG);

        $tot         = count($pagamenti);
        $tot_con_pdf = 0;
        $tot_importo = 0.0;
        foreach ($pagamenti as $p) {
            if (!empty($p->receipt_url)) {
                $tot_con_pdf++;
            }
            $tot_importo += floatval($p->importo);
        }

        // Messaggi di stato (notice) da redirect
        $notice = isset($_GET['wecoop_notice']) ? sanitize_text_field(wp_unslash($_GET['wecoop_notice'])) : '';
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:10px;">
                <span class="dashicons dashicons-media-spreadsheet" style="font-size:28px;width:28px;height:28px;"></span>
                WeCoop Fatture
            </h1>
            <p style="color:#555;max-width:780px;">
                Elenco di tutte le fatture/ricevute generate dai pagamenti completati.
                Da qui puoi scaricare le singole fatture, scaricarle in blocco (ZIP) oppure esportare l'elenco in CSV.
            </p>

            <?php if ($notice === 'fattura_generata'): ?>
                <div class="notice notice-success is-dismissible"><p>✅ Fattura generata con successo.</p></div>
            <?php elseif ($notice === 'fattura_errore'): ?>
                <div class="notice notice-error is-dismissible"><p>❌ Impossibile generare la fattura. Controlla che il pagamento sia completato.</p></div>
            <?php elseif ($notice === 'bulk_vuoto'): ?>
                <div class="notice notice-warning is-dismissible"><p>⚠️ Nessuna fattura selezionata o nessun PDF disponibile.</p></div>
            <?php endif; ?>

            <!-- Riepilogo -->
            <div style="display:flex;gap:16px;flex-wrap:wrap;margin:16px 0;">
                <?php
                self::stat_card('Pagamenti completati', $tot, '#2271b1');
                self::stat_card('Fatture disponibili', $tot_con_pdf, '#00a32a');
                self::stat_card('Da generare', max(0, $tot - $tot_con_pdf), '#dba617');
                self::stat_card('Totale incassato', '€ ' . number_format($tot_importo, 2, ',', '.'), '#3c434a');
                ?>
            </div>

            <!-- Filtri -->
            <form method="get" style="margin:12px 0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::MENU_SLUG); ?>">
                <div>
                    <label style="display:block;font-weight:600;">Anno</label>
                    <select name="f_anno">
                        <option value="">Tutti</option>
                        <?php foreach ($anni as $anno): ?>
                            <option value="<?php echo esc_attr($anno); ?>" <?php selected($filtri['anno'], (string) $anno); ?>><?php echo esc_html($anno); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;">Fattura</label>
                    <select name="f_fattura">
                        <option value="" <?php selected($filtri['fattura'], ''); ?>>Tutte</option>
                        <option value="si" <?php selected($filtri['fattura'], 'si'); ?>>Disponibile</option>
                        <option value="no" <?php selected($filtri['fattura'], 'no'); ?>>Da generare</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:600;">Cerca (ID, pratica, transazione)</label>
                    <input type="text" name="s" value="<?php echo esc_attr($filtri['search']); ?>" placeholder="Cerca...">
                </div>
                <div>
                    <button type="submit" class="button button-primary">Filtra</button>
                    <a href="<?php echo esc_url($base_url); ?>" class="button">Reset</a>
                </div>
            </form>

            <!-- Azioni globali -->
            <div style="margin:10px 0;display:flex;gap:10px;flex-wrap:wrap;">
                <a class="button" href="<?php echo esc_url(self::action_url('wecoop_export_fatture_csv', $filtri)); ?>">
                    📊 Esporta CSV
                </a>
            </div>

            <!-- Tabella + bulk -->
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="wecoop_download_fatture_bulk">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>

                <div class="tablenav top">
                    <div class="alignleft actions">
                        <button type="submit" class="button button-primary">⬇️ Scarica selezionate (ZIP)</button>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="check-column"><input type="checkbox" onclick="jQuery('.wecoop-fattura-cb').prop('checked', this.checked);"></td>
                            <th>N. Fattura</th>
                            <th>Cliente</th>
                            <th>Servizio</th>
                            <th>Pratica</th>
                            <th>Importo</th>
                            <th>Stato pag.</th>
                            <th>Data</th>
                            <th>Fattura</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagamenti)): ?>
                            <tr><td colspan="10" style="text-align:center;padding:24px;color:#777;">Nessuna fattura trovata con i filtri selezionati.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pagamenti as $p):
                                $info = self::get_richiesta_info($p->richiesta_id);
                                $ts   = !empty($p->paid_at) ? strtotime($p->paid_at) : strtotime($p->created_at);
                                $anno = $ts ? date('Y', $ts) : date('Y');
                                $numero_fattura = $p->id . '/' . $anno;
                                $ha_pdf = !empty($p->receipt_url);
                            ?>
                            <tr>
                                <th class="check-column">
                                    <?php if ($ha_pdf): ?>
                                        <input type="checkbox" class="wecoop-fattura-cb" name="payment_ids[]" value="<?php echo esc_attr($p->id); ?>">
                                    <?php endif; ?>
                                </th>
                                <td><strong><?php echo esc_html($numero_fattura); ?></strong></td>
                                <td><?php echo esc_html($info['cliente']); ?></td>
                                <td><?php echo esc_html($info['servizio']); ?></td>
                                <td><?php echo esc_html($info['numero_pratica']); ?></td>
                                <td>€ <?php echo esc_html(number_format(floatval($p->importo), 2, ',', '.')); ?></td>
                                <td><?php echo esc_html($p->stato); ?></td>
                                <td><?php echo esc_html($ts ? date_i18n('d/m/Y', $ts) : '—'); ?></td>
                                <td>
                                    <?php if ($ha_pdf): ?>
                                        <span style="color:#00a32a;font-weight:600;">● Disponibile</span>
                                    <?php else: ?>
                                        <span style="color:#dba617;font-weight:600;">● Da generare</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ha_pdf): ?>
                                        <a class="button button-small button-primary"
                                           href="<?php echo esc_url(self::action_url('wecoop_download_fattura', ['payment_id' => $p->id])); ?>">
                                            ⬇️ Scarica
                                        </a>
                                    <?php else: ?>
                                        <a class="button button-small"
                                           href="<?php echo esc_url(self::action_url('wecoop_genera_fattura', ['payment_id' => $p->id])); ?>">
                                            ⚙️ Genera
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
        <?php
    }

    private static function stat_card($label, $value, $color) {
        ?>
        <div style="flex:1;min-width:170px;background:#fff;border:1px solid #e2e4e7;border-left:4px solid <?php echo esc_attr($color); ?>;border-radius:6px;padding:14px 16px;">
            <div style="font-size:12px;text-transform:uppercase;color:#777;letter-spacing:.4px;"><?php echo esc_html($label); ?></div>
            <div style="font-size:22px;font-weight:700;color:<?php echo esc_attr($color); ?>;margin-top:4px;"><?php echo esc_html($value); ?></div>
        </div>
        <?php
    }

    /**
     * Costruisce un URL verso admin-post.php con nonce.
     */
    private static function action_url($action, $args = []) {
        $url = add_query_arg(array_merge(['action' => $action], $args), admin_url('admin-post.php'));
        return wp_nonce_url($url, self::NONCE_ACTION);
    }

    /* ===================== HANDLER ===================== */

    private static function check_access() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'wecoop-fatture'));
        }
        check_admin_referer(self::NONCE_ACTION);
    }

    private static function redirect_to_list($notice = '') {
        $url = admin_url('admin.php?page=' . self::MENU_SLUG);
        if ($notice !== '') {
            $url = add_query_arg('wecoop_notice', $notice, $url);
        }
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Recupera il record pagamento.
     */
    private static function get_payment($payment_id) {
        global $wpdb;
        $table = self::table();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $payment_id));
    }

    /**
     * Converte un upload URL nel path filesystem locale.
     * Tiene conto di eventuali cambi di dominio/migrazioni.
     */
    private static function upload_url_to_path($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $upload_dir = wp_upload_dir();

        // Strategia 1: sostituzione baseurl -> basedir
        if (!empty($upload_dir['baseurl']) && strpos($url, $upload_dir['baseurl']) === 0) {
            $path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
            if (file_exists($path)) {
                return $path;
            }
        }

        // Strategia 2: usa la parte dopo /uploads/
        $needle = '/uploads/';
        $pos = strpos($url, $needle);
        if ($pos !== false) {
            $relative = substr($url, $pos + strlen($needle));
            $path = trailingslashit($upload_dir['basedir']) . ltrim($relative, '/');
            if (file_exists($path)) {
                return $path;
            }
        }

        // Strategia 3: fallback sul basename nella cartella fatture-wecoop
        $basename = basename(parse_url($url, PHP_URL_PATH) ?: $url);
        if ($basename !== '') {
            $path = trailingslashit($upload_dir['basedir']) . 'fatture-wecoop/' . $basename;
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    /**
     * Risolve il path locale del PDF della fattura per un pagamento,
     * generandolo se manca.
     *
     * @return string Path locale o '' se non disponibile.
     */
    private static function resolve_fattura_path($payment) {
        if (!$payment) {
            return '';
        }

        $receipt_url = isset($payment->receipt_url) ? trim((string) $payment->receipt_url) : '';

        if ($receipt_url !== '') {
            $path = self::upload_url_to_path($receipt_url);
            if ($path !== '') {
                return $path;
            }
        }

        // Genera al volo se manca o se il file non c'è più
        if (class_exists('WeCoop_Ricevuta_PDF')) {
            $result = WeCoop_Ricevuta_PDF::generate_ricevuta($payment->id);
            if (!empty($result['success'])) {
                if (!empty($result['filepath']) && file_exists($result['filepath'])) {
                    return $result['filepath'];
                }
                if (!empty($result['receipt_url'])) {
                    return self::upload_url_to_path($result['receipt_url']);
                }
            }
        }

        return '';
    }

    private static function fattura_filename($payment) {
        $ts   = !empty($payment->paid_at) ? strtotime($payment->paid_at) : strtotime($payment->created_at);
        $anno = $ts ? date('Y', $ts) : date('Y');
        return 'Fattura_' . $payment->id . '_' . $anno . '.pdf';
    }

    /**
     * Stream di un file in download e uscita.
     */
    private static function stream_file_download($file_path, $download_name, $content_type, $delete_after = false) {
        if (!file_exists($file_path)) {
            wp_die(__('File non trovato.', 'wecoop-fatture'));
        }

        if (ob_get_level()) {
            @ob_end_clean();
        }

        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($file_path);

        if ($delete_after) {
            @unlink($file_path);
        }
        exit;
    }

    /**
     * Download singola fattura.
     */
    public static function handle_download_fattura() {
        self::check_access();

        $payment_id = isset($_GET['payment_id']) ? absint($_GET['payment_id']) : 0;
        $payment = self::get_payment($payment_id);
        if (!$payment) {
            wp_die(__('Pagamento non trovato.', 'wecoop-fatture'));
        }

        $path = self::resolve_fattura_path($payment);
        if ($path === '') {
            self::redirect_to_list('fattura_errore');
        }

        self::stream_file_download($path, self::fattura_filename($payment), 'application/pdf');
    }

    /**
     * Genera (o rigenera) la fattura per un pagamento e torna alla lista.
     */
    public static function handle_genera_fattura() {
        self::check_access();

        $payment_id = isset($_GET['payment_id']) ? absint($_GET['payment_id']) : 0;
        $payment = self::get_payment($payment_id);
        if (!$payment || !class_exists('WeCoop_Ricevuta_PDF')) {
            self::redirect_to_list('fattura_errore');
        }

        $result = WeCoop_Ricevuta_PDF::generate_ricevuta($payment_id);
        self::redirect_to_list(!empty($result['success']) ? 'fattura_generata' : 'fattura_errore');
    }

    /**
     * Download in blocco (ZIP) delle fatture selezionate.
     */
    public static function handle_download_fatture_bulk() {
        self::check_access();

        $ids = isset($_POST['payment_ids']) ? array_map('absint', (array) $_POST['payment_ids']) : [];
        $ids = array_filter(array_unique($ids));

        if (empty($ids) || !class_exists('ZipArchive')) {
            self::redirect_to_list('bulk_vuoto');
        }

        $tmp_zip = wp_tempnam('wecoop-fatture-') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            self::redirect_to_list('fattura_errore');
        }

        $aggiunti = 0;
        foreach ($ids as $id) {
            $payment = self::get_payment($id);
            if (!$payment) {
                continue;
            }
            $path = self::resolve_fattura_path($payment);
            if ($path !== '' && file_exists($path)) {
                $zip->addFile($path, self::fattura_filename($payment));
                $aggiunti++;
            }
        }
        $zip->close();

        if ($aggiunti === 0) {
            @unlink($tmp_zip);
            self::redirect_to_list('bulk_vuoto');
        }

        $zip_name = 'Fatture_WeCoop_' . date('Ymd_His') . '.zip';
        self::stream_file_download($tmp_zip, $zip_name, 'application/zip', true);
    }

    /**
     * Esporta l'elenco fatture in CSV (rispetta i filtri della pagina).
     */
    public static function handle_export_csv() {
        self::check_access();

        $filtri = [
            'anno'    => isset($_GET['anno']) ? sanitize_text_field(wp_unslash($_GET['anno'])) : '',
            'fattura' => isset($_GET['fattura']) ? sanitize_text_field(wp_unslash($_GET['fattura'])) : '',
            'search'  => isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '',
        ];

        $pagamenti = self::get_pagamenti($filtri);

        if (ob_get_level()) {
            @ob_end_clean();
        }

        $filename = 'Fatture_WeCoop_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM per Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, [
            'N. Fattura', 'Payment ID', 'Richiesta ID', 'Pratica', 'Cliente',
            'Servizio', 'Importo', 'Stato', 'Metodo', 'Transazione',
            'Data pagamento', 'Fattura disponibile', 'URL fattura',
        ]);

        foreach ($pagamenti as $p) {
            $info = self::get_richiesta_info($p->richiesta_id);
            $ts   = !empty($p->paid_at) ? strtotime($p->paid_at) : strtotime($p->created_at);
            $anno = $ts ? date('Y', $ts) : date('Y');
            fputcsv($out, [
                $p->id . '/' . $anno,
                $p->id,
                $p->richiesta_id,
                $info['numero_pratica'],
                $info['cliente'],
                $info['servizio'],
                number_format(floatval($p->importo), 2, ',', '.'),
                $p->stato,
                $p->metodo_pagamento,
                $p->transaction_id,
                $ts ? date('d/m/Y H:i', $ts) : '',
                !empty($p->receipt_url) ? 'Si' : 'No',
                $p->receipt_url,
            ]);
        }

        fclose($out);
        exit;
    }
}
