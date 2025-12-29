<?php
/**
 * Template: Dettaglio Utente
 * 
 * Variables available:
 * @var WP_User $user
 * @var int $user_id
 * @var bool $is_socio
 * @var bool $profilo_completo
 * @var string $telefono_completo
 * @var string $whatsapp_url
 * @var string $numero_pratica
 * @var string $message
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-users" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php echo esc_html($user->display_name); ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=wecoop-users-list'); ?>" class="page-title-action">
        ← Torna alla lista
    </a>
    
    <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" 
       class="page-title-action" style="background: #25D366; border-color: #25D366; color: white;">
        <span class="dashicons dashicons-whatsapp"></span> Apri WhatsApp
    </a>
    
    <?php if ($message === 'profilo_salvato'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✅ Profilo salvato con successo!</strong></p>
        </div>
    <?php elseif ($message === 'socio_approvato'): ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>🎉 Utente approvato come SOCIO con successo!</strong></p>
        </div>
    <?php elseif ($message === 'socio_revocato'): ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>⚠️ Socio revocato. Utente tornato a subscriber.</strong></p>
        </div>
    <?php elseif ($message === 'errore'): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>❌ Errore:</strong> <?php echo esc_html(urldecode($_GET['error_msg'] ?? '')); ?></p>
        </div>
    <?php elseif ($message === 'errore_richiesta'): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>❌ Richiesta socio non trovata per questo utente.</strong></p>
        </div>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <?php require WECOOP_USERS_PLUGIN_DIR . 'templates/partials/user-info.php'; ?>
    <?php require WECOOP_USERS_PLUGIN_DIR . 'templates/partials/user-form.php'; ?>
    <?php require WECOOP_USERS_PLUGIN_DIR . 'templates/partials/user-actions.php'; ?>
</div>
