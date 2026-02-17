<?php
/**
 * Debug Documenti Richiesta Servizio
 * URL: https://wecoop.org/debug-documenti-richiesta.php?richiesta_id=444
 */

// Carica WordPress
require_once __DIR__ . '/wp-load.php';

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Devi essere amministratore.');
}

// Ottieni richiesta_id da parametro GET
$richiesta_id = isset($_GET['richiesta_id']) ? absint($_GET['richiesta_id']) : 0;

if (!$richiesta_id) {
    die('❌ Parametro richiesta_id mancante. Usa: ?richiesta_id=444');
}

$richiesta = get_post($richiesta_id);

if (!$richiesta || $richiesta->post_type !== 'richiesta_servizio') {
    die("❌ Richiesta #{$richiesta_id} non trovata o non è una richiesta_servizio");
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Documenti - Richiesta #<?php echo $richiesta_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #667eea;
            border-radius: 5px;
        }
        .info-item label {
            display: block;
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .info-item .value {
            color: #333;
            font-size: 14px;
        }
        pre {
            background: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
        }
        .success { color: #22c55e; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        .documento-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        .documento-card h4 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .documento-card .meta {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 5px;
            font-size: 13px;
        }
        .documento-card .meta strong {
            color: #666;
        }
        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            min-width: 150px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-box .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .query-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .query-box h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        a.button {
            display: inline-block;
            padding: 8px 15px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            margin-top: 10px;
        }
        a.button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Documenti - Richiesta #<?php echo $richiesta_id; ?></h1>

        <!-- Info Richiesta -->
        <div class="section">
            <h2>📋 Informazioni Richiesta</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>ID Post</label>
                    <div class="value"><?php echo $richiesta_id; ?></div>
                </div>
                <div class="info-item">
                    <label>Titolo</label>
                    <div class="value"><?php echo esc_html($richiesta->post_title); ?></div>
                </div>
                <div class="info-item">
                    <label>Numero Pratica</label>
                    <div class="value"><?php echo get_post_meta($richiesta_id, 'numero_pratica', true); ?></div>
                </div>
                <div class="info-item">
                    <label>User ID</label>
                    <div class="value">
                        <?php 
                        $user_id = get_post_meta($richiesta_id, 'user_id', true);
                        echo $user_id ?: '<span class="error">Non impostato</span>';
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <label>Socio ID</label>
                    <div class="value">
                        <?php 
                        $socio_id = get_post_meta($richiesta_id, 'socio_id', true);
                        echo $socio_id ?: '<span class="warning">Non impostato</span>';
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <label>Servizio</label>
                    <div class="value"><?php echo get_post_meta($richiesta_id, 'servizio', true); ?></div>
                </div>
            </div>
        </div>

        <?php
        // 1. Verifica meta documenti_allegati
        $documenti_allegati = get_post_meta($richiesta_id, 'documenti_allegati', true);
        ?>

        <!-- Meta documenti_allegati -->
        <div class="section">
            <h2>🗂️ Meta: documenti_allegati</h2>
            <?php if (empty($documenti_allegati)): ?>
                <p class="error">❌ Meta 'documenti_allegati' VUOTO o NON ESISTE</p>
            <?php else: ?>
                <p class="success">✅ Meta trovato con <?php echo count($documenti_allegati); ?> documento/i</p>
                <pre><?php print_r($documenti_allegati); ?></pre>
            <?php endif; ?>
        </div>

        <?php
        // 2. Cerca attachment con richiesta_id
        global $wpdb;
        $attachments_by_meta = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, p.post_author,
                   pm1.meta_value AS tipo_documento,
                   pm2.meta_value AS richiesta_id,
                   pm3.meta_value AS documento_socio
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'tipo_documento'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'richiesta_id'
            LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'documento_socio'
            WHERE p.post_type = 'attachment'
              AND pm2.meta_value = %d
        ", $richiesta_id));
        ?>

        <!-- Attachment con richiesta_id -->
        <div class="section">
            <h2>📎 Attachment con meta richiesta_id = <?php echo $richiesta_id; ?></h2>
            <?php if (empty($attachments_by_meta)): ?>
                <p class="error">❌ Nessun attachment trovato con richiesta_id = <?php echo $richiesta_id; ?></p>
                <div class="query-box">
                    <h4>Query eseguita:</h4>
                    <pre>SELECT * FROM wp_postmeta
WHERE meta_key = 'richiesta_id'
  AND meta_value = '<?php echo $richiesta_id; ?>'</pre>
                </div>
            <?php else: ?>
                <p class="success">✅ Trovati <?php echo count($attachments_by_meta); ?> attachment</p>
                <?php foreach ($attachments_by_meta as $att): ?>
                    <div class="documento-card">
                        <h4>📄 Attachment #<?php echo $att->ID; ?></h4>
                        <div class="meta">
                            <strong>Titolo:</strong> <span><?php echo esc_html($att->post_title); ?></span>
                            <strong>Author:</strong> <span><?php echo $att->post_author; ?></span>
                            <strong>Tipo:</strong> <span><?php echo $att->tipo_documento ?: '<em>Non impostato</em>'; ?></span>
                            <strong>documento_socio:</strong> <span><?php echo $att->documento_socio ?: '<em>Non impostato</em>'; ?></span>
                            <strong>URL:</strong> <span><?php echo wp_get_attachment_url($att->ID); ?></span>
                        </div>
                        <a href="<?php echo wp_get_attachment_url($att->ID); ?>" target="_blank" class="button">👁️ Visualizza File</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php
        // 3. Documenti utente (se user_id esiste)
        if ($user_id):
            $user_docs = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, p.post_author, p.post_date,
                       pm1.meta_value AS tipo_documento,
                       pm2.meta_value AS socio_id,
                       pm3.meta_value AS documento_socio,
                       pm4.meta_value AS richiesta_id
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'tipo_documento'
                LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'socio_id'
                LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'documento_socio'
                LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'richiesta_id'
                WHERE p.post_type = 'attachment'
                  AND p.post_author = %d
                  AND pm3.meta_value = 'yes'
            ", $user_id));
        ?>

        <!-- Documenti Utente -->
        <div class="section">
            <h2>👤 Documenti Utente (user_id = <?php echo $user_id; ?>)</h2>
            <?php if (empty($user_docs)): ?>
                <p class="error">❌ Nessun documento trovato per questo utente</p>
                <div class="query-box">
                    <h4>L'utente NON ha caricato documenti nel profilo</h4>
                    <p>Query: <code>post_author = <?php echo $user_id; ?> AND documento_socio = 'yes'</code></p>
                </div>
            <?php else: ?>
                <p class="success">✅ Trovati <?php echo count($user_docs); ?> documenti nel profilo utente</p>
                <?php foreach ($user_docs as $doc): ?>
                    <div class="documento-card">
                        <h4>📄 Attachment #<?php echo $doc->ID; ?></h4>
                        <div class="meta">
                            <strong>Titolo:</strong> <span><?php echo esc_html($doc->post_title); ?></span>
                            <strong>Caricato il:</strong> <span><?php echo date('d/m/Y H:i', strtotime($doc->post_date)); ?></span>
                            <strong>Tipo:</strong> <span><?php echo $doc->tipo_documento ?: '<em>Non impostato</em>'; ?></span>
                            <strong>socio_id:</strong> <span><?php echo $doc->socio_id ?: '<em>Non impostato</em>'; ?></span>
                            <strong>richiesta_id:</strong> <span><?php echo $doc->richiesta_id ?: '<span class="warning">NON COLLEGATO</span>'; ?></span>
                            <strong>URL:</strong> <span><?php echo wp_get_attachment_url($doc->ID); ?></span>
                        </div>
                        <a href="<?php echo wp_get_attachment_url($doc->ID); ?>" target="_blank" class="button">👁️ Visualizza File</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php endif; ?>

        <!-- Statistiche Riepilogative -->
        <div class="section">
            <h2>📊 Statistiche</h2>
            <div class="stats">
                <div class="stat-box">
                    <div class="number"><?php echo count($documenti_allegati ?: []); ?></div>
                    <div class="label">Meta documenti_allegati</div>
                </div>
                <div class="stat-box">
                    <div class="number"><?php echo count($attachments_by_meta); ?></div>
                    <div class="label">Attachment con richiesta_id</div>
                </div>
                <?php if ($user_id): ?>
                <div class="stat-box">
                    <div class="number"><?php echo count($user_docs); ?></div>
                    <div class="label">Documenti profilo utente</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Diagnosi -->
        <div class="section">
            <h2>🩺 Diagnosi</h2>
            <?php
            $problemi = [];
            $soluzioni = [];

            // Check 1: Meta documenti_allegati vuoto
            if (empty($documenti_allegati)) {
                $problemi[] = "❌ Meta 'documenti_allegati' è vuoto o non esiste";
                $soluzioni[] = "Il backend deve salvare questo meta quando la richiesta viene creata";
            }

            // Check 2: Nessun attachment con richiesta_id
            if (empty($attachments_by_meta)) {
                $problemi[] = "❌ Nessun attachment ha meta 'richiesta_id' = {$richiesta_id}";
                $soluzioni[] = "Gli attachment devono essere collegati alla richiesta con update_post_meta(attachment_id, 'richiesta_id', post_id)";
            }

            // Check 3: Utente senza documenti
            if ($user_id && empty($user_docs)) {
                $problemi[] = "⚠️ L'utente #{$user_id} non ha documenti caricati nel profilo";
                $soluzioni[] = "L'utente deve caricare documenti tramite l'endpoint /soci/me/upload-documento";
            }

            // Check 4: Documenti utente non collegati
            if ($user_id && !empty($user_docs)) {
                $non_collegati = 0;
                foreach ($user_docs as $doc) {
                    if (empty($doc->richiesta_id)) {
                        $non_collegati++;
                    }
                }
                if ($non_collegati > 0) {
                    $problemi[] = "⚠️ {$non_collegati} documenti dell'utente NON sono collegati a questa richiesta";
                    $soluzioni[] = "Il sistema di auto-recovery dovrebbe collegarli automaticamente quando la richiesta viene creata";
                }
            }

            if (empty($problemi)):
            ?>
                <p class="success">✅ Tutto OK! I documenti sono correttamente configurati.</p>
            <?php else: ?>
                <h3 style="color: #ef4444; margin-bottom: 10px;">Problemi Rilevati:</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($problemi as $p): ?>
                        <li style="padding: 8px; margin-bottom: 5px; background: #fee; border-left: 3px solid #ef4444;">
                            <?php echo $p; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h3 style="color: #f59e0b; margin-top: 20px; margin-bottom: 10px;">Soluzioni:</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($soluzioni as $s): ?>
                        <li style="padding: 8px; margin-bottom: 5px; background: #fffbeb; border-left: 3px solid #f59e0b;">
                            💡 <?php echo $s; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Script SQL per Fix Manuale -->
        <?php if ($user_id && !empty($user_docs) && empty($attachments_by_meta)): ?>
        <div class="section">
            <h2>🔧 Fix Manuale: Collega Documenti Esistenti</h2>
            <p>Se vuoi collegare manualmente i documenti dell'utente a questa richiesta, esegui questo SQL:</p>
            <pre><?php
$doc_ids = array_map(function($doc) { return $doc->ID; }, $user_docs);
echo "-- Collega tutti i documenti dell'utente alla richiesta\n";
foreach ($doc_ids as $doc_id) {
    echo "UPDATE {$wpdb->postmeta}\n";
    echo "SET meta_value = '{$richiesta_id}'\n";
    echo "WHERE post_id = {$doc_id}\n";
    echo "  AND meta_key = 'richiesta_id';\n\n";
    echo "-- Se il meta non esiste, crealo:\n";
    echo "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)\n";
    echo "VALUES ({$doc_id}, 'richiesta_id', '{$richiesta_id}')\n";
    echo "ON DUPLICATE KEY UPDATE meta_value = '{$richiesta_id}';\n\n";
}
            ?></pre>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
