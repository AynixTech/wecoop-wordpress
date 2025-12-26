<?php
/**
 * Sistema Pagamenti Custom per WeCoop Servizi
 * 
 * Gestisce pagamenti senza WooCommerce
 * 
 * @package WECOOP_Servizi
 * @since 2.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Payment_System {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_action('init', [__CLASS__, 'create_payment_table']);
        add_action('save_post_richiesta_servizio', [__CLASS__, 'on_richiesta_save'], 20, 3);
        add_action('add_meta_boxes', [__CLASS__, 'add_payment_metabox']);
        add_action('rest_api_init', [__CLASS__, 'register_api_routes']);
    }
    
    /**
     * Crea tabella pagamenti
     */
    public static function create_payment_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            richiesta_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            importo decimal(10,2) NOT NULL,
            stato varchar(50) NOT NULL DEFAULT 'pending',
            metodo_pagamento varchar(50),
            transaction_id varchar(255),
            note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            paid_at datetime,
            PRIMARY KEY  (id),
            KEY richiesta_id (richiesta_id),
            KEY user_id (user_id),
            KEY stato (stato),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        error_log('[WECOOP PAYMENT] Tabella pagamenti verificata/creata');
    }
    
    /**
     * Quando salvi richiesta e stato = awaiting_payment
     */
    public static function on_richiesta_save($post_id, $post, $update) {
        if (defined('WECOOP_SERVIZI_UPDATING')) return;
        if (!$update) return;
        if (wp_is_post_revision($post_id)) return;
        
        $stato = get_post_meta($post_id, 'stato', true);
        $payment_id = get_post_meta($post_id, 'payment_id', true);
        
        if ($stato === 'awaiting_payment' && !$payment_id) {
            self::create_payment($post_id);
        }
    }
    
    /**
     * Crea pagamento
     */
    public static function create_payment($richiesta_id) {
        global $wpdb;
        
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        
        if (!$user_id || !$importo || $importo <= 0) {
            error_log('[WECOOP PAYMENT] Dati mancanti: user_id=' . $user_id . ', importo=' . $importo);
            return false;
        }
        
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        
        $result = $wpdb->insert(
            $table_name,
            [
                'richiesta_id' => $richiesta_id,
                'user_id' => $user_id,
                'importo' => $importo,
                'stato' => 'pending',
            ],
            ['%d', '%d', '%f', '%s']
        );
        
        if ($result) {
            $payment_id = $wpdb->insert_id;
            update_post_meta($richiesta_id, 'payment_id', $payment_id);
            update_post_meta($richiesta_id, 'payment_status', 'pending');
            
            error_log("[WECOOP PAYMENT] Pagamento #{$payment_id} creato per richiesta #{$richiesta_id}");
            
            self::send_payment_email($richiesta_id, $payment_id);
            
            return $payment_id;
        }
        
        return false;
    }
    
    /**
     * Invia email pagamento
     */
    public static function send_payment_email($richiesta_id, $payment_id) {
        $user_id = get_post_meta($richiesta_id, 'user_id', true);
        $importo = get_post_meta($richiesta_id, 'importo', true);
        $servizio = get_post_meta($richiesta_id, 'servizio', true);
        
        $user = get_userdata($user_id);
        if (!$user) return;
        
        $payment_url = home_url('/pagamento/?id=' . $payment_id);
        
        $subject = 'Richiesta di Pagamento - WeCoop';
        $message = "Ciao {$user->display_name},\n\n";
        $message .= "È richiesto un pagamento per il servizio: {$servizio}\n";
        $message .= "Importo: €" . number_format($importo, 2) . "\n\n";
        $message .= "Paga tramite questo link:\n{$payment_url}\n\n";
        $message .= "Grazie,\nIl team WeCoop";
        
        wp_mail($user->user_email, $subject, $message);
        
        error_log("[WECOOP PAYMENT] Email inviata a {$user->user_email}");
    }
    
    /**
     * Ottieni pagamento
     */
    public static function get_payment($payment_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wecoop_pagamenti';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $payment_id
        ));
    }
    
    /**
     * Aggiorna stato pagamento
     */
    public static function update_payment_status($payment_id, $stato, $data = []) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        
        $update_data = ['stato' => $stato];
        $update_format = ['%s'];
        
        if (isset($data['metodo_pagamento'])) {
            $update_data['metodo_pagamento'] = $data['metodo_pagamento'];
            $update_format[] = '%s';
        }
        
        if (isset($data['transaction_id'])) {
            $update_data['transaction_id'] = $data['transaction_id'];
            $update_format[] = '%s';
        }
        
        if (isset($data['note'])) {
            $update_data['note'] = $data['note'];
            $update_format[] = '%s';
        }
        
        if ($stato === 'completed' || $stato === 'paid') {
            $update_data['paid_at'] = current_time('mysql');
            $update_format[] = '%s';
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            ['id' => $payment_id],
            $update_format,
            ['%d']
        );
        
        if ($stato === 'completed' || $stato === 'paid') {
            $payment = self::get_payment($payment_id);
            if ($payment) {
                update_post_meta($payment->richiesta_id, 'stato', 'paid');
                update_post_meta($payment->richiesta_id, 'payment_status', 'paid');
            }
        }
        
        error_log("[WECOOP PAYMENT] Pagamento #{$payment_id} aggiornato a: {$stato}");
        
        return $result !== false;
    }
    
    /**
     * API REST
     */
    public static function register_api_routes() {
        register_rest_route('wecoop/v1', '/payment/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'api_get_payment'],
            'permission_callback' => [__CLASS__, 'api_check_permission'],
        ]);
        
        register_rest_route('wecoop/v1', '/payment/richiesta/(?P<richiesta_id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'api_get_payment_by_richiesta'],
            'permission_callback' => [__CLASS__, 'api_check_permission'],
        ]);
        
        register_rest_route('wecoop/v1', '/payments/user/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'api_get_user_payments'],
            'permission_callback' => [__CLASS__, 'api_check_permission'],
        ]);
        
        register_rest_route('wecoop/v1', '/payment/(?P<id>\d+)/confirm', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'api_confirm_payment'],
            'permission_callback' => [__CLASS__, 'api_check_permission'],
        ]);
    }
    
    public static function api_check_permission($request) {
        return is_user_logged_in();
    }
    
    public static function api_get_payment_by_richiesta($request) {
        global $wpdb;
        $richiesta_id = $request['richiesta_id'];
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        
        error_log("[WECOOP PAYMENT API] 🔍 Cercando pagamento per richiesta_id: {$richiesta_id}");
        
        $payment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE richiesta_id = %d ORDER BY created_at DESC LIMIT 1",
            $richiesta_id
        ));
        
        if (!$payment) {
            error_log("[WECOOP PAYMENT API] ❌ Nessun pagamento trovato per richiesta_id: {$richiesta_id}");
            
            // Log per debug - verifica se la richiesta esiste
            $richiesta = get_post($richiesta_id);
            if ($richiesta) {
                $servizio = get_post_meta($richiesta_id, 'servizio', true);
                $categoria = get_post_meta($richiesta_id, 'categoria', true);
                $stato = get_post_meta($richiesta_id, 'stato', true);
                error_log("[WECOOP PAYMENT API] ℹ️ Richiesta esiste - Servizio: '{$servizio}', Categoria: '{$categoria}', Stato: '{$stato}'");
            } else {
                error_log("[WECOOP PAYMENT API] ⚠️ Richiesta #{$richiesta_id} non esiste nel database!");
            }
            
            return new WP_Error('not_found', 'Pagamento non trovato', ['status' => 404]);
        }
        
        error_log("[WECOOP PAYMENT API] ✅ Pagamento trovato - ID: {$payment->id}, Importo: €{$payment->importo}, Stato: {$payment->stato}");
        
        $current_user_id = get_current_user_id();
        if ($payment->user_id != $current_user_id && !current_user_can('manage_options')) {
            error_log("[WECOOP PAYMENT API] ❌ Accesso negato - User {$current_user_id} ha cercato pagamento di user {$payment->user_id}");
            return new WP_Error('forbidden', 'Non autorizzato', ['status' => 403]);
        }
        
        $servizio = get_post_meta($payment->richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($payment->richiesta_id, 'numero_pratica', true);
        
        return [
            'id' => $payment->id,
            'importo' => floatval($payment->importo),
            'stato' => $payment->stato,
            'servizio' => $servizio,
            'numero_pratica' => $numero_pratica,
            'metodo_pagamento' => $payment->metodo_pagamento,
            'transaction_id' => $payment->transaction_id,
            'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
            'created_at' => $payment->created_at,
            'paid_at' => $payment->paid_at,
        ];
    }
    
    public static function api_get_payment($request) {
        $payment_id = $request['id'];
        $payment = self::get_payment($payment_id);
        
        if (!$payment) {
            return new WP_Error('not_found', 'Pagamento non trovato', ['status' => 404]);
        }
        
        $current_user_id = get_current_user_id();
        if ($payment->user_id != $current_user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non autorizzato', ['status' => 403]);
        }
        
        $servizio = get_post_meta($payment->richiesta_id, 'servizio', true);
        $numero_pratica = get_post_meta($payment->richiesta_id, 'numero_pratica', true);
        
        return [
            'id' => $payment->id,
            'importo' => floatval($payment->importo),
            'stato' => $payment->stato,
            'servizio' => $servizio,
            'numero_pratica' => $numero_pratica,
            'metodo_pagamento' => $payment->metodo_pagamento,
            'transaction_id' => $payment->transaction_id,
            'created_at' => $payment->created_at,
            'paid_at' => $payment->paid_at,
        ];
    }
    
    public static function api_get_user_payments($request) {
        global $wpdb;
        $user_id = $request['user_id'];
        $current_user_id = get_current_user_id();
        
        if ($user_id != $current_user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non autorizzato', ['status' => 403]);
        }
        
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
        
        $result = [];
        foreach ($payments as $payment) {
            $servizio = get_post_meta($payment->richiesta_id, 'servizio', true);
            $numero_pratica = get_post_meta($payment->richiesta_id, 'numero_pratica', true);
            
            $result[] = [
                'id' => $payment->id,
                'importo' => floatval($payment->importo),
                'stato' => $payment->stato,
                'servizio' => $servizio,
                'numero_pratica' => $numero_pratica,
                'created_at' => $payment->created_at,
                'paid_at' => $payment->paid_at,
            ];
        }
        
        return $result;
    }
    
    public static function api_confirm_payment($request) {
        $payment_id = $request['id'];
        $payment = self::get_payment($payment_id);
        
        if (!$payment) {
            return new WP_Error('not_found', 'Pagamento non trovato', ['status' => 404]);
        }
        
        $current_user_id = get_current_user_id();
        if ($payment->user_id != $current_user_id && !current_user_can('manage_options')) {
            return new WP_Error('forbidden', 'Non autorizzato', ['status' => 403]);
        }
        
        $params = $request->get_json_params();
        
        $result = self::update_payment_status($payment_id, 'completed', [
            'metodo_pagamento' => $params['metodo_pagamento'] ?? 'app',
            'transaction_id' => $params['transaction_id'] ?? null,
            'note' => $params['note'] ?? null,
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Pagamento confermato'];
        } else {
            return new WP_Error('error', 'Errore aggiornamento', ['status' => 500]);
        }
    }
    
    /**
     * Meta box pagamento
     */
    public static function add_payment_metabox() {
        add_meta_box(
            'wecoop_payment_info',
            '💳 Informazioni Pagamento',
            [__CLASS__, 'render_payment_metabox'],
            'richiesta_servizio',
            'side',
            'high'
        );
    }
    
    public static function render_payment_metabox($post) {
        $payment_id = get_post_meta($post->ID, 'payment_id', true);
        
        if (!$payment_id) {
            echo '<p>Nessun pagamento richiesto</p>';
            return;
        }
        
        $payment = self::get_payment($payment_id);
        
        if (!$payment) {
            echo '<p>Pagamento non trovato</p>';
            return;
        }
        
        $stato_labels = [
            'pending' => '⏳ In attesa',
            'completed' => '✅ Completato',
            'paid' => '✅ Pagato',
            'failed' => '❌ Fallito',
            'cancelled' => '🚫 Annullato',
        ];
        
        echo '<table class="form-table">';
        echo '<tr><th>ID:</th><td>#' . $payment->id . '</td></tr>';
        echo '<tr><th>Importo:</th><td><strong>€' . number_format($payment->importo, 2) . '</strong></td></tr>';
        echo '<tr><th>Stato:</th><td>' . ($stato_labels[$payment->stato] ?? $payment->stato) . '</td></tr>';
        
        if ($payment->metodo_pagamento) {
            echo '<tr><th>Metodo:</th><td>' . esc_html($payment->metodo_pagamento) . '</td></tr>';
        }
        
        if ($payment->transaction_id) {
            echo '<tr><th>Transaction:</th><td><code>' . esc_html($payment->transaction_id) . '</code></td></tr>';
        }
        
        echo '<tr><th>Creato:</th><td>' . date('d/m/Y H:i', strtotime($payment->created_at)) . '</td></tr>';
        
        if ($payment->paid_at) {
            echo '<tr><th>Pagato:</th><td>' . date('d/m/Y H:i', strtotime($payment->paid_at)) . '</td></tr>';
        }
        
        echo '</table>';
        
        if ($payment->stato === 'pending') {
            $payment_url = home_url('/pagamento/?id=' . $payment->id);
            echo '<p><a href="' . $payment_url . '" class="button button-primary" target="_blank">🔗 Link</a></p>';
        }
    }
}

WECOOP_Servizi_Payment_System::init();
