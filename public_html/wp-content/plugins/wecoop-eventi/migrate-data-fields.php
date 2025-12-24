<?php
/**
 * Script di migrazione: data_evento → data_inizio, ora_evento → ora_inizio
 * 
 * Visita: https://www.wecoop.org/wp-content/plugins/wecoop-eventi/migrate-data-fields.php
 * per eseguire la migrazione
 */

require_once '../../../../wp-load.php';

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('Accesso negato. Solo amministratori possono eseguire questo script.');
}

echo "<h1>Migrazione Campi Eventi: data_evento → data_inizio</h1>";
echo "<p>Iniziando migrazione...</p>";

// Ottieni tutti gli eventi
$eventi = get_posts([
    'post_type' => 'evento',
    'post_status' => 'any',
    'posts_per_page' => -1
]);

$migrati = 0;
$gia_aggiornati = 0;
$errori = 0;

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Titolo</th><th>data_evento</th><th>data_inizio</th><th>Azione</th></tr>";

foreach ($eventi as $evento) {
    $data_evento = get_post_meta($evento->ID, 'data_evento', true);
    $ora_evento = get_post_meta($evento->ID, 'ora_evento', true);
    $data_inizio = get_post_meta($evento->ID, 'data_inizio', true);
    $ora_inizio = get_post_meta($evento->ID, 'ora_inizio', true);
    
    echo "<tr>";
    echo "<td>{$evento->ID}</td>";
    echo "<td>" . esc_html($evento->post_title) . "</td>";
    echo "<td>{$data_evento} {$ora_evento}</td>";
    echo "<td>{$data_inizio} {$ora_inizio}</td>";
    
    // Se ha data_evento ma NON ha data_inizio, migra
    if ($data_evento && !$data_inizio) {
        update_post_meta($evento->ID, 'data_inizio', $data_evento);
        if ($ora_evento) {
            update_post_meta($evento->ID, 'ora_inizio', $ora_evento);
        }
        echo "<td style='color: green; font-weight: bold;'>✅ MIGRATO</td>";
        $migrati++;
    } 
    // Se ha già data_inizio
    elseif ($data_inizio) {
        echo "<td style='color: blue;'>✓ Già aggiornato</td>";
        $gia_aggiornati++;
    }
    // Nessuna data
    else {
        echo "<td style='color: orange;'>⚠️ Nessuna data</td>";
        $errori++;
    }
    
    echo "</tr>";
}

echo "</table>";

echo "<h2>Riepilogo:</h2>";
echo "<ul>";
echo "<li><strong>Eventi migrati:</strong> {$migrati}</li>";
echo "<li><strong>Già aggiornati:</strong> {$gia_aggiornati}</li>";
echo "<li><strong>Senza data:</strong> {$errori}</li>";
echo "<li><strong>Totale eventi:</strong> " . count($eventi) . "</li>";
echo "</ul>";

echo "<p><strong>✅ Migrazione completata!</strong></p>";
echo "<p><a href='/wp-admin/edit.php?post_type=evento'>← Torna agli eventi</a></p>";
