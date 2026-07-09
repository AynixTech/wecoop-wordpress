<?php
/**
 * Autenticazione REST tramite API Key.
 *
 * Se una richiesta REST arriva con header 'X-WeCoop-ApiKey' (o query param
 * 'wecoop_api_key') contenente una chiave attiva, autentica la richiesta come
 * l'utente di servizio (primo amministratore del sito). Questo permette agli
 * endpoint wecoop/v1 esistenti (che usano current_user_can('manage_options'))
 * di accettare la chiave SENZA modificarli.
 *
 * @package WeCoop\ApiKey
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WeCoop_ApiKey_Auth {

    const HEADER      = 'X-WeCoop-ApiKey';
    const QUERY_PARAM = 'wecoop_api_key';

    /**
     * Utente autenticato dalla chiave in questa richiesta (cache).
     *
     * @var int|null
     */
    protected static $resolved_user = null;

    /**
     * Registra gli hook.
     */
    public static function init() {
        // Priorità alta per agganciarsi prima dei permission_callback.
        add_filter( 'determine_current_user', array( __CLASS__, 'authenticate' ), 20 );
        add_filter( 'rest_authentication_errors', array( __CLASS__, 'maybe_clear_auth_error' ), 20 );
    }

    /**
     * Legge la chiave dalla richiesta corrente.
     *
     * @return string
     */
    protected static function read_key() {
        // Header standard (Apache può prefissare con HTTP_).
        $candidates = array(
            'HTTP_X_WECOOP_APIKEY',
            'HTTP_X_WECOOP_API_KEY',
        );
        foreach ( $candidates as $srv ) {
            if ( ! empty( $_SERVER[ $srv ] ) ) {
                return sanitize_text_field( wp_unslash( $_SERVER[ $srv ] ) );
            }
        }

        // Fallback: query param (utile per test rapidi).
        if ( ! empty( $_GET[ self::QUERY_PARAM ] ) ) {
            return sanitize_text_field( wp_unslash( $_GET[ self::QUERY_PARAM ] ) );
        }

        return '';
    }

    /**
     * Determina se la richiesta corrente è una richiesta REST.
     *
     * @return bool
     */
    protected static function is_rest_request() {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return true;
        }
        $rest_prefix = trailingslashit( rest_get_url_prefix() );
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
        return false !== strpos( $request_uri, $rest_prefix );
    }

    /**
     * Filtro determine_current_user: autentica via API Key.
     *
     * @param int|false $user_id ID utente già determinato (o false).
     * @return int|false
     */
    public static function authenticate( $user_id ) {
        // Se qualcuno è già autenticato (cookie/JWT), non interferire.
        if ( ! empty( $user_id ) ) {
            return $user_id;
        }

        if ( ! self::is_rest_request() ) {
            return $user_id;
        }

        $key = self::read_key();
        if ( '' === $key ) {
            return $user_id;
        }

        $record = WeCoop_ApiKey_Store::find_active_by_plain( $key );
        if ( ! $record ) {
            return $user_id;
        }

        $service_user = self::get_service_user_id();
        if ( ! $service_user ) {
            return $user_id;
        }

        WeCoop_ApiKey_Store::touch( $record['id'] );
        self::$resolved_user = $service_user;

        return $service_user;
    }

    /**
     * Se abbiamo autenticato via API Key, evita che il core segnali errore auth.
     *
     * @param WP_Error|null|true $errors Stato errori auth.
     * @return WP_Error|null|true
     */
    public static function maybe_clear_auth_error( $errors ) {
        if ( null !== self::$resolved_user ) {
            return true;
        }
        return $errors;
    }

    /**
     * Ritorna l'ID dell'utente di servizio (primo amministratore del sito).
     *
     * @return int
     */
    protected static function get_service_user_id() {
        $admins = get_users(
            array(
                'role'    => 'administrator',
                'number'  => 1,
                'orderby' => 'ID',
                'order'   => 'ASC',
                'fields'  => 'ID',
            )
        );
        return ! empty( $admins ) ? (int) $admins[0] : 0;
    }
}
