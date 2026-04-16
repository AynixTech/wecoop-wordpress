<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gestisce la scadenza automatica degli annunci e il flusso pagamento.
 *
 * - Ogni giorno controlla annunci scaduti e li imposta come 'scaduto' (non viene rimosso dallo stato publish,
 *   ma l'API non li espone più).
 * - Espone link di pagamento (€1/giorno extra) che può essere integrato con WooCommerce o Stripe Checkout.
 */
class WECOOP_Annuncio_Publication {

    public function __construct() {
        // Cron giornaliero per scadere gli annunci
        add_action( 'init', [ $this, 'schedule_cron' ] );
        add_action( 'wecoop_annunci_daily_check', [ $this, 'expire_annunci' ] );

        // Endpoint REST per avviare pagamento
        add_action( 'rest_api_init', [ $this, 'register_payment_routes' ] );

        // Colonna admin: scadenza
        add_filter( 'manage_wecoop_annuncio_posts_columns', [ $this, 'add_admin_columns' ] );
        add_action( 'manage_wecoop_annuncio_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
    }

    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'wecoop_annunci_daily_check' ) ) {
            wp_schedule_event( time(), 'daily', 'wecoop_annunci_daily_check' );
        }
    }

    public function expire_annunci() {
        $today = date( 'Y-m-d' );

        $posts = get_posts( [
            'post_type'      => 'wecoop_annuncio',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => '_annuncio_data_scadenza',
                    'value'   => $today,
                    'compare' => '<',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => '_annuncio_stato_pagamento',
                    'value'   => 'scaduto',
                    'compare' => '!=',
                ],
            ],
        ] );

        foreach ( $posts as $post_id ) {
            update_post_meta( $post_id, '_annuncio_stato_pagamento', 'scaduto' );
            // Opzionale: sposta in bozza
            // wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
        }
    }

    public function register_payment_routes() {
        register_rest_route( 'wecoop/v1', '/annunci/(?P<id>\d+)/estendi', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'extend_publication' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
            'args'                => [
                'id'     => [ 'type' => 'integer', 'required' => true ],
                'giorni' => [ 'type' => 'integer', 'required' => true, 'minimum' => 1, 'maximum' => 30 ],
            ],
        ] );
    }

    public function check_owner_or_admin( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        $post = get_post( (int) $request['id'] );
        if ( ! $post ) {
            return false;
        }
        return current_user_can( 'manage_options' ) || (int) $post->post_author === get_current_user_id();
    }

    /**
     * Estendi pubblicazione: ritorna importo da pagare e session_id Stripe (da implementare).
     * Per ora ritorna l'importo e una URL di pagamento placeholder.
     * L'integrazione reale avviene tramite webhook Stripe/WooCommerce che chiama
     * wecoop_annunci_complete_payment( $post_id, $giorni ).
     */
    public function extend_publication( $request ) {
        $post_id = (int) $request['id'];
        $giorni  = (int) $request['giorni'];
        $importo = $giorni * WECOOP_ANNUNCI_PRICE_PER_DAY;

        // Salva intent di pagamento
        $pending = [
            'post_id'   => $post_id,
            'giorni'    => $giorni,
            'importo'   => $importo,
            'user_id'   => get_current_user_id(),
            'creato_il' => current_time( 'mysql' ),
        ];
        update_post_meta( $post_id, '_annuncio_pagamento_pending', $pending );

        return rest_ensure_response( [
            'post_id'      => $post_id,
            'giorni'       => $giorni,
            'importo'      => $importo,
            'valuta'       => 'EUR',
            'messaggio'    => 'Per completare, effettua il pagamento di €' . number_format( $importo, 2 ) . '.',
            // Qui andrà il link Stripe Checkout o WooCommerce product URL
            'payment_url'  => home_url( '/pagamento-annuncio/?post_id=' . $post_id . '&giorni=' . $giorni ),
        ] );
    }

    // Chiamata dopo pagamento confermato (da webhook)
    public static function complete_payment( $post_id, $giorni, $transaction_id = '' ) {
        global $wpdb;

        $scadenza_attuale = get_post_meta( $post_id, '_annuncio_data_scadenza', true );
        $base = ( $scadenza_attuale && strtotime( $scadenza_attuale ) > time() )
            ? $scadenza_attuale
            : date( 'Y-m-d' );

        $nuova_scadenza = date( 'Y-m-d', strtotime( $base . " +{$giorni} days" ) );
        $giorni_totali  = (int) get_post_meta( $post_id, '_annuncio_giorni_pubb', true ) + $giorni;

        update_post_meta( $post_id, '_annuncio_data_scadenza', $nuova_scadenza );
        update_post_meta( $post_id, '_annuncio_giorni_pubb', $giorni_totali );
        update_post_meta( $post_id, '_annuncio_giorni_pagati', (int) get_post_meta( $post_id, '_annuncio_giorni_pagati', true ) + $giorni );
        update_post_meta( $post_id, '_annuncio_stato_pagamento', 'pagato' );
        delete_post_meta( $post_id, '_annuncio_pagamento_pending' );

        // Registra in tabella pagamenti
        $post       = get_post( $post_id );
        $importo    = $giorni * WECOOP_ANNUNCI_PRICE_PER_DAY;
        $wpdb->insert(
            $wpdb->prefix . 'annunci_pagamenti',
            [
                'post_id'        => $post_id,
                'user_id'        => $post ? $post->post_author : 0,
                'giorni_aggiuntivi' => $giorni,
                'importo'        => $importo,
                'stato'          => 'completato',
                'transaction_id' => sanitize_text_field( $transaction_id ),
            ],
            [ '%d', '%d', '%d', '%f', '%s', '%s' ]
        );

        return $nuova_scadenza;
    }

    // -------------------------------------------------------------------------
    // Admin columns
    // -------------------------------------------------------------------------

    public function add_admin_columns( $columns ) {
        $new = [];
        foreach ( $columns as $k => $v ) {
            $new[ $k ] = $v;
            if ( $k === 'title' ) {
                $new['scadenza']      = 'Scade il';
                $new['stato_pubb']    = 'Stato';
                $new['data_evento']   = 'Data evento';
            }
        }
        return $new;
    }

    public function render_admin_columns( $column, $post_id ) {
        if ( $column === 'scadenza' ) {
            $d = get_post_meta( $post_id, '_annuncio_data_scadenza', true );
            $past = $d && strtotime( $d ) < strtotime( 'today' );
            echo '<span style="color:' . ( $past ? '#c00' : '#090' ) . '">' . esc_html( $d ?: '—' ) . '</span>';
        }
        if ( $column === 'stato_pubb' ) {
            $s = get_post_meta( $post_id, '_annuncio_stato_pagamento', true );
            $icons = [ 'gratuito' => '🟡', 'pagato' => '🟢', 'scaduto' => '🔴' ];
            echo esc_html( ( $icons[ $s ] ?? '⚪' ) . ' ' . ucfirst( $s ?: 'gratuito' ) );
        }
        if ( $column === 'data_evento' ) {
            $d = get_post_meta( $post_id, '_annuncio_data_inizio', true );
            $o = get_post_meta( $post_id, '_annuncio_ora_inizio', true );
            echo esc_html( $d ? $d . ( $o ? ' ' . $o : '' ) : '—' );
        }
    }
}
