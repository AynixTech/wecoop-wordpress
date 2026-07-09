<?php
/**
 * Pagina admin per la gestione delle API Key.
 *
 * Permette di creare chiavi per azienda, attivarle/disattivarle ed eliminarle.
 * La chiave in chiaro è mostrata una sola volta subito dopo la creazione.
 *
 * @package WeCoop\ApiKey
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WeCoop_ApiKey_Admin {

    const MENU_SLUG = 'wecoop-apikeys';
    const CAPABILITY = 'manage_options';
    const NONCE      = 'wecoop_apikey_action';

    /**
     * Registra gli hook.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_post_wecoop_apikey_create', array( __CLASS__, 'handle_create' ) );
        add_action( 'admin_post_wecoop_apikey_toggle', array( __CLASS__, 'handle_toggle' ) );
        add_action( 'admin_post_wecoop_apikey_delete', array( __CLASS__, 'handle_delete' ) );
    }

    /**
     * Menu top-level.
     */
    public static function register_menu() {
        add_menu_page(
            __( 'API Key', 'wecoop-apikey-auth' ),
            __( 'API Key', 'wecoop-apikey-auth' ),
            self::CAPABILITY,
            self::MENU_SLUG,
            array( __CLASS__, 'render' ),
            'dashicons-admin-network',
            65
        );
    }

    /**
     * Crea una nuova chiave.
     */
    public static function handle_create() {
        self::guard();
        $azienda = isset( $_POST['azienda'] ) ? sanitize_text_field( wp_unslash( $_POST['azienda'] ) ) : '';

        if ( '' === $azienda ) {
            self::redirect( array( 'msg' => 'empty' ) );
        }

        $created = WeCoop_ApiKey_Store::create( $azienda );

        // Mostra la chiave in chiaro una sola volta tramite transient.
        set_transient( 'wecoop_apikey_new_' . get_current_user_id(), $created['plain'], 120 );

        self::redirect( array( 'msg' => 'created', 'new' => $created['id'] ) );
    }

    /**
     * Attiva/disattiva una chiave.
     */
    public static function handle_toggle() {
        self::guard();
        $id     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $active = ! empty( $_POST['active'] ) ? true : false;
        WeCoop_ApiKey_Store::set_active( $id, $active );
        self::redirect( array( 'msg' => $active ? 'enabled' : 'disabled' ) );
    }

    /**
     * Elimina una chiave.
     */
    public static function handle_delete() {
        self::guard();
        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        WeCoop_ApiKey_Store::delete( $id );
        self::redirect( array( 'msg' => 'deleted' ) );
    }

    /**
     * Controllo permessi + nonce.
     */
    protected static function guard() {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_die( esc_html__( 'Permesso negato.', 'wecoop-apikey-auth' ) );
        }
        check_admin_referer( self::NONCE );
    }

    /**
     * Redirect alla pagina con parametri.
     *
     * @param array $args Query args.
     */
    protected static function redirect( $args ) {
        $args['page'] = self::MENU_SLUG;
        wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Renderizza la pagina.
     */
    public static function render() {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'API Key — WeCoop', 'wecoop-apikey-auth' ) . '</h1>';
        echo '<p>' . esc_html__( 'Gestisci le chiavi API per i client esterni (es. HubWeCoop). Ogni chiave è associata a un\'azienda e può essere attivata o disattivata.', 'wecoop-apikey-auth' ) . '</p>';

        self::render_notice();
        self::render_new_key_banner();
        self::render_create_form();
        self::render_table();

        echo '</div>';
    }

    /**
     * Avvisi in base al parametro msg.
     */
    protected static function render_notice() {
        if ( empty( $_GET['msg'] ) ) {
            return;
        }
        $msg = sanitize_key( wp_unslash( $_GET['msg'] ) );
        $map = array(
            'created'  => array( 'success', __( 'Chiave creata. Copiala ora: non sarà più visibile.', 'wecoop-apikey-auth' ) ),
            'enabled'  => array( 'success', __( 'Chiave attivata.', 'wecoop-apikey-auth' ) ),
            'disabled' => array( 'success', __( 'Chiave disattivata.', 'wecoop-apikey-auth' ) ),
            'deleted'  => array( 'success', __( 'Chiave eliminata.', 'wecoop-apikey-auth' ) ),
            'empty'    => array( 'error', __( 'Inserisci il nome dell\'azienda.', 'wecoop-apikey-auth' ) ),
        );
        if ( ! isset( $map[ $msg ] ) ) {
            return;
        }
        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr( $map[ $msg ][0] ),
            esc_html( $map[ $msg ][1] )
        );
    }

    /**
     * Banner con la chiave appena creata (in chiaro, una volta sola).
     */
    protected static function render_new_key_banner() {
        $plain = get_transient( 'wecoop_apikey_new_' . get_current_user_id() );
        if ( ! $plain ) {
            return;
        }
        delete_transient( 'wecoop_apikey_new_' . get_current_user_id() );

        echo '<div class="notice notice-info" style="padding:16px;">';
        echo '<p style="margin-top:0;"><strong>' . esc_html__( 'La tua nuova API Key (copiala adesso):', 'wecoop-apikey-auth' ) . '</strong></p>';
        echo '<input type="text" readonly onclick="this.select();" value="' . esc_attr( $plain ) . '" class="large-text code" style="font-size:15px;padding:8px;" />';
        echo '<p class="description">' . esc_html__( 'Per motivi di sicurezza questa chiave non verrà mostrata di nuovo. Incollala nella configurazione di HubWeCoop.', 'wecoop-apikey-auth' ) . '</p>';
        echo '</div>';
    }

    /**
     * Form di creazione.
     */
    protected static function render_create_form() {
        echo '<h2>' . esc_html__( 'Nuova chiave', 'wecoop-apikey-auth' ) . '</h2>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        wp_nonce_field( self::NONCE );
        echo '<input type="hidden" name="action" value="wecoop_apikey_create" />';
        echo '<table class="form-table" role="presentation"><tbody>';
        echo '<tr><th scope="row"><label for="azienda">' . esc_html__( 'Nome azienda', 'wecoop-apikey-auth' ) . '</label></th><td>';
        echo '<input name="azienda" id="azienda" type="text" class="regular-text" required placeholder="' . esc_attr__( 'Es. HubWeCoop Milano', 'wecoop-apikey-auth' ) . '" />';
        echo '</td></tr>';
        echo '</tbody></table>';
        submit_button( __( 'Genera API Key', 'wecoop-apikey-auth' ) );
        echo '</form>';
    }

    /**
     * Tabella delle chiavi esistenti.
     */
    protected static function render_table() {
        $keys = WeCoop_ApiKey_Store::all();

        echo '<h2>' . esc_html__( 'Chiavi esistenti', 'wecoop-apikey-auth' ) . '</h2>';

        if ( empty( $keys ) ) {
            echo '<p>' . esc_html__( 'Nessuna chiave creata.', 'wecoop-apikey-auth' ) . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Azienda', 'wecoop-apikey-auth' ) . '</th>';
        echo '<th>' . esc_html__( 'Chiave (prefisso)', 'wecoop-apikey-auth' ) . '</th>';
        echo '<th>' . esc_html__( 'Stato', 'wecoop-apikey-auth' ) . '</th>';
        echo '<th>' . esc_html__( 'Creata', 'wecoop-apikey-auth' ) . '</th>';
        echo '<th>' . esc_html__( 'Ultimo uso', 'wecoop-apikey-auth' ) . '</th>';
        echo '<th>' . esc_html__( 'Azioni', 'wecoop-apikey-auth' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $keys as $record ) {
            $active = ! empty( $record['active'] );
            echo '<tr>';
            echo '<td><strong>' . esc_html( $record['azienda'] ) . '</strong></td>';
            echo '<td><code>' . esc_html( $record['prefix'] ) . '…</code></td>';
            echo '<td>';
            if ( $active ) {
                echo '<span style="color:#1e8e3e;font-weight:600;">● ' . esc_html__( 'Attiva', 'wecoop-apikey-auth' ) . '</span>';
            } else {
                echo '<span style="color:#b3261e;font-weight:600;">● ' . esc_html__( 'Disattivata', 'wecoop-apikey-auth' ) . '</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html( $record['created_at'] ) . '</td>';
            echo '<td>' . esc_html( $record['last_used'] ? $record['last_used'] : '—' ) . '</td>';
            echo '<td>';

            // Toggle attiva/disattiva.
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;">';
            wp_nonce_field( self::NONCE );
            echo '<input type="hidden" name="action" value="wecoop_apikey_toggle" />';
            echo '<input type="hidden" name="id" value="' . esc_attr( $record['id'] ) . '" />';
            echo '<input type="hidden" name="active" value="' . ( $active ? '0' : '1' ) . '" />';
            $btn = $active ? __( 'Disattiva', 'wecoop-apikey-auth' ) : __( 'Attiva', 'wecoop-apikey-auth' );
            echo '<button type="submit" class="button button-small">' . esc_html( $btn ) . '</button>';
            echo '</form> ';

            // Elimina.
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;" onsubmit="return confirm(\'' . esc_js( __( 'Eliminare questa chiave?', 'wecoop-apikey-auth' ) ) . '\');">';
            wp_nonce_field( self::NONCE );
            echo '<input type="hidden" name="action" value="wecoop_apikey_delete" />';
            echo '<input type="hidden" name="id" value="' . esc_attr( $record['id'] ) . '" />';
            echo '<button type="submit" class="button button-small button-link-delete">' . esc_html__( 'Elimina', 'wecoop-apikey-auth' ) . '</button>';
            echo '</form>';

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '<h2>' . esc_html__( 'Come si usa', 'wecoop-apikey-auth' ) . '</h2>';
        echo '<p>' . esc_html__( 'I client devono inviare la chiave nell\'header HTTP:', 'wecoop-apikey-auth' ) . '</p>';
        echo '<p><code>' . esc_html( WeCoop_ApiKey_Auth::HEADER ) . ': &lt;la-tua-chiave&gt;</code></p>';
    }
}
