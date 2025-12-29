<?php
/**
 * Template Partial: User Profile Form
 */

if (!defined('ABSPATH')) exit;

$richiesta_socio = get_posts([
    'post_type' => 'richiesta_socio',
    'author' => $user_id,
    'posts_per_page' => 1,
    'post_status' => 'any'
]);

if (empty($richiesta_socio)) {
    echo '<div class="notice notice-error"><p>⚠️ Nessuna richiesta socio trovata per questo utente.</p></div>';
    return;
}

$richiesta = $richiesta_socio[0];
$richiesta_id = $richiesta->ID;

// Dati dalla richiesta_socio (post_meta)
$nome = get_post_meta($richiesta_id, 'nome', true);
$cognome = get_post_meta($richiesta_id, 'cognome', true);
$cf = get_post_meta($richiesta_id, 'cf', true);
$data_nascita = get_post_meta($richiesta_id, 'data_nascita', true);
$luogo_nascita = get_post_meta($richiesta_id, 'luogo_nascita', true);
$indirizzo = get_post_meta($richiesta_id, 'indirizzo', true);
$civico = get_post_meta($richiesta_id, 'civico', true);
$cap = get_post_meta($richiesta_id, 'cap', true);
$citta = get_post_meta($richiesta_id, 'citta', true);
$provincia = get_post_meta($richiesta_id, 'provincia', true);
$nazione = get_post_meta($richiesta_id, 'nazione', true);
?>

<div class="profile-form-card">
    <h2>✏️ Modifica Profilo Utente</h2>
    <p class="description">Puoi salvare anche solo alcuni campi. I campi obbligatori per l'approvazione come socio sono contrassegnati con *</p>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('wecoop_users_completa_profilo', 'wecoop_users_completa_profilo'); ?>
        <input type="hidden" name="action" value="wecoop_users_completa_profilo">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        
        <table class="form-table">
            <tr>
                <th><label for="first_name">Nome *</label></th>
                <td>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo esc_attr($nome); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="last_name">Cognome *</label></th>
                <td>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo esc_attr($cognome); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="email">Email</label></th>
                <td>
                    <input type="email" id="email" name="email" 
                           value="<?php echo esc_attr($user->user_email); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="codice_fiscale">Codice Fiscale *</label></th>
                <td>
                    <input type="text" id="codice_fiscale" name="codice_fiscale" 
                           value="<?php echo esc_attr($cf); ?>" 
                           class="regular-text" maxlength="16" 
                           pattern="[A-Z0-9]{16}" 
                           style="text-transform: uppercase;">
                    <p class="description">16 caratteri alfanumerici (es: RSSMRA80A01H501U)</p>
                </td>
            </tr>
            
            <tr>
                <th><label for="data_nascita">Data di Nascita *</label></th>
                <td>
                    <input type="date" id="data_nascita" name="data_nascita" 
                           value="<?php echo esc_attr($data_nascita); ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="luogo_nascita">Luogo di Nascita *</label></th>
                <td>
                    <input type="text" id="luogo_nascita" name="luogo_nascita" 
                           value="<?php echo esc_attr($luogo_nascita); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th colspan="2"><h3>📍 Indirizzo di Residenza</h3></th>
            </tr>
            
            <tr>
                <th><label for="indirizzo">Via/Piazza *</label></th>
                <td>
                    <input type="text" id="indirizzo" name="indirizzo" 
                           value="<?php echo esc_attr($indirizzo); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="civico">Numero Civico *</label></th>
                <td>
                    <input type="text" id="civico" name="civico" 
                           value="<?php echo esc_attr($civico); ?>" 
                           class="small-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="cap">CAP *</label></th>
                <td>
                    <input type="text" id="cap" name="cap" 
                           value="<?php echo esc_attr($cap); ?>" 
                           class="small-text" maxlength="5" 
                           pattern="[0-9]{5}">
                </td>
            </tr>
            
            <tr>
                <th><label for="citta">Città *</label></th>
                <td>
                    <input type="text" id="citta" name="citta" 
                           value="<?php echo esc_attr($citta); ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="provincia">Provincia *</label></th>
                <td>
                    <input type="text" id="provincia" name="provincia" 
                           value="<?php echo esc_attr($provincia); ?>" 
                           class="small-text" maxlength="2" 
                           style="text-transform: uppercase;">
                    <p class="description">Sigla provincia (es: RM, MI, NA)</p>
                </td>
            </tr>
            
            <tr>
                <th><label for="nazione">Nazione *</label></th>
                <td>
                    <input type="text" id="nazione" name="nazione" 
                           value="<?php echo esc_attr($nazione ?: 'Italia'); ?>" 
                           class="regular-text">
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary button-large">
                💾 Salva Profilo
            </button>
            <span class="description" style="margin-left: 15px;">
                Puoi salvare anche solo alcuni campi. L'approvazione come socio richiede tutti i campi obbligatori (*).
            </span>
        </p>
    </form>
</div>

<style>
.profile-form-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.profile-form-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.profile-form-card h3 {
    margin: 10px 0;
    color: #555;
    font-size: 16px;
}

.profile-form-card input[type="text"],
.profile-form-card input[type="email"],
.profile-form-card input[type="date"] {
    padding: 8px;
}

.profile-form-card .button-large {
    height: 40px;
    padding: 0 24px;
    font-size: 14px;
}

.profile-form-card .description {
    color: #666;
    font-style: italic;
}
</style>
