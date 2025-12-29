<?php
/**
 * Test Registrazione Utente
 * Testa la creazione utente WordPress senza passare per REST API
 * 
 * URL: https://www.wecoop.org/test-registrazione.php
 */

// Carica WordPress
require_once('./wp-load.php');

// Abilita errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Header
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Registrazione WeCoop</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 4px; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 4px; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0; border-radius: 4px; color: #0c5460; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🧪 Test Registrazione WeCoop</h1>
    
    <?php
    // Test 1: Verifica funzioni WordPress
    echo '<div class="test-section">';
    echo '<h2>1️⃣ Verifica Ambiente WordPress</h2>';
    
    if (function_exists('wp_create_user')) {
        echo '<div class="success">✅ wp_create_user() disponibile</div>';
    } else {
        echo '<div class="error">❌ wp_create_user() NON disponibile</div>';
    }
    
    if (function_exists('username_exists')) {
        echo '<div class="success">✅ username_exists() disponibile</div>';
    } else {
        echo '<div class="error">❌ username_exists() NON disponibile</div>';
    }
    
    echo '<div class="info"><strong>WordPress Version:</strong> ' . get_bloginfo('version') . '</div>';
    echo '<div class="info"><strong>PHP Version:</strong> ' . PHP_VERSION . '</div>';
    echo '</div>';
    
    // Test 2: Crea utente di test
    if (isset($_GET['test']) && $_GET['test'] === 'crea') {
        echo '<div class="test-section">';
        echo '<h2>2️⃣ Test Creazione Utente</h2>';
        
        $test_username = '+39' . rand(1000000000, 9999999999);
        $test_password = 'TestPass' . rand(1000, 9999);
        
        echo '<div class="info">';
        echo '<strong>Username test:</strong> ' . htmlspecialchars($test_username) . '<br>';
        echo '<strong>Password test:</strong> ' . htmlspecialchars($test_password) . '<br>';
        echo '</div>';
        
        // Verifica se username esiste
        $existing = username_exists($test_username);
        if ($existing) {
            echo '<div class="error">⚠️ Username già esistente (ID: ' . $existing . ')</div>';
        } else {
            echo '<div class="success">✅ Username disponibile</div>';
            
            // Prova a creare utente
            echo '<div class="info">🔄 Tentativo creazione utente...</div>';
            
            $user_id = wp_create_user($test_username, $test_password, null);
            
            if (is_wp_error($user_id)) {
                echo '<div class="error">';
                echo '<strong>❌ ERRORE nella creazione:</strong><br>';
                echo '<strong>Codice:</strong> ' . $user_id->get_error_code() . '<br>';
                echo '<strong>Messaggio:</strong> ' . $user_id->get_error_message() . '<br>';
                echo '<strong>Tutti i messaggi:</strong><br>';
                echo '<pre>' . print_r($user_id->get_error_messages(), true) . '</pre>';
                echo '</div>';
            } else {
                echo '<div class="success">';
                echo '<strong>✅ UTENTE CREATO CON SUCCESSO!</strong><br>';
                echo '<strong>User ID:</strong> ' . $user_id . '<br>';
                echo '</div>';
                
                // Verifica che esista
                $verify_user = get_userdata($user_id);
                if ($verify_user) {
                    echo '<div class="success">';
                    echo '<strong>✅ Utente verificato in database</strong><br>';
                    echo '<strong>Username in DB:</strong> ' . $verify_user->user_login . '<br>';
                    echo '<strong>Email:</strong> ' . ($verify_user->user_email ?: 'nessuna') . '<br>';
                    echo '<strong>Registrato:</strong> ' . $verify_user->user_registered . '<br>';
                    echo '</div>';
                    
                    // Assegna ruolo
                    $user = new WP_User($user_id);
                    $user->set_role('subscriber');
                    
                    echo '<div class="success">✅ Ruolo "subscriber" assegnato</div>';
                    
                    // Test login
                    echo '<div class="info">';
                    echo '<strong>🔐 Test autenticazione:</strong><br>';
                    $auth_user = wp_authenticate($test_username, $test_password);
                    if (is_wp_error($auth_user)) {
                        echo '<span style="color: red;">❌ Login fallito: ' . $auth_user->get_error_message() . '</span>';
                    } else {
                        echo '<span style="color: green;">✅ Login riuscito!</span>';
                    }
                    echo '</div>';
                    
                } else {
                    echo '<div class="error">❌ ERRORE: Utente NON trovato in wp_users dopo creazione!</div>';
                }
            }
        }
        
        echo '</div>';
    }
    
    // Test 3: Verifica plugin
    echo '<div class="test-section">';
    echo '<h2>3️⃣ Verifica Plugin WeCoop Soci</h2>';
    
    $plugin_file = WP_PLUGIN_DIR . '/wecoop-soci/wecoop-soci.php';
    $endpoint_file = WP_PLUGIN_DIR . '/wecoop-soci/includes/api/class-soci-endpoint.php';
    
    if (file_exists($plugin_file)) {
        echo '<div class="success">✅ Plugin wecoop-soci trovato</div>';
        
        if (is_plugin_active('wecoop-soci/wecoop-soci.php')) {
            echo '<div class="success">✅ Plugin ATTIVO</div>';
        } else {
            echo '<div class="error">❌ Plugin NON ATTIVO</div>';
        }
        
        if (file_exists($endpoint_file)) {
            echo '<div class="success">✅ File endpoint trovato</div>';
            
            // Verifica se contiene wp_create_user
            $endpoint_content = file_get_contents($endpoint_file);
            if (strpos($endpoint_content, 'wp_create_user') !== false) {
                echo '<div class="success">✅ Codice wp_create_user PRESENTE nel file</div>';
                
                // Conta quante volte appare
                $count = substr_count($endpoint_content, 'wp_create_user');
                echo '<div class="info">📊 wp_create_user trovato ' . $count . ' volte</div>';
                
                // Verifica log WP-CREATE-USER
                if (strpos($endpoint_content, 'WP-CREATE-USER') !== false) {
                    echo '<div class="success">✅ Log [WP-CREATE-USER] PRESENTI (codice aggiornato!)</div>';
                } else {
                    echo '<div class="error">❌ Log [WP-CREATE-USER] NON trovati (codice vecchio!)</div>';
                }
            } else {
                echo '<div class="error">❌ Codice wp_create_user NON TROVATO nel file (codice vecchio!)</div>';
            }
            
            // Data ultima modifica
            $last_modified = filemtime($endpoint_file);
            echo '<div class="info"><strong>Ultima modifica file:</strong> ' . date('Y-m-d H:i:s', $last_modified) . '</div>';
            
        } else {
            echo '<div class="error">❌ File endpoint NON trovato</div>';
        }
    } else {
        echo '<div class="error">❌ Plugin wecoop-soci NON trovato</div>';
    }
    
    echo '</div>';
    
    // Test 4: Lista ultimi utenti
    echo '<div class="test-section">';
    echo '<h2>4️⃣ Ultimi Utenti Registrati</h2>';
    
    global $wpdb;
    $users = $wpdb->get_results("
        SELECT ID, user_login, user_email, user_registered 
        FROM {$wpdb->users} 
        ORDER BY user_registered DESC 
        LIMIT 10
    ");
    
    if ($users) {
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background: #f4f4f4;">';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">ID</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Username</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Email</th>';
        echo '<th style="padding: 8px; border: 1px solid #ddd;">Registrato</th>';
        echo '</tr>';
        
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . $user->ID . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($user->user_login) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($user->user_email) . '</td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;">' . $user->user_registered . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<div class="info">Nessun utente trovato</div>';
    }
    
    echo '</div>';
    
    // Pulsanti azione
    echo '<div class="test-section">';
    echo '<h2>🎯 Azioni</h2>';
    echo '<a href="?test=crea" class="button">🧪 Crea Utente di Test</a> ';
    echo '<a href="?" class="button" style="background: #6c757d;">🔄 Ricarica Pagina</a>';
    echo '</div>';
    ?>
    
</body>
</html>
