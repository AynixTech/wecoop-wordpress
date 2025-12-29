<?php
/**
 * Gestione Socio - Admin Panel
 * Completa profilo utente e approva come socio
 * 
 * URL: https://www.wecoop.org/gestione-socio.php?user_id=123
 */

// Carica WordPress
require_once('./wp-load.php');

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    wp_die('Accesso negato. Solo amministratori possono accedere a questa pagina.');
}

// Gestione form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'completa_profilo') {
        // Aggiorna profilo utente
        $update_data = [
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'display_name' => sanitize_text_field($_POST['display_name'])
        ];
        
        $result = wp_update_user($update_data);
        
        if (!is_wp_error($result)) {
            // Aggiorna user meta
            update_user_meta($user_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
            update_user_meta($user_id, 'citta', sanitize_text_field($_POST['citta']));
            update_user_meta($user_id, 'cap', sanitize_text_field($_POST['cap']));
            update_user_meta($user_id, 'provincia', sanitize_text_field($_POST['provincia']));
            update_user_meta($user_id, 'codice_fiscale', sanitize_text_field($_POST['codice_fiscale']));
            update_user_meta($user_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
            update_user_meta($user_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
            update_user_meta($user_id, 'profilo_completo', true);
            
            // Aggiorna anche i meta nel post richiesta_socio
            $richiesta = get_posts([
                'post_type' => 'richiesta_socio',
                'meta_key' => 'user_id_socio',
                'meta_value' => $user_id,
                'posts_per_page' => 1
            ]);
            
            if (!empty($richiesta)) {
                $post_id = $richiesta[0]->ID;
                update_post_meta($post_id, 'email', sanitize_email($_POST['email']));
                update_post_meta($post_id, 'indirizzo', sanitize_text_field($_POST['indirizzo']));
                update_post_meta($post_id, 'citta', sanitize_text_field($_POST['citta']));
                update_post_meta($post_id, 'cap', sanitize_text_field($_POST['cap']));
                update_post_meta($post_id, 'provincia', sanitize_text_field($_POST['provincia']));
                update_post_meta($post_id, 'codice_fiscale', sanitize_text_field($_POST['codice_fiscale']));
                update_post_meta($post_id, 'data_nascita', sanitize_text_field($_POST['data_nascita']));
                update_post_meta($post_id, 'luogo_nascita', sanitize_text_field($_POST['luogo_nascita']));
                update_post_meta($post_id, 'profilo_completo', true);
            }
            
            $message = '✅ Profilo completato con successo!';
            $message_type = 'success';
        } else {
            $message = '❌ Errore: ' . $result->get_error_message();
            $message_type = 'error';
        }
    }
    
    if ($action === 'approva_socio') {
        // Trova richiesta_socio
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            $post_id = $richiesta[0]->ID;
            
            // Aggiorna status
            update_post_meta($post_id, 'is_socio', true);
            update_post_meta($post_id, 'data_approvazione', current_time('mysql'));
            update_user_meta($user_id, 'is_socio', true);
            
            // Cambia ruolo a 'socio' se esiste, altrimenti lascia subscriber
            $user = new WP_User($user_id);
            if (get_role('socio')) {
                $user->set_role('socio');
            } else {
                // Crea ruolo socio se non esiste
                add_role('socio', 'Socio', get_role('subscriber')->capabilities);
                $user->set_role('socio');
            }
            
            // Cambia status post
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish'
            ]);
            
            $message = '🎉 Utente approvato come SOCIO con successo!';
            $message_type = 'success';
        } else {
            $message = '❌ Richiesta socio non trovata per questo utente';
            $message_type = 'error';
        }
    }
    
    if ($action === 'revoca_socio') {
        update_user_meta($user_id, 'is_socio', false);
        
        $richiesta = get_posts([
            'post_type' => 'richiesta_socio',
            'meta_key' => 'user_id_socio',
            'meta_value' => $user_id,
            'posts_per_page' => 1
        ]);
        
        if (!empty($richiesta)) {
            update_post_meta($richiesta[0]->ID, 'is_socio', false);
            update_post_meta($richiesta[0]->ID, 'data_revoca', current_time('mysql'));
        }
        
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        $message = '⚠️ Socio revocato. Utente tornato a subscriber.';
        $message_type = 'warning';
    }
}

// Recupera user_id dalla query string
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    wp_die('ID utente non specificato. Usa: ?user_id=123');
}

$user = get_userdata($user_id);
if (!$user) {
    wp_die('Utente non trovato.');
}

// Recupera dati utente
$is_socio = get_user_meta($user_id, 'is_socio', true);
$profilo_completo = get_user_meta($user_id, 'profilo_completo', true);
$telefono_completo = get_user_meta($user_id, 'telefono_completo', true);
$prefix = get_user_meta($user_id, 'prefix', true);
$telefono = get_user_meta($user_id, 'telefono', true);
$indirizzo = get_user_meta($user_id, 'indirizzo', true);
$citta = get_user_meta($user_id, 'citta', true);
$cap = get_user_meta($user_id, 'cap', true);
$provincia = get_user_meta($user_id, 'provincia', true);
$codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
$data_nascita = get_user_meta($user_id, 'data_nascita', true);
$luogo_nascita = get_user_meta($user_id, 'luogo_nascita', true);

// Recupera richiesta_socio
$richiesta = get_posts([
    'post_type' => 'richiesta_socio',
    'meta_key' => 'user_id_socio',
    'meta_value' => $user_id,
    'posts_per_page' => 1
]);

$numero_pratica = '';
$data_registrazione = $user->user_registered;
if (!empty($richiesta)) {
    $numero_pratica = get_post_meta($richiesta[0]->ID, 'numero_pratica', true);
    $data_registrazione = $richiesta[0]->post_date;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Socio - <?php echo esc_html($user->display_name); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #2d3748;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1a202c;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.socio { background: #48bb78; color: white; }
        .badge.subscriber { background: #ed8936; color: white; }
        .badge.completo { background: #4299e1; color: white; }
        .badge.incompleto { background: #f56565; color: white; }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h2 {
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            font-size: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 12px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 3px solid #4299e1;
        }
        .info-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 15px;
            color: #2d3748;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4299e1;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }
        .btn-warning {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }
        .btn-danger {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
        }
        .btn-secondary {
            background: #cbd5e0;
            color: #2d3748;
        }
        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .message.success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }
        .message.error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }
        .message.warning {
            background: #feebc8;
            color: #7c2d12;
            border-left: 4px solid #ed8936;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4299e1;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .empty {
            color: #a0aec0;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?php echo admin_url('users.php'); ?>" class="back-link">← Torna agli utenti</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo esc_attr($message_type); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1>
                <?php echo esc_html($user->display_name); ?>
                <span class="badge <?php echo $is_socio ? 'socio' : 'subscriber'; ?>">
                    <?php echo $is_socio ? '✓ Socio' : 'Subscriber'; ?>
                </span>
                <span class="badge <?php echo $profilo_completo ? 'completo' : 'incompleto'; ?>">
                    <?php echo $profilo_completo ? '✓ Profilo Completo' : '⚠ Profilo Incompleto'; ?>
                </span>
            </h1>
            <p style="color: #718096; margin-top: 5px;">ID Utente: <?php echo $user_id; ?> | Username: <?php echo esc_html($user->user_login); ?></p>
        </div>
        
        <!-- Informazioni Base -->
        <div class="card">
            <h2>📋 Informazioni Base</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo esc_html($user->user_login); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo $user->user_email ?: '<span class="empty">Non impostata</span>'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Telefono</div>
                    <div class="info-value"><?php echo esc_html($telefono_completo ?: $telefono); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data Registrazione</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($data_registrazione)); ?></div>
                </div>
                <?php if ($numero_pratica): ?>
                <div class="info-item">
                    <div class="info-label">Numero Pratica</div>
                    <div class="info-value"><?php echo esc_html($numero_pratica); ?></div>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <div class="info-label">Ruolo WordPress</div>
                    <div class="info-value"><?php echo implode(', ', $user->roles); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Form Completa Profilo -->
        <div class="card">
            <h2>✏️ Completa/Modifica Profilo</h2>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <input type="hidden" name="action" value="completa_profilo">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome *</label>
                        <input type="text" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Cognome *</label>
                        <input type="text" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome Visualizzato *</label>
                        <input type="text" name="display_name" value="<?php echo esc_attr($user->display_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Codice Fiscale *</label>
                    <input type="text" name="codice_fiscale" value="<?php echo esc_attr($codice_fiscale); ?>" 
                           pattern="[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]" 
                           style="text-transform: uppercase;" maxlength="16" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Data di Nascita *</label>
                        <input type="date" name="data_nascita" value="<?php echo esc_attr($data_nascita); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Luogo di Nascita *</label>
                        <input type="text" name="luogo_nascita" value="<?php echo esc_attr($luogo_nascita); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Indirizzo *</label>
                    <input type="text" name="indirizzo" value="<?php echo esc_attr($indirizzo); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Città *</label>
                        <input type="text" name="citta" value="<?php echo esc_attr($citta); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>CAP *</label>
                        <input type="text" name="cap" value="<?php echo esc_attr($cap); ?>" pattern="[0-9]{5}" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label>Provincia *</label>
                        <input type="text" name="provincia" value="<?php echo esc_attr($provincia); ?>" maxlength="2" 
                               style="text-transform: uppercase;" pattern="[A-Z]{2}" required>
                    </div>
                </div>
                
                <div class="actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Salva Profilo
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Azioni Socio -->
        <div class="card">
            <h2>🎯 Gestione Status Socio</h2>
            
            <?php if (!$is_socio): ?>
                <p style="margin-bottom: 20px; color: #718096;">
                    Questo utente non è ancora socio. Completa il profilo e clicca il bottone qui sotto per approvarlo come socio.
                </p>
                <form method="POST" action="" onsubmit="return confirm('Confermi di voler approvare questo utente come SOCIO?');">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="action" value="approva_socio">
                    <button type="submit" class="btn btn-success" <?php echo !$profilo_completo ? 'disabled title="Completa prima il profilo"' : ''; ?>>
                        ✓ Approva come SOCIO
                    </button>
                </form>
            <?php else: ?>
                <p style="margin-bottom: 20px; color: #48bb78; font-weight: 600;">
                    ✓ Questo utente è un SOCIO attivo
                </p>
                <form method="POST" action="" onsubmit="return confirm('Confermi di voler REVOCARE lo status di socio?');">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="action" value="revoca_socio">
                    <button type="submit" class="btn btn-danger">
                        ✗ Revoca Socio
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
