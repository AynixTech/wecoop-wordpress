<?php
/**
 * Template Partial: User Info Card
 */

if (!defined('ABSPATH')) exit;
?>

<div class="user-info-card">
    <h2>📋 Informazioni Utente</h2>
    
    <table class="form-table">
        <tr>
            <th>ID Utente:</th>
            <td><code>#<?php echo $user_id; ?></code></td>
        </tr>
        <tr>
            <th>Username:</th>
            <td><strong><?php echo esc_html($user->user_login); ?></strong></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td>
                <?php if ($user->user_email): ?>
                    <a href="mailto:<?php echo esc_attr($user->user_email); ?>">
                        <?php echo esc_html($user->user_email); ?>
                    </a>
                <?php else: ?>
                    <span style="color: #999;">Non specificata</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Telefono:</th>
            <td>
                <?php if ($telefono_completo): ?>
                    <a href="tel:<?php echo esc_attr($telefono_completo); ?>">
                        <?php echo esc_html($telefono_completo); ?>
                    </a>
                <?php else: ?>
                    <span style="color: #999;">Non specificato</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Ruolo:</th>
            <td>
                <?php 
                $roles = $user->roles;
                echo !empty($roles) ? esc_html(ucfirst($roles[0])) : 'Nessun ruolo';
                ?>
            </td>
        </tr>
        <tr>
            <th>Stato Socio:</th>
            <td>
                <?php if ($is_socio): ?>
                    <span class="badge badge-success">✅ SOCIO ATTIVO</span>
                <?php else: ?>
                    <span class="badge badge-warning">⏳ NON SOCIO</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Profilo Completo:</th>
            <td>
                <?php if ($profilo_completo): ?>
                    <span class="badge badge-success">✅ COMPLETO</span>
                <?php else: ?>
                    <span class="badge badge-error">❌ INCOMPLETO</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($numero_pratica): ?>
        <tr>
            <th>Numero Pratica:</th>
            <td><code><?php echo esc_html($numero_pratica); ?></code></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th>Registrato il:</th>
            <td><?php echo date_i18n('d/m/Y H:i', strtotime($user->user_registered)); ?></td>
        </tr>
    </table>
</div>

<style>
.user-info-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.user-info-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-error {
    background: #f8d7da;
    color: #721c24;
}
</style>
