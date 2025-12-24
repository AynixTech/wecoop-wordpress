<?php
/**
 * Admin: Gestione Eventi
 * 
 * @package WECOOP_Eventi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Eventi_Admin {
    
    /**
     * Inizializza
     */
    public static function init() {
        add_filter('manage_evento_posts_columns', [__CLASS__, 'add_quick_edit_columns']);
        add_action('quick_edit_custom_box', [__CLASS__, 'render_quick_edit'], 10, 2);
        add_action('save_post_evento', [__CLASS__, 'save_quick_edit']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
    }
    
    /**
     * Aggiungi colonne per quick edit
     */
    public static function add_quick_edit_columns($columns) {
        $columns['titolo_it'] = 'Titolo IT';
        $columns['titolo_en'] = 'Titolo EN';
        return $columns;
    }
    
    /**
     * Render quick edit
     */
    public static function render_quick_edit($column_name, $post_type) {
        if ($post_type !== 'evento') return;
        
        if ($column_name === 'titolo_it') {
            ?>
            <fieldset class="inline-edit-col-left">
                <div class="inline-edit-col">
                    <label>
                        <span class="title">Titolo IT</span>
                        <span class="input-text-wrap">
                            <input type="text" name="titolo_it" class="ptitle" value="">
                        </span>
                    </label>
                    
                    <label>
                        <span class="title">Titolo EN</span>
                        <span class="input-text-wrap">
                            <input type="text" name="titolo_en" class="ptitle" value="">
                        </span>
                    </label>
                    
                    <label>
                        <span class="title">Titolo FR</span>
                        <span class="input-text-wrap">
                            <input type="text" name="titolo_fr" class="ptitle" value="">
                        </span>
                    </label>
                    
                    <label>
                        <span class="title">Titolo ES</span>
                        <span class="input-text-wrap">
                            <input type="text" name="titolo_es" class="ptitle" value="">
                        </span>
                    </label>
                    
                    <label>
                        <span class="title">Titolo AR</span>
                        <span class="input-text-wrap">
                            <input type="text" name="titolo_ar" class="ptitle" value="">
                        </span>
                    </label>
                </div>
            </fieldset>
            <?php
        }
    }
    
    /**
     * Salva quick edit
     */
    public static function save_quick_edit($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        $languages = ['it', 'en', 'fr', 'es', 'ar'];
        foreach ($languages as $lang) {
            if (isset($_POST['titolo_' . $lang])) {
                update_post_meta($post_id, 'titolo_' . $lang, sanitize_text_field($_POST['titolo_' . $lang]));
            }
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($post_type !== 'evento') return;
        
        if ($hook === 'edit.php') {
            wp_add_inline_script('jquery', "
                jQuery(document).ready(function($) {
                    var \$wp_inline_edit = inlineEditPost.edit;
                    inlineEditPost.edit = function(id) {
                        \$wp_inline_edit.apply(this, arguments);
                        
                        var post_id = 0;
                        if (typeof(id) == 'object') {
                            post_id = parseInt(this.getId(id));
                        }
                        
                        if (post_id > 0) {
                            var \$row = $('#post-' + post_id);
                            var \$edit_row = $('#edit-' + post_id);
                            
                            // Popola campi quick edit
                            var languages = ['it', 'en', 'fr', 'es', 'ar'];
                            languages.forEach(function(lang) {
                                var value = \$row.find('.titolo_' + lang).text();
                                \$edit_row.find('input[name=\"titolo_' + lang + '\"]').val(value);
                            });
                        }
                    };
                });
            ");
        }
    }
}
