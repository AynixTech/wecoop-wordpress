<?php
/**
 * Debug Pagamenti WeCoop
 * 
 * URL: https://www.wecoop.org/debug-pagamenti.php
 */

// Carica WordPress
require_once(__DIR__ . '/public_html/wp-load.php');

// Solo admin
if (!current_user_can('manage_options')) {
    die('⛔ Accesso negato. Solo amministratori.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔍 Debug Sistema Pagamenti WeCoop</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2271b1; }
        h2 { 
            color: #1d2327;
            border-bottom: 2px solid #2271b1;
            padding-bottom: 10px;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th {
            background: #2271b1;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background: #f0f6fc;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-left: 4px solid #2271b1;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
        .btn:hover {
            background: #135e96;
        }
    </style>
</head>
<body>
    <h1>🔍 Debug Sistema Pagamenti WeCoop</h1>
    <p>Aggiornato: <?php echo date('d/m/Y H:i:s'); ?></p>

    <!-- 1. VERIFICA CLASSI -->
    <div class="card">
        <h2>1️⃣ Verifica Classi Caricate</h2>
        <table>
            <tr>
                <th>Classe</th>
                <th>Stato</th>
            </tr>
            <tr>
                <td>WECOOP_Servizi_Payment_System</td>
                <td><?php echo class_exists('WECOOP_Servizi_Payment_System') ? '<span class="success">✅ Caricata</span>' : '<span class="error">❌ NON trovata</span>'; ?></td>
            </tr>
            <tr>
                <td>WeCoop_Payment_System (alias)</td>
                <td><?php echo class_exists('WeCoop_Payment_System') ? '<span class="success">✅ Caricata</span>' : '<span class="error">❌ NON trovata</span>'; ?></td>
            </tr>
            <tr>
                <td>WECOOP_Servizi_Endpoint</td>
                <td><?php echo class_exists('WECOOP_Servizi_Endpoint') ? '<span class="success">✅ Caricata</span>' : '<span class="error">❌ NON trovata</span>'; ?></td>
            </tr>
            <tr>
                <td>WECOOP_Servizi_Stripe_Payment_Intent</td>
                <td><?php echo class_exists('WECOOP_Servizi_Stripe_Payment_Intent') ? '<span class="success">✅ Caricata</span>' : '<span class="error">❌ NON trovata</span>'; ?></td>
            </tr>
        </table>
    </div>

    <!-- 2. VERIFICA DATABASE -->
    <div class="card">
        <h2>2️⃣ Verifica Database</h2>
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'wecoop_pagamenti';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if ($table_exists) {
            echo '<p class="success">✅ Tabella <code>' . $table_name . '</code> esiste</p>';
            
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo '<p>📊 Totale pagamenti: <strong>' . $count . '</strong></p>';
            
            // Ultimi 5 pagamenti
            $payments = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5");
            
            if ($payments) {
                echo '<h3>Ultimi 5 pagamenti:</h3>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Richiesta</th><th>User</th><th>Importo</th><th>Stato</th><th>Creato</th></tr>';
                foreach ($payments as $p) {
                    echo '<tr>';
                    echo '<td>' . $p->id . '</td>';
                    echo '<td>' . $p->richiesta_id . '</td>';
                    echo '<td>' . $p->user_id . '</td>';
                    echo '<td>€' . number_format($p->importo, 2) . '</td>';
                    echo '<td>' . $p->stato . '</td>';
                    echo '<td>' . $p->created_at . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<p class="error">❌ Tabella <code>' . $table_name . '</code> NON esiste!</p>';
            echo '<p><a href="?action=create_table" class="btn">Crea Tabella</a></p>';
        }
        ?>
    </div>

    <!-- 3. VERIFICA LISTINO PREZZI -->
    <div class="card">
        <h2>3️⃣ Listino Prezzi</h2>
        <?php
        $prezzi_servizi = get_option('wecoop_listino_servizi', []);
        $prezzi_categorie = get_option('wecoop_listino_categorie', []);
        
        if (empty($prezzi_servizi) && empty($prezzi_categorie)) {
            echo '<p class="warning">⚠️ Nessun prezzo configurato nel listino!</p>';
            echo '<p>Vai su <a href="/wp-admin/admin.php?page=wecoop-servizi-listino">Listino Prezzi</a> per configurare i prezzi.</p>';
        } else {
            if (!empty($prezzi_servizi)) {
                echo '<h3>Prezzi Servizi (' . count($prezzi_servizi) . '):</h3>';
                echo '<table>';
                echo '<tr><th>Servizio</th><th>Prezzo</th></tr>';
                foreach ($prezzi_servizi as $servizio => $prezzo) {
                    echo '<tr><td>' . esc_html($servizio) . '</td><td class="success">€' . number_format($prezzo, 2) . '</td></tr>';
                }
                echo '</table>';
            }
            
            if (!empty($prezzi_categorie)) {
                echo '<h3>Prezzi Categorie (' . count($prezzi_categorie) . '):</h3>';
                echo '<table>';
                echo '<tr><th>Categoria</th><th>Prezzo</th></tr>';
                foreach ($prezzi_categorie as $categoria => $prezzo) {
                    echo '<tr><td>' . esc_html($categoria) . '</td><td class="success">€' . number_format($prezzo, 2) . '</td></tr>';
                }
                echo '</table>';
            }
        }
        ?>
    </div>

    <!-- 4. RICHIESTE SENZA PAGAMENTO -->
    <div class="card">
        <h2>4️⃣ Richieste Senza Pagamento</h2>
        <?php
        $richieste_query = new WP_Query([
            'post_type' => 'richiesta_servizio',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if ($richieste_query->have_posts()) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Numero</th><th>Servizio</th><th>Categoria</th><th>Stato</th><th>Pagamento</th><th>Prezzo Listino</th></tr>';
            
            while ($richieste_query->have_posts()) {
                $richieste_query->the_post();
                $post_id = get_the_ID();
                $numero_pratica = get_post_meta($post_id, 'numero_pratica', true);
                $servizio = get_post_meta($post_id, 'servizio', true);
                $categoria = get_post_meta($post_id, 'categoria', true);
                $stato = get_post_meta($post_id, 'stato', true);
                
                // Verifica pagamento
                $payment = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE richiesta_id = %d",
                    $post_id
                ));
                
                // Prezzo listino
                $prezzo_listino = null;
                if (isset($prezzi_servizi[$servizio])) {
                    $prezzo_listino = $prezzi_servizi[$servizio];
                } elseif ($categoria && isset($prezzi_categorie[$categoria])) {
                    $prezzo_listino = $prezzi_categorie[$categoria];
                }
                
                echo '<tr>';
                echo '<td>' . $post_id . '</td>';
                echo '<td>' . esc_html($numero_pratica) . '</td>';
                echo '<td>' . esc_html($servizio) . '</td>';
                echo '<td>' . esc_html($categoria) . '</td>';
                echo '<td>' . esc_html($stato) . '</td>';
                
                if ($payment) {
                    echo '<td class="success">✅ #' . $payment->id . ' (€' . number_format($payment->importo, 2) . ')</td>';
                } else {
                    echo '<td class="error">❌ Nessuno</td>';
                }
                
                if ($prezzo_listino) {
                    echo '<td class="info">€' . number_format($prezzo_listino, 2) . '</td>';
                } else {
                    echo '<td class="warning">⚠️ Non configurato</td>';
                }
                
                echo '</tr>';
            }
            
            echo '</table>';
            wp_reset_postdata();
        }
        ?>
    </div>

    <!-- 5. TEST API -->
    <div class="card">
        <h2>5️⃣ Test Endpoint API</h2>
        <?php
        // Verifica REST routes
        $routes = rest_get_server()->get_routes();
        
        $api_routes = [
            '/wecoop/v1/payment/richiesta/(?P<richiesta_id>\d+)' => 'GET payment by richiesta_id',
            '/wecoop/v1/create-payment-intent' => 'POST create Stripe PaymentIntent',
            '/wecoop/v1/stripe-webhook' => 'POST Stripe webhook',
            '/wecoop/v1/richiesta-servizio' => 'POST create richiesta'
        ];
        
        echo '<table>';
        echo '<tr><th>Endpoint</th><th>Descrizione</th><th>Stato</th></tr>';
        
        foreach ($api_routes as $route => $desc) {
            $exists = isset($routes[$route]) || isset($routes['/wecoop/v1/payment/richiesta/(?P<richiesta_id>\d+)']);
            echo '<tr>';
            echo '<td><code>' . $route . '</code></td>';
            echo '<td>' . $desc . '</td>';
            echo '<td>' . ($exists ? '<span class="success">✅ Registrato</span>' : '<span class="error">❌ NON trovato</span>') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        ?>
    </div>

    <!-- AZIONI -->
    <?php
    if (isset($_GET['action'])) {
        echo '<div class="card">';
        echo '<h2>⚙️ Azione Eseguita</h2>';
        
        switch ($_GET['action']) {
            case 'create_table':
                if (class_exists('WECOOP_Servizi_Payment_System')) {
                    WECOOP_Servizi_Payment_System::create_payment_table();
                    echo '<p class="success">✅ Tabella creata!</p>';
                    echo '<p><a href="?" class="btn">Ricarica Pagina</a></p>';
                } else {
                    echo '<p class="error">❌ Classe WECOOP_Servizi_Payment_System non trovata!</p>';
                }
                break;
        }
        
        echo '</div>';
    }
    ?>

    <div class="card">
        <h2>🔧 Azioni Rapide</h2>
        <a href="?" class="btn">🔄 Ricarica</a>
        <a href="?action=create_table" class="btn">🗄️ Ricrea Tabella</a>
        <a href="/wp-admin/admin.php?page=wecoop-servizi-listino" class="btn">💰 Listino Prezzi</a>
        <a href="/wp-admin/admin.php?page=wecoop-servizi" class="btn">📋 Richieste</a>
    </div>

</body>
</html>
