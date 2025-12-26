<?php
/**
 * Custom Post Type: Richiesta Servizio
 * 
 * Gestisce le richieste di servizi da parte dei soci
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Richiesta_Servizio_CPT {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_richiesta_servizio', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_richiesta_servizio_posts_columns', [__CLASS__, 'set_custom_columns']);
        add_action('manage_richiesta_servizio_posts_custom_column', [__CLASS__, 'custom_column_content'], 10, 2);
    }
    
    /**
     * Registra CPT
     */
    public static function register_post_type() {
        register_post_type('richiesta_servizio', [
            'labels' => [
                'name' => 'Richieste Servizi',
                'singular_name' => 'Richiesta Servizio',
                'add_new' => 'Nuova Richiesta',
                'add_new_item' => 'Aggiungi Richiesta Servizio',
                'edit_item' => 'Modifica Richiesta',
                'view_item' => 'Visualizza Richiesta',
                'search_items' => 'Cerca Richieste',
                'not_found' => 'Nessuna richiesta trovata',
                'menu_name' => 'Richieste Servizi'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
            'has_archive' => false,
            'menu_icon' => 'dashicons-clipboard'
        ]);
    }
    
    /**
     * Aggiungi metaboxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'richiesta_servizio_details',
            'Dettagli Richiesta Servizio',
            [__CLASS__, 'render_metabox'],
            'richiesta_servizio',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox
     */
    public static function render_metabox($post) {
        wp_nonce_field('richiesta_servizio_save', 'richiesta_servizio_nonce');
        
        $servizio = get_post_meta($post->ID, 'servizio', true);
        $categoria = get_post_meta($post->ID, 'categoria', true);
        $dati_json = get_post_meta($post->ID, 'dati', true);
        $stato = get_post_meta($post->ID, 'stato', true) ?: 'pending';
        $numero_pratica = get_post_meta($post->ID, 'numero_pratica', true);
        $user_id = get_post_meta($post->ID, 'user_id', true);
        $socio_id = get_post_meta($post->ID, 'socio_id', true);
        
        // Decodifica dati JSON
        $dati = json_decode($dati_json, true) ?: [];
        
        ?>
        <style>
            .servizio-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }
            .servizio-full {
                grid-column: 1 / -1;
            }
            .servizio-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .servizio-field input,
            .servizio-field textarea,
            .servizio-field select {
                width: 100%;
            }
            .servizio-info-box {
                background: #f0f0f0;
                padding: 15px;
                border-left: 4px solid #2271b1;
                margin-bottom: 15px;
            }
        </style>
        
        <div class="servizio-info-box">
            <strong>Numero Pratica:</strong> <?php echo $numero_pratica ?: 'Da generare'; ?><br>
            <strong>Utente ID:</strong> <?php echo $user_id ?: 'N/A'; ?><br>
            <strong>Socio ID:</strong> <?php echo $socio_id ?: 'N/A'; ?><br>
            <strong>Data Richiesta:</strong> <?php echo get_the_date('d/m/Y H:i', $post); ?>
        </div>
        
        <div class="servizio-grid">
            <div class="servizio-field">
                <label>Servizio *</label>
                <input type="text" name="servizio" value="<?php echo esc_attr($servizio); ?>" required>
            </div>
            
            <div class="servizio-field">
                <label>Categoria</label>
                <select name="categoria">
                    <option value="">Seleziona categoria</option>
                    <option value="consulenza" <?php selected($categoria, 'consulenza'); ?>>Consulenza</option>
                    <option value="pratiche" <?php selected($categoria, 'pratiche'); ?>>Pratiche</option>
                    <option value="formazione" <?php selected($categoria, 'formazione'); ?>>Formazione</option>
                    <option value="assistenza" <?php selected($categoria, 'assistenza'); ?>>Assistenza</option>
                    <option value="altro" <?php selected($categoria, 'altro'); ?>>Altro</option>
                </select>
            </div>
            
            <div class="servizio-field">
                <label>Importo (€)</label>
                <input type="number" name="importo" step="0.01" min="0" 
                       value="<?php echo esc_attr(get_post_meta($post->ID, 'importo', true)); ?>" 
                       placeholder="0.00">
                <p class="description">Importo del servizio per generare pagamento</p>
            </div>
            
            <div class="servizio-field">
                <label>Stato *</label>
                <select name="stato" required>
                    <option value="pending" <?php selected($stato, 'pending'); ?>>In Attesa</option>
                    <option value="awaiting_payment" <?php selected($stato, 'awaiting_payment'); ?>>Da Pagare</option>
                    <option value="processing" <?php selected($stato, 'processing'); ?>>In Lavorazione</option>
                    <option value="completed" <?php selected($stato, 'completed'); ?>>Completata</option>
                    <option value="cancelled" <?php selected($stato, 'cancelled'); ?>>Annullata</option>
                </select>
            </div>
            
            <div class="servizio-field servizio-full">
                <label>Dati Richiesta (JSON)</label>
                <textarea name="dati" rows="8" placeholder='{"campo": "valore"}'><?php echo esc_textarea($dati_json); ?></textarea>
                <p class="description">Inserire dati in formato JSON</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Salva metabox
     */
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['richiesta_servizio_nonce']) || 
            !wp_verify_nonce($_POST['richiesta_servizio_nonce'], 'richiesta_servizio_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        // Campi base (aggiungi 'importo')
        $fields = ['servizio', 'categoria', 'dati', 'stato', 'user_id', 'socio_id', 'importo'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Genera numero pratica se non esiste
        $numero_pratica = get_post_meta($post_id, 'numero_pratica', true);
        if (empty($numero_pratica)) {
            $numero_pratica = self::genera_numero_pratica($post_id);
            update_post_meta($post_id, 'numero_pratica', $numero_pratica);
        }
        
        // Aggiorna titolo post
        if (isset($_POST['servizio'])) {
            $servizio = sanitize_text_field($_POST['servizio']);
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $numero_pratica . ' - ' . $servizio
            ]);
        }
    }
    
    /**
     * Genera numero pratica automatico
     */
    public static function genera_numero_pratica($post_id) {
        $year = date('Y');
        
        // Conta richieste dell'anno corrente
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = 'numero_pratica' 
            AND meta_value LIKE %s
        ", 'WECOOP-' . $year . '-%'));
        
        $numero_sequenziale = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        
        return 'WECOOP-' . $year . '-' . $numero_sequenziale;
    }
    
    /**
     * Colonne personalizzate
     */
    public static function set_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = 'Richiesta';
        $new_columns['numero_pratica'] = 'N. Pratica';
        $new_columns['servizio'] = 'Servizio';
        $new_columns['categoria'] = 'Categoria';
        $new_columns['stato'] = 'Stato';
        $new_columns['user'] = 'Utente';
        $new_columns['date'] = 'Data';
        return $new_columns;
    }
    
    /**
     * Contenuto colonne
     */
    public static function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'numero_pratica':
                echo esc_html(get_post_meta($post_id, 'numero_pratica', true));
                break;
                
            case 'servizio':
                echo esc_html(get_post_meta($post_id, 'servizio', true));
                break;
                
            case 'categoria':
                $cat = get_post_meta($post_id, 'categoria', true);
                echo $cat ? esc_html(ucfirst($cat)) : '—';
                break;
                
            case 'stato':
                $stato = get_post_meta($post_id, 'stato', true);
                $colors = [
                    'pending' => '#ff9800',
                    'awaiting_payment' => '#9c27b0',
                    'processing' => '#2196f3',
                    'completed' => '#4caf50',
                    'cancelled' => '#f44336'
                ];
                $labels = [
                    'pending' => 'In Attesa',
                    'awaiting_payment' => 'Da Pagare',
                    'processing' => 'In Lavorazione',
                    'completed' => 'Completata',
                    'cancelled' => 'Annullata'
                ];
                $color = $colors[$stato] ?? '#999';
                $label = $labels[$stato] ?? $stato;
                echo '<span style="background: ' . $color . '; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">' . $label . '</span>';
                break;
                
            case 'user':
                $user_id = get_post_meta($post_id, 'user_id', true);
                if ($user_id) {
                    $user = get_userdata($user_id);
                    echo $user ? esc_html($user->display_name) : 'User #' . $user_id;
                } else {
                    echo '—';
                }
                break;
        }
    }
}
