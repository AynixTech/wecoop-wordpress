<?php
/**
 * Store delle API Key: CRUD su wp_options.
 *
 * Ogni chiave: id, azienda (nome), prefisso visibile, hash della chiave,
 * stato attivo/inattivo, data creazione e ultimo utilizzo.
 *
 * La chiave in chiaro viene mostrata UNA SOLA VOLTA alla creazione: nel DB
 * si conserva solo l'hash (hash_hmac sha256 con AUTH_SALT).
 *
 * @package WeCoop\ApiKey
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WeCoop_ApiKey_Store {

    const OPTION = 'wecoop_apikeys';

    /**
     * Prefisso identificativo delle chiavi generate.
     */
    const KEY_PREFIX = 'wck_';

    /**
     * Attivazione: crea l'opzione vuota se manca.
     */
    public static function on_activation() {
        if ( false === get_option( self::OPTION, false ) ) {
            add_option( self::OPTION, array() );
        }
    }

    /**
     * Ritorna tutte le chiavi (array indicizzato per id).
     *
     * @return array
     */
    public static function all() {
        $keys = get_option( self::OPTION, array() );
        return is_array( $keys ) ? $keys : array();
    }

    /**
     * Calcola l'hash di una chiave in chiaro.
     *
     * @param string $plain Chiave in chiaro.
     * @return string
     */
    public static function hash( $plain ) {
        $salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : 'wecoop-apikey-fallback-salt';
        return hash_hmac( 'sha256', $plain, $salt );
    }

    /**
     * Crea una nuova API Key per un'azienda.
     *
     * @param string $azienda Nome dell'azienda.
     * @return array { id, azienda, plain } dove 'plain' è la chiave in chiaro (mostrala una volta).
     */
    public static function create( $azienda ) {
        $keys = self::all();

        $id     = self::generate_id();
        $secret = self::KEY_PREFIX . self::random_token( 32 );

        $keys[ $id ] = array(
            'id'         => $id,
            'azienda'    => $azienda,
            'prefix'     => substr( $secret, 0, 12 ), // es. wck_ab12cd34 (parte visibile).
            'hash'       => self::hash( $secret ),
            'active'     => true,
            'created_at' => current_time( 'mysql' ),
            'last_used'  => '',
        );

        update_option( self::OPTION, $keys );

        return array(
            'id'      => $id,
            'azienda' => $azienda,
            'plain'   => $secret,
        );
    }

    /**
     * Attiva/disattiva una chiave.
     *
     * @param string $id     ID chiave.
     * @param bool   $active Nuovo stato.
     * @return bool
     */
    public static function set_active( $id, $active ) {
        $keys = self::all();
        if ( ! isset( $keys[ $id ] ) ) {
            return false;
        }
        $keys[ $id ]['active'] = (bool) $active;
        update_option( self::OPTION, $keys );
        return true;
    }

    /**
     * Elimina una chiave.
     *
     * @param string $id ID chiave.
     * @return bool
     */
    public static function delete( $id ) {
        $keys = self::all();
        if ( ! isset( $keys[ $id ] ) ) {
            return false;
        }
        unset( $keys[ $id ] );
        update_option( self::OPTION, $keys );
        return true;
    }

    /**
     * Cerca una chiave attiva a partire dal valore in chiaro.
     *
     * @param string $plain Chiave in chiaro ricevuta nell'header.
     * @return array|null Record chiave, o null se non valida/inattiva.
     */
    public static function find_active_by_plain( $plain ) {
        if ( empty( $plain ) ) {
            return null;
        }
        $hash = self::hash( $plain );
        foreach ( self::all() as $record ) {
            if ( empty( $record['active'] ) ) {
                continue;
            }
            if ( hash_equals( (string) $record['hash'], $hash ) ) {
                return $record;
            }
        }
        return null;
    }

    /**
     * Aggiorna il timestamp di ultimo utilizzo di una chiave.
     *
     * @param string $id ID chiave.
     */
    public static function touch( $id ) {
        $keys = self::all();
        if ( isset( $keys[ $id ] ) ) {
            $keys[ $id ]['last_used'] = current_time( 'mysql' );
            update_option( self::OPTION, $keys );
        }
    }

    /**
     * Genera un id univoco per la chiave.
     *
     * @return string
     */
    protected static function generate_id() {
        return 'k_' . self::random_token( 8 );
    }

    /**
     * Genera un token casuale sicuro esadecimale.
     *
     * @param int $bytes Numero di byte.
     * @return string
     */
    protected static function random_token( $bytes ) {
        if ( function_exists( 'random_bytes' ) ) {
            try {
                return bin2hex( random_bytes( $bytes ) );
            } catch ( \Exception $e ) {
                // Fallback sotto.
            }
        }
        return bin2hex( wp_generate_password( $bytes, false, false ) );
    }
}
