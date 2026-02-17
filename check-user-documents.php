<?php
/**
 * Quick Check: Documenti User #37
 * URL: https://wecoop.org/check-user-documents.php
 */

require_once __DIR__ . '/wp-load.php';

// Verifica permessi - TEMPORANEAMENTE DISABILITATO PER DEBUG
// if (!current_user_can('manage_options')) {
//     die('❌ Accesso negato');
// }

global $wpdb;
$user_id = 37;

echo "<h1>🔍 Verifica Documenti User #37</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} pre{background:#fff;padding:15px;border-radius:5px;overflow:auto;}</style>";

// 1. Query completa documenti
echo "<h2>1. Tutti gli Attachments di User #37</h2>";
$all_attachments = $wpdb->get_results($wpdb->prepare("
    SELECT p.ID, p.post_title, p.post_date, p.guid
    FROM {$wpdb->posts} p
    WHERE p.post_type = 'attachment'
      AND p.post_author = %d
    ORDER BY p.post_date DESC
", $user_id));

echo "<pre>";
if (empty($all_attachments)) {
    echo "❌ Nessun attachment trovato per user #37\n";
} else {
    echo "✅ Trovati " . count($all_attachments) . " attachments:\n\n";
    foreach ($all_attachments as $att) {
        echo "Attachment ID: {$att->ID}\n";
        echo "Titolo: {$att->post_title}\n";
        echo "Data: {$att->post_date}\n";
        echo "URL: {$att->guid}\n";
        
        // Controlla meta
        $documento_socio = get_post_meta($att->ID, 'documento_socio', true);
        $tipo_documento = get_post_meta($att->ID, 'tipo_documento', true);
        $socio_id = get_post_meta($att->ID, 'socio_id', true);
        
        echo "Meta:\n";
        echo "  - documento_socio: " . ($documento_socio ?: 'NON IMPOSTATO') . "\n";
        echo "  - tipo_documento: " . ($tipo_documento ?: 'NON IMPOSTATO') . "\n";
        echo "  - socio_id: " . ($socio_id ?: 'NON IMPOSTATO') . "\n";
        echo "\n" . str_repeat('-', 60) . "\n\n";
    }
}
echo "</pre>";

// 2. Query documenti con meta documento_socio='yes'
echo "<h2>2. Attachments con documento_socio='yes'</h2>";
$documents_with_meta = $wpdb->get_results($wpdb->prepare("
    SELECT p.ID, p.post_title, p.post_date,
           pm1.meta_value AS documento_socio,
           pm2.meta_value AS tipo_documento,
           pm3.meta_value AS socio_id
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'documento_socio'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'tipo_documento'
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'socio_id'
    WHERE p.post_type = 'attachment'
      AND p.post_author = %d
      AND pm1.meta_value = 'yes'
", $user_id));

echo "<pre>";
if (empty($documents_with_meta)) {
    echo "❌ Nessun documento con meta 'documento_socio=yes'\n";
    echo "\n⚠️ PROBLEMA: Il documento è stato caricato ma NON ha il meta corretto!\n";
    echo "\nPossibili cause:\n";
    echo "1. Upload fatto tramite Media Library (non via API)\n";
    echo "2. Meta non salvato correttamente\n";
    echo "3. Upload via API fallito silenziosamente\n";
} else {
    echo "✅ Trovati " . count($documents_with_meta) . " documenti corretti:\n\n";
    foreach ($documents_with_meta as $doc) {
        echo "ID: {$doc->ID}\n";
        echo "Titolo: {$doc->post_title}\n";
        echo "Data: {$doc->post_date}\n";
        echo "Tipo: {$doc->tipo_documento}\n";
        echo "\n";
    }
}
echo "</pre>";

// 3. User meta documenti_caricati
echo "<h2>3. User Meta 'documenti_caricati'</h2>";
$documenti_caricati = get_user_meta($user_id, 'documenti_caricati', true);
echo "<pre>";
if (empty($documenti_caricati)) {
    echo "❌ User meta 'documenti_caricati' vuoto o non esiste\n";
} else {
    echo "✅ User meta trovato:\n\n";
    print_r($documenti_caricati);
}
echo "</pre>";

// 4. Query richiesta #446
echo "<h2>4. Richiesta WECOOP-2026-00003 (#446)</h2>";
$richiesta = get_post(446);
$documenti_allegati = get_post_meta(446, 'documenti_allegati', true);
echo "<pre>";
echo "Post Status: {$richiesta->post_status}\n";
echo "Post Author: {$richiesta->post_author}\n";
echo "\nDocumenti allegati meta:\n";
if (empty($documenti_allegati)) {
    echo "❌ Array vuoto\n";
} else {
    print_r($documenti_allegati);
}
echo "</pre>";

// 5. Suggerimenti
echo "<h2>✅ Prossimi Passi</h2>";
echo "<pre>";
if (empty($all_attachments)) {
    echo "1. User #37 NON ha mai caricato nessun file\n";
    echo "2. Provare a caricare via: https://wecoop.org/test-upload-documento.php\n";
} else if (empty($documents_with_meta)) {
    echo "1. Gli attachment esistono MA mancano i meta corretti\n";
    echo "2. Possibile upload via Media Library invece che via API\n";
    echo "3. Soluzione: Caricare nuovamente via /soci/me/upload-documento\n";
    echo "\n";
    echo "SQL Fix (se vuoi salvare i file esistenti):\n";
    foreach ($all_attachments as $att) {
        echo "UPDATE {$wpdb->postmeta} SET meta_value = 'yes' WHERE post_id = {$att->ID} AND meta_key = 'documento_socio';\n";
        echo "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) \n";
        echo "VALUES ({$att->ID}, 'documento_socio', 'yes') ON DUPLICATE KEY UPDATE meta_value = 'yes';\n";
        echo "UPDATE {$wpdb->postmeta} SET meta_value = '37' WHERE post_id = {$att->ID} AND meta_key = 'socio_id';\n";
        echo "UPDATE {$wpdb->postmeta} SET meta_value = 'altro' WHERE post_id = {$att->ID} AND meta_key = 'tipo_documento';\n";
        echo "\n";
    }
} else {
    echo "✅ Tutto corretto! I documenti dovrebbero essere visibili.\n";
    echo "\nSe ancora non appaiono, verifica:\n";
    echo "1. https://wecoop.org/debug-documenti-richiesta.php?richiesta_id=446\n";
    echo "2. Creare una NUOVA richiesta (l'auto-recovery funzionerà)\n";
}
echo "</pre>";

echo "<hr><p><a href='/wp-admin/'>← Torna a WP Admin</a> | ";
echo "<a href='/test-upload-documento.php'>Test Upload</a> | ";
echo "<a href='/debug-documenti-richiesta.php?richiesta_id=446'>Debug Richiesta #446</a></p>";
