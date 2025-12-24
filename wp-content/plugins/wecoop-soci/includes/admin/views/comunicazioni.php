<?php
/**
 * Comunicazioni
 * 
 * @package WECOOP_CRM
 */

if (!defined('ABSPATH')) exit;

// Gestisci invio comunicazione
if (isset($_POST['send_communication'])) {
    check_admin_referer('wecoop_send_communication');
    
    $destinatari = $_POST['destinatari'];
    $subject = sanitize_text_field($_POST['subject']);
    $message = wp_kses_post($_POST['message']);
    
    $soci_emails = [];
    
    if ($destinatari === 'all') {
        $soci = get_users(['meta_key' => 'numero_tessera', 'meta_compare' => 'EXISTS']);
        foreach ($soci as $socio) {
            $soci_emails[] = $socio->user_email;
        }
    } elseif ($destinatari === 'active') {
        $soci = get_users(['meta_key' => 'numero_tessera', 'meta_compare' => 'EXISTS']);
        foreach ($soci as $socio) {
            $status = get_user_meta($socio->ID, 'status_socio', true);
            if ($status === 'attivo' || empty($status)) {
                $soci_emails[] = $socio->user_email;
            }
        }
    }
    
    // Invia con template WeCoop se disponibile
    $sent_count = 0;
    foreach ($soci_emails as $email) {
        $success = false;
        if (class_exists('WeCoop_Email_Template_Unified')) {
            $success = WeCoop_Email_Template_Unified::send($email, $subject, $message);
        } else {
            $success = wp_mail($email, $subject, $message);
        }
        if ($success) {
            $sent_count++;
        }
    }
    
    echo '<div class="notice notice-success"><p>Comunicazione inviata a ' . $sent_count . ' soci!</p></div>';
}
?>

<div class="wrap">
    <h1>📧 Invia Comunicazione ai Soci</h1>
    
    <form method="post" style="background: #fff; padding: 20px; margin: 20px 0;">
        <?php wp_nonce_field('wecoop_send_communication'); ?>
        
        <table class="form-table">
            <tr>
                <th><label for="destinatari">Destinatari</label></th>
                <td>
                    <select name="destinatari" id="destinatari" class="regular-text">
                        <option value="all">Tutti i Soci</option>
                        <option value="active">Solo Soci Attivi</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="subject">Oggetto</label></th>
                <td>
                    <input type="text" name="subject" id="subject" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="message">Messaggio</label></th>
                <td>
                    <textarea name="message" id="message" rows="10" class="large-text" required></textarea>
                    <p class="description">Il messaggio verrà inviato via email a tutti i destinatari selezionati.</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="send_communication" class="button button-primary">Invia Comunicazione</button>
        </p>
    </form>
</div>
