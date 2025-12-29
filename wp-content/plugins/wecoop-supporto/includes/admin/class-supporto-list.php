<?php
/**
 * Admin List - Custom columns for Richieste Supporto
 */

if (!defined('ABSPATH')) exit;

class WeCoop_Supporto_List {
    
    public function __construct() {
        add_filter('manage_richiesta_supporto_posts_columns', [$this, 'add_columns']);
        add_action('manage_richiesta_supporto_posts_custom_column', [$this, 'render_columns'], 10, 2);
        add_filter('manage_edit-richiesta_supporto_sortable_columns', [$this, 'sortable_columns']);
    }
    
    public function add_columns($columns) {
        $new_columns = [
            'cb' => $columns['cb'],
            'title' => 'Richiesta',
            'numero_ticket' => 'Ticket',
            'utente' => 'Utente',
            'servizio' => 'Servizio',
            'priorita' => 'Priorità',
            'status' => 'Status',
            'whatsapp' => 'WhatsApp',
            'date' => 'Data'
        ];
        
        return $new_columns;
    }
    
    public function render_columns($column, $post_id) {
        switch ($column) {
            case 'numero_ticket':
                echo '<strong>' . esc_html(get_post_meta($post_id, 'numero_ticket', true)) . '</strong>';
                break;
                
            case 'utente':
                $user_name = get_post_meta($post_id, 'user_name', true);
                $user_phone = get_post_meta($post_id, 'user_phone', true);
                $user_email = get_post_meta($post_id, 'user_email', true);
                echo '<strong>' . esc_html($user_name) . '</strong><br>';
                echo '<small>' . esc_html($user_phone) . '</small><br>';
                echo '<small><a href="mailto:' . esc_attr($user_email) . '">' . esc_html($user_email) . '</a></small>';
                break;
                
            case 'servizio':
                $service_name = get_post_meta($post_id, 'service_name', true);
                $service_category = get_post_meta($post_id, 'service_category', true);
                echo '<strong>' . esc_html($service_name) . '</strong><br>';
                echo '<small style="color: #72777c;">' . esc_html($service_category) . '</small>';
                break;
                
            case 'priorita':
                $priorita = get_post_meta($post_id, 'priorita', true);
                $colors = [
                    'alta' => '#dc3232',
                    'media' => '#f56e28',
                    'bassa' => '#46b450'
                ];
                $color = $colors[$priorita] ?? '#72777c';
                echo '<span style="display: inline-block; padding: 4px 10px; background: ' . $color . '; color: white; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase;">';
                echo esc_html($priorita);
                echo '</span>';
                break;
                
            case 'status':
                $status = get_post_meta($post_id, 'status', true);
                $statuses = [
                    'aperta' => ['label' => '🔵 Aperta', 'color' => '#00a0d2'],
                    'in_lavorazione' => ['label' => '🟡 In Lavorazione', 'color' => '#f56e28'],
                    'risolta' => ['label' => '🟢 Risolta', 'color' => '#46b450'],
                    'chiusa' => ['label' => '⚫ Chiusa', 'color' => '#72777c']
                ];
                $info = $statuses[$status] ?? ['label' => $status, 'color' => '#72777c'];
                echo '<span style="display: inline-block; padding: 4px 10px; background: ' . $info['color'] . '; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">';
                echo esc_html($info['label']);
                echo '</span>';
                break;
                
            case 'whatsapp':
                $user_phone = get_post_meta($post_id, 'user_phone', true);
                $user_name = get_post_meta($post_id, 'user_name', true);
                $service_name = get_post_meta($post_id, 'service_name', true);
                
                $whatsapp_number = preg_replace('/[^0-9]/', '', $user_phone);
                $message = "Ciao {$user_name}, ti contattiamo riguardo la tua richiesta di supporto per il servizio {$service_name}.";
                $whatsapp_url = "https://wa.me/{$whatsapp_number}?text=" . urlencode($message);
                
                echo '<a href="' . esc_url($whatsapp_url) . '" target="_blank" class="button button-small button-primary" style="background: #25D366; border-color: #25D366;">';
                echo '<span class="dashicons dashicons-whatsapp" style="font-size: 14px; margin-top: 2px;"></span> Chat';
                echo '</a>';
                break;
        }
    }
    
    public function sortable_columns($columns) {
        $columns['numero_ticket'] = 'numero_ticket';
        $columns['priorita'] = 'priorita';
        $columns['status'] = 'status';
        return $columns;
    }
}

new WeCoop_Supporto_List();
