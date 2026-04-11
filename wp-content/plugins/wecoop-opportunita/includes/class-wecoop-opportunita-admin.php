<?php

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Opportunita_Admin {

    public static function init() {
        add_filter('manage_' . WeCoop_Opportunita_CPT::POST_TYPE . '_posts_columns', [__CLASS__, 'set_custom_columns']);
        add_action('manage_' . WeCoop_Opportunita_CPT::POST_TYPE . '_posts_custom_column', [__CLASS__, 'render_custom_column'], 10, 2);
        add_filter('manage_edit-' . WeCoop_Opportunita_CPT::POST_TYPE . '_sortable_columns', [__CLASS__, 'sortable_columns']);
        add_action('pre_get_posts', [__CLASS__, 'handle_admin_sorting']);
    }

    public static function set_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = isset($columns['cb']) ? $columns['cb'] : '';
        $new_columns['title'] = 'Titolo';
        $new_columns['categoria_opportunita'] = 'Categoria';
        $new_columns['tipo_contenuto_opportunita'] = 'Tipo';
        $new_columns['tag_opportunita'] = 'Tag';
        $new_columns['is_featured'] = 'Featured';
        $new_columns['publish_in_app'] = 'In App';
        $new_columns['priority_order'] = 'Priority';
        $new_columns['date'] = 'Pubblicato';

        return $new_columns;
    }

    public static function render_custom_column($column, $post_id) {
        if ($column === 'is_featured') {
            echo get_post_meta($post_id, 'is_featured', true) === '1' ? 'Si' : 'No';
            return;
        }

        if ($column === 'publish_in_app') {
            echo get_post_meta($post_id, 'publish_in_app', true) === '1' ? 'Si' : 'No';
            return;
        }

        if ($column === 'priority_order') {
            $priority = (int) get_post_meta($post_id, 'priority_order', true);
            echo esc_html((string) $priority);
        }
    }

    public static function sortable_columns($columns) {
        $columns['is_featured'] = 'is_featured';
        $columns['priority_order'] = 'priority_order';

        return $columns;
    }

    public static function handle_admin_sorting($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('post_type') !== WeCoop_Opportunita_CPT::POST_TYPE) {
            return;
        }

        $orderby = $query->get('orderby');

        if ($orderby === 'is_featured') {
            $query->set('meta_key', 'is_featured');
            $query->set('orderby', 'meta_value_num');
        }

        if ($orderby === 'priority_order') {
            $query->set('meta_key', 'priority_order');
            $query->set('orderby', 'meta_value_num');
        }
    }
}
