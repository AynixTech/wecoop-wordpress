<?php
/**
 * Endpoint Stripe Payment Intent per WeCoop Servizi
 * 
 * Crea Payment Intent su Stripe per processare pagamenti nell'app mobile
 * 
 * @package WECOOP_Servizi
 * @since 2.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Stripe_Payment_Intent {
    
    /**
     * Inizializza endpoint
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    /**
     * Registra route REST API
     */
    public static function register_routes() {
        register_rest_route('wecoop/v1', '/create-payment-intent', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'create_payment_intent'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);
        
        // Webhook Stripe per conferma pagamento
        register_rest_route('wecoop/v1', '/stripe-webhook', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_webhook'],
            'permission_callback' => '__return_true', // Stripe usa signature
        ]);
    }
    
    /**
     * Verifica permessi - solo utenti autenticati
     */
    public static function check_permissions($request) {
        return is_user_logged_in();
    }
    
    /**
     * Crea Payment Intent su Stripe
     */
    public static function create_payment_intent($request) {
        try {
            // Carica Stripe SDK
            if (!self::load_stripe_sdk()) {
                return new WP_Error('stripe_sdk_missing', 'Stripe SDK non installato', ['status' => 500]);
            }
            
            // Ottieni parametri
            $params = json_decode($request->get_body(), true);
            $amount = intval($params['amount'] ?? 0);
            $currency = sanitize_text_field($params['currency'] ?? 'eur');
            $payment_id = intval($params['payment_id'] ?? 0);
            
            // Validazione
            if ($amount <= 0) {
                return new WP_Error('invalid_amount', 'Importo non valido', ['status' => 400]);
            }
            
            if ($payment_id <= 0) {
                return new WP_Error('invalid_payment_id', 'Payment ID non valido', ['status' => 400]);
            }
            
            // Verifica pagamento
            global $wpdb;
            $table = $wpdb->prefix . 'wecoop_pagamenti';
            
            $payment = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $payment_id
            ));
            
            if (!$payment) {
                return new WP_Error('payment_not_found', 'Pagamento non trovato', ['status' => 404]);
            }
            
            // Verifica ownership
            $current_user_id = get_current_user_id();
            if ($payment->user_id != $current_user_id && !current_user_can('manage_options')) {
                return new WP_Error('forbidden', 'Non autorizzato', ['status' => 403]);
            }
            
            // Verifica già pagato
            if ($payment->stato === 'paid' || $payment->stato === 'completed') {
                return new WP_Error('already_paid', 'Pagamento già completato', ['status' => 400]);
            }
            
            // Secret Key
            $secret_key = self::get_stripe_secret_key();
            if (!$secret_key) {
                return new WP_Error('stripe_key_missing', 'Chiave Stripe non configurata', ['status' => 500]);
            }
            
            // Inizializza Stripe
            \Stripe\Stripe::setApiKey($secret_key);
            
            // Info metadata
            $user = get_userdata($payment->user_id);
            $servizio = get_post_meta($payment->richiesta_id, 'servizio', true);
            $numero_pratica = get_post_meta($payment->richiesta_id, 'numero_pratica', true);
            
            // Crea Payment Intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => [
                    'payment_id' => $payment_id,
                    'user_id' => $payment->user_id,
                    'user_email' => $user ? $user->user_email : '',
                    'richiesta_id' => $payment->richiesta_id,
                    'servizio' => $servizio,
                    'numero_pratica' => $numero_pratica,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => "WeCoop - {$servizio} ({$numero_pratica})",
            ]);
            
            // Salva nel DB
            $wpdb->update(
                $table,
                [
                    'transaction_id' => $paymentIntent->id,
                    'metodo_pagamento' => 'stripe',
                    'note' => 'Payment Intent: ' . $paymentIntent->id,
                ],
                ['id' => $payment_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
            
            error_log("[WECOOP STRIPE] Payment Intent: {$paymentIntent->id} per #{$payment_id}");
            
            return rest_ensure_response([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('[WECOOP STRIPE] API Error: ' . $e->getMessage());
            return new WP_Error('stripe_error', $e->getMessage(), ['status' => 500]);
            
        } catch (Exception $e) {
            error_log('[WECOOP STRIPE] Error: ' . $e->getMessage());
            return new WP_Error('server_error', 'Errore creazione pagamento', ['status' => 500]);
        }
    }
    
    /**
     * Gestisci Webhook Stripe
     */
    public static function handle_webhook($request) {
        try {
            if (!self::load_stripe_sdk()) {
                return new WP_Error('stripe_sdk_missing', 'Stripe SDK non installato', ['status' => 500]);
            }
            
            $payload = $request->get_body();
            $sig_header = $request->get_header('stripe-signature');
            $webhook_secret = self::get_stripe_webhook_secret();
            
            if (!$webhook_secret) {
                error_log('[WECOOP STRIPE] Webhook secret non configurato');
                return rest_ensure_response(['received' => true]);
            }
            
            // Verifica signature
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $webhook_secret
                );
            } catch (\UnexpectedValueException $e) {
                error_log('[WECOOP STRIPE] Invalid signature: ' . $e->getMessage());
                return new WP_Error('invalid_signature', 'Signature non valida', ['status' => 400]);
            }
            
            // Gestisci evento
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    self::handle_payment_success($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    self::handle_payment_failed($event->data->object);
                    break;
            }
            
            return rest_ensure_response(['received' => true]);
            
        } catch (Exception $e) {
            error_log('[WECOOP STRIPE] Webhook error: ' . $e->getMessage());
            return new WP_Error('webhook_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Pagamento riuscito
     */
    private static function handle_payment_success($paymentIntent) {
        $payment_id = $paymentIntent->metadata->payment_id ?? null;
        
        if (!$payment_id) {
            error_log('[WECOOP STRIPE] Payment ID mancante');
            return;
        }
        
        error_log("[WECOOP STRIPE] Success: PI {$paymentIntent->id}, Payment #{$payment_id}");
        
        WECOOP_Servizi_Payment_System::update_payment_status($payment_id, 'paid', [
            'transaction_id' => $paymentIntent->id,
            'metodo_pagamento' => 'stripe',
            'note' => 'Pagato via Stripe Webhook',
        ]);
        
        // Genera ricevuta PDF automaticamente
        if (class_exists('WeCoop_Ricevuta_PDF')) {
            $result = WeCoop_Ricevuta_PDF::generate_ricevuta($payment_id);
            if (!$result['success']) {
                error_log("[WECOOP STRIPE] Errore generazione ricevuta: " . $result['message']);
            } else {
                error_log("[WECOOP STRIPE] Ricevuta generata: " . $result['receipt_url']);
            }
        }
    }
    
    /**
     * Pagamento fallito
     */
    private static function handle_payment_failed($paymentIntent) {
        $payment_id = $paymentIntent->metadata->payment_id ?? null;
        
        if (!$payment_id) {
            return;
        }
        
        error_log("[WECOOP STRIPE] Failed: PI {$paymentIntent->id}, Payment #{$payment_id}");
        
        WECOOP_Servizi_Payment_System::update_payment_status($payment_id, 'failed', [
            'note' => 'Fallito: ' . ($paymentIntent->last_payment_error->message ?? 'Errore'),
        ]);
    }
    
    /**
     * Carica SDK
     */
    private static function load_stripe_sdk() {
        $autoload = WECOOP_SERVIZI_PLUGIN_DIR . 'vendor/autoload.php';
        
        if (!file_exists($autoload)) {
            error_log('[WECOOP STRIPE] SDK non trovato: ' . $autoload);
            return false;
        }
        
        require_once $autoload;
        return true;
    }
    
    /**
     * Secret Key
     */
    private static function get_stripe_secret_key() {
        if (defined('WECOOP_STRIPE_SECRET_KEY')) {
            return WECOOP_STRIPE_SECRET_KEY;
        }
        
        $key = get_option('wecoop_stripe_secret_key');
        
        if (!$key) {
            error_log('[WECOOP STRIPE] Secret key non configurata');
        }
        
        return $key;
    }
    
    /**
     * Webhook Secret
     */
    private static function get_stripe_webhook_secret() {
        if (defined('WECOOP_STRIPE_WEBHOOK_SECRET')) {
            return WECOOP_STRIPE_WEBHOOK_SECRET;
        }
        
        return get_option('wecoop_stripe_webhook_secret');
    }
}

// Inizializza
WECOOP_Servizi_Stripe_Payment_Intent::init();
