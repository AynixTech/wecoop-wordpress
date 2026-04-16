<?php
/**
 * One-time migration: aggiorna slug categorie annunci da italiano a inglese.
 * Visita una volta questo file come admin, poi eliminalo.
 */
define('ABSPATH', __DIR__ . '/');
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Non autorizzato');
}

$mapping = [
    'evento'    => ['slug' => 'event',      'name' => 'Evento'],
    'concerto'  => ['slug' => 'concert',    'name' => 'Concerto / Musica'],
    'ristorante'=> ['slug' => 'restaurant', 'name' => 'Ristorante / Food'],
    'servizio'  => ['slug' => 'service',    'name' => 'Servizio'],
    'vendita'   => ['slug' => 'sale',       'name' => 'Vendita'],
    'sport'     => ['slug' => 'sport',      'name' => 'Sport / Fitness'],
    'cultura'   => ['slug' => 'culture',    'name' => 'Cultura / Arte'],
    'lavoro'    => ['slug' => 'work',       'name' => 'Lavoro / Collaborazione'],
    'altro'     => ['slug' => 'other',      'name' => 'Altro'],
];

echo '<pre>';
foreach ($mapping as $old_slug => $new) {
    $term = get_term_by('slug', $old_slug, 'categoria_annuncio');
    if (!$term) {
        echo "❌ Termine non trovato: $old_slug\n";
        continue;
    }
    $result = wp_update_term($term->term_id, 'categoria_annuncio', [
        'slug' => $new['slug'],
        'name' => $new['name'],
    ]);
    if (is_wp_error($result)) {
        echo "❌ Errore aggiornamento '$old_slug': " . $result->get_error_message() . "\n";
    } else {
        echo "✅ '$old_slug' → '{$new['slug']}' (ID: {$term->term_id})\n";
    }
}
echo '</pre>';
echo '<p>✅ Migrazione completata. Elimina questo file.</p>';
