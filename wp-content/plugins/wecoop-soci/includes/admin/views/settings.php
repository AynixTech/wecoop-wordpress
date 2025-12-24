<?php
/**
 * Impostazioni CRM
 * 
 * @package WECOOP_CRM
 */

if (!defined('ABSPATH')) exit;

if (isset($_POST['wecoop_crm_save_settings'])) {
    check_admin_referer('wecoop_crm_settings');
    
    $options = [
        'enable_richieste' => isset($_POST['enable_richieste']),
        'enable_documenti' => isset($_POST['enable_documenti']),
        'enable_comunicazioni' => isset($_POST['enable_comunicazioni']),
        'enable_statistiche' => isset($_POST['enable_statistiche']),
        'enable_servizi' => isset($_POST['enable_servizi']),
        'auto_approve' => isset($_POST['auto_approve']),
        'email_notifications' => isset($_POST['email_notifications']),
    ];
    
    update_option('wecoop_crm_options', $options);
    echo '<div class="notice notice-success"><p>Impostazioni salvate!</p></div>';
}

$options = get_option('wecoop_crm_options', [
    'enable_richieste' => true,
    'enable_documenti' => true,
    'enable_comunicazioni' => true,
    'enable_statistiche' => true,
    'enable_servizi' => true,
    'auto_approve' => false,
    'email_notifications' => true,
]);
?>

<div class="wrap">
    <h1>⚙️ Impostazioni WECOOP CRM</h1>
    
    <form method="post" style="background: #fff; padding: 20px; margin: 20px 0;">
        <?php wp_nonce_field('wecoop_crm_settings'); ?>
        
        <h2>Moduli Attivi</h2>
        <table class="form-table">
            <tr>
                <th>Richieste Adesione</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_richieste" <?php checked($options['enable_richieste'] ?? true); ?>>
                        Abilita gestione richieste di adesione soci
                    </label>
                </td>
            </tr>
            <tr>
                <th>Richieste Servizi</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_servizi" <?php checked($options['enable_servizi'] ?? true); ?>>
                        Abilita gestione richieste servizi (permessi, cittadinanza, fiscale, contabile)
                    </label>
                </td>
            </tr>
            <tr>
                <th>Documenti Soci</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_documenti" <?php checked($options['enable_documenti'] ?? true); ?>>
                        Abilita gestione documenti soci
                    </label>
                </td>
            </tr>
            <tr>
                <th>Comunicazioni</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_comunicazioni" <?php checked($options['enable_comunicazioni'] ?? true); ?>>
                        Abilita invio comunicazioni massive
                    </label>
                </td>
            </tr>
            <tr>
                <th>Statistiche</th>
                <td>
                    <label>
                        <input type="checkbox" name="enable_statistiche" <?php checked($options['enable_statistiche'] ?? true); ?>>
                        Abilita dashboard statistiche
                    </label>
                </td>
            </tr>
        </table>
        
        <h2>Opzioni Richieste</h2>
        <table class="form-table">
            <tr>
                <th>Approvazione Automatica</th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_approve" <?php checked($options['auto_approve'] ?? false); ?>>
                        Approva automaticamente le richieste (sconsigliato)
                    </label>
                </td>
            </tr>
            <tr>
                <th>Notifiche Email</th>
                <td>
                    <label>
                        <input type="checkbox" name="email_notifications" <?php checked($options['email_notifications'] ?? true); ?>>
                        Invia notifiche email per nuove richieste
                    </label>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="wecoop_crm_save_settings" class="button button-primary">Salva Impostazioni</button>
        </p>
    </form>
    
    <div style="background: #e8f4f8; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
        <h3 style="margin-top: 0;">ℹ️ Informazioni Plugin</h3>
        <p><strong>Versione:</strong> <?php echo WECOOP_SOCI_VERSION; ?></p>
        <p><strong>Endpoint API:</strong> <code><?php echo rest_url('wecoop/v1/'); ?></code></p>
        <p><strong>Servizi Gestiti:</strong></p>
        <ul>
            <li>Permesso di Soggiorno (Lavoro Subordinato, Lavoro Autonomo, Motivi Familiari, Studio)</li>
            <li>Cittadinanza Italiana</li>
            <li>Asilo Politico</li>
            <li>Visa per Turismo</li>
            <li>Mediazione Fiscale (730, Persona Fisica)</li>
            <li>Supporto Contabile (Partita IVA, Tasse, Consulenza)</li>
        </ul>
    </div>
</div>
