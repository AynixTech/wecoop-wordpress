<?php
/**
 * Script diagnostico - Stato della richiesta 491 e documento firmato
 */

// Carica WordPress
require_once __DIR__ . '/wp-load.php';

$richiesta_id = 491;
$post = get_post($richiesta_id);

if (!$post || $post->post_type !== 'richiesta_servizio') {
    die("❌ Richiesta 491 non trovata o tipo errato\n");
}

echo "=== DIAGNOSTICA RICHIESTA 491 ===\n\n";

// 1. Metadati documento
echo "📋 METADATI DOCUMENTO:\n";
$doc_url = get_post_meta($richiesta_id, 'documento_unico_url', true);
$doc_url_orig = get_post_meta($richiesta_id, 'documento_unico_url_originale', true);
$doc_merged_url = get_post_meta($richiesta_id, 'documento_unico_merged_url', true);

echo "  documento_unico_url: " . ($doc_url ? "✓ " . substr($doc_url, -50) : "❌ VUOTO") . "\n";
echo "  documento_unico_url_originale: " . ($doc_url_orig ? "✓ " . substr($doc_url_orig, -50) : "❌ VUOTO") . "\n";
echo "  documento_unico_merged_url: " . ($doc_merged_url ? "✓ " . substr($doc_merged_url, -50) : "❌ VUOTO") . "\n";

// 2. Metadati firma
echo "\n🔐 METADATI FIRMA:\n";
$firmware = get_post_meta($richiesta_id, 'documento_unico_firmato', true);
$attestato_url = get_post_meta($richiesta_id, 'documento_unico_attestato_firma_url', true);
$merged_firma = get_post_meta($richiesta_id, 'documento_unico_merged_firma', true);

echo "  documento_unico_firmato: " . ($firmware === 'yes' ? "✓ YES" : "❌ NO") . "\n";
echo "  documento_unico_attestato_firma_url: " . ($attestato_url ? "✓ " . substr($attestato_url, -50) : "❌ VUOTO") . "\n";
echo "  documento_unico_merged_firma: " . ($merged_firma === 'yes' ? "✓ YES" : "❌ NO") . "\n";

// 3. Status richiesta
echo "\n📊 STATUS RICHIESTA:\n";
$stato = get_post_meta($richiesta_id, 'stato', true);
echo "  stato: " . ($stato ?: "❌ VUOTO") . "\n";

// 4. Verifica file su disco
echo "\n💾 VERIFICA FILE SU DISCO:\n";
$upload_dir = wp_upload_dir();
$base_dir = rtrim($upload_dir['basedir'], '/');
$base_url = rtrim($upload_dir['baseurl'], '/');

function check_file_exists($url, $base_url, $base_dir) {
    if (empty($url)) return "❌ URL VUOTA";
    
    if (strpos($url, $base_url) === 0) {
        $relative = ltrim(substr($url, strlen($base_url)), '/');
        $path = $base_dir . '/' . rawurldecode($relative);
        
        if (file_exists($path)) {
            $size = filesize($path);
            return "✓ ESISTE (" . round($size / 1024, 1) . " KB) → " . substr($path, -40);
        } else {
            return "❌ FILE NON TROVATO → " . substr($path, -40);
        }
    }
    return "⚠️ URL ESTERNA";
}

echo "  documento_unico_url: " . check_file_exists($doc_url, $base_url, $base_dir) . "\n";
echo "  documento_unico_url_originale: " . check_file_exists($doc_url_orig, $base_url, $base_dir) . "\n";
echo "  documento_unico_merged_url: " . check_file_exists($doc_merged_url, $base_url, $base_dir) . "\n";
echo "  documento_unico_attestato_firma_url: " . check_file_exists($attestato_url, $base_url, $base_dir) . "\n";

// 5. Directory documenti
echo "\n📁 CONTENUTO DIRECTORY wecoop-documenti-unici/:\n";
$doc_dir = $base_dir . '/wecoop-documenti-unici/';
if (is_dir($doc_dir)) {
    $files = glob($doc_dir . 'Documento_Unico_491*');
    if (count($files) > 0) {
        foreach ($files as $f) {
            echo "  ✓ " . basename($f) . " (" . round(filesize($f) / 1024, 1) . " KB)\n";
        }
    } else {
        echo "  ❌ NESSUN FILE PER RICHIESTA 491\n";
    }
} else {
    echo "  ❌ DIRECTORY NON ESISTE: $doc_dir\n";
}

// 6. Contenuto completo della directory
echo "\n  Tutti i file nella directory:\n";
if (is_dir($doc_dir)) {
    $all_files = array_slice(scandir($doc_dir), 2); // Skip . e ..
    if (count($all_files) > 0) {
        foreach (array_slice($all_files, 0, 20) as $f) { // Max 20
            echo "    - $f\n";
        }
        if (count($all_files) > 20) {
            echo "    ... e " . (count($all_files) - 20) . " altri file\n";
        }
    } else {
        echo "    (vuota)\n";
    }
}

// 7. Test merge
echo "\n🔧 TEST MERGE:\n";
if ($firmware === 'yes' && !empty($attestato_url) && !empty($doc_url)) {
    echo "  Provo a simulare il merge...\n";
    
    $doc_path = rtrim($base_dir, '/') . '/' . ltrim(str_replace($base_url, '', $doc_url), '/');
    $att_path = rtrim($base_dir, '/') . '/' . ltrim(str_replace($base_url, '', $attestato_url), '/');
    
    echo "    Documento path: " . (file_exists($doc_path) ? "✓" : "❌") . " " . substr($doc_path, -40) . "\n";
    echo "    Attestato path: " . (file_exists($att_path) ? "✓" : "❌") . " " . substr($att_path, -40) . "\n";
    
    if (!file_exists($doc_path)) {
        echo "\n  ⚠️ IL PROBLEMA: Il documento originale non esiste sul disco!\n";
        echo "     URL in DB: $doc_url\n";
        echo "     Path atteso: $doc_path\n";
    }
}

echo "\n=== FINE DIAGNOSTICA ===\n";
?>
