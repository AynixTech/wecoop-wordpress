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
        add_action('wp_ajax_wecoop_pratiche_search_users', [__CLASS__, 'ajax_search_users']);

        // Sezione nella scheda utente.
        // NB: gli hook show_user_profile / edit_user_profile vengono eseguiti
        // DENTRO il <form id="your-profile"> di WordPress. Poiche' la nostra
        // sezione contiene dei <form> (upload/elimina) e i form HTML non possono
        // essere annidati, stampare qui romperebbe il form del profilo (il pulsante
        // "Aggiorna utente" finirebbe fuori dal form e il salvataggio non
        // funzionerebbe). Per questo NON stampiamo nulla dentro questi hook:
        // segnaliamo solo che siamo nella pagina utente e renderizziamo la sezione
        // in 'admin_footer' (fuori dal form), spostandola poi via JS nel punto
        // giusto della pagina.
        add_action('show_user_profile', [__CLASS__, 'flag_user_screen']);
        add_action('edit_user_profile', [__CLASS__, 'flag_user_screen']);
        add_action('admin_footer', [__CLASS__, 'render_user_section_footer']);
    }

    /** @var int|null ID utente della schermata di modifica corrente. */
    private static $current_profile_user_id = null;

    /**
     * Registra l'utente della schermata profilo corrente (chiamato dentro il form,
     * ma senza stampare nulla per non rompere il form di WordPress).
     */
    public static function flag_user_screen($user) {
        self::$current_profile_user_id = (int) $user->ID;
    }

    private static function can_manage() {
        return current_user_can(self::CAP) || current_user_can('manage_options');
    }

    /**
     * Cerca utenti per nome, cognome, email, login, telefono o codice fiscale.
     * Ritorna un array di utenti (max $limit) con dati essenziali.
     */
    public static function search_users($term, $limit = 15) {
        $term = trim((string) $term);
        if ($term === '') {
            return [];
        }

        $ids = [];

        // 1) Ricerca sulle colonne utente (login, email, display_name, nicename).
        $col_q = new WP_User_Query([
            'number'         => $limit,
            'fields'         => 'ID',
            'search'         => '*' . esc_attr($term) . '*',
            'search_columns' => ['user_login', 'user_email', 'display_name', 'user_nicename'],
        ]);
        foreach ((array) $col_q->get_results() as $id) {
            $ids[(int) $id] = true;
        }

        // 2) Ricerca sui meta del profilo (nome, cognome, telefono, CF, citta).
        $meta_q = new WP_User_Query([
            'number'     => $limit,
            'fields'     => 'ID',
            'meta_query' => [
                'relation' => 'OR',
                ['key' => 'nome', 'value' => $term, 'compare' => 'LIKE'],
                ['key' => 'cognome', 'value' => $term, 'compare' => 'LIKE'],
                ['key' => 'telefono', 'value' => $term, 'compare' => 'LIKE'],
                ['key' => 'telefono_completo', 'value' => $term, 'compare' => 'LIKE'],
                ['key' => 'codice_fiscale', 'value' => $term, 'compare' => 'LIKE'],
                ['key' => 'citta', 'value' => $term, 'compare' => 'LIKE'],
            ],
        ]);
        foreach ((array) $meta_q->get_results() as $id) {
            $ids[(int) $id] = true;
        }

        $ids = array_slice(array_keys($ids), 0, $limit);
        if (empty($ids)) {
            return [];
        }

        $results = [];
        foreach ($ids as $id) {
            $user = get_userdata($id);
            if (!$user) {
                continue;
            }

            $nome = get_user_meta($id, 'nome', true) ?: $user->first_name;
            $cognome = get_user_meta($id, 'cognome', true) ?: $user->last_name;
            $full = trim($nome . ' ' . $cognome);
            $cf = get_user_meta($id, 'codice_fiscale', true);
            $tel = get_user_meta($id, 'telefono_completo', true) ?: get_user_meta($id, 'telefono', true);

            $results[] = [
                'id'      => (int) $id,
                'name'    => $full !== '' ? $full : $user->display_name,
                'email'   => $user->user_email,
                'cf'      => $cf,
                'tel'     => $tel,
                'doc_count' => WeCoop_Storico_Pratiche_Repository::count_by_user($id),
            ];
        }

        // Ordina alfabeticamente per nome.
        usort($results, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $results;
    }

    /**
     * Endpoint AJAX per la ricerca live degli utenti.
     */
    public static function ajax_search_users() {
        if (!self::can_manage()) {
            wp_send_json_error(['message' => 'Permessi insufficienti'], 403);
        }
        check_ajax_referer('wecoop_pratiche_search', 'nonce');

        $term = isset($_GET['term']) ? sanitize_text_field(wp_unslash($_GET['term'])) : '';
        $results = self::search_users($term);

        wp_send_json_success(['users' => $results]);
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
        $search_term = isset($_GET['us']) ? sanitize_text_field(wp_unslash($_GET['us'])) : '';

        echo '<div class="wrap">';
        echo '<h1>Storico Pratiche</h1>';
        echo '<p>Cerca il cliente, apri la scheda e carica i documenti (730, ISEE, ...). Il cliente li trovera\' nella sezione "Storico pratiche" dell\'app.</p>';

        if ($status === 'uploaded' || $status === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        } elseif ($status === 'error') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        }

        self::render_search_box($search_term, $user_id);

        if ($user) {
            $self_url = add_query_arg(['page' => self::MENU_SLUG, 'user_id' => $user_id], admin_url('admin.php'));
            echo '<h2 style="margin-top:28px;">Documenti di ' . esc_html($user->display_name) . ' <small>(' . esc_html($user->user_email) . ')</small></h2>';
            echo '<p><a href="' . esc_url(add_query_arg(['page' => self::MENU_SLUG], admin_url('admin.php'))) . '">&larr; Cambia cliente</a></p>';
            self::render_upload_form($user_id, $self_url);
            self::render_documents_table($user_id, $self_url);
        } else {
            echo '<h2 style="margin-top:28px;">Riepilogo</h2>';
            echo '<p>Totale documenti archiviati: <strong>' . esc_html((string) WeCoop_Storico_Pratiche_Repository::count_all()) . '</strong></p>';
        }

        echo '</div>';

        self::render_search_assets();
    }

    /**
     * Box di ricerca cliente: input con ricerca live (AJAX) + risultati,
     * con fallback server-side se JS non e' disponibile.
     */
    private static function render_search_box($search_term, $selected_user_id) {
        ?>
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;max-width:760px;margin-top:16px;">
            <h2 style="margin-top:0;">Cerca cliente</h2>
            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" id="wecoop-pratiche-search-form">
                <input type="hidden" name="page" value="<?php echo esc_attr(self::MENU_SLUG); ?>">
                <div style="position:relative;">
                    <input
                        type="search"
                        name="us"
                        id="wecoop-pratiche-search"
                        value="<?php echo esc_attr($search_term); ?>"
                        placeholder="Nome, cognome, email, telefono o codice fiscale..."
                        autocomplete="off"
                        style="width:100%;max-width:520px;height:38px;padding:0 12px;font-size:14px;"
                    >
                    <button type="submit" class="button button-primary" style="height:38px;vertical-align:top;">Cerca</button>
                    <div id="wecoop-pratiche-suggest" style="display:none;position:absolute;z-index:50;left:0;top:40px;width:100%;max-width:520px;background:#fff;border:1px solid #c3c4c7;border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,.12);max-height:340px;overflow:auto;"></div>
                </div>
                <p class="description" style="margin-top:8px;">Inizia a digitare per vedere i suggerimenti, oppure premi Cerca.</p>
            </form>

            <?php
            // Risultati server-side (fallback / submit esplicito).
            if ($search_term !== '') {
                $matches = self::search_users($search_term, 30);
                echo '<div style="margin-top:12px;">';
                if (empty($matches)) {
                    echo '<p>Nessun cliente trovato per <strong>' . esc_html($search_term) . '</strong>.</p>';
                } else {
                    echo '<p style="color:#646970;">' . esc_html(count($matches)) . ' risultati per "<strong>' . esc_html($search_term) . '</strong>":</p>';
                    echo '<table class="widefat striped" style="max-width:720px;"><thead><tr><th>Cliente</th><th>Contatti</th><th>Documenti</th><th></th></tr></thead><tbody>';
                    foreach ($matches as $m) {
                        $open_url = add_query_arg(['page' => self::MENU_SLUG, 'user_id' => $m['id']], admin_url('admin.php'));
                        echo '<tr>';
                        echo '<td><strong>' . esc_html($m['name']) . '</strong><br><small>' . esc_html($m['email']) . '</small>';
                        if (!empty($m['cf'])) {
                            echo '<br><small>CF: ' . esc_html($m['cf']) . '</small>';
                        }
                        echo '</td>';
                        echo '<td>' . esc_html($m['tel'] ?: '-') . '</td>';
                        echo '<td>' . esc_html((string) $m['doc_count']) . '</td>';
                        echo '<td><a class="button button-small button-primary" href="' . esc_url($open_url) . '">Apri scheda</a></td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * Inline JS/CSS per la ricerca live (autocomplete).
     */
    private static function render_search_assets() {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('wecoop_pratiche_search');
        $base_url = add_query_arg(['page' => self::MENU_SLUG], admin_url('admin.php'));
        ?>
        <script>
        (function () {
            var input = document.getElementById('wecoop-pratiche-search');
            var box = document.getElementById('wecoop-pratiche-suggest');
            if (!input || !box) { return; }

            var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
            var nonce = <?php echo wp_json_encode($nonce); ?>;
            var baseUrl = <?php echo wp_json_encode($base_url); ?>;
            var timer = null;
            var lastTerm = '';

            function hide() { box.style.display = 'none'; box.innerHTML = ''; }

            function escapeHtml(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function render(users) {
                if (!users || !users.length) {
                    box.innerHTML = '<div style="padding:10px 12px;color:#646970;">Nessun cliente trovato</div>';
                    box.style.display = 'block';
                    return;
                }
                var html = '';
                users.forEach(function (u) {
                    var url = baseUrl + '&user_id=' + encodeURIComponent(u.id);
                    var meta = [];
                    if (u.email) meta.push(escapeHtml(u.email));
                    if (u.cf) meta.push('CF: ' + escapeHtml(u.cf));
                    if (u.tel) meta.push(escapeHtml(u.tel));
                    html += '<a href="' + url + '" style="display:block;padding:9px 12px;border-bottom:1px solid #f0f0f1;text-decoration:none;color:#1d2327;">'
                        + '<strong>' + escapeHtml(u.name) + '</strong>'
                        + ' <span style="color:#2271b1;font-size:12px;">(' + (u.doc_count || 0) + ' doc)</span>'
                        + '<br><small style="color:#646970;">' + meta.join(' · ') + '</small>'
                        + '</a>';
                });
                box.innerHTML = html;
                box.style.display = 'block';
            }

            function search(term) {
                var url = ajaxUrl + '?action=wecoop_pratiche_search_users&nonce='
                    + encodeURIComponent(nonce) + '&term=' + encodeURIComponent(term);
                fetch(url, { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (json) {
                        if (json && json.success && json.data) {
                            render(json.data.users || []);
                        } else {
                            hide();
                        }
                    })
                    .catch(function () { hide(); });
            }

            input.addEventListener('input', function () {
                var term = input.value.trim();
                if (term === lastTerm) { return; }
                lastTerm = term;
                if (timer) { clearTimeout(timer); }
                if (term.length < 2) { hide(); return; }
                timer = setTimeout(function () { search(term); }, 250);
            });

            document.addEventListener('click', function (e) {
                if (!box.contains(e.target) && e.target !== input) { hide(); }
            });
            input.addEventListener('focus', function () {
                if (input.value.trim().length >= 2 && box.innerHTML) { box.style.display = 'block'; }
            });
        })();
        </script>
        <?php
    }


    /**
     * Sezione nella schermata edit-user, stampata in 'admin_footer' (quindi FUORI
     * dal <form id="your-profile">), poi spostata via JS subito dopo il form.
     * Questo evita form annidati e mantiene funzionante il pulsante "Aggiorna utente".
     */
    public static function render_user_section_footer() {
        $user_id = self::$current_profile_user_id;
        if (!$user_id) {
            return; // Non siamo nella schermata di modifica utente.
        }
        if (!self::can_manage()) {
            return;
        }

        $redirect_to = get_edit_user_link($user_id);

        echo '<div id="wecoop-storico-pratiche-wrap" class="wecoop-storico-pratiche-section" style="max-width:960px;">';
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
        echo '</div>';
        ?>
        <script>
        (function () {
            var wrap = document.getElementById('wecoop-storico-pratiche-wrap');
            var form = document.getElementById('your-profile');
            if (wrap && form && form.parentNode) {
                // Posiziona la sezione subito dopo il form del profilo utente.
                form.parentNode.insertBefore(wrap, form.nextSibling);
            }
        })();
        </script>
        <?php
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
