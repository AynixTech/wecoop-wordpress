<?php
/**
 * Template Partial: User Documents
 */

if (!defined('ABSPATH')) exit;
?>

<div class="user-documents-card">
    <h2>📎 Documenti Utente</h2>
    
    <!-- Upload Form -->
    <div class="upload-section">
        <h3>Carica Nuovo Documento</h3>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('wecoop_users_upload_documento', 'wecoop_users_upload_documento'); ?>
            <input type="hidden" name="action" value="wecoop_users_upload_documento">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="tipo_documento">Tipo Documento *</label></th>
                    <td>
                        <select id="tipo_documento" name="tipo_documento" required style="min-width: 250px;">
                            <option value="">-- Seleziona tipo --</option>
                            <option value="carta_identita">Carta d'Identità</option>
                            <option value="codice_fiscale">Codice Fiscale</option>
                            <option value="patente">Patente di Guida</option>
                            <option value="passaporto">Passaporto</option>
                            <option value="permesso_soggiorno">Permesso di Soggiorno</option>
                            <option value="autocertificazione">Autocertificazione</option>
                            <option value="altro">Altro Documento</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="documento_file">File *</label></th>
                    <td>
                        <input type="file" id="documento_file" name="documento_file" 
                               accept="image/jpeg,image/jpg,image/png,application/pdf" required>
                        <p class="description">
                            Formati consentiti: JPG, PNG, PDF • Dimensione massima: 5MB
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">
                    📤 Carica Documento
                </button>
            </p>
        </form>
    </div>
    
    <hr style="margin: 30px 0;">
    
    <!-- Documents List -->
    <div class="documents-list">
        <h3>📁 Documenti Caricati</h3>
        
        <?php if (empty($documenti)): ?>
            <div class="empty-documents">
                <span class="dashicons dashicons-media-default" style="font-size: 48px; color: #ccc;"></span>
                <p style="color: #999;">Nessun documento caricato.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="30%">Tipo Documento</th>
                        <th width="30%">Nome File</th>
                        <th width="20%">Data Caricamento</th>
                        <th width="20%">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documenti as $index => $doc): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php 
                                    $tipo_labels = [
                                        'carta_identita' => '🪪 Carta d\'Identità',
                                        'codice_fiscale' => '🔢 Codice Fiscale',
                                        'patente' => '🚗 Patente',
                                        'passaporto' => '🛂 Passaporto',
                                        'permesso_soggiorno' => '📋 Permesso Soggiorno',
                                        'autocertificazione' => '📝 Autocertificazione',
                                        'altro' => '📄 Altro'
                                    ];
                                    echo $tipo_labels[$doc['tipo']] ?? $doc['tipo'];
                                    ?>
                                </strong>
                            </td>
                            <td>
                                <code><?php echo esc_html($doc['filename']); ?></code>
                            </td>
                            <td>
                                <?php echo date_i18n('d/m/Y H:i', strtotime($doc['data_upload'])); ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($doc['url']); ?>" 
                                   target="_blank" 
                                   class="button button-small">
                                    👁️ Visualizza
                                </a>
                                
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('⚠️ Sei sicuro di voler eliminare questo documento?');">
                                    <?php wp_nonce_field('wecoop_users_elimina_documento', 'wecoop_users_elimina_documento'); ?>
                                    <input type="hidden" name="action" value="wecoop_users_elimina_documento">
                                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                    <input type="hidden" name="doc_index" value="<?php echo $index; ?>">
                                    <button type="submit" class="button button-small button-link-delete">
                                        🗑️ Elimina
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.user-documents-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.user-documents-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.user-documents-card h3 {
    margin: 20px 0 15px;
    color: #555;
    font-size: 16px;
}

.upload-section {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
    margin: 20px 0;
}

.empty-documents {
    text-align: center;
    padding: 40px 20px;
    background: #f9f9f9;
    border-radius: 4px;
}

.documents-list .wp-list-table {
    margin-top: 15px;
}

.documents-list .button-small {
    padding: 3px 8px !important;
    font-size: 12px !important;
    height: auto !important;
}

.button-link-delete {
    color: #d63638 !important;
}

.button-link-delete:hover {
    background: #d63638 !important;
    color: white !important;
    border-color: #d63638 !important;
}
</style>
