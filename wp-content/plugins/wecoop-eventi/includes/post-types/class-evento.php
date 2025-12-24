<?php
/**
 * Custom Post Type: Evento
 * 
 * Gestisce gli eventi con supporto multilingua
 * 
 * @package WECOOP_Eventi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Evento_CPT {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_evento', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_evento_posts_columns', [__CLASS__, 'set_custom_columns']);
        add_action('manage_evento_posts_custom_column', [__CLASS__, 'custom_column_content'], 10, 2);
    }
    
    /**
     * Registra CPT
     */
    public static function register_post_type() {
        register_post_type('evento', [
            'labels' => [
                'name' => 'Eventi',
                'singular_name' => 'Evento',
                'add_new' => 'Nuovo Evento',
                'add_new_item' => 'Aggiungi Evento',
                'edit_item' => 'Modifica Evento',
                'view_item' => 'Visualizza Evento',
                'search_items' => 'Cerca Eventi',
                'not_found' => 'Nessun evento trovato',
                'menu_name' => 'Eventi'
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'eventi',
            'supports' => ['title', 'thumbnail', 'excerpt'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow'
            ],
            'map_meta_cap' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'eventi'],
            'menu_icon' => 'dashicons-calendar-alt'
        ]);
    }
    
    /**
     * Aggiungi metaboxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'evento_details',
            'Dettagli Evento',
            [__CLASS__, 'render_metabox_details'],
            'evento',
            'normal',
            'high'
        );
        
        add_meta_box(
            'evento_multilang',
            'Contenuto Multilingua',
            [__CLASS__, 'render_metabox_multilang'],
            'evento',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox dettagli
     */
    public static function render_metabox_details($post) {
        wp_nonce_field('evento_save', 'evento_nonce');
        
        $luogo = get_post_meta($post->ID, 'luogo', true);
        $indirizzo = get_post_meta($post->ID, 'indirizzo', true);
        $citta = get_post_meta($post->ID, 'citta', true);
        $data_inizio = get_post_meta($post->ID, 'data_inizio', true);
        $ora_inizio = get_post_meta($post->ID, 'ora_inizio', true);
        $data_fine = get_post_meta($post->ID, 'data_fine', true);
        $ora_fine = get_post_meta($post->ID, 'ora_fine', true);
        $stato = get_post_meta($post->ID, 'stato', true) ?: 'attivo';
        $posti_disponibili = get_post_meta($post->ID, 'posti_disponibili', true) ?: 0;
        $richiede_iscrizione = get_post_meta($post->ID, 'richiede_iscrizione', true);
        $evento_online = get_post_meta($post->ID, 'evento_online', true);
        $link_online = get_post_meta($post->ID, 'link_online', true);
        $prezzo = get_post_meta($post->ID, 'prezzo', true) ?: 0;
        $organizzatore = get_post_meta($post->ID, 'organizzatore', true);
        $email_organizzatore = get_post_meta($post->ID, 'email_organizzatore', true);
        $telefono_organizzatore = get_post_meta($post->ID, 'telefono_organizzatore', true);
        $programma = get_post_meta($post->ID, 'programma', true);
        $iscritti = get_post_meta($post->ID, 'iscritti', true) ?: [];
        
        ?>
        <style>
            .evento-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }
            .evento-full {
                grid-column: 1 / -1;
            }
            .evento-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .evento-field input,
            .evento-field textarea,
            .evento-field select {
                width: 100%;
            }
            .evento-info {
                background: #f0f0f1;
                padding: 10px;
                border-left: 3px solid #2271b1;
                margin-top: 15px;
            }
            .evento-section {
                background: #fff;
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 4px;
            }
            .evento-section h4 {
                margin-top: 0;
                border-bottom: 1px solid #ddd;
                padding-bottom: 10px;
            }
        </style>
        
        <div class="evento-section">
            <h4>📍 Localizzazione</h4>
            <div class="evento-grid">
                <div class="evento-field">
                    <label><input type="checkbox" name="evento_online" value="1" <?php checked($evento_online, '1'); ?>> Evento Online</label>
                </div>
                
                <div class="evento-field">
                    <label>Link Online (se evento online)</label>
                    <input type="url" name="link_online" value="<?php echo esc_attr($link_online); ?>">
                </div>
                
                <div class="evento-field evento-full">
                    <label>Luogo *</label>
                    <input type="text" name="luogo" value="<?php echo esc_attr($luogo); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Indirizzo</label>
                    <input type="text" name="indirizzo" value="<?php echo esc_attr($indirizzo); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Città</label>
                    <input type="text" name="citta" value="<?php echo esc_attr($citta); ?>">
                </div>
            </div>
        </div>
        
        <div class="evento-section">
            <h4>📅 Date e Orari</h4>
            <div class="evento-grid">
                <div class="evento-field">
                    <label>Data Inizio *</label>
                    <input type="date" name="data_inizio" value="<?php echo esc_attr($data_inizio); ?>" required>
                </div>
                
                <div class="evento-field">
                    <label>Ora Inizio</label>
                    <input type="time" name="ora_inizio" value="<?php echo esc_attr($ora_inizio); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Data Fine</label>
                    <input type="date" name="data_fine" value="<?php echo esc_attr($data_fine); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Ora Fine</label>
                    <input type="time" name="ora_fine" value="<?php echo esc_attr($ora_fine); ?>">
                </div>
            </div>
        </div>
        
        <div class="evento-section">
            <h4>👥 Iscrizioni</h4>
            <div class="evento-grid">
                <div class="evento-field">
                    <label><input type="checkbox" name="richiede_iscrizione" value="1" <?php checked($richiede_iscrizione, '1'); ?>> Richiede Iscrizione</label>
                </div>
                
                <div class="evento-field">
                    <label>Posti Disponibili (0 = illimitati)</label>
                    <input type="number" name="posti_disponibili" value="<?php echo esc_attr($posti_disponibili); ?>" min="0">
                </div>
                
                <div class="evento-field">
                    <label>Prezzo (€)</label>
                    <input type="number" name="prezzo" value="<?php echo esc_attr($prezzo); ?>" min="0" step="0.01">
                </div>
                
                <div class="evento-field">
                    <label>Stato</label>
                    <select name="stato">
                        <option value="attivo" <?php selected($stato, 'attivo'); ?>>Attivo</option>
                        <option value="annullato" <?php selected($stato, 'annullato'); ?>>Annullato</option>
                        <option value="completato" <?php selected($stato, 'completato'); ?>>Completato</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="evento-section">
            <h4>👤 Organizzatore</h4>
            <div class="evento-grid">
                <div class="evento-field">
                    <label>Nome Organizzatore</label>
                    <input type="text" name="organizzatore" value="<?php echo esc_attr($organizzatore); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Email Organizzatore</label>
                    <input type="email" name="email_organizzatore" value="<?php echo esc_attr($email_organizzatore); ?>">
                </div>
                
                <div class="evento-field">
                    <label>Telefono Organizzatore</label>
                    <input type="tel" name="telefono_organizzatore" value="<?php echo esc_attr($telefono_organizzatore); ?>">
                </div>
            </div>
        </div>
        
        <div class="evento-section">
            <h4>📝 Programma</h4>
            <div class="evento-field evento-full">
                <label>Programma dell'Evento</label>
                <textarea name="programma" rows="5"><?php echo esc_textarea($programma); ?></textarea>
            </div>
        </div>
        
        <div class="evento-info">
            <strong>Iscritti:</strong> <?php echo count($iscritti); ?> partecipanti
        </div>
        <?php
    }
    
    /**
     * Render metabox multilingua
     */
    public static function render_metabox_multilang($post) {
        $languages = ['it' => 'Italiano', 'en' => 'Inglese', 'fr' => 'Francese', 'es' => 'Spagnolo', 'ar' => 'Arabo'];
        
        ?>
        <style>
            .evento-lang-tabs {
                border-bottom: 1px solid #ccc;
                margin-bottom: 20px;
            }
            .evento-lang-tab {
                display: inline-block;
                padding: 10px 20px;
                cursor: pointer;
                border: 1px solid #ccc;
                border-bottom: none;
                margin-right: 5px;
                background: #f5f5f5;
            }
            .evento-lang-tab.active {
                background: white;
                border-bottom: 1px solid white;
                margin-bottom: -1px;
                font-weight: 600;
            }
            .evento-lang-content {
                display: none;
            }
            .evento-lang-content.active {
                display: block;
            }
        </style>
        
        <div class="evento-lang-tabs">
            <?php foreach ($languages as $code => $label): ?>
                <span class="evento-lang-tab <?php echo $code === 'it' ? 'active' : ''; ?>" data-lang="<?php echo $code; ?>">
                    <?php echo $label; ?>
                </span>
            <?php endforeach; ?>
        </div>
        
        <?php foreach ($languages as $code => $label): 
            $titolo = get_post_meta($post->ID, 'titolo_' . $code, true);
            $descrizione = get_post_meta($post->ID, 'descrizione_' . $code, true);
        ?>
        <div class="evento-lang-content <?php echo $code === 'it' ? 'active' : ''; ?>" data-lang="<?php echo $code; ?>">
            <div class="evento-field">
                <label>Titolo (<?php echo $label; ?>)</label>
                <input type="text" name="titolo_<?php echo $code; ?>" value="<?php echo esc_attr($titolo); ?>">
            </div>
            
            <div class="evento-field" style="margin-top: 15px;">
                <label>Descrizione (<?php echo $label; ?>)</label>
                <textarea name="descrizione_<?php echo $code; ?>" rows="6"><?php echo esc_textarea($descrizione); ?></textarea>
            </div>
        </div>
        <?php endforeach; ?>
        
        <script>
        jQuery(document).ready(function($) {
            $('.evento-lang-tab').on('click', function() {
                var lang = $(this).data('lang');
                
                $('.evento-lang-tab').removeClass('active');
                $(this).addClass('active');
                
                $('.evento-lang-content').removeClass('active');
                $('.evento-lang-content[data-lang="' + lang + '"]').addClass('active');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Salva metabox
     */
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['evento_nonce']) || 
            !wp_verify_nonce($_POST['evento_nonce'], 'evento_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Campi base
        $fields = [
            'luogo', 'indirizzo', 'citta',
            'data_inizio', 'ora_inizio', 'data_fine', 'ora_fine',
            'stato', 'posti_disponibili',
            'link_online', 'prezzo',
            'organizzatore', 'email_organizzatore', 'telefono_organizzatore',
            'programma'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Checkbox
        update_post_meta($post_id, 'richiede_iscrizione', isset($_POST['richiede_iscrizione']) ? '1' : '');
        update_post_meta($post_id, 'evento_online', isset($_POST['evento_online']) ? '1' : '');
        
        // Campi multilingua
        $languages = ['it', 'en', 'fr', 'es', 'ar'];
        foreach ($languages as $lang) {
            if (isset($_POST['titolo_' . $lang])) {
                update_post_meta($post_id, 'titolo_' . $lang, sanitize_text_field($_POST['titolo_' . $lang]));
            }
            if (isset($_POST['descrizione_' . $lang])) {
                update_post_meta($post_id, 'descrizione_' . $lang, sanitize_textarea_field($_POST['descrizione_' . $lang]));
            }
        }
    }
    
    /**
     * Colonne personalizzate
     */
    public static function set_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = 'Evento';
        $new_columns['luogo'] = 'Luogo';
        $new_columns['data_inizio'] = 'Data';
        $new_columns['ora_inizio'] = 'Ora';
        $new_columns['stato'] = 'Stato';
        $new_columns['iscritti'] = 'Iscritti';
        $new_columns['thumbnail'] = 'Immagine';
        $new_columns['date'] = 'Pubblicato';
        return $new_columns;
    }
    
    /**
     * Contenuto colonne
     */
    public static function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'luogo':
                echo esc_html(get_post_meta($post_id, 'luogo', true));
                break;
                
            case 'data_inizio':
                $data = get_post_meta($post_id, 'data_inizio', true);
                if ($data) {
                    echo date('d/m/Y', strtotime($data));
                }
                break;
                
            case 'ora_inizio':
                echo esc_html(get_post_meta($post_id, 'ora_inizio', true));
                break;
                
            case 'stato':
                $stato = get_post_meta($post_id, 'stato', true) ?: 'attivo';
                $colors = [
                    'attivo' => '#10b981',
                    'annullato' => '#ef4444',
                    'completato' => '#6b7280'
                ];
                echo '<span style="color: ' . $colors[$stato] . '; font-weight: 600;">' . ucfirst($stato) . '</span>';
                break;
                
            case 'iscritti':
                $iscritti = get_post_meta($post_id, 'iscritti', true) ?: [];
                $posti = (int)get_post_meta($post_id, 'posti_disponibili', true);
                $count = count($iscritti);
                
                if ($posti > 0) {
                    echo $count . ' / ' . $posti;
                } else {
                    echo $count;
                }
                break;
                
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, [50, 50]);
                } else {
                    echo '—';
                }
                break;
        }
    }
}
