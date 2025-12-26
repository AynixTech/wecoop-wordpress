<?php
/**
 * Integrazione WooCommerce per Richieste Servizi
 * 
 * Gestisce creazione ordini e pagamenti per le richieste servizi
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_WooCommerce_Integration {
    
    /**
     * Inizializza
     */
    public static function init() {
        // Hook quando salvi una richiesta servizio
        add_action('save_post_richiesta_servizio', [__CLASS__, 'on_richiesta_save'], 20, 3);
        
        // Hook quando lo stato dell'ordine WooCommerce cambia
        add_action('woocommerce_order_status_changed', [__CLASS__, 'on_order_status_change'], 10, 4);
        
        // Aggiungi meta box per vedere ordine collegato
        add_action('add_meta_boxes', [__CLASS__, 'add_order_metabox']);
        
        // Permetti pagamento ordini senza login se hanno chiave valida
        add_action('template_redirect', [__CLASS__, 'allow_guest_payment']);
        
        // Disabilita email automatiche WooCommerce per ordini creati da noi
        add_filter('woocommerce_email_enabled_customer_on_hold_order', [__CLASS__, 'disable_wc_emails'], 10, 2);
        add_filter('woocommerce_email_enabled_customer_processing_order', [__CLASS__, 'disable_wc_emails'], 10, 2);
        add_filter('woocommerce_email_enabled_customer_completed_order', [__CLASS__, 'disable_wc_emails'], 10, 2);
        add_filter('woocommerce_email_enabled_new_order', [__CLASS__, 'disable_wc_emails'], 10, 2);
        
        // Carica template personalizzato per mostrare items in order-pay
        add_action('wp', [__CLASS__, 'load_order_pay_template']);
    }
    
    /**
     * Carica template personalizzato per order-pay
     */
    public static function load_order_pay_template() {
        error_log('[WECOOP] load_order_pay_template chiamato');
        error_log('[WECOOP] is_wc_endpoint_url(order-pay): ' . (is_wc_endpoint_url('order-pay') ? 'YES' : 'NO'));
        error_log('[WECOOP] Current URL: ' . $_SERVER['REQUEST_URI']);
        
        if (is_wc_endpoint_url('order-pay')) {
            $template_file = WECOOP_SERVIZI_PATH . 'templates/order-pay-items.php';
            error_log('[WECOOP] Template file path: ' . $template_file);
            error_log('[WECOOP] Template exists: ' . (file_exists($template_file) ? 'YES' : 'NO'));
            
            if (file_exists($template_file)) {
                error_log('[WECOOP] ✅ Caricamento template order-pay-items.php');
                include_once $template_file;
            } else {
                error_log('[WECOOP] ❌ Template NON trovato!');
            }
        }
    }
    
    /**
     * Disabilita email WooCommerce per ordini creati da WeCoop Servizi
     */
    public static function disable_wc_emails($enabled, $order) {
        if (!$order) return $enabled;
        
        $created_via = $order->get_meta('_created_via');
        if ($created_via === 'wecoop_servizi') {
            return false; // Disabilita email WooCommerce
        }
        
        return $enabled;
    }
    
    /**
     * Permetti agli utenti di pagare senza login se hanno il link con chiave corretta
     */
    public static function allow_guest_payment() {
        error_log('[WECOOP] allow_guest_payment chiamato');
        error_log('[WECOOP] is_checkout: ' . (is_checkout() ? 'YES' : 'NO'));
        error_log('[WECOOP] is_order_received_page: ' . (is_order_received_page() ? 'YES' : 'NO'));
        error_log('[WECOOP] GET params: ' . print_r($_GET, true));
        
        // Previeni conflitti con endpoint GDPR
        if (isset($_GET['action']) && $_GET['action'] === 'delete_customer') {
            error_log('[WECOOP] Skipping: GDPR delete_customer action');
            return;
        }
        
        if (!is_checkout() && !is_order_received_page()) {
            error_log('[WECOOP] Skipping: Non è checkout né order-received');
            return;
        }
        
        // Se c'è un parametro key nell'URL, forza checkout guest
        if (isset($_GET['key'])) {
            error_log('[WECOOP] ✅ Parametro KEY trovato: ' . $_GET['key']);
            error_log('[WECOOP] Applicazione filtri per guest payment...');
            
            // Forza checkout guest
            add_filter('woocommerce_enable_guest_checkout', '__return_true', 999);
            add_filter('pre_option_woocommerce_enable_guest_checkout', function() { return 'yes'; }, 999);
            add_filter('woocommerce_checkout_login_message', '__return_false', 999);
            add_filter('woocommerce_enable_signup_and_login_from_checkout', '__return_false', 999);
            add_filter('option_woocommerce_enable_checkout_login_reminder', '__return_false', 999);
            
            // Nascondi form di login
            remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
            
            // Permetti il checkout anche se l'utente è loggato
            add_filter('woocommerce_checkout_registration_required', '__return_false', 999);
            
            // Permetti il pagamento di ordini pending
            add_filter('woocommerce_valid_order_statuses_for_payment', function($statuses) {
                if (!in_array('pending', $statuses)) {
                    $statuses[] = 'pending';
                }
                if (!in_array('on-hold', $statuses)) {
                    $statuses[] = 'on-hold';
                }
                error_log('[WECOOP] Valid payment statuses: ' . implode(', ', $statuses));
                return $statuses;
            }, 999);
            
            // Forza needs_payment a true
            add_filter('woocommerce_order_needs_payment', function($needs_payment, $order) {
                if ($order && in_array($order->get_status(), ['pending', 'on-hold']) && $order->get_total() > 0) {
                    error_log('[WECOOP] Order #' . $order->get_id() . ' needs payment: FORCED TRUE');
                    return true;
                }
                error_log('[WECOOP] Order needs payment: ' . ($needs_payment ? 'YES' : 'NO'));
                return $needs_payment;
            }, 999, 2);
            
            // Forza visualizzazione contenuto ordine
            add_filter('woocommerce_is_purchasable', '__return_true', 999);
            add_filter('woocommerce_order_item_visible', '__return_true', 999);
            add_filter('woocommerce_order_item_quantity_html', function($qty_html, $item) {
                return $item->get_quantity();
            }, 999, 2);
            
            error_log('[WECOOP] ✅ Tutti i filtri applicati');
        } else {
            error_log('[WECOOP] Nessun parametro KEY trovato');
        }
    }
    
    /**
     * Quando salvi una richiesta servizio e cambi lo stato a "awaiting_payment"
     */
    public static function on_richiesta_save($post_id, $post, $update) {
        // Previeni loop infiniti
        if (defined('WECOOP_SERVIZI_UPDATING')) return;
        
        if (!$update) return; // Solo su update, non su nuovo post
        if (wp_is_post_revision($post_id)) return;
        
        $stato = get_post_meta($post_id, 'stato', true);
        $order_id = get_post_meta($post_id, 'wc_order_id', true);
        
        // Se stato = awaiting_payment e non c'è ancora un ordine
        if ($stato === 'awaiting_payment' && !$order_id) {
            self::crea_ordine_woocommerce($post_id);
        }
    }
    
    /**
     * Crea ordine WooCommerce per la richiesta servizio
     */
    public static function crea_ordine_woocommerce($richiesta_id) {
        if (!class_exists('WooCommerce')) {
            error_log('[WECOOP SERVIZI] WooCommerce non è attivo!');
            return false;
        }
        
        $richiesta = get_post($richiesta_id);
        if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
            return false;
        }
        
        // Dati richiesta
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        $dati_json = get_post_meta($richiesta_id, 'dati', true);
        $dati = json_decode($dati_json, true) ?: [];
        
        if (!$importo || $importo <= 0) {
            error_log('[WECOOP SERVIZI] Impossibile creare ordine: importo non valido');
            update_post_meta($richiesta_id, 'payment_error', 'Importo non specificato');
            return false;
        }
        
        if (!$user_id) {
            error_log('[WECOOP SERVIZI] Impossibile creare ordine: user_id mancante');
            update_post_meta($richiesta_id, 'payment_error', 'Utente non trovato');
            return false;
        }
        
        try {
            // Ottieni o crea il prodotto virtuale generico per i servizi
            $product_id = self::get_or_create_service_product();
            if (!$product_id) {
                throw new Exception('Impossibile creare prodotto virtuale');
            }
            
            error_log('========================================');
            error_log('[WECOOP SERVIZI] INIZIO CREAZIONE ORDINE');
            error_log('[WECOOP SERVIZI] Richiesta ID: ' . $richiesta_id);
            error_log('[WECOOP SERVIZI] User ID: ' . $user_id);
            error_log('[WECOOP SERVIZI] Servizio: ' . $servizio);
            error_log('[WECOOP SERVIZI] Importo: ' . $importo);
            error_log('[WECOOP SERVIZI] Prodotto WC ID: ' . $product_id);
            
            // Abilita temporaneamente il checkout per guest
            add_filter('pre_option_woocommerce_enable_guest_checkout', function() { return 'yes'; }, 999);
            add_filter('pre_option_woocommerce_enable_checkout_login_reminder', function() { return 'no'; }, 999);
            add_filter('woocommerce_checkout_registration_required', '__return_false', 999);
            
            // Crea ordine WooCommerce associato all'utente
            error_log('[WECOOP SERVIZI] Creazione ordine WC...');
            $order = wc_create_order([
                'customer_id' => $user_id, // Ordine associato all'utente reale
                'status' => 'pending'
            ]);
            
            if (is_wp_error($order)) {
                error_log('[WECOOP SERVIZI] ERRORE wc_create_order: ' . $order->get_error_message());
                throw new Exception($order->get_error_message());
            }
            
            error_log('[WECOOP SERVIZI] Ordine creato ID: ' . $order->get_id());
            
            // Salva meta dati per tracciamento interno
            $order->update_meta_data('_created_via', 'wecoop_servizi');
            $order->update_meta_data('_wecoop_richiesta_id', $richiesta_id);
            
            error_log('[WECOOP SERVIZI] Meta dati ordine salvati');
            
            // Imposta dati di fatturazione dall'utente/richiesta
            $user = get_userdata($user_id);
            if ($user) {
                // Prepara dati di fatturazione
                $first_name = $dati['nome'] ?? $user->first_name ?? '';
                $last_name = $dati['cognome'] ?? $user->last_name ?? '';
                $email = $dati['email'] ?? $user->user_email ?? '';
                $phone = $dati['telefono'] ?? $dati['cellulare'] ?? '';
                $address = $dati['indirizzo'] ?? $dati['via'] ?? '';
                $city = $dati['citta'] ?? $dati['comune'] ?? '';
                $postcode = $dati['cap'] ?? '';
                
                // Usa i metodi setter di WooCommerce per garantire la persistenza
                $order->set_billing_first_name($first_name);
                $order->set_billing_last_name($last_name);
                $order->set_billing_email($email);
                $order->set_billing_phone($phone);
                $order->set_billing_address_1($address);
                $order->set_billing_city($city);
                $order->set_billing_postcode($postcode);
                $order->set_billing_country('IT');
                
                // Imposta anche i dati di spedizione uguali
                $order->set_shipping_first_name($first_name);
                $order->set_shipping_last_name($last_name);
                $order->set_shipping_address_1($address);
                $order->set_shipping_city($city);
                $order->set_shipping_postcode($postcode);
                $order->set_shipping_country('IT');
                
                error_log('[WECOOP SERVIZI] Dati fatturazione impostati: ' . $first_name . ' ' . $last_name . ' - ' . $email);
            }
            
            // Salva prima l'ordine con i dati di fatturazione
            error_log('[WECOOP SERVIZI] Salvataggio ordine...');
            $order->save();
            error_log('[WECOOP SERVIZI] Ordine salvato, inizio inserimento item...');
            
            // Aggiungi line item manualmente usando il database
            global $wpdb;
            
            $item_name = $servizio . ' - Pratica ' . $numero_pratica;
            error_log('[WECOOP SERVIZI] Nome item: ' . $item_name);
            
            // Inserisci l'item nella tabella order_items
            $insert_result = $wpdb->insert(
                $wpdb->prefix . 'woocommerce_order_items',
                [
                    'order_item_name' => $item_name,
                    'order_item_type' => 'line_item',
                    'order_id' => $order->get_id()
                ],
                ['%s', '%s', '%d']
            );
            
            if ($insert_result === false) {
                error_log('[WECOOP SERVIZI] ERRORE wpdb->insert: ' . $wpdb->last_error);
                throw new Exception('Errore inserimento item: ' . $wpdb->last_error);
            }
            
            $item_id = $wpdb->insert_id;
            
            if (!$item_id) {
                error_log('[WECOOP SERVIZI] ERRORE: insert_id = 0');
                throw new Exception('Impossibile creare line item');
            }
            
            error_log('[WECOOP SERVIZI] ✅ Item ID creato: ' . $item_id);
            
            // Aggiungi meta dati dell'item
            $item_meta = [
                '_qty' => 1,
                '_line_subtotal' => $importo,
                '_line_total' => $importo,
                '_line_subtotal_tax' => 0,
                '_line_tax' => 0,
                '_line_tax_data' => serialize([]),
                '_richiesta_servizio_id' => $richiesta_id,
                '_numero_pratica' => $numero_pratica,
                '_tipo_servizio' => $servizio,
            ];
            
            error_log('[WECOOP SERVIZI] Aggiunta meta dati item...');
            foreach ($item_meta as $meta_key => $meta_value) {
                $meta_result = wc_add_order_item_meta($item_id, $meta_key, $meta_value);
                error_log('[WECOOP SERVIZI]   - ' . $meta_key . ': ' . ($meta_result ? 'OK' : 'FAILED'));
            }
            
            error_log('[WECOOP SERVIZI] ✅ Meta dati item aggiunti');
            
            // Aggiorna i totali dell'ordine
            error_log('[WECOOP SERVIZI] Impostazione totale ordine a: ' . $importo);
            $order->set_total($importo);
            $order->save();
            
            // Ricarica l'ordine per verificare
            error_log('[WECOOP SERVIZI] Ricaricamento ordine...');
            $order = wc_get_order($order->get_id());
            $saved_items = $order->get_items();
            
            error_log('[WECOOP SERVIZI] ✅ Items dopo reload: ' . count($saved_items));
            
            if (count($saved_items) > 0) {
                foreach ($saved_items as $item) {
                    error_log('[WECOOP SERVIZI]   - Item: ' . $item->get_name() . ' | Total: ' . $item->get_total());
                }
            } else {
                error_log('[WECOOP SERVIZI] ❌ ERRORE: Nessun item trovato!');
            }
            
            if (count($saved_items) === 0) {
                throw new Exception('Errore: nessun item trovato dopo il salvataggio');
            }
            
            // Aggiungi note
            $order->add_order_note(sprintf(
                'Ordine creato automaticamente per richiesta servizio #%s (%s)',
                $numero_pratica,
                $servizio
            ));
            
            // Salva l'ordine
            $order->save();
            
            // Log dettagliato ordine creato
            $payment_url = $order->get_checkout_payment_url(true);
            error_log('[WECOOP SERVIZI] ========================================');
            error_log('[WECOOP SERVIZI] ✅ ORDINE CREATO CON SUCCESSO');
            error_log('[WECOOP SERVIZI] Order ID: ' . $order->get_id());
            error_log('[WECOOP SERVIZI] Richiesta: ' . $numero_pratica);
            error_log('[WECOOP SERVIZI] Importo: €' . number_format($importo, 2));
            error_log('[WECOOP SERVIZI] Status: ' . $order->get_status());
            error_log('[WECOOP SERVIZI] Needs Payment: ' . ($order->needs_payment() ? 'YES' : 'NO'));
            error_log('[WECOOP SERVIZI] Payment URL: ' . $payment_url);
            error_log('[WECOOP SERVIZI] Order Key: ' . $order->get_order_key());
            error_log('[WECOOP SERVIZI] Total: ' . $order->get_total());
            error_log('[WECOOP SERVIZI] Items Count: ' . count($order->get_items()));
            error_log('[WECOOP SERVIZI] ========================================');
            
            // Collega ordine alla richiesta
            update_post_meta($richiesta_id, 'wc_order_id', $order->get_id());
            update_post_meta($richiesta_id, 'payment_status', 'pending');
            update_post_meta($richiesta_id, 'payment_created_at', current_time('mysql'));
            
            // Salva link richiesta nell'ordine
            update_post_meta($order->get_id(), '_richiesta_servizio_id', $richiesta_id);
            
            // Invia email con link pagamento
            self::invia_email_pagamento($richiesta_id, $order->get_id());
            
            return $order->get_id();
            
        } catch (Exception $e) {
            error_log('[WECOOP SERVIZI] ❌❌❌ ERRORE CREAZIONE ORDINE ❌❌❌');
            error_log('[WECOOP SERVIZI] Messaggio: ' . $e->getMessage());
            error_log('[WECOOP SERVIZI] File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('[WECOOP SERVIZI] Stack trace: ' . $e->getTraceAsString());
            error_log('[WECOOP SERVIZI] ========================================');
            update_post_meta($richiesta_id, 'payment_error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invia email con link pagamento
     */
    public static function invia_email_pagamento($richiesta_id, $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("[WECOOP SERVIZI] Ordine non trovato: $order_id");
            return;
        }
        
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $user = get_userdata($user_id);
        if (!$user) {
            error_log("[WECOOP SERVIZI] Utente non trovato: $user_id");
            return;
        }
        
        // Assicurati che l'ordine sia in uno stato pagabile
        $current_status = $order->get_status();
        if (!in_array($current_status, ['pending', 'on-hold'])) {
            $order->update_status('pending', 'Ordine reimpostato a pending per pagamento');
            error_log("[WECOOP SERVIZI] Ordine $order_id reimpostato da $current_status a pending");
        }
        
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($richiesta_id, 'numero_pratica', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        
        // URL pagamento - usa metodo sicuro
        $payment_url = $order->get_checkout_payment_url(true); // true = force SSL
        
        error_log("[WECOOP SERVIZI] URL pagamento generato: $payment_url");
        error_log("[WECOOP SERVIZI] Stato ordine: " . $order->get_status());
        error_log("[WECOOP SERVIZI] Order needs payment: " . ($order->needs_payment() ? 'YES' : 'NO'));
        
        // Invia email multilingua se disponibile
        if (class_exists('WeCoop_Multilingual_Email')) {
            WeCoop_Multilingual_Email::send(
                $user->user_email,
                'service_payment_required',
                [
                    'nome' => $user->display_name,
                    'servizio' => $servizio,
                    'numero_pratica' => $numero_pratica,
                    'importo' => number_format($importo, 2, ',', '.') . ' €',
                    'payment_url' => $payment_url,
                    'button_url' => $payment_url,
                    'button_text' => 'Paga Ora'
                ],
                $user_id
            );
            
            error_log("[WECOOP SERVIZI] Email pagamento inviata a {$user->user_email}");
        } else {
            // Fallback: email standard WordPress
            $subject = 'Richiesta di Pagamento - Pratica ' . $numero_pratica;
            $message = sprintf(
                "Ciao %s,\n\n" .
                "La tua richiesta di servizio è stata presa in carico:\n\n" .
                "Servizio: %s\n" .
                "Numero Pratica: %s\n" .
                "Importo: €%s\n\n" .
                "Per procedere, completa il pagamento cliccando qui:\n%s\n\n" .
                "Grazie,\nIl Team WeCoop",
                $user->display_name,
                $servizio,
                $numero_pratica,
                number_format($importo, 2, ',', '.'),
                $payment_url
            );
            
            wp_mail($user->user_email, $subject, $message);
        }
    }
    
    /**
     * Ottieni o crea un prodotto virtuale generico per i servizi
     */
    private static function get_or_create_service_product() {
        // Cerca prodotto esistente
        $product_id = get_option('wecoop_servizi_virtual_product_id');
        
        if ($product_id && get_post($product_id)) {
            return $product_id;
        }
        
        // Crea nuovo prodotto virtuale
        $product = new WC_Product_Simple();
        $product->set_name('Servizio WeCoop');
        $product->set_status('private'); // Nascosto dal catalogo
        $product->set_catalog_visibility('hidden');
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_price(0); // Il prezzo verrà sovrascritto dinamicamente
        $product->set_regular_price(0);
        
        $product_id = $product->save();
        
        if ($product_id) {
            update_option('wecoop_servizi_virtual_product_id', $product_id);
            error_log('[WECOOP SERVIZI] Creato prodotto virtuale ID: ' . $product_id);
        }
        
        return $product_id;
    }
    
    /**
     * Quando lo stato dell'ordine WooCommerce cambia
     */
    public static function on_order_status_change($order_id, $old_status, $new_status, $order) {
        // Previeni loop
        if (defined('WECOOP_SERVIZI_UPDATING')) return;
        
        // Trova richiesta collegata
        $richiesta_id = get_post_meta($order_id, '_richiesta_servizio_id', true);
        if (!$richiesta_id) return;
        
        define('WECOOP_SERVIZI_UPDATING', true);
        
        // Sincronizza stato
        switch ($new_status) {
            case 'completed':
            case 'processing':
                // Pagamento ricevuto
                update_post_meta($richiesta_id, 'payment_status', 'paid');
                update_post_meta($richiesta_id, 'payment_paid_at', current_time('mysql'));
                update_post_meta($richiesta_id, 'stato', 'processing');
                
                error_log("[WECOOP SERVIZI] Pagamento ricevuto per richiesta #{$richiesta_id}");
                break;
                
            case 'failed':
            case 'cancelled':
                update_post_meta($richiesta_id, 'payment_status', 'failed');
                update_post_meta($richiesta_id, 'stato', 'pending');
                
                error_log("[WECOOP SERVIZI] Pagamento fallito per richiesta #{$richiesta_id}");
                break;
                
            case 'refunded':
                update_post_meta($richiesta_id, 'payment_status', 'refunded');
                
                error_log("[WECOOP SERVIZI] Pagamento rimborsato per richiesta #{$richiesta_id}");
                break;
        }
    }
    
    /**
     * Metabox per mostrare ordine collegato
     */
    public static function add_order_metabox() {
        add_meta_box(
            'richiesta_servizio_payment',
            'Pagamento',
            [__CLASS__, 'render_payment_metabox'],
            'richiesta_servizio',
            'side',
            'high'
        );
    }
    
    /**
     * Render metabox pagamento
     */
    public static function render_payment_metabox($post) {
        $order_id = get_post_meta($post->ID, 'wc_order_id', true);
        $payment_status = get_post_meta($post->ID, 'payment_status', true);
        $importo = get_post_meta($post->ID, 'importo', true);
        $payment_error = get_post_meta($post->ID, 'payment_error', true);
        
        ?>
        <div style="padding: 10px 0;">
            <p><strong>Importo Servizio:</strong></p>
            <input type="number" name="importo" step="0.01" min="0" 
                   value="<?php echo esc_attr($importo); ?>" 
                   style="width: 100%; margin-bottom: 10px;"
                   placeholder="0.00">
            <p class="description">Inserisci l'importo per abilitare il pagamento</p>
            
            <?php if ($order_id): 
                $order = wc_get_order($order_id);
                if ($order):
            ?>
                <hr style="margin: 15px 0;">
                <p><strong>Ordine WooCommerce:</strong></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')); ?>" 
                       target="_blank">
                        Ordine #<?php echo $order_id; ?>
                    </a>
                </p>
                <p><strong>Stato Ordine:</strong> 
                    <span style="color: <?php echo $order->get_status() === 'completed' ? 'green' : 'orange'; ?>;">
                        <?php echo wc_get_order_status_name($order->get_status()); ?>
                    </span>
                </p>
                <p><strong>Importo:</strong> <?php echo $order->get_formatted_order_total(); ?></p>
                <p>
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" 
                       class="button button-primary" target="_blank">
                        Link Pagamento
                    </a>
                </p>
                <?php endif; ?>
            <?php elseif ($payment_status === 'pending' || $importo > 0): ?>
                <p><em>L'ordine verrà creato quando salvi con stato "Da pagare"</em></p>
            <?php endif; ?>
            
            <?php if ($payment_error): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">
                    <strong>Errore:</strong> <?php echo esc_html($payment_error); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
