<?php
/**
 * Template Partial: User Actions (Approve/Revoke)
 */

if (!defined('ABSPATH')) exit;
?>

<div class="user-actions-card">
    <h2>⚙️ Azioni Amministrative</h2>
    
    <?php if (!$profilo_completo): ?>
        <div class="notice notice-warning inline">
            <p>⚠️ <strong>Completa il profilo prima di approvare l'utente come socio.</strong></p>
        </div>
    <?php endif; ?>
    
    <div class="actions-buttons">
        <?php if (!$is_socio && $profilo_completo): ?>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline-block;" 
                  onsubmit="return confirm('✅ Sei sicuro di voler APPROVARE questo utente come SOCIO?\n\nL\'utente verrà promosso al ruolo \'socio\'.');">
                <?php wp_nonce_field('wecoop_users_approva_socio'); ?>
                <input type="hidden" name="action" value="wecoop_users_approva_socio">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <button type="submit" class="button button-primary button-hero">
                    ✅ Approva come SOCIO
                </button>
            </form>
        <?php elseif ($is_socio): ?>
            <div class="socio-approved-notice">
                <span class="dashicons dashicons-yes-alt"></span>
                <strong>Utente già approvato come SOCIO</strong>
            </div>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline-block; margin-left: 20px;" 
                  onsubmit="return confirm('⚠️ ATTENZIONE!\n\nSei sicuro di voler REVOCARE lo stato di SOCIO?\n\nL\'utente tornerà al ruolo di \'subscriber\'.');">
                <?php wp_nonce_field('wecoop_users_revoca_socio'); ?>
                <input type="hidden" name="action" value="wecoop_users_revoca_socio">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <button type="submit" class="button button-secondary">
                    🚫 Revoca stato SOCIO
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <hr style="margin: 30px 0;">
    
    <div class="whatsapp-section">
        <h3>💬 Contatto WhatsApp</h3>
        <p>Clicca il pulsante per aprire WhatsApp e inviare un messaggio all'utente:</p>
        
        <a href="<?php echo esc_url($whatsapp_url); ?>" 
           target="_blank" 
           class="button button-whatsapp button-hero">
            <span class="dashicons dashicons-whatsapp"></span>
            Apri Chat WhatsApp
        </a>
        
        <?php if (!$telefono_completo): ?>
            <p style="color: #999; margin-top: 10px;">
                <em>⚠️ Numero di telefono non disponibile</em>
            </p>
        <?php endif; ?>
    </div>
</div>

<style>
.user-actions-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.user-actions-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.user-actions-card h3 {
    margin: 20px 0 10px;
    color: #555;
    font-size: 16px;
}

.notice.inline {
    display: inline-block;
    margin: 0 0 20px 0;
    padding: 10px 15px;
}

.actions-buttons {
    margin: 20px 0;
}

.socio-approved-notice {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    font-size: 14px;
}

.socio-approved-notice .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.button-hero {
    height: 50px !important;
    padding: 0 30px !important;
    font-size: 16px !important;
    line-height: 50px !important;
}

.button-whatsapp {
    background: #25D366 !important;
    border-color: #25D366 !important;
    color: white !important;
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
}

.button-whatsapp:hover {
    background: #1da851 !important;
    border-color: #1da851 !important;
    color: white !important;
}

.button-whatsapp .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.whatsapp-section {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}
</style>
