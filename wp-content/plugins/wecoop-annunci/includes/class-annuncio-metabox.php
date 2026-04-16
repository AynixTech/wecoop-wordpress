<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tutti i meta fields per wecoop_annuncio.
 *
 * CAMPI GENERALI
 *   _annuncio_tipo_evento     : tipo evento (libero testo o select)
 *   _annuncio_luogo           : nome del posto / venue
 *   _annuncio_indirizzo       : indirizzo completo
 *   _annuncio_citta           : città
 *   _annuncio_cap             : CAP
 *   _annuncio_telefono        : telefono contatto
 *   _annuncio_email           : email contatto
 *   _annuncio_sito_web        : sito web / link prenotazione
 *   _annuncio_prezzo_ingresso : prezzo biglietto / ingresso (€, 0 = gratuito)
 *   _annuncio_lingua          : lingua principale dell'evento
 *
 * CAMPI DATA/ORA
 *   _annuncio_data_inizio     : YYYY-MM-DD
 *   _annuncio_ora_inizio      : HH:MM
 *   _annuncio_data_fine       : YYYY-MM-DD (opzionale se evento su più giorni)
 *   _annuncio_ora_fine        : HH:MM
 *
 * CAMPI SPECIFICI RISTORANTE/FOOD
 *   _annuncio_menu_giorno     : menu del giorno (testo libero)
 *   _annuncio_offerta_speciale: offerta speciale del giorno
 *   _annuncio_coperti_disp    : numero coperti disponibili
 *   _annuncio_prenota_tavolo  : link / numero per prenotare
 *
 * CAMPI SPECIFICI CONCERTO/MUSICA
 *   _annuncio_artista         : nome artista / band
 *   _annuncio_genere_musicale : genere musicale
 *   _annuncio_link_biglietti  : link acquisto biglietti
 *   _annuncio_capienza_max    : capienza massima venue
 *
 * CAMPI SPECIFICI SERVIZI/VENDITA
 *   _annuncio_disponibilita   : quando è disponibile il servizio
 *   _annuncio_tariffa         : tariffa / prezzo servizio
 *
 * CAMPI PUBBLICAZIONE
 *   _annuncio_data_scadenza   : YYYY-MM-DD (calcolata automaticamente)
 *   _annuncio_giorni_pubb     : numero giorni totali di pubblicazione
 *   _annuncio_giorni_pagati   : giorni extra pagati (oltre i 3 gratis)
 *   _annuncio_stato_pagamento : 'gratuito' | 'pagato' | 'scaduto'
 *   _annuncio_pubblicato_da   : user_id dell'autore
 */
class WECOOP_Annuncio_Metabox {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'register_metaboxes' ] );
        add_action( 'save_post_wecoop_annuncio', [ $this, 'save_metaboxes' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) {
            return;
        }
        global $post;
        if ( ! $post || $post->post_type !== 'wecoop_annuncio' ) {
            return;
        }
        wp_enqueue_script(
            'wecoop-annunci-admin',
            WECOOP_ANNUNCI_URL . 'assets/admin.js',
            [ 'jquery' ],
            WECOOP_ANNUNCI_VERSION,
            true
        );
        wp_enqueue_style(
            'wecoop-annunci-admin',
            WECOOP_ANNUNCI_URL . 'assets/admin.css',
            [],
            WECOOP_ANNUNCI_VERSION
        );
    }

    public function register_metaboxes() {
        add_meta_box(
            'wecoop_annuncio_dettagli',
            '📋 Dettagli Annuncio',
            [ $this, 'render_dettagli' ],
            'wecoop_annuncio',
            'normal',
            'high'
        );

        add_meta_box(
            'wecoop_annuncio_datetime',
            '📅 Data e Ora',
            [ $this, 'render_datetime' ],
            'wecoop_annuncio',
            'normal',
            'high'
        );

        add_meta_box(
            'wecoop_annuncio_specifici',
            '🎪 Dettagli Specifici (Concerto / Ristorante / Servizio)',
            [ $this, 'render_specifici' ],
            'wecoop_annuncio',
            'normal',
            'default'
        );

        add_meta_box(
            'wecoop_annuncio_contatti',
            '📞 Contatti e Posizione',
            [ $this, 'render_contatti' ],
            'wecoop_annuncio',
            'side',
            'default'
        );

        add_meta_box(
            'wecoop_annuncio_pubblicazione',
            '⏱ Pubblicazione',
            [ $this, 'render_pubblicazione' ],
            'wecoop_annuncio',
            'side',
            'high'
        );
    }

    // -------------------------------------------------------------------------
    // Render helpers
    // -------------------------------------------------------------------------

    private function field( $post, $meta_key, $label, $type = 'text', $options = [], $placeholder = '' ) {
        $value = get_post_meta( $post->ID, $meta_key, true );
        $id    = esc_attr( $meta_key );
        echo '<p><label for="' . $id . '"><strong>' . esc_html( $label ) . '</strong></label><br>';

        if ( $type === 'select' && ! empty( $options ) ) {
            echo '<select name="' . $id . '" id="' . $id . '" style="width:100%">';
            echo '<option value="">— Seleziona —</option>';
            foreach ( $options as $opt ) {
                $sel = selected( $value, $opt, false );
                echo '<option value="' . esc_attr( $opt ) . '" ' . $sel . '>' . esc_html( $opt ) . '</option>';
            }
            echo '</select>';
        } elseif ( $type === 'textarea' ) {
            echo '<textarea name="' . $id . '" id="' . $id . '" rows="4" style="width:100%">' . esc_textarea( $value ) . '</textarea>';
        } elseif ( $type === 'number' ) {
            echo '<input type="number" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $value ) . '" min="0" step="0.01" style="width:100%" placeholder="' . esc_attr( $placeholder ) . '">';
        } elseif ( $type === 'date' ) {
            echo '<input type="date" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $value ) . '" style="width:100%">';
        } elseif ( $type === 'time' ) {
            echo '<input type="time" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $value ) . '" style="width:100%">';
        } else {
            echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . esc_attr( $value ) . '" style="width:100%" placeholder="' . esc_attr( $placeholder ) . '">';
        }

        echo '</p>';
    }

    public function render_dettagli( $post ) {
        wp_nonce_field( 'wecoop_annuncio_save', 'wecoop_annuncio_nonce' );

        $this->field( $post, '_annuncio_tipo_evento', 'Tipo di annuncio', 'select', [
            'Evento generico',
            'Concerto / Live music',
            'DJ Set / Serata',
            'Festival',
            'Spettacolo teatrale',
            'Serata ristorante',
            'Menu speciale / Offerta food',
            'Degustazione / Wine tasting',
            'Sport / Torneo',
            'Escursione / Tour',
            'Corso / Workshop',
            'Mostra / Galleria',
            'Mercatino / Fiera',
            'Vendita oggetti',
            'Servizio professionale',
            'Collaborazione / Lavoro',
            'Altro',
        ] );

        $this->field( $post, '_annuncio_lingua', 'Lingua principale', 'select', [
            'Italiano',
            'Arabo',
            'Francese',
            'Inglese',
            'Spagnolo',
            'Rumeno',
            'Cinese',
            'Hindi',
            'Urdu',
            'Tagalog',
            'Altra',
        ] );

        $this->field( $post, '_annuncio_prezzo_ingresso', 'Prezzo ingresso / Costo (€) — 0 = gratuito', 'number' );
    }

    public function render_datetime( $post ) {
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">';
        $this->field( $post, '_annuncio_data_inizio', 'Data inizio *', 'date' );
        $this->field( $post, '_annuncio_ora_inizio', 'Ora inizio', 'time' );
        $this->field( $post, '_annuncio_data_fine', 'Data fine (se multi-giorno)', 'date' );
        $this->field( $post, '_annuncio_ora_fine', 'Ora fine', 'time' );
        echo '</div>';
    }

    public function render_specifici( $post ) {
        echo '<h4 style="margin-bottom:4px;color:#1282A8">🎵 Concerto / Musica</h4>';
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        $this->field( $post, '_annuncio_artista', 'Artista / Band' );
        $this->field( $post, '_annuncio_genere_musicale', 'Genere musicale', 'select', [
            'Pop', 'Rock', 'Hip-Hop / Rap', 'R&B / Soul', 'Elettronica / EDM', 'Jazz',
            'Blues', 'Classica', 'Folk / Tradizionale', 'Reggae', 'Latino / Salsa',
            'Musica araba', 'Musica africana', 'Altro',
        ] );
        $this->field( $post, '_annuncio_link_biglietti', 'Link acquisto biglietti', 'text', [], 'https://...' );
        $this->field( $post, '_annuncio_capienza_max', 'Capienza massima', 'number' );
        echo '</div>';

        echo '<hr><h4 style="margin-bottom:4px;color:#1282A8">🍽 Ristorante / Food</h4>';
        $this->field( $post, '_annuncio_menu_giorno', 'Menu del giorno / Offerta', 'textarea' );
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        $this->field( $post, '_annuncio_offerta_speciale', 'Promozione speciale (es. Aperitivo incluso)' );
        $this->field( $post, '_annuncio_coperti_disp', 'Coperti disponibili', 'number' );
        $this->field( $post, '_annuncio_prenota_tavolo', 'Link / Tel prenotazione tavolo', 'text', [], 'Tel. o URL' );
        echo '</div>';

        echo '<hr><h4 style="margin-bottom:4px;color:#1282A8">🔧 Servizio / Vendita</h4>';
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        $this->field( $post, '_annuncio_disponibilita', 'Disponibilità / Orari', 'text', [], 'Es. Lun-Ven 9-18' );
        $this->field( $post, '_annuncio_tariffa', 'Tariffa / Prezzo servizio (€)', 'number' );
        echo '</div>';
    }

    public function render_contatti( $post ) {
        $this->field( $post, '_annuncio_luogo', 'Nome del posto / Venue' );
        $this->field( $post, '_annuncio_indirizzo', 'Indirizzo' );
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">';
        $this->field( $post, '_annuncio_citta', 'Città' );
        $this->field( $post, '_annuncio_cap', 'CAP' );
        echo '</div>';
        $this->field( $post, '_annuncio_telefono', 'Telefono', 'text', [], '+39...' );
        $this->field( $post, '_annuncio_email', 'Email' );
        $this->field( $post, '_annuncio_sito_web', 'Sito web / Link prenotazione', 'text', [], 'https://...' );
    }

    public function render_pubblicazione( $post ) {
        $giorni_pubb     = (int) get_post_meta( $post->ID, '_annuncio_giorni_pubb', true ) ?: WECOOP_ANNUNCI_FREE_DAYS;
        $giorni_pagati   = (int) get_post_meta( $post->ID, '_annuncio_giorni_pagati', true );
        $stato_pagamento = get_post_meta( $post->ID, '_annuncio_stato_pagamento', true ) ?: 'gratuito';
        $data_scadenza   = get_post_meta( $post->ID, '_annuncio_data_scadenza', true );

        $costo_extra = max( 0, $giorni_pubb - WECOOP_ANNUNCI_FREE_DAYS ) * WECOOP_ANNUNCI_PRICE_PER_DAY;

        echo '<p><strong>Giorni di pubblicazione</strong><br>';
        echo '<input type="number" name="_annuncio_giorni_pubb" id="_annuncio_giorni_pubb" value="' . esc_attr( $giorni_pubb ) . '" min="1" max="365" style="width:80px"> giorni</p>';

        echo '<p style="color:#555;font-size:12px">✅ <strong>' . WECOOP_ANNUNCI_FREE_DAYS . ' giorni</strong> gratuiti<br>';
        if ( $costo_extra > 0 ) {
            echo '💳 Costo extra: <strong>€' . number_format( $costo_extra, 2 ) . '</strong> (' . ( $giorni_pubb - WECOOP_ANNUNCI_FREE_DAYS ) . ' giorni extra × €' . WECOOP_ANNUNCI_PRICE_PER_DAY . '/giorno)</p>';
        } else {
            echo '💡 Oltre i ' . WECOOP_ANNUNCI_FREE_DAYS . ' giorni: €' . WECOOP_ANNUNCI_PRICE_PER_DAY . '/giorno</p>';
        }

        echo '<p><strong>Stato:</strong> ';
        $badge = $stato_pagamento === 'pagato' ? '🟢 Pagato' : ( $stato_pagamento === 'scaduto' ? '🔴 Scaduto' : '🟡 Gratuito' );
        echo '<strong>' . esc_html( $badge ) . '</strong></p>';

        if ( $data_scadenza ) {
            echo '<p><strong>Scade il:</strong> ' . esc_html( $data_scadenza ) . '</p>';
        }

        // Admin può forzare stato
        if ( current_user_can( 'manage_options' ) ) {
            echo '<p><label><strong>Forza stato (admin)</strong></label><br>';
            echo '<select name="_annuncio_stato_pagamento" style="width:100%">';
            foreach ( [ 'gratuito', 'pagato', 'scaduto' ] as $s ) {
                $sel = selected( $stato_pagamento, $s, false );
                echo '<option value="' . $s . '" ' . $sel . '>' . ucfirst( $s ) . '</option>';
            }
            echo '</select></p>';

            echo '<p><label><strong>Data scadenza manuale</strong></label><br>';
            echo '<input type="date" name="_annuncio_data_scadenza" value="' . esc_attr( $data_scadenza ) . '" style="width:100%"></p>';
        }
    }

    // -------------------------------------------------------------------------
    // Save
    // -------------------------------------------------------------------------

    public function save_metaboxes( $post_id ) {
        if ( ! isset( $_POST['wecoop_annuncio_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wecoop_annuncio_nonce'] ) ), 'wecoop_annuncio_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $text_fields = [
            '_annuncio_tipo_evento', '_annuncio_lingua', '_annuncio_luogo',
            '_annuncio_indirizzo', '_annuncio_citta', '_annuncio_cap',
            '_annuncio_telefono', '_annuncio_email', '_annuncio_artista',
            '_annuncio_genere_musicale', '_annuncio_offerta_speciale',
            '_annuncio_prenota_tavolo', '_annuncio_disponibilita',
        ];

        $url_fields = [ '_annuncio_sito_web', '_annuncio_link_biglietti' ];

        $number_fields = [
            '_annuncio_prezzo_ingresso', '_annuncio_coperti_disp',
            '_annuncio_capienza_max', '_annuncio_tariffa',
            '_annuncio_giorni_pubb', '_annuncio_giorni_pagati',
        ];

        $date_fields = [ '_annuncio_data_inizio', '_annuncio_data_fine', '_annuncio_data_scadenza' ];
        $time_fields = [ '_annuncio_ora_inizio', '_annuncio_ora_fine' ];
        $textarea_fields = [ '_annuncio_menu_giorno' ];

        foreach ( $text_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        foreach ( $url_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, esc_url_raw( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        foreach ( $number_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, floatval( $_POST[ $key ] ) );
            }
        }

        foreach ( $date_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $d = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
                update_post_meta( $post_id, $key, preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d ) ? $d : '' );
            }
        }

        foreach ( $time_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $t = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
                update_post_meta( $post_id, $key, preg_match( '/^\d{2}:\d{2}$/', $t ) ? $t : '' );
            }
        }

        foreach ( $textarea_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        // Calcola data scadenza automatica se non impostata dall'admin
        if ( ! current_user_can( 'manage_options' ) || empty( $_POST['_annuncio_data_scadenza'] ) ) {
            $giorni = isset( $_POST['_annuncio_giorni_pubb'] ) ? max( 1, (int) $_POST['_annuncio_giorni_pubb'] ) : WECOOP_ANNUNCI_FREE_DAYS;
            // I giorni oltre il gratuito richiedono pagamento: per ora si limitano a FREE_DAYS se non pagato
            $stato_attuale = get_post_meta( $post_id, '_annuncio_stato_pagamento', true );
            if ( $stato_attuale !== 'pagato' ) {
                $giorni = min( $giorni, WECOOP_ANNUNCI_FREE_DAYS );
                update_post_meta( $post_id, '_annuncio_giorni_pubb', $giorni );
            }
            $scadenza = date( 'Y-m-d', strtotime( "+{$giorni} days" ) );
            update_post_meta( $post_id, '_annuncio_data_scadenza', $scadenza );
        }

        // Stato pagamento (solo admin)
        if ( current_user_can( 'manage_options' ) && isset( $_POST['_annuncio_stato_pagamento'] ) ) {
            $stati_validi = [ 'gratuito', 'pagato', 'scaduto' ];
            $stato = sanitize_text_field( wp_unslash( $_POST['_annuncio_stato_pagamento'] ) );
            if ( in_array( $stato, $stati_validi, true ) ) {
                update_post_meta( $post_id, '_annuncio_stato_pagamento', $stato );
            }
        } elseif ( ! get_post_meta( $post_id, '_annuncio_stato_pagamento', true ) ) {
            update_post_meta( $post_id, '_annuncio_stato_pagamento', 'gratuito' );
        }
    }
}
