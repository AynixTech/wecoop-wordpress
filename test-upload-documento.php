<?php
/**
 * Test Upload Documento
 * 
 * Script per testare l'endpoint /soci/me/upload-documento
 * 
 * Usage:
 * 1. Carica questo file nella root di WordPress
 * 2. Accedi come utente (es. user_id 37)
 * 3. Apri: https://wecoop.org/test-upload-documento.php
 * 4. Carica un file di test
 */

require_once __DIR__ . '/wp-load.php';

// Deve essere loggato
if (!is_user_logged_in()) {
    die('❌ Devi essere loggato per testare l\'upload. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Login</a>');
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Gestione upload
$upload_result = null;
$upload_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $tipo_documento = sanitize_text_field($_POST['tipo_documento'] ?? 'carta_identita');
    
    // Upload file
    $attachment_id = media_handle_upload('file', 0);
    
    if (is_wp_error($attachment_id)) {
        $upload_error = $attachment_id->get_error_message();
    } else {
        // Salva metadata
        update_post_meta($attachment_id, 'documento_socio', 'yes');
        update_post_meta($attachment_id, 'socio_id', $user_id);
        update_post_meta($attachment_id, 'tipo_documento', $tipo_documento);
        update_post_meta($attachment_id, 'data_upload', current_time('mysql'));
        
        // Segna che l'utente ha caricato documenti
        $documenti_caricati = get_user_meta($user_id, 'documenti_caricati', true) ?: [];
        $documenti_caricati[] = [
            'id' => $attachment_id,
            'tipo' => $tipo_documento,
            'data' => current_time('Y-m-d H:i:s')
        ];
        update_user_meta($user_id, 'documenti_caricati', $documenti_caricati);
        
        $upload_result = [
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'tipo' => $tipo_documento,
            'filename' => basename(get_attached_file($attachment_id))
        ];
    }
}

// Recupera documenti esistenti
global $wpdb;
$documenti_utente = $wpdb->get_results($wpdb->prepare("
    SELECT p.ID, p.post_title, p.post_date, p.guid,
           pm1.meta_value AS tipo_documento,
           pm2.meta_value AS socio_id,
           pm3.meta_value AS richiesta_id
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'tipo_documento'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'socio_id'
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'richiesta_id'
    WHERE p.post_type = 'attachment'
      AND p.post_author = %d
      AND EXISTS (
          SELECT 1 FROM {$wpdb->postmeta}
          WHERE post_id = p.ID
            AND meta_key = 'documento_socio'
            AND meta_value = 'yes'
      )
    ORDER BY p.post_date DESC
", $user_id));

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload Documento</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .user-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .user-info strong {
            color: #667eea;
        }
        .upload-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        select, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            opacity: 0.9;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .documento-list {
            display: grid;
            gap: 15px;
        }
        .documento-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .documento-item .icon {
            font-size: 32px;
        }
        .documento-item .info {
            flex: 1;
        }
        .documento-item .info h4 {
            color: #667eea;
            margin-bottom: 5px;
        }
        .documento-item .info p {
            font-size: 13px;
            color: #666;
            margin: 2px 0;
        }
        .documento-item .actions {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn-small:hover {
            background: #5568d3;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 36px;
            font-weight: bold;
        }
        .stat-box .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 5px;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🧪 Test Upload Documento</h1>
            <div class="user-info">
                <strong>👤 Utente:</strong> <?php echo esc_html($current_user->display_name); ?> (ID: <?php echo $user_id; ?>)<br>
                <strong>📧 Email:</strong> <?php echo esc_html($current_user->user_email); ?>
            </div>

            <?php if ($upload_result): ?>
                <div class="success">
                    <strong>✅ Upload completato con successo!</strong><br><br>
                    <strong>ID Attachment:</strong> <?php echo $upload_result['id']; ?><br>
                    <strong>Tipo:</strong> <?php echo esc_html($upload_result['tipo']); ?><br>
                    <strong>File:</strong> <?php echo esc_html($upload_result['filename']); ?><br>
                    <strong>URL:</strong> <a href="<?php echo esc_url($upload_result['url']); ?>" target="_blank"><?php echo esc_html($upload_result['url']); ?></a>
                </div>
            <?php endif; ?>

            <?php if ($upload_error): ?>
                <div class="error">
                    <strong>❌ Errore durante l'upload:</strong><br>
                    <?php echo esc_html($upload_error); ?>
                </div>
            <?php endif; ?>

            <h2>📤 Carica Nuovo Documento</h2>
            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tipo Documento</label>
                    <select name="tipo_documento" required>
                        <option value="carta_identita">🪪 Carta d'Identità</option>
                        <option value="passaporto">🛂 Passaporto</option>
                        <option value="codice_fiscale">🧾 Codice Fiscale</option>
                        <option value="permesso_soggiorno">📋 Permesso di Soggiorno</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>File (PDF, JPG, PNG - Max 10MB)</label>
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <button type="submit">📤 Carica Documento</button>
            </form>
        </div>

        <div class="card">
            <h2>📚 I Miei Documenti</h2>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="number"><?php echo count($documenti_utente); ?></div>
                    <div class="label">Documenti Caricati</div>
                </div>
                <div class="stat-box">
                    <div class="number">
                        <?php 
                        $collegati = 0;
                        foreach ($documenti_utente as $doc) {
                            if (!empty($doc->richiesta_id)) $collegati++;
                        }
                        echo $collegati;
                        ?>
                    </div>
                    <div class="label">Collegati a Richieste</div>
                </div>
            </div>

            <?php if (empty($documenti_utente)): ?>
                <div class="empty-state">
                    <div class="icon">📄</div>
                    <p><strong>Nessun documento caricato</strong></p>
                    <p>Carica il tuo primo documento usando il form sopra</p>
                </div>
            <?php else: ?>
                <div class="documento-list">
                    <?php foreach ($documenti_utente as $doc): ?>
                        <div class="documento-item">
                            <div class="icon">
                                <?php 
                                $icons = [
                                    'carta_identita' => '🪪',
                                    'passaporto' => '🛂',
                                    'codice_fiscale' => '🧾',
                                    'permesso_soggiorno' => '📋'
                                ];
                                echo $icons[$doc->tipo_documento] ?? '📄';
                                ?>
                            </div>
                            <div class="info">
                                <h4>
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', $doc->tipo_documento))); ?>
                                    <?php if (!empty($doc->richiesta_id)): ?>
                                        <span class="badge badge-success">Collegato</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Non collegato</span>
                                    <?php endif; ?>
                                </h4>
                                <p><strong>ID:</strong> <?php echo $doc->ID; ?></p>
                                <p><strong>Caricato:</strong> <?php echo date('d/m/Y H:i', strtotime($doc->post_date)); ?></p>
                                <?php if (!empty($doc->richiesta_id)): ?>
                                    <p><strong>Richiesta ID:</strong> <?php echo $doc->richiesta_id; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="actions">
                                <a href="<?php echo esc_url($doc->guid); ?>" target="_blank" class="btn-small">👁️ Visualizza</a>
                                <a href="<?php echo admin_url('post.php?post=' . $doc->ID . '&action=edit'); ?>" target="_blank" class="btn-small">✏️ Modifica</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>🔍 Test Endpoint API</h2>
            <p style="margin-bottom: 15px;">Per testare tramite curl:</p>
            <pre style="background: #282c34; color: #abb2bf; padding: 15px; border-radius: 5px; overflow-x: auto;">curl -X POST https://wecoop.org/wp-json/wecoop/v1/soci/me/upload-documento \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "file=@test_document.pdf" \
  -F "tipo_documento=carta_identita"</pre>

            <p style="margin: 15px 0;">Per listare i documenti:</p>
            <pre style="background: #282c34; color: #abb2bf; padding: 15px; border-radius: 5px; overflow-x: auto;">curl -X GET https://wecoop.org/wp-json/wecoop/v1/soci/me/documenti \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"</pre>
        </div>

        <div class="card" style="background: #fff3cd; border: 2px solid #ffc107;">
            <h2>⚠️ Importante</h2>
            <p style="margin-bottom: 10px;"><strong>Sistema Auto-Recovery Attivo</strong></p>
            <p>Quando crei una nuova richiesta servizio, il backend recupera automaticamente tutti i documenti che hai caricato e li collega alla richiesta.</p>
            <br>
            <p><strong>Flusso corretto:</strong></p>
            <ol style="margin-left: 20px; line-height: 1.8;">
                <li>📤 Carica i documenti nella sezione "I miei documenti" (questo form o dall'app)</li>
                <li>✅ Verifica che i documenti appaiano nella lista qui sotto</li>
                <li>📝 Crea una richiesta servizio</li>
                <li>🔗 Il sistema collega automaticamente i documenti alla richiesta</li>
            </ol>
        </div>
    </div>
</body>
</html>
