<?php

declare(strict_types=1);

require_once __DIR__ . '/wp-load.php';

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    status_header(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Accesso negato. Effettua login come amministratore.';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$filters = [
    'q' => isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '',
    'ambito' => isset($_GET['ambito']) ? sanitize_text_field(wp_unslash($_GET['ambito'])) : 'all',
    'categoria' => isset($_GET['categoria']) ? sanitize_text_field(wp_unslash($_GET['categoria'])) : '',
    'scope' => isset($_GET['scope']) ? sanitize_text_field(wp_unslash($_GET['scope'])) : '',
];

if (!in_array($filters['ambito'], ['all', 'seek', 'offer'], true)) {
    $filters['ambito'] = 'all';
}
if (!in_array($filters['scope'], ['', 'job', 'service'], true)) {
    $filters['scope'] = '';
}

$meta_query = [];
if ($filters['ambito'] === 'seek') {
    $meta_query[] = [
        'key' => 'category_direction',
        'value' => 'seek',
        'compare' => '=',
    ];
} elseif ($filters['ambito'] === 'offer') {
    $meta_query[] = [
        'relation' => 'OR',
        [
            'key' => 'category_direction',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key' => 'category_direction',
            'value' => 'offer',
            'compare' => '=',
        ],
    ];
}

if ($filters['scope'] !== '') {
    $meta_query[] = [
        'key' => 'category_scope',
        'value' => $filters['scope'],
        'compare' => '=',
    ];
}

$query_args = [
    'post_type' => ['wecoop_job_offer', 'wecoop_job_submission'],
    'post_status' => 'publish',
    'posts_per_page' => 20,
    'orderby' => ['meta_value_num' => 'DESC', 'date' => 'DESC'],
    'meta_key' => 'is_featured',
];

if (!empty($meta_query)) {
    $query_args['meta_query'] = $meta_query;
}
if ($filters['q'] !== '') {
    $query_args['s'] = $filters['q'];
}
if ($filters['categoria'] !== '') {
    $query_args['tax_query'] = [[
        'taxonomy' => 'wecoop_job_category',
        'field' => 'slug',
        'terms' => $filters['categoria'],
    ]];
}

$debug_query = new WP_Query($query_args);

$type_counts = [];
foreach (['wecoop_job_offer', 'wecoop_job_submission', 'post'] as $post_type) {
    $q = new WP_Query([
        'post_type' => $post_type,
        'post_status' => ['publish', 'pending', 'draft', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);
    $type_counts[$post_type] = (int) $q->found_posts;
}

$app_post_count = new WP_Query([
    'post_type' => 'post',
    'post_status' => ['publish', 'pending', 'draft', 'private'],
    'posts_per_page' => -1,
    'meta_key' => 'submitted_from_app',
    'meta_value' => '1',
    'fields' => 'ids',
]);

$recent = get_posts([
    'post_type' => ['wecoop_job_offer', 'wecoop_job_submission', 'post'],
    'post_status' => ['publish', 'pending', 'draft', 'private'],
    'posts_per_page' => 50,
    'orderby' => 'date',
    'order' => 'DESC',
]);

$terms = get_terms([
    'taxonomy' => 'wecoop_job_category',
    'hide_empty' => false,
]);

?><!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Debug Annunci WECOOP</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif; margin: 24px; color: #1f2933; }
    h1 { margin: 0 0 16px; }
    .box { border: 1px solid #d9e0e7; border-radius: 10px; padding: 14px; margin: 14px 0; background: #fff; }
    table { border-collapse: collapse; width: 100%; margin-top: 8px; }
    th, td { border: 1px solid #e6ecf0; padding: 8px; font-size: 13px; text-align: left; }
    th { background: #f6f9fb; }
    code { background: #f2f6f8; padding: 2px 5px; border-radius: 5px; }
    .ok { color: #0f7a3a; font-weight: 700; }
    .warn { color: #8a4a00; font-weight: 700; }
    .err { color: #b42318; font-weight: 700; }
    .row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    input, select, button { padding: 8px; border-radius: 8px; border: 1px solid #c7d3dd; }
    button { background: #0e7a3d; color: #fff; border: 0; cursor: pointer; }
  </style>
</head>
<body>
  <h1>Debug Annunci WECOOP</h1>

  <div class="box">
    <strong>Contatori rapidi</strong>
    <table>
      <tr><th>Tipo</th><th>Totale</th></tr>
      <tr><td>wecoop_job_offer</td><td><?php echo esc($type_counts['wecoop_job_offer']); ?></td></tr>
      <tr><td>wecoop_job_submission</td><td><?php echo esc($type_counts['wecoop_job_submission']); ?></td></tr>
      <tr><td>post</td><td><?php echo esc($type_counts['post']); ?></td></tr>
      <tr><td>post con submitted_from_app=1</td><td><?php echo esc($app_post_count->found_posts); ?></td></tr>
    </table>
  </div>

  <div class="box">
    <strong>Simulazione query frontend</strong>
    <form method="get" class="row" style="margin-top: 10px;">
      <input type="text" name="q" placeholder="search" value="<?php echo esc($filters['q']); ?>">
      <select name="ambito">
        <option value="all" <?php selected($filters['ambito'], 'all'); ?>>all</option>
        <option value="seek" <?php selected($filters['ambito'], 'seek'); ?>>seek</option>
        <option value="offer" <?php selected($filters['ambito'], 'offer'); ?>>offer</option>
      </select>
      <select name="scope">
        <option value="" <?php selected($filters['scope'], ''); ?>>scope any</option>
        <option value="job" <?php selected($filters['scope'], 'job'); ?>>job</option>
        <option value="service" <?php selected($filters['scope'], 'service'); ?>>service</option>
      </select>
      <select name="categoria">
        <option value="">categoria any</option>
        <?php if (!is_wp_error($terms)) : foreach ($terms as $term) : ?>
          <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($filters['categoria'], $term->slug); ?>><?php echo esc_html($term->name); ?></option>
        <?php endforeach; endif; ?>
      </select>
      <button type="submit">Esegui debug</button>
    </form>

    <p style="margin-top:10px;">Risultati trovati: <strong><?php echo esc($debug_query->found_posts); ?></strong></p>
    <p>SQL: <code><?php echo esc($debug_query->request); ?></code></p>
  </div>

  <div class="box">
    <strong>Ultimi 50 record utili</strong>
    <table>
      <tr>
        <th>ID</th><th>Titolo</th><th>Tipo</th><th>Stato</th><th>City</th><th>Scope</th><th>Direction</th><th>submitted_from_app</th><th>Data</th>
      </tr>
      <?php foreach ($recent as $p) :
        $id = (int) $p->ID;
        $city = (string) get_post_meta($id, 'city', true);
        $scope = (string) get_post_meta($id, 'category_scope', true);
        $direction = (string) get_post_meta($id, 'category_direction', true);
        $from_app = (string) get_post_meta($id, 'submitted_from_app', true);
      ?>
      <tr>
        <td><?php echo esc($id); ?></td>
        <td><?php echo esc($p->post_title); ?></td>
        <td><?php echo esc($p->post_type); ?></td>
        <td><?php echo esc($p->post_status); ?></td>
        <td><?php echo esc($city); ?></td>
        <td><?php echo esc($scope); ?></td>
        <td><?php echo esc($direction); ?></td>
        <td><?php echo esc($from_app); ?></td>
        <td><?php echo esc(get_date_from_gmt((string) $p->post_date_gmt, 'd/m/Y H:i')); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>
