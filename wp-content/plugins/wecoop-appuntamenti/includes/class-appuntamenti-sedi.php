<?php
/**
 * Gestione Sedi WECOOP.
 * Registra il CPT 'wecoop_sede' (nome + indirizzo + note) cosi' che l'operatore
 * possa salvare una volta le sedi e riutilizzarle nei form appuntamento tramite
 * un menu a tendina, senza riscrivere ogni volta le informazioni.
 *
 * @package WECOOP_Appuntamenti
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Appuntamenti_Sedi {

    const POST_TYPE = 'wecoop_sede';

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_metabox']);
        add_action('save_post_' . self::POST_TYPE, [__CLASS__, 'save_metabox']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [__CLASS__, 'columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [__CLASS__, 'column_content'], 10, 2);
    }

    /**
     * Registra il CPT sede sotto il menu Appuntamenti.
     */
    public static function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'Sedi',
                'singular_name' => 'Sede',
                'add_new'       => 'Nuova sede',
                'add_new_item'  => 'Aggiungi sede',
                'edit_item'     => 'Modifica sede',
                'new_item'      => 'Nuova sede',
                'view_item'     => 'Visualizza sede',
                'search_items'  => 'Cerca sedi',
                'not_found'     => 'Nessuna sede trovata',
                'menu_name'     => 'Sedi',
            ],
            'public'            => false,
            'show_ui'           => true,
            // Mostrato come sottomenu della pagina Appuntamenti (vedi Admin_Page::MENU_SLUG).
            'show_in_menu'      => 'wecoop-appuntamenti',
            'supports'          => ['title'],
            'capability_type'   => 'post',
            'has_archive'       => false,
            'menu_icon'         => 'dashicons-location',
        ]);
    }

    public static function add_metabox() {
        add_meta_box(
            'wecoop_sede_details',
            'Dettagli sede',
            [__CLASS__, 'render_metabox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function render_metabox($post) {
        wp_nonce_field('wecoop_sede_save', 'wecoop_sede_nonce');
        $indirizzo = get_post_meta($post->ID, 'indirizzo', true);
        $citta     = get_post_meta($post->ID, 'citta', true);
        $telefono  = get_post_meta($post->ID, 'telefono', true);
        $note      = get_post_meta($post->ID, 'note', true);

        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label for="wecoop_sede_indirizzo">Indirizzo</label></th><td>';
        echo '<input type="text" id="wecoop_sede_indirizzo" name="wecoop_sede_indirizzo" class="regular-text" value="' . esc_attr($indirizzo) . '" placeholder="Via ..., n.">';
        echo '</td></tr>';
        echo '<tr><th><label for="wecoop_sede_citta">Citta</label></th><td>';
        echo '<input type="text" id="wecoop_sede_citta" name="wecoop_sede_citta" class="regular-text" value="' . esc_attr($citta) . '" placeholder="Es. Milano">';
        echo '</td></tr>';
        echo '<tr><th><label for="wecoop_sede_telefono">Telefono</label></th><td>';
        echo '<input type="text" id="wecoop_sede_telefono" name="wecoop_sede_telefono" class="regular-text" value="' . esc_attr($telefono) . '">';
        echo '</td></tr>';
        echo '<tr><th><label for="wecoop_sede_note">Note</label></th><td>';
        echo '<textarea id="wecoop_sede_note" name="wecoop_sede_note" class="large-text" rows="3">' . esc_textarea($note) . '</textarea>';
        echo '</td></tr>';
        echo '</tbody></table>';
    }

    public static function save_metabox($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!isset($_POST['wecoop_sede_nonce']) || !wp_verify_nonce($_POST['wecoop_sede_nonce'], 'wecoop_sede_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            'indirizzo' => 'wecoop_sede_indirizzo',
            'citta'     => 'wecoop_sede_citta',
            'telefono'  => 'wecoop_sede_telefono',
        ];
        foreach ($fields as $meta => $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
            update_post_meta($post_id, $meta, $value);
        }
        $note = isset($_POST['wecoop_sede_note']) ? sanitize_textarea_field($_POST['wecoop_sede_note']) : '';
        update_post_meta($post_id, 'note', $note);
    }

    public static function columns($columns) {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['wecoop_indirizzo'] = 'Indirizzo';
                $new['wecoop_citta']     = 'Citta';
                $new['wecoop_telefono']  = 'Telefono';
            }
        }
        return $new;
    }

    public static function column_content($column, $post_id) {
        switch ($column) {
            case 'wecoop_indirizzo':
                echo esc_html(get_post_meta($post_id, 'indirizzo', true));
                break;
            case 'wecoop_citta':
                echo esc_html(get_post_meta($post_id, 'citta', true));
                break;
            case 'wecoop_telefono':
                echo esc_html(get_post_meta($post_id, 'telefono', true));
                break;
        }
    }

    /* ---------------------------------------------------------------------
     * Helper riutilizzabili
     * ------------------------------------------------------------------- */

    /**
     * Restituisce l'elenco delle sedi pubblicate.
     *
     * @return array<int, array{id:int,nome:string,indirizzo:string,citta:string,telefono:string,note:string}>
     */
    public static function get_all() {
        $posts = get_posts([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'numberposts'    => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'suppress_filters' => false,
        ]);

        $sedi = [];
        foreach ($posts as $p) {
            $sedi[] = [
                'id'        => (int) $p->ID,
                'nome'      => $p->post_title,
                'indirizzo' => (string) get_post_meta($p->ID, 'indirizzo', true),
                'citta'     => (string) get_post_meta($p->ID, 'citta', true),
                'telefono'  => (string) get_post_meta($p->ID, 'telefono', true),
                'note'      => (string) get_post_meta($p->ID, 'note', true),
            ];
        }
        return $sedi;
    }

    /**
     * Indirizzo completo di una sede (indirizzo + citta).
     */
    public static function full_address(array $sede) {
        $parts = array_filter([
            trim((string) ($sede['indirizzo'] ?? '')),
            trim((string) ($sede['citta'] ?? '')),
        ]);
        return implode(', ', $parts);
    }

    /**
     * Renderizza il <select> delle sedi + JS che auto-compila i campi
     * sede/indirizzo del form.
     *
     * @param string $select_name    name dell'input select (es. 'sede_id').
     * @param string $sede_field_id  id dell'input testo "sede" da compilare.
     * @param string $indir_field_id id dell'input testo "indirizzo" da compilare.
     */
    public static function render_select($select_name = 'sede_id', $sede_field_id = 'wecoop_sede_field', $indir_field_id = 'wecoop_indirizzo_field', $selected = 0) {
        $sedi = self::get_all();
        $uid  = esc_attr($select_name);

        if (empty($sedi)) {
            $new_url = admin_url('post-new.php?post_type=' . self::POST_TYPE);
            echo '<em>Nessuna sede salvata. </em><a href="' . esc_url($new_url) . '">Aggiungi una sede</a> per poterla selezionare.';
            return;
        }

        echo '<select name="' . $uid . '" id="' . $uid . '" class="wecoop-sede-select">';
        echo '<option value="0" data-indirizzo="">— Seleziona una sede —</option>';
        foreach ($sedi as $s) {
            $full = self::full_address($s);
            printf(
                '<option value="%d" data-nome="%s" data-indirizzo="%s"%s>%s</option>',
                $s['id'],
                esc_attr($s['nome']),
                esc_attr($full),
                selected((int) $selected, $s['id'], false),
                esc_html($s['nome'] . ($full ? ' — ' . $full : ''))
            );
        }
        echo '</select>';

        // JS: al cambio della sede compila i campi testo sede/indirizzo.
        $sede_id  = esc_js($sede_field_id);
        $indir_id = esc_js($indir_field_id);
        echo '<script>(function(){';
        echo 'var sel=document.getElementById("' . $uid . '");';
        echo 'if(!sel)return;';
        echo 'sel.addEventListener("change",function(){';
        echo 'var o=this.options[this.selectedIndex];';
        echo 'var nome=o.getAttribute("data-nome")||"";';
        echo 'var ind=o.getAttribute("data-indirizzo")||"";';
        echo 'var sf=document.getElementById("' . $sede_id . '");';
        echo 'var inf=document.getElementById("' . $indir_id . '");';
        echo 'if(sf)sf.value=nome;';
        echo 'if(inf)inf.value=ind;';
        echo '});';
        echo '})();</script>';
    }
}
