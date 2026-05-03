<?php
/**
 * Aggiunge la sezione "Fatturazione Servizi - KINTI SRL" alla pagina Note Legali (ID 592).
 *
 * COME USARE:
 *   1. Visita https://www.wecoop.org/update-note-legali-kinti.php?token=kintiupdate2026
 *   2. Verifica l'output
 *   3. ELIMINA questo file dopo l'esecuzione
 */

if ( ! defined('ABSPATH') ) {
    require_once __DIR__ . '/wp-load.php';
}

define('UPDATE_SECRET', 'kintiupdate2026');

if ( empty($_GET['token']) || $_GET['token'] !== UPDATE_SECRET ) {
    wp_die('Accesso negato. Aggiungi ?token=kintiupdate2026 all\'URL.');
}

echo '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px}h2{color:#1a472a}.ok{color:green}.err{color:red}</style>';
echo '<h1>🔧 Aggiornamento Note Legali — KINTI SRL</h1>';

$page_id = 592;
$page = get_post($page_id);

if ( ! $page ) {
    echo '<p class="err">❌ Pagina ID 592 non trovata. Verifica l\'ID corretto.</p>';
    exit;
}

// Blocco KINTI da inserire dopo la sezione "Informazioni Societarie"
$kinti_block = '

<!-- wp:heading -->
<h2>Fatturazione Servizi</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Per i servizi a pagamento erogati nell\'ambito del progetto WECOOP, la gestione economica e la fatturazione sono a cura di <strong>KINTI SRL</strong>.</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul>
<li><strong>Sede legale:</strong> Via San Martino di Tours, 2 - 20900 Monza (MB)</li>
<li><strong>Ufficio:</strong> Via Populonia, 8 - 20159 Milano (MI)</li>
<li><strong>CF/P.IVA:</strong> 12201260960</li>
<li><strong>SDI:</strong> T9K4ZHO</li>
<li><strong>Telefono:</strong> +39 331 393 5170</li>
<li><strong>Email:</strong> <a href="mailto:info@kinti.it">info@kinti.it</a></li>
</ul>
<!-- /wp:list -->';

$current_content = $page->post_content;

// Controlla se la sezione è già presente
if ( strpos($current_content, 'Fatturazione Servizi') !== false ) {
    echo '<p class="ok">ℹ️ La sezione "Fatturazione Servizi" è già presente nella pagina. Nessuna modifica necessaria.</p>';
    echo '<p><a href="' . get_permalink($page_id) . '" target="_blank">→ Visualizza la pagina</a></p>';
    exit;
}

// Inserisce il blocco KINTI dopo la sezione "Informazioni Societarie"
// Cerca il marker della sezione Proprietà Intellettuale e inserisce prima di essa
$marker = '<!-- wp:heading -->' . "\n" . '<h2>Proprietà Intellettuale</h2>';
$marker_alt = '<h2>Propert';

if ( strpos($current_content, $marker) !== false ) {
    $new_content = str_replace($marker, $kinti_block . "\n\n" . $marker, $current_content);
} else {
    // Fallback: aggiunge in fondo al contenuto
    $new_content = $current_content . $kinti_block;
    echo '<p>⚠️ Marker "Proprietà Intellettuale" non trovato — sezione aggiunta in fondo al contenuto.</p>';
}

$result = wp_update_post([
    'ID'           => $page_id,
    'post_content' => $new_content,
]);

if ( is_wp_error($result) || $result === 0 ) {
    echo '<p class="err">❌ Errore durante l\'aggiornamento: ' . esc_html( is_wp_error($result) ? $result->get_error_message() : 'wp_update_post ha restituito 0' ) . '</p>';
} else {
    echo '<p class="ok">✅ Sezione "Fatturazione Servizi - KINTI SRL" aggiunta con successo alla pagina Note Legali (ID ' . $page_id . ').</p>';
    echo '<p><a href="' . get_permalink($page_id) . '" target="_blank">→ Visualizza la pagina aggiornata</a></p>';
    echo '<p><strong>⚠️ Importante: elimina questo file dopo l\'esecuzione.</strong></p>';
}
