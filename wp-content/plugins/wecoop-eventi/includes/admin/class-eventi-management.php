<?php
/**
 * Admin: Gestione Eventi e Iscritti
 * 
 * @package WECOOP_Eventi
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Eventi_Management {
    
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
        add_action('wp_ajax_wecoop_get_evento_iscritti', [__CLASS__, 'ajax_get_iscritti']);
        add_action('wp_ajax_wecoop_export_iscritti', [__CLASS__, 'ajax_export_iscritti']);
    }
    
    /**
     * Aggiungi menu
     */
    public static function add_menu_page() {
        add_submenu_page(
            'edit.php?post_type=evento',
            'Crea Nuovo Evento',
            '➕ Crea Evento',
            'edit_posts',
            'wecoop-nuovo-evento',
            [__CLASS__, 'render_create_page']
        );
        
        add_submenu_page(
            'edit.php?post_type=evento',
            'Gestione Iscritti',
            'Iscritti',
            'manage_options',
            'eventi-iscritti',
            [__CLASS__, 'render_iscritti_page']
        );
    }
    
    /**
     * Enqueue scripts
     */
    public static function enqueue_scripts($hook) {
        if ($hook === 'evento_page_eventi-iscritti' || $hook === 'evento_page_wecoop-nuovo-evento') {
            wp_enqueue_style('wecoop-eventi-admin', WECOOP_EVENTI_PLUGIN_URL . 'assets/css/admin.css', [], WECOOP_EVENTI_VERSION);
            wp_enqueue_script('wecoop-eventi-admin', WECOOP_EVENTI_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WECOOP_EVENTI_VERSION, true);
            
            wp_localize_script('wecoop-eventi-admin', 'wecoopEventiAdmin', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wecoop_eventi_admin')
            ]);
        }
    }
    
    /**
     * Render pagina iscritti
     */
    public static function render_iscritti_page() {
        // Get tutti gli eventi
        $eventi = get_posts([
            'post_type' => 'evento',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => 'data_inizio',
            'order' => 'DESC'
        ]);
        
        ?>
        <div class="wrap">
            <h1>Gestione Iscritti Eventi</h1>
            
            <div class="wecoop-eventi-admin">
                <div class="card">
                    <h2>Seleziona Evento</h2>
                    <select id="evento-select" class="large-text">
                        <option value="">Seleziona un evento...</option>
                        <?php foreach ($eventi as $evento): 
                            $data = get_post_meta($evento->ID, 'data_inizio', true);
                            $luogo = get_post_meta($evento->ID, 'luogo', true);
                            $iscritti_count = count(get_post_meta($evento->ID, 'iscritti', true) ?: []);
                        ?>
                        <option value="<?php echo $evento->ID; ?>">
                            <?php echo esc_html($evento->post_title); ?> 
                            (<?php echo $data ? date('d/m/Y', strtotime($data)) : ''; ?> - <?php echo $luogo; ?>) 
                            - <?php echo $iscritti_count; ?> iscritti
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="iscritti-container" style="display: none;">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2 id="evento-title"></h2>
                            <button id="export-iscritti" class="button button-primary">Esporta CSV</button>
                        </div>
                        
                        <div id="evento-info" style="background: #f0f0f1; padding: 15px; border-radius: 5px; margin-bottom: 20px;"></div>
                        
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Note</th>
                                    <th>Data Iscrizione</th>
                                </tr>
                            </thead>
                            <tbody id="iscritti-list">
                                <tr><td colspan="5">Caricamento...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX: Get iscritti
     */
    public static function ajax_get_iscritti() {
        check_ajax_referer('wecoop_eventi_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }
        
        $evento_id = intval($_POST['evento_id']);
        
        $evento = get_post($evento_id);
        if (!$evento || $evento->post_type !== 'evento') {
            wp_send_json_error(['message' => 'Evento non trovato']);
        }
        
        $partecipanti = get_post_meta($evento_id, 'partecipanti', true) ?: [];
        $data_inizio = get_post_meta($evento_id, 'data_inizio', true);
        $ora_inizio = get_post_meta($evento_id, 'ora_inizio', true);
        $luogo = get_post_meta($evento_id, 'luogo', true);
        $posti = get_post_meta($evento_id, 'posti_disponibili', true);
        
        wp_send_json_success([
            'evento' => [
                'titolo' => $evento->post_title,
                'data' => $data_inizio ? date('d/m/Y', strtotime($data_inizio)) : '',
                'ora' => $ora_inizio,
                'luogo' => $luogo,
                'posti' => $posti
            ],
            'partecipanti' => array_values($partecipanti),
            'totale' => count($partecipanti)
        ]);
    }
    
    /**
     * AJAX: Export iscritti CSV
     */
    public static function ajax_export_iscritti() {
        check_ajax_referer('wecoop_eventi_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti');
        }
        
        $evento_id = intval($_POST['evento_id']);
        
        $evento = get_post($evento_id);
        if (!$evento || $evento->post_type !== 'evento') {
            wp_die('Evento non trovato');
        }
        
        $partecipanti = get_post_meta($evento_id, 'partecipanti', true) ?: [];
        
        // Headers CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="iscritti_' . sanitize_file_name($evento->post_title) . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['Nome', 'Email', 'Telefono', 'Note', 'Data Iscrizione']);
        
        // Dati
        foreach ($partecipanti as $p) {
            fputcsv($output, [
                $p['nome'] ?? '',
                $p['email'] ?? '',
                $p['telefono'] ?? '',
                $p['note'] ?? '',
                $p['data_iscrizione'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Pagina creazione evento
     */
    public static function render_create_page() {
        // Salva evento se form inviato
        if (isset($_POST['create_evento']) && wp_verify_nonce($_POST['evento_nonce'], 'create_evento')) {
            $evento_id = self::save_evento();
            if ($evento_id) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ Evento creato con successo! <a href="' . get_edit_post_link($evento_id) . '">Modifica</a> | <a href="' . get_permalink($evento_id) . '" target="_blank">Visualizza</a></p></div>';
            }
        }
        
        include WECOOP_EVENTI_PLUGIN_DIR . 'includes/admin/views/create-evento.php';
    }
    
    /**
     * Salva evento
     */
    private static function save_evento() {
        // Crea post evento
        $post_data = [
            'post_title' => sanitize_text_field($_POST['titolo']),
            'post_excerpt' => sanitize_textarea_field($_POST['excerpt'] ?? ''),
            'post_type' => 'evento',
            'post_status' => 'publish'
        ];
        
        $evento_id = wp_insert_post($post_data);
        
        if (is_wp_error($evento_id)) {
            return false;
        }
        
        // Salva meta fields
        $meta_fields = [
            'luogo', 'indirizzo', 'citta', 'data_inizio', 'ora_inizio',
            'data_fine', 'ora_fine', 'stato', 'posti_disponibili',
            'richiede_iscrizione', 'evento_online', 'link_online',
            'prezzo', 'organizzatore', 'email_organizzatore',
            'telefono_organizzatore', 'programma'
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($evento_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Salva traduzioni
        $lingue = ['it', 'en', 'es'];
        foreach ($lingue as $lang) {
            if (!empty($_POST["titolo_$lang"])) {
                update_post_meta($evento_id, "titolo_$lang", sanitize_text_field($_POST["titolo_$lang"]));
            }
            if (!empty($_POST["descrizione_$lang"])) {
                update_post_meta($evento_id, "descrizione_$lang", sanitize_textarea_field($_POST["descrizione_$lang"]));
            }
        }
        
        // Gestisci thumbnail
        if (!empty($_FILES['thumbnail']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('thumbnail', $evento_id);
            if (!is_wp_error($attachment_id)) {
                set_post_thumbnail($evento_id, $attachment_id);
            }
        }
        
        return $evento_id;
    }
}
