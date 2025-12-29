<?php
/**
 * Pagina Dettaglio Utente
 * Include form completamento profilo, approvazione socio e WhatsApp
 */

if (!defined('ABSPATH')) exit;

class WeCoop_User_Detail_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_wecoop_users_completa_profilo', [$this, 'handle_completa_profilo']);
        add_action('admin_post_wecoop_users_approva_socio', [$this, 'handle_approva_socio']);
        add_action('admin_post_wecoop_users_revoca_socio', [$this, 'handle_revoca_socio']);
        add_action('admin_post_wecoop_users_upload_documento', [$this, 'handle_upload_documento']);
        add_action('admin_post_wecoop_users_elimina_documento', [$this, 'handle_elimina_documento']);
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            null, // Hidden submenu
            'Dettaglio Utente',
            'Dettaglio Utente',
            'manage_options',
            'wecoop-user-detail',
            [$this, 'render_page']
        );
    }
    
    public function handle_completa_profilo() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_completa_profilo');
        
        $user_id = intval($_POST['user_id']);
        
        // Aggiorna profilo utente (campi opzionali, salva solo se presenti)
        $update_data = ['ID' => $user_id];
        
        if (!empty($_POST['email'])) {
            $update_data['user_email'] = sanitize_email($_POST['email']);
        }
        if (!empty($_POST['first_name'])) {
            $update_data['first_name'] = sanitize_text_field($_POST['first_name']);
        }
        if (!empty($_POST['last_name'])) {
            $update_data['last_name'] = sanitize_text_field($_POST['last_name']);
        }
        if (!empty($_POST['display_name'])) {
            $update_data['display_name'] = sanitize_text_field($_POST['display_name']);
        }
        
        $result = wp_update_user($update_data);
        
        if (!is_wp_error($result)) {
            // Aggiorna user meta (solo se valorizzati)
            $meta_fields = [
                'indirizzo', 'civico', 'citta', 'cap', 'provincia', 'nazione',
                'codice_fiscale', 'data_nascita', 'luogo_nascita'
            ];
            
            foreach ($meta_fields as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $value = sanitize_text_field($_POST[$field]);
                    if (in_array($field, ['provincia', 'codice_fiscale'])) {
                        $value = strtoupper($value);
                    }
                    update_user_meta($user_id, $field, $value);
                }
            }
            
            // Verifica se profilo completo
            $campi_obbligatori = ['first_name', 'last_name', 'codice_fiscale', 'data_nascita', 
                                  'luogo_nascita', 'indirizzo', 'civico', 'cap', 'citta', 'provincia'];
            $completo = true;
            foreach ($campi_obbligatori as $campo) {
                $valore = ($campo === 'first_name' || $campo === 'last_name') 
                    ? get_userdata($user_id)->$campo 
                    : get_user_meta($user_id, $campo, true);
                if (empty($valore)) {
                    $completo = false;
                    break;
                }
            }
            update_user_meta($user_id, 'profilo_completo', $completo);
            
            // Aggiorna anche i meta nel post richiesta_socio
            $richiesta = get_posts([
                'post_type' => 'richiesta_socio',
                'meta_key' => 'user_id_socio',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ]);
            
            if (!empty($richiesta)) {
                $post_id = $richiesta[0]->ID;
                foreach ($meta_fields as $field) {
                    if (isset($_POST[$field]) && $_POST[$field] !== '') {
                        $value = sanitize_text_field($_POST[$field]);
                        if (in_array($field, ['provincia', 'codice_fiscale'])) {
                            $value = strtoupper($value);
                        }
                        update_post_meta($post_id, $field, $value);
                    }
                }
                if (!empty($_POST['email'])) {
                    update_post_meta($post_id, 'email', sanitize_email($_POST['email']));
                }
                update_post_meta($post_id, 'profilo_completo', $completo);
            }
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'profilo_salvato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode($result->get_error_message())
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function handle_approva_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_approva_socio');
        
        $user_id = intval($_POST['user_id']);
        
        // Trova richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            $post_id = $richiesta[0]->ID;
            
            // Aggiorna status
            update_post_meta($post_id, 'is_socio', true);
            update_post_meta($post_id, 'data_approvazione', current_time('mysql'));
            update_user_meta($user_id, 'is_socio', true);
            
            // Cambia ruolo a 'socio'
            $user = new WP_User($user_id);
            if (!get_role('socio')) {
                add_role('socio', 'Socio', get_role('subscriber')->capabilities);
            }
            $user->set_role('socio');
            
            // Cambia status post
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish'
            ]);
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'socio_approvato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore_richiesta'
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function handle_revoca_socio() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_revoca_socio');
        
        $user_id = intval($_POST['user_id']);
        
        update_user_meta($user_id, 'is_socio', false);
        
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            update_post_meta($richiesta[0]->ID, 'is_socio', false);
            update_post_meta($richiesta[0]->ID, 'data_revoca', current_time('mysql'));
        }
        
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        wp_redirect(add_query_arg([
            'page' => 'wecoop-user-detail',
            'user_id' => $user_id,
            'message' => 'socio_revocato'
        ], admin_url('admin.php')));
        exit;
    }
    
    public function handle_upload_documento() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_upload_documento');
        
        $user_id = intval($_POST['user_id']);
        $tipo_documento = sanitize_text_field($_POST['tipo_documento']);
        
        if (empty($_FILES['documento_file']['name'])) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode('Nessun file selezionato')
            ], admin_url('admin.php')));
            exit;
        }
        
        // Crea directory upload se non esiste
        $upload_dir = wp_upload_dir();
        $wecoop_dir = $upload_dir['basedir'] . '/wecoop-users/' . $user_id;
        if (!file_exists($wecoop_dir)) {
            wp_mkdir_p($wecoop_dir);
        }
        
        // Valida tipo file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $file_type = $_FILES['documento_file']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode('Tipo file non consentito. Solo JPG, PNG o PDF.')
            ], admin_url('admin.php')));
            exit;
        }
        
        // Valida dimensione (max 5MB)
        if ($_FILES['documento_file']['size'] > 5 * 1024 * 1024) {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode('File troppo grande. Massimo 5MB.')
            ], admin_url('admin.php')));
            exit;
        }
        
        // Genera nome file univoco
        $extension = pathinfo($_FILES['documento_file']['name'], PATHINFO_EXTENSION);
        $filename = $tipo_documento . '_' . time() . '.' . $extension;
        $filepath = $wecoop_dir . '/' . $filename;
        
        // Upload file
        if (move_uploaded_file($_FILES['documento_file']['tmp_name'], $filepath)) {
            // Salva riferimento in user_meta
            $documenti = get_user_meta($user_id, 'documenti', true) ?: [];
            $documenti[] = [
                'tipo' => $tipo_documento,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => $upload_dir['baseurl'] . '/wecoop-users/' . $user_id . '/' . $filename,
                'data_upload' => current_time('mysql')
            ];
            update_user_meta($user_id, 'documenti', $documenti);
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'documento_caricato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode('Errore durante l\'upload del file')
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function handle_elimina_documento() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        check_admin_referer('wecoop_users_elimina_documento');
        
        $user_id = intval($_POST['user_id']);
        $doc_index = intval($_POST['doc_index']);
        
        $documenti = get_user_meta($user_id, 'documenti', true) ?: [];
        
        if (isset($documenti[$doc_index])) {
            // Elimina file fisico
            if (file_exists($documenti[$doc_index]['filepath'])) {
                unlink($documenti[$doc_index]['filepath']);
            }
            
            // Rimuovi da array
            array_splice($documenti, $doc_index, 1);
            update_user_meta($user_id, 'documenti', $documenti);
            
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'documento_eliminato'
            ], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg([
                'page' => 'wecoop-user-detail',
                'user_id' => $user_id,
                'message' => 'errore',
                'error_msg' => urlencode('Documento non trovato')
            ], admin_url('admin.php')));
        }
        exit;
    }
    
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Accesso negato');
        }
        
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        if (!$user_id) {
            echo '<div class="wrap">';
            echo '<h1>Dettaglio Utente</h1>';
            echo '<div class="notice notice-error"><p>ID utente non specificato.</p></div>';
            echo '</div>';
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            echo '<div class="wrap">';
            echo '<h1>Dettaglio Utente</h1>';
            echo '<div class="notice notice-error"><p>Utente non trovato.</p></div>';
            echo '</div>';
            return;
        }
        
        // Recupera dati utente
        $is_socio = get_user_meta($user_id, 'is_socio', true);
        $profilo_completo = get_user_meta($user_id, 'profilo_completo', true);
        $telefono_completo = get_user_meta($user_id, 'telefono_completo', true) ?: $user->user_login;
        $indirizzo = get_user_meta($user_id, 'indirizzo', true);
        $civico = get_user_meta($user_id, 'civico', true);
        $citta = get_user_meta($user_id, 'citta', true);
        $cap = get_user_meta($user_id, 'cap', true);
        $provincia = get_user_meta($user_id, 'provincia', true);
        $nazione = get_user_meta($user_id, 'nazione', true);
        $codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
        $data_nascita = get_user_meta($user_id, 'data_nascita', true);
        $luogo_nascita = get_user_meta($user_id, 'luogo_nascita', true);
        $documenti = get_user_meta($user_id, 'documenti', true) ?: [];
        
        // Recupera richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        $numero_pratica = '';
        $data_registrazione = $user->user_registered;
        if (!empty($richiesta)) {
            $numero_pratica = get_post_meta($richiesta[0]->ID, 'numero_pratica', true);
            $data_registrazione = $richiesta[0]->post_date;
        }
        
        // Messaggio WhatsApp
        $whatsapp_number = preg_replace('/[^0-9]/', '', $telefono_completo);
        $whatsapp_message = "Ciao {$user->display_name}, ti contattiamo da WeCoop per completare il tuo profilo socio.";
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($whatsapp_message);
        
        // Messaggi
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        
        require WECOOP_USERS_PLUGIN_DIR . 'templates/user-detail.php';
    }
}

new WeCoop_User_Detail_Page();
