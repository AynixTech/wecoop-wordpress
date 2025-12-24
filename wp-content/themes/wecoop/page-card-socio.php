<?php
/**
 * Template Name: Tessera Socio
 */

get_header();

// Recupera il parametro card_id dalla URL
$card_id = isset($_GET['card_id']) ? sanitize_text_field($_GET['card_id']) : null;

if (!$card_id) {
  echo '<div class="card-socio-wrapper"><p class="card-socio-error">Nessuna tessera trovata.</p></div>';
  get_footer();
  exit;
}

// Cerca l'utente con quel carta_id
$args = [
  'meta_key' => 'carta_id',
  'meta_value' => $card_id,
  'number' => 1,
];
$users = get_users($args);

if (empty($users)) {
  echo '<div class="card-socio-wrapper"><p class="card-socio-error">Tessera non valida o utente non trovato.</p></div>';
  get_footer();
  exit;
}

$user = $users[0];
$nome = $user->display_name;
$citta = get_user_meta($user->ID, 'citta', true);
$interessi = get_user_meta($user->ID, 'interessi', true);
$telefono = get_user_meta($user->ID, 'telefono', true);
?>

<style>
.card-socio-wrapper {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background: #fff9e6;
  border: 2px solid #ffc107;
  border-radius: 12px;
  font-family: 'Segoe UI', sans-serif;
}

.card-socio-wrapper h1 {
  text-align: center;
  color: #d48806;
  font-size: 28px;
  margin-bottom: 20px;
}

.card-socio-box {
  background: #fff;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 0 6px rgba(0,0,0,0.1);
}

.card-socio-box p {
  font-size: 16px;
  margin: 10px 0;
  line-height: 1.4;
}

.card-socio-box p strong {
  color: #333;
}

.card-socio-error {
  text-align: center;
  color: #c00;
  font-weight: bold;
  font-size: 18px;
  margin-top: 40px;
}
</style>

<div class="card-socio-wrapper">
  <h1>Tessera Socio WECOOP</h1>
  <div class="card-socio-box">
    <p><strong>Nome:</strong> <?php echo esc_html($nome); ?></p>
    <p><strong>Città:</strong> <?php echo esc_html($citta); ?></p>
    <p><strong>Interessi:</strong> <?php echo esc_html($interessi); ?></p>
    <p><strong>Telefono:</strong> <?php echo esc_html($telefono); ?></p>
    <p><strong>Carta ID:</strong> <?php echo esc_html($card_id); ?></p>
  </div>
</div>

<?php get_footer(); ?>
