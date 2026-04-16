<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WECOOP_Annuncio_CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
        add_action( 'init', [ $this, 'register_stato_terms' ] );
    }

    public function register_cpt() {
        $labels = [
            'name'               => 'Annunci',
            'singular_name'      => 'Annuncio',
            'add_new'            => 'Nuovo Annuncio',
            'add_new_item'       => 'Aggiungi Annuncio',
            'edit_item'          => 'Modifica Annuncio',
            'view_item'          => 'Vedi Annuncio',
            'search_items'       => 'Cerca Annunci',
            'not_found'          => 'Nessun annuncio trovato.',
            'menu_name'          => 'Annunci WeCoop',
        ];

        register_post_type( 'wecoop_annuncio', [
            'labels'              => $labels,
            'public'              => true,
            'show_in_rest'        => true,
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'annunci' ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'author' ],
            'menu_icon'           => 'dashicons-megaphone',
            'menu_position'       => 25,
            'show_in_menu'        => true,
            'capability_type'     => 'post',
            // Gli utenti registrati possono creare annunci
            'map_meta_cap'        => true,
        ] );
    }

    public function register_taxonomies() {
        // Categoria annuncio
        register_taxonomy( 'categoria_annuncio', 'wecoop_annuncio', [
            'label'        => 'Categoria',
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => [ 'slug' => 'categoria-annuncio' ],
        ] );

        // Tag liberi
        register_taxonomy( 'tag_annuncio', 'wecoop_annuncio', [
            'label'        => 'Tag',
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => [ 'slug' => 'tag-annuncio' ],
        ] );
    }

    public function register_stato_terms() {
        // Categorie di default al primo avvio
        $categorie = [
            'evento'      => 'Evento',
            'concerto'    => 'Concerto / Musica',
            'ristorante'  => 'Ristorante / Food',
            'servizio'    => 'Servizio',
            'vendita'     => 'Vendita',
            'sport'       => 'Sport / Fitness',
            'cultura'     => 'Cultura / Arte',
            'lavoro'      => 'Lavoro / Collaborazione',
            'altro'       => 'Altro',
        ];

        foreach ( $categorie as $slug => $name ) {
            if ( ! term_exists( $slug, 'categoria_annuncio' ) ) {
                wp_insert_term( $name, 'categoria_annuncio', [ 'slug' => $slug ] );
            }
        }
    }
}
