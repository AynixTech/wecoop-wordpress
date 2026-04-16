<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WECOOP_Annuncio_REST_API {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes() {
        $ns = 'wecoop/v1';

        // GET lista annunci (pubblico)
        register_rest_route( $ns, '/annunci', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_annunci' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'categoria' => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'citta'     => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'page'      => [ 'type' => 'integer', 'default' => 1, 'minimum' => 1 ],
                'per_page'  => [ 'type' => 'integer', 'default' => 20, 'maximum' => 50 ],
                'search'    => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );

        // GET singolo annuncio (pubblico)
        register_rest_route( $ns, '/annunci/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_annuncio' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'id' => [ 'type' => 'integer', 'required' => true ],
            ],
        ] );

        // POST crea annuncio (autenticato)
        register_rest_route( $ns, '/annunci', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'create_annuncio' ],
            'permission_callback' => [ $this, 'check_auth' ],
        ] );

        // PUT/PATCH modifica annuncio (autore o admin)
        register_rest_route( $ns, '/annunci/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => [ $this, 'update_annuncio' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
        ] );

        // DELETE (solo autore o admin)
        register_rest_route( $ns, '/annunci/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [ $this, 'delete_annuncio' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
        ] );

        // GET categorie (pubblico)
        register_rest_route( $ns, '/annunci/categorie', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_categorie' ],
            'permission_callback' => '__return_true',
        ] );

        // POST upload foto copertina (featured image)
        register_rest_route( $ns, '/annunci/(?P<id>\d+)/upload-copertina', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'upload_copertina' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
        ] );

        // POST upload foto galleria (allegato all'annuncio)
        register_rest_route( $ns, '/annunci/(?P<id>\d+)/upload-foto', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'upload_foto' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
        ] );

        // DELETE foto galleria
        register_rest_route( $ns, '/annunci/(?P<id>\d+)/foto/(?P<attachment_id>\d+)', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [ $this, 'delete_foto' ],
            'permission_callback' => [ $this, 'check_owner_or_admin' ],
        ] );

        // POST migliora descrizione con AI (autenticato)
        register_rest_route( $ns, '/annunci/improve-description', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'improve_description' ],
            'permission_callback' => [ $this, 'check_auth' ],
        ] );
    }

    // -------------------------------------------------------------------------
    // Permissions
    // -------------------------------------------------------------------------

    public function check_auth( $request ) {
        return is_user_logged_in();
    }

    public function check_owner_or_admin( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        $post_id = (int) $request['id'];
        $post    = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'wecoop_annuncio' ) {
            return false;
        }
        return current_user_can( 'manage_options' ) || (int) $post->post_author === get_current_user_id();
    }

    // -------------------------------------------------------------------------
    // Handlers
    // -------------------------------------------------------------------------

    public function get_annunci( $request ) {
        $today = date( 'Y-m-d' );

        $args = [
            'post_type'      => 'wecoop_annuncio',
            'post_status'    => 'publish',
            'posts_per_page' => (int) $request['per_page'],
            'paged'          => (int) $request['page'],
            'orderby'        => 'date',
            'order'          => 'DESC',
            // Solo annunci non scaduti (o senza data di scadenza = sempre visibili)
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key'     => '_annuncio_data_scadenza',
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                    [
                        'key'     => '_annuncio_data_scadenza',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => '_annuncio_data_scadenza',
                        'value'   => '',
                        'compare' => '=',
                    ],
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => '_annuncio_stato_pagamento',
                        'value'   => 'scaduto',
                        'compare' => '!=',
                    ],
                    [
                        'key'     => '_annuncio_stato_pagamento',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ],
        ];

        if ( ! empty( $request['categoria'] ) ) {
            $args['tax_query'] = [ [
                'taxonomy' => 'categoria_annuncio',
                'field'    => 'slug',
                'terms'    => $request['categoria'],
            ] ];
        }

        if ( ! empty( $request['citta'] ) ) {
            $args['meta_query'][] = [
                'key'     => '_annuncio_citta',
                'value'   => $request['citta'],
                'compare' => 'LIKE',
            ];
        }

        if ( ! empty( $request['search'] ) ) {
            $args['s'] = $request['search'];
        }

        $query = new WP_Query( $args );
        $items = [];

        foreach ( $query->posts as $post ) {
            $items[] = $this->format_annuncio( $post );
        }

        $response = rest_ensure_response( $items );
        $response->header( 'X-WP-Total', $query->found_posts );
        $response->header( 'X-WP-TotalPages', $query->max_num_pages );

        return $response;
    }

    public function get_annuncio( $request ) {
        $post = get_post( (int) $request['id'] );

        if ( ! $post || $post->post_type !== 'wecoop_annuncio' || $post->post_status !== 'publish' ) {
            return new WP_Error( 'not_found', 'Annuncio non trovato.', [ 'status' => 404 ] );
        }

        return rest_ensure_response( $this->format_annuncio( $post, true ) );
    }

    public function create_annuncio( $request ) {
        $body = $request->get_json_params();
        if ( empty( $body ) ) {
            $body = $request->get_body_params();
        }

        $title = isset( $body['titolo'] ) ? sanitize_text_field( $body['titolo'] ) : '';
        if ( empty( $title ) ) {
            return new WP_Error( 'missing_title', 'Il titolo è obbligatorio.', [ 'status' => 400 ] );
        }

        $post_id = wp_insert_post( [
            'post_title'   => $title,
            'post_content' => isset( $body['descrizione'] ) ? wp_kses_post( $body['descrizione'] ) : '',
            'post_status'  => 'publish',
            'post_type'    => 'wecoop_annuncio',
            'post_author'  => get_current_user_id(),
        ] );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Meta fields consentiti via API
        $allowed_meta = [
            'tipo_evento', 'lingua', 'luogo', 'indirizzo', 'citta', 'cap',
            'telefono', 'email', 'sito_web', 'prezzo_ingresso',
            'data_inizio', 'ora_inizio', 'data_fine', 'ora_fine',
            'artista', 'genere_musicale', 'link_biglietti', 'capienza_max',
            'menu_giorno', 'offerta_speciale', 'coperti_disp', 'prenota_tavolo',
            'disponibilita', 'tariffa',
        ];

        foreach ( $allowed_meta as $field ) {
            if ( isset( $body[ $field ] ) ) {
                $meta_key = '_annuncio_' . $field;
                $val      = sanitize_text_field( $body[ $field ] );
                update_post_meta( $post_id, $meta_key, $val );
            }
        }

        // Giorni pubblicazione: max FREE senza pagamento
        $giorni = isset( $body['giorni_pubblicazione'] ) ? (int) $body['giorni_pubblicazione'] : WECOOP_ANNUNCI_FREE_DAYS;
        $giorni = min( $giorni, WECOOP_ANNUNCI_FREE_DAYS ); // limite gratuito
        update_post_meta( $post_id, '_annuncio_giorni_pubb', $giorni );
        update_post_meta( $post_id, '_annuncio_stato_pagamento', 'gratuito' );
        update_post_meta( $post_id, '_annuncio_data_scadenza', date( 'Y-m-d', strtotime( "+{$giorni} days" ) ) );

        // Categoria
        if ( ! empty( $body['categoria'] ) ) {
            wp_set_post_terms( $post_id, [ sanitize_text_field( $body['categoria'] ) ], 'categoria_annuncio' );
        }

        return rest_ensure_response( $this->format_annuncio( get_post( $post_id ), true ) );
    }

    public function update_annuncio( $request ) {
        $post_id = (int) $request['id'];
        $body    = $request->get_json_params();
        if ( empty( $body ) ) {
            $body = $request->get_body_params();
        }

        if ( isset( $body['titolo'] ) ) {
            wp_update_post( [ 'ID' => $post_id, 'post_title' => sanitize_text_field( $body['titolo'] ) ] );
        }
        if ( isset( $body['descrizione'] ) ) {
            wp_update_post( [ 'ID' => $post_id, 'post_content' => wp_kses_post( $body['descrizione'] ) ] );
        }

        $updatable = [
            'tipo_evento', 'lingua', 'luogo', 'indirizzo', 'citta', 'cap',
            'telefono', 'email', 'sito_web', 'prezzo_ingresso',
            'data_inizio', 'ora_inizio', 'data_fine', 'ora_fine',
            'artista', 'genere_musicale', 'link_biglietti',
            'menu_giorno', 'offerta_speciale', 'coperti_disp', 'prenota_tavolo',
            'disponibilita', 'tariffa',
        ];

        foreach ( $updatable as $field ) {
            if ( isset( $body[ $field ] ) ) {
                update_post_meta( $post_id, '_annuncio_' . $field, sanitize_text_field( $body[ $field ] ) );
            }
        }

        return rest_ensure_response( $this->format_annuncio( get_post( $post_id ), true ) );
    }

    public function delete_annuncio( $request ) {
        $post_id = (int) $request['id'];
        wp_trash_post( $post_id );
        return rest_ensure_response( [ 'deleted' => true, 'id' => $post_id ] );
    }

    public function get_categorie( $request ) {
        $terms = get_terms( [
            'taxonomy'   => 'categoria_annuncio',
            'hide_empty' => false,
        ] );

        $result = [];
        foreach ( $terms as $term ) {
            $result[] = [
                'id'    => $term->term_id,
                'name'  => $term->name,
                'slug'  => $term->slug,
                'count' => $term->count,
            ];
        }

        return rest_ensure_response( $result );
    }

    // -------------------------------------------------------------------------
    // Upload handlers
    // -------------------------------------------------------------------------

    /**
     * Carica/sostituisce la foto copertina (featured image) dell'annuncio.
     * Multipart POST con campo 'file' (image/jpeg, image/png, image/webp).
     */
    public function upload_copertina( $request ) {
        $post_id = (int) $request['id'];

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $files = $request->get_file_params();
        if ( empty( $files['file'] ) ) {
            return new WP_Error( 'no_file', 'Nessun file ricevuto.', [ 'status' => 400 ] );
        }

        $attachment_id = $this->_do_upload( $files['file'], $post_id );
        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        // Rimuovi vecchia copertina se esiste
        $old_thumb = get_post_thumbnail_id( $post_id );
        if ( $old_thumb ) {
            wp_delete_attachment( $old_thumb, true );
        }

        set_post_thumbnail( $post_id, $attachment_id );

        $url = get_the_post_thumbnail_url( $post_id, 'large' )
            ?: get_the_post_thumbnail_url( $post_id, 'full' )
            ?: wp_get_attachment_url( $attachment_id )
            ?: '';

        return rest_ensure_response( [
            'success'        => true,
            'attachment_id'  => $attachment_id,
            'url'            => wp_get_attachment_url( $attachment_id ),
            'thumbnail_url'  => $url,
        ] );
    }

    /**
     * Aggiunge una foto alla galleria dell'annuncio (meta _annuncio_galleria).
     * Multipart POST con campo 'file'. Max 8 foto per annuncio.
     */
    public function upload_foto( $request ) {
        $post_id = (int) $request['id'];

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Limite foto galleria
        $galleria = get_post_meta( $post_id, '_annuncio_galleria', true );
        $galleria = is_array( $galleria ) ? $galleria : [];
        if ( count( $galleria ) >= 8 ) {
            return new WP_Error( 'max_photos', 'Limite massimo di 8 foto raggiunto.', [ 'status' => 400 ] );
        }

        $files = $request->get_file_params();
        if ( empty( $files['file'] ) ) {
            return new WP_Error( 'no_file', 'Nessun file ricevuto.', [ 'status' => 400 ] );
        }

        $attachment_id = $this->_do_upload( $files['file'], $post_id );
        if ( is_wp_error( $attachment_id ) ) {
            return $attachment_id;
        }

        $galleria[] = $attachment_id;
        update_post_meta( $post_id, '_annuncio_galleria', $galleria );

        return rest_ensure_response( [
            'success'       => true,
            'attachment_id' => $attachment_id,
            'url'           => wp_get_attachment_url( $attachment_id ),
            'galleria_ids'  => $galleria,
        ] );
    }

    /**
     * Elimina una foto dalla galleria.
     */
    public function delete_foto( $request ) {
        $post_id       = (int) $request['id'];
        $attachment_id = (int) $request['attachment_id'];

        $galleria = get_post_meta( $post_id, '_annuncio_galleria', true );
        $galleria = is_array( $galleria ) ? $galleria : [];
        $galleria = array_values( array_filter( $galleria, fn( $id ) => (int) $id !== $attachment_id ) );
        update_post_meta( $post_id, '_annuncio_galleria', $galleria );

        wp_delete_attachment( $attachment_id, true );

        return rest_ensure_response( [
            'success'      => true,
            'deleted_id'   => $attachment_id,
            'galleria_ids' => $galleria,
        ] );
    }

    /**
     * Helper interno: carica un file e crea un attachment WP.
     */
    private function _do_upload( array $file_data, int $post_id ) {
        $allowed_mime = [ 'image/jpeg', 'image/png', 'image/webp', 'image/jpg' ];

        // Detect MIME dal file reale se il tipo dichiarato è assente o non valido
        $file_type = $file_data['type'] ?? '';
        if ( $file_type === '' || ! in_array( $file_type, $allowed_mime, true ) ) {
            if ( function_exists( 'mime_content_type' ) && ! empty( $file_data['tmp_name'] ) ) {
                $detected = mime_content_type( $file_data['tmp_name'] );
                if ( $detected !== false ) {
                    $file_type = $detected;
                }
            }
            // jpg non standard → normalizza
            if ( $file_type === 'image/jpg' ) {
                $file_type = 'image/jpeg';
            }
        }

        $allowed_validated = [ 'image/jpeg', 'image/png', 'image/webp' ];
        if ( ! in_array( $file_type, $allowed_validated, true ) ) {
            return new WP_Error(
                'invalid_type',
                'Formato non supportato (' . esc_html( $file_type ) . '). Usa JPG, PNG o WebP.',
                [ 'status' => 415 ]
            );
        }

        // Correggi il tipo nel file_data per wp_handle_upload
        $file_data['type'] = $file_type;
        }

        // Limite 5MB
        if ( $file_data['size'] > 5 * 1024 * 1024 ) {
            return new WP_Error( 'file_too_large', 'Il file supera il limite di 5MB.', [ 'status' => 413 ] );
        }

        $upload = wp_handle_upload( $file_data, [ 'test_form' => false ] );
        if ( isset( $upload['error'] ) ) {
            return new WP_Error( 'upload_error', $upload['error'], [ 'status' => 500 ] );
        }

        $attachment_id = wp_insert_attachment( [
            'guid'           => $upload['url'],
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name( $file_data['name'] ),
            'post_status'    => 'inherit',
            'post_parent'    => $post_id,
        ], $upload['file'], $post_id );

        $meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
        wp_update_attachment_metadata( $attachment_id, $meta );

        return $attachment_id;
    }

    // -------------------------------------------------------------------------
    // Formatter
    // -------------------------------------------------------------------------

    private function format_annuncio( $post, $full = false ) {
        $id = $post->ID;
        $get = fn( $key ) => get_post_meta( $id, $key, true );

        $thumb_url = '';
        if ( has_post_thumbnail( $id ) ) {
            $thumb_id  = (int) get_post_thumbnail_id( $id );
            $thumb_url = (string) (
                get_the_post_thumbnail_url( $id, 'large' )
                ?: get_the_post_thumbnail_url( $id, 'full' )
                ?: ( $thumb_id > 0 ? wp_get_attachment_url( $thumb_id ) : '' )
                ?: ''
            );
        }

        $categorie = wp_get_post_terms( $id, 'categoria_annuncio', [ 'fields' => 'all' ] );
        $cat_data  = [];
        foreach ( $categorie as $cat ) {
            $cat_data[] = [ 'id' => $cat->term_id, 'name' => $cat->name, 'slug' => $cat->slug ];
        }

        // Galleria foto
        $galleria_ids  = get_post_meta( $id, '_annuncio_galleria', true );
        $galleria_ids  = is_array( $galleria_ids ) ? $galleria_ids : [];
        $galleria_urls = array_values( array_filter( array_map( fn( $aid ) => wp_get_attachment_url( $aid ) ?: null, $galleria_ids ) ) );

        $autore    = get_userdata( $post->post_author );
        $giorni    = (int) $get( '_annuncio_giorni_pubb' ) ?: WECOOP_ANNUNCI_FREE_DAYS;
        $scadenza  = $get( '_annuncio_data_scadenza' );
        $is_scaduto = $scadenza && strtotime( $scadenza ) < strtotime( 'today' );

        $data = [
            'id'                => $id,
            'titolo'            => $post->post_title,
            'descrizione'       => $full ? wp_strip_all_tags( $post->post_content ) : wp_trim_words( $post->post_content, 25 ),
            'immagine_url'      => $thumb_url,
            'galleria_urls'     => $galleria_urls,
            'categorie'         => $cat_data,
            'tipo_evento'       => $get( '_annuncio_tipo_evento' ),
            'lingua'            => $get( '_annuncio_lingua' ),
            'luogo'             => $get( '_annuncio_luogo' ),
            'citta'             => $get( '_annuncio_citta' ),
            'data_inizio'       => $get( '_annuncio_data_inizio' ),
            'ora_inizio'        => $get( '_annuncio_ora_inizio' ),
            'data_fine'         => $get( '_annuncio_data_fine' ),
            'ora_fine'          => $get( '_annuncio_ora_fine' ),
            'prezzo_ingresso'   => (float) $get( '_annuncio_prezzo_ingresso' ),
            'data_scadenza'     => $scadenza,
            'giorni_pubb'       => $giorni,
            'stato_pagamento'   => $is_scaduto ? 'scaduto' : ( $get( '_annuncio_stato_pagamento' ) ?: 'gratuito' ),
            'autore_id'         => (int) $post->post_author,
            'autore_nome'       => $autore ? $autore->display_name : '',
            'permalink'         => get_permalink( $id ),
            'pubblicato'        => $post->post_date,
        ];

        if ( $full ) {
            $data = array_merge( $data, [
                'indirizzo'        => $get( '_annuncio_indirizzo' ),
                'cap'              => $get( '_annuncio_cap' ),
                'telefono'         => $get( '_annuncio_telefono' ),
                'email'            => $get( '_annuncio_email' ),
                'sito_web'         => $get( '_annuncio_sito_web' ),
                // Concerto
                'artista'          => $get( '_annuncio_artista' ),
                'genere_musicale'  => $get( '_annuncio_genere_musicale' ),
                'link_biglietti'   => $get( '_annuncio_link_biglietti' ),
                'capienza_max'     => (int) $get( '_annuncio_capienza_max' ),
                // Ristorante
                'menu_giorno'      => $get( '_annuncio_menu_giorno' ),
                'offerta_speciale' => $get( '_annuncio_offerta_speciale' ),
                'coperti_disp'     => (int) $get( '_annuncio_coperti_disp' ),
                'prenota_tavolo'   => $get( '_annuncio_prenota_tavolo' ),
                // Servizio
                'disponibilita'    => $get( '_annuncio_disponibilita' ),
                'tariffa'          => (float) $get( '_annuncio_tariffa' ),
                // Galleria
                'galleria_ids'     => $galleria_ids,
            ] );
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // AI: migliora descrizione annuncio
    // -------------------------------------------------------------------------

    public function improve_description( $request ) {
        $payload = $request->get_json_params();
        if ( ! is_array( $payload ) ) {
            $payload = $request->get_params();
        }

        $titolo      = sanitize_text_field( (string) ( $payload['titolo'] ?? '' ) );
        $citta       = sanitize_text_field( (string) ( $payload['citta'] ?? '' ) );
        $categoria   = sanitize_text_field( (string) ( $payload['categoria'] ?? '' ) );
        $descrizione = sanitize_textarea_field( (string) ( $payload['descrizione'] ?? '' ) );

        if ( strlen( $descrizione ) < 12 ) {
            return new WP_Error( 'short_description', 'Inserisci almeno 12 caratteri di descrizione per usare l\'AI.', [ 'status' => 400 ] );
        }

        $api_key = defined( 'OPENAI_API_KEY' ) ? OPENAI_API_KEY : (string) get_option( 'wecoop_openai_api_key', '' );
        if ( $api_key === '' ) {
            return new WP_REST_Response( [
                'success' => true,
                'data'    => [
                    'ai_description' => $this->_fallback_description( $titolo, $citta, $categoria, $descrizione ),
                    'source'         => 'template_fallback',
                ],
            ], 200 );
        }

        $result = $this->_openai_improve( $api_key, $titolo, $citta, $categoria, $descrizione );

        if ( is_wp_error( $result ) ) {
            return new WP_REST_Response( [
                'success' => true,
                'data'    => [
                    'ai_description' => $this->_fallback_description( $titolo, $citta, $categoria, $descrizione ),
                    'source'         => 'template_fallback',
                    'note'           => 'Fallback: ' . $result->get_error_message(),
                ],
            ], 200 );
        }

        return new WP_REST_Response( [
            'success' => true,
            'data'    => [
                'ai_description' => $result,
                'source'         => 'openai',
            ],
        ], 200 );
    }

    private function _openai_improve( string $api_key, string $titolo, string $citta, string $categoria, string $descrizione ) {
        $model = apply_filters( 'wecoop_annunci_openai_model', 'gpt-4o-mini' );

        $messages = [
            [
                'role'    => 'system',
                'content' => 'Sei un assistente editoriale per annunci locali (eventi, concerti, ristoranti, servizi). Scrivi testi chiari, vivaci e coinvolgenti. Restituisci solo JSON valido.',
            ],
            [
                'role'    => 'user',
                'content' => "Migliora questa bozza di annuncio locale mantenendo tutti i dati reali.\n"
                    . "Titolo: $titolo\n"
                    . "Città: $citta\n"
                    . "Categoria: $categoria\n"
                    . "Testo originale: $descrizione\n"
                    . "Regole: massimo 1200 caratteri, frasi brevi e coinvolgenti, includi luogo/città se disponibile, orari/date se presenti, come partecipare o contattare. Non inventare dati assenti.\n"
                    . 'Rispondi solo con JSON: {"ai_description":"testo migliorato"}',
            ],
        ];

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'timeout' => 25,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [
                'model'           => $model,
                'temperature'     => 0.25,
                'response_format' => [ 'type' => 'json_object' ],
                'messages'        => $messages,
            ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status  = (int) wp_remote_retrieve_response_code( $response );
        $body    = wp_remote_retrieve_body( $response );
        $decoded = json_decode( $body, true );

        if ( $status < 200 || $status >= 300 || ! is_array( $decoded ) ) {
            return new WP_Error( 'openai_http_error', 'Errore chiamata OpenAI: HTTP ' . $status );
        }

        $content = (string) ( $decoded['choices'][0]['message']['content'] ?? '' );
        $parsed  = json_decode( $content, true );
        if ( ! is_array( $parsed ) ) {
            return new WP_Error( 'openai_parse_error', 'Risposta AI non leggibile' );
        }

        $text = trim( (string) ( $parsed['ai_description'] ?? '' ) );
        if ( $text === '' ) {
            return new WP_Error( 'openai_empty', 'Descrizione AI vuota' );
        }

        return preg_replace( '/\s+/', ' ', $text ) ?: $text;
    }

    private function _fallback_description( string $titolo, string $citta, string $categoria, string $descrizione ): string {
        $intro    = trim( $titolo ) !== '' ? $titolo . '.' : 'Annuncio locale.';
        $location = trim( $citta ) !== '' ? ' Luogo: ' . $citta . '.' : '';
        $cat      = trim( $categoria ) !== '' ? ' Categoria: ' . $categoria . '.' : '';
        $clean    = preg_replace( '/\s+/', ' ', trim( $descrizione ) );
        return trim( $intro . ' ' . $clean . $location . $cat );
    }
}
