<?php
/**
 * Custom Post Type: Richiesta Adesione Socio
 * 
 * Gestisce le richieste di adesione alla cooperativa
 * 
 * @package WECOOP_CRM
 * @since 2.1.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Richiesta_Socio_CPT {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_richiesta_socio', [__CLASS__, 'save_meta_boxes']);
        add_filter('manage_richiesta_socio_posts_columns', [__CLASS__, 'set_custom_columns']);
        add_action('manage_richiesta_socio_posts_custom_column', [__CLASS__, 'custom_column_content'], 10, 2);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
        add_action('admin_footer', [__CLASS__, 'render_approval_modal']);
    }
    
    /**
     * Registra CPT
     */
    public static function register_post_type() {
        register_post_type('richiesta_socio', [
            'labels' => [
                'name' => 'Richieste Adesione',
                'singular_name' => 'Richiesta Adesione',
                'add_new' => 'Nuova Richiesta',
                'add_new_item' => 'Aggiungi Richiesta Adesione',
                'edit_item' => 'Modifica Richiesta',
                'view_item' => 'Visualizza Richiesta',
                'search_items' => 'Cerca Richieste',
                'not_found' => 'Nessuna richiesta trovata',
                'menu_name' => 'Richieste Adesione'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Integrato nel menu CRM
            'supports' => ['title'],
            'capability_type' => 'post',
            'has_archive' => false,
            'menu_icon' => 'dashicons-groups'
        ]);
    }
    
    /**
     * Aggiungi metaboxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'richiesta_socio_details',
            'Dettagli Richiesta Adesione',
            [__CLASS__, 'render_metabox'],
            'richiesta_socio',
            'normal',
            'high'
        );
    }
    
    /**
     * Render metabox
     */
    public static function render_metabox($post) {
        wp_nonce_field('richiesta_socio_save', 'richiesta_socio_nonce');
        
        $nome = get_post_meta($post->ID, 'nome', true);
        $cognome = get_post_meta($post->ID, 'cognome', true);
        $email = get_post_meta($post->ID, 'email', true);
        $telefono = get_post_meta($post->ID, 'telefono', true);
        $data_nascita = get_post_meta($post->ID, 'data_nascita', true);
        $luogo_nascita = get_post_meta($post->ID, 'luogo_nascita', true);
        $codice_fiscale = get_post_meta($post->ID, 'codice_fiscale', true);
        $indirizzo = get_post_meta($post->ID, 'indirizzo', true);
        $citta = get_post_meta($post->ID, 'citta', true);
        $cap = get_post_meta($post->ID, 'cap', true);
        $provincia = get_post_meta($post->ID, 'provincia', true);
        $professione = get_post_meta($post->ID, 'professione', true);
        $motivazione = get_post_meta($post->ID, 'motivazione', true);
        $note_admin = get_post_meta($post->ID, 'note_admin', true);
        
        ?>
        <style>
            .richiesta-socio-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }
            .richiesta-socio-full {
                grid-column: 1 / -1;
            }
            .richiesta-socio-field label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .richiesta-socio-field input,
            .richiesta-socio-field textarea {
                width: 100%;
            }
        </style>
        
        <div class="richiesta-socio-grid">
            <div class="richiesta-socio-field">
                <label>Nome *</label>
                <input type="text" name="nome" value="<?php echo esc_attr($nome); ?>" required>
            </div>
            
            <div class="richiesta-socio-field">
                <label>Cognome *</label>
                <input type="text" name="cognome" value="<?php echo esc_attr($cognome); ?>" required>
            </div>
            
            <div class="richiesta-socio-field">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo esc_attr($email); ?>" required>
            </div>
            
            <div class="richiesta-socio-field">
                <label>Telefono</label>
                <input type="text" name="telefono" value="<?php echo esc_attr($telefono); ?>">
            </div>
            
            <div class="richiesta-socio-field">
                <label>Data di Nascita</label>
                <input type="date" name="data_nascita" value="<?php echo esc_attr($data_nascita); ?>">
            </div>
            
            <div class="richiesta-socio-field">
                <label>Luogo di Nascita</label>
                <input type="text" name="luogo_nascita" value="<?php echo esc_attr($luogo_nascita); ?>">
            </div>
            
            <div class="richiesta-socio-field richiesta-socio-full">
                <label>Codice Fiscale</label>
                <input type="text" name="codice_fiscale" value="<?php echo esc_attr($codice_fiscale); ?>" maxlength="16">
            </div>
            
            <div class="richiesta-socio-field richiesta-socio-full">
                <label>Indirizzo</label>
                <input type="text" name="indirizzo" value="<?php echo esc_attr($indirizzo); ?>">
            </div>
            
            <div class="richiesta-socio-field">
                <label>Città</label>
                <input type="text" name="citta" value="<?php echo esc_attr($citta); ?>">
            </div>
            
            <div class="richiesta-socio-field">
                <label>CAP</label>
                <input type="text" name="cap" value="<?php echo esc_attr($cap); ?>" maxlength="5">
            </div>
            
            <div class="richiesta-socio-field">
                <label>Provincia</label>
                <input type="text" name="provincia" value="<?php echo esc_attr($provincia); ?>" maxlength="2">
            </div>
            
            <div class="richiesta-socio-field">
                <label>Professione</label>
                <input type="text" name="professione" value="<?php echo esc_attr($professione); ?>">
            </div>
            
            <div class="richiesta-socio-field richiesta-socio-full">
                <label>Motivazione Adesione</label>
                <textarea name="motivazione" rows="4"><?php echo esc_textarea($motivazione); ?></textarea>
            </div>
            
            <div class="richiesta-socio-field richiesta-socio-full">
                <label>Note Admin (riservate)</label>
                <textarea name="note_admin" rows="3"><?php echo esc_textarea($note_admin); ?></textarea>
            </div>
        </div>
        
        <p>
            <strong>Data Richiesta:</strong> <?php echo get_the_date('d/m/Y H:i', $post); ?><br>
            <strong>Stato:</strong> 
            <select name="post_status">
                <option value="pending" <?php selected($post->post_status, 'pending'); ?>>In Attesa</option>
                <option value="publish" <?php selected($post->post_status, 'publish'); ?>>Approvata</option>
                <option value="draft" <?php selected($post->post_status, 'draft'); ?>>In Revisione</option>
            </select>
        </p>
        <?php
    }
    
    /**
     * Salva metabox
     */
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['richiesta_socio_nonce']) || 
            !wp_verify_nonce($_POST['richiesta_socio_nonce'], 'richiesta_socio_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        $fields = [
            'nome', 'cognome', 'email', 'telefono', 'data_nascita', 
            'luogo_nascita', 'codice_fiscale', 'indirizzo', 'citta', 
            'cap', 'provincia', 'professione', 'motivazione', 'note_admin'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Aggiorna titolo post automaticamente
        if (isset($_POST['nome']) && isset($_POST['cognome'])) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => sanitize_text_field($_POST['nome'] . ' ' . $_POST['cognome'])
            ]);
        }
    }
    
    /**
     * Colonne personalizzate
     */
    public static function set_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = 'Socio';
        $new_columns['email'] = 'Email';
        $new_columns['telefono'] = 'Telefono';
        $new_columns['citta'] = 'Città';
        $new_columns['data'] = 'Data Richiesta';
        $new_columns['stato'] = 'Stato';
        $new_columns['azioni'] = 'Azioni';
        return $new_columns;
    }
    
    /**
     * Contenuto colonne
     */
    public static function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'email':
                echo esc_html(get_post_meta($post_id, 'email', true));
                break;
                
            case 'telefono':
                echo esc_html(get_post_meta($post_id, 'telefono', true));
                break;
                
            case 'citta':
                echo esc_html(get_post_meta($post_id, 'citta', true));
                break;
                
            case 'data':
                echo get_the_date('d/m/Y', $post_id);
                break;
                
            case 'stato':
                $status = get_post_status($post_id);
                $colors = [
                    'pending' => '#ff9800',
                    'publish' => '#4caf50',
                    'draft' => '#2196f3'
                ];
                $labels = [
                    'pending' => 'In Attesa',
                    'publish' => 'Approvata',
                    'draft' => 'In Revisione'
                ];
                $color = $colors[$status] ?? '#999';
                $label = $labels[$status] ?? $status;
                echo '<span style="background: ' . $color . '; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">' . $label . '</span>';
                break;
                
            case 'azioni':
                $status = get_post_status($post_id);
                $user_id_socio = get_post_meta($post_id, 'user_id_socio', true);
                $nome = get_post_meta($post_id, 'nome', true);
                $cognome = get_post_meta($post_id, 'cognome', true);
                $email = get_post_meta($post_id, 'email', true);
                
                if ($status === 'pending') {
                    // Pulsante Approva
                    echo '<button type="button" class="button button-primary wecoop-approva-btn" 
                            data-richiesta-id="' . $post_id . '"
                            data-nome="' . esc_attr($nome . ' ' . $cognome) . '"
                            data-email="' . esc_attr($email) . '">✓ Approva</button> ';
                    echo '<button type="button" class="button wecoop-rifiuta-btn" 
                            data-richiesta-id="' . $post_id . '"
                            data-nome="' . esc_attr($nome . ' ' . $cognome) . '"
                            style="background: #dc3545; color: white; border-color: #dc3545;">✗ Rifiuta</button>';
                } elseif ($status === 'publish' && $user_id_socio) {
                    echo '<span style="color: #4caf50;">✓ Socio creato (ID: ' . $user_id_socio . ')</span>';
                } elseif ($status === 'publish' && !$user_id_socio) {
                    echo '<button type="button" class="button button-primary wecoop-approva-btn" 
                            data-richiesta-id="' . $post_id . '"
                            data-nome="' . esc_attr($nome . ' ' . $cognome) . '"
                            data-email="' . esc_attr($email) . '"
                            style="background: #ff9800; border-color: #ff9800;">⚠ Crea Socio</button>';
                } else {
                    echo '<span style="color: #999;">Rifiutata</span>';
                }
                break;
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($post_type !== 'richiesta_socio') {
            return;
        }
        
        wp_localize_script('jquery', 'wecoopRichieste', [
            'apiUrl' => rest_url('wecoop/v1'),
            'nonce' => wp_create_nonce('wp_rest')
        ]);
    }
    
    /**
     * Render modal approvazione
     */
    public static function render_approval_modal() {
        global $post_type;
        
        if ($post_type !== 'richiesta_socio') {
            return;
        }
        
        // Genera UUID per numero tessera
        $uuid_tessera = wp_generate_uuid4();
        
        ?>
        <style>
        .wecoop-modal {
            display: none;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .wecoop-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 500px;
            max-width: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .wecoop-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .wecoop-modal-close:hover {
            color: #000;
        }
        .wecoop-modal h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        </style>
        
        <!-- Modal Approvazione -->
        <div id="wecoop-approval-modal" class="wecoop-modal">
            <div class="wecoop-modal-content">
                <span class="wecoop-modal-close">&times;</span>
                <h2>✓ Approva Richiesta Adesione</h2>
                <div id="wecoop-approval-form">
                    <p><strong>Socio:</strong> <span id="modal-socio-nome"></span></p>
                    <p><strong>Email:</strong> <span id="modal-socio-email"></span></p>
                    <hr style="margin: 20px 0;">
                    
                    <div class="form-group">
                        <label>Numero Tessera (UUID) *</label>
                        <input type="text" id="modal-numero-tessera" value="<?php echo esc_attr($uuid_tessera); ?>" readonly style="width: 100%; padding: 8px; background: #f0f0f0; font-family: monospace; font-size: 11px;">
                        <p class="description">Generato automaticamente - Univoco e sicuro</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Data Adesione</label>
                        <input type="date" id="modal-data-adesione" value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 8px;">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="modal-quota-pagata">
                            Quota associativa pagata
                        </label>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="button" id="modal-cancel-btn">Annulla</button>
                        <button type="button" class="button button-primary" id="modal-approve-btn">✓ Conferma Approvazione</button>
                    </div>
                    
                    <div id="modal-message" style="margin-top: 15px; padding: 10px; display: none; border-radius: 4px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Modal Rifiuto -->
        <div id="wecoop-reject-modal" class="wecoop-modal">
            <div class="wecoop-modal-content">
                <span class="wecoop-modal-close">&times;</span>
                <h2>✗ Rifiuta Richiesta</h2>
                <div id="wecoop-reject-form">
                    <p><strong>Socio:</strong> <span id="modal-reject-socio-nome"></span></p>
                    
                    <div class="form-group">
                        <label>Motivo rifiuto</label>
                        <textarea id="modal-motivo-rifiuto" rows="4" style="width: 100%; padding: 8px;"></textarea>
                    </div>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="button" class="button" id="modal-reject-cancel-btn">Annulla</button>
                        <button type="button" class="button" id="modal-reject-btn" style="background: #dc3545; color: white; border-color: #dc3545;">✗ Conferma Rifiuto</button>
                    </div>
                    
                    <div id="modal-reject-message" style="margin-top: 15px; padding: 10px; display: none; border-radius: 4px;"></div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            console.log('WECOOP Richieste: Script caricato');
            
            const approvalModal = $('#wecoop-approval-modal');
            const rejectModal = $('#wecoop-reject-modal');
            let currentRichiestaId = null;
            
            // Apri modal approvazione
            $(document).on('click', '.wecoop-approva-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('Click su approva, ID:', $(this).data('richiesta-id'));
                
                currentRichiestaId = $(this).data('richiesta-id');
                $('#modal-socio-nome').text($(this).data('nome'));
                $('#modal-socio-email').text($(this).data('email'));
                $('#modal-message').hide();
                
                // Usa setTimeout per assicurarsi che la modale si apra
                setTimeout(function() {
                    approvalModal.css('display', 'block');
                    console.log('Modale aperta');
                }, 10);
                
                return false;
            });
            
            // Apri modal rifiuto
            $(document).on('click', '.wecoop-rifiuta-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                currentRichiestaId = $(this).data('richiesta-id');
                $('#modal-reject-socio-nome').text($(this).data('nome'));
                $('#modal-reject-message').hide();
                
                setTimeout(function() {
                    rejectModal.css('display', 'block');
                }, 10);
                
                return false;
            });
            
            // Chiudi modals
            $('.wecoop-modal-close, #modal-cancel-btn, #modal-reject-cancel-btn').click(function(e) {
                e.preventDefault();
                approvalModal.css('display', 'none');
                rejectModal.css('display', 'none');
            });
            
            $(window).click(function(e) {
                if ($(e.target).hasClass('wecoop-modal')) {
                    approvalModal.css('display', 'none');
                    rejectModal.css('display', 'none');
                }
            });
            
            // Conferma approvazione
            $('#modal-approve-btn').click(function() {
                const numeroTessera = $('#modal-numero-tessera').val();
                const dataAdesione = $('#modal-data-adesione').val();
                const quotaPagata = $('#modal-quota-pagata').is(':checked');
                
                if (!numeroTessera) {
                    alert('Inserisci il numero tessera');
                    return;
                }
                
                $(this).prop('disabled', true).text('Approvazione in corso...');
                
                $.ajax({
                    url: wecoopRichieste.apiUrl + '/soci/richiesta/' + currentRichiestaId + '/approva',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', wecoopRichieste.nonce);
                    },
                    data: {
                        numero_tessera: numeroTessera,
                        data_adesione: dataAdesione,
                        quota_pagata: quotaPagata
                    },
                    success: function(response) {
                        $('#modal-message')
                            .css('background', '#d4edda')
                            .css('color', '#155724')
                            .html('✓ Richiesta approvata! Socio creato con ID: ' + response.data.user_id + '<br>Email inviata a: ' + response.data.email)
                            .show();
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON ? xhr.responseJSON.message : 'Errore sconosciuto';
                        $('#modal-message')
                            .css('background', '#f8d7da')
                            .css('color', '#721c24')
                            .text('✗ Errore: ' + error)
                            .show();
                        $('#modal-approve-btn').prop('disabled', false).text('✓ Conferma Approvazione');
                    }
                });
            });
            
            // Conferma rifiuto
            $('#modal-reject-btn').click(function() {
                const motivo = $('#modal-motivo-rifiuto').val();
                
                $(this).prop('disabled', true).text('Rifiuto in corso...');
                
                $.ajax({
                    url: wecoopRichieste.apiUrl + '/soci/richiesta/' + currentRichiestaId + '/rifiuta',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', wecoopRichieste.nonce);
                    },
                    data: {
                        motivo: motivo
                    },
                    success: function(response) {
                        $('#modal-reject-message')
                            .css('background', '#d4edda')
                            .css('color', '#155724')
                            .text('✓ Richiesta rifiutata')
                            .show();
                        
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON ? xhr.responseJSON.message : 'Errore sconosciuto';
                        $('#modal-reject-message')
                            .css('background', '#f8d7da')
                            .css('color', '#721c24')
                            .text('✗ Errore: ' + error)
                            .show();
                        $('#modal-reject-btn').prop('disabled', false).text('✗ Conferma Rifiuto');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
