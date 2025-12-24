<?php
/**
 * Template Name: Tessera Socio
 * 
 * Pagina personale del socio con tessera digitale
 * 
 * @package WECOOP_CRM
 */

get_header();

// Verifica login
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Verifica se è socio
$numero_tessera = get_user_meta($user_id, 'numero_tessera', true);
if (!$numero_tessera) {
    echo '<div style="text-align: center; padding: 50px;">';
    echo '<h1>Accesso Negato</h1>';
    echo '<p>Solo i soci possono accedere a questa pagina.</p>';
    echo '</div>';
    get_footer();
    exit;
}

// Dati socio
$nome = get_user_meta($user_id, 'nome', true);
$cognome = get_user_meta($user_id, 'cognome', true);
$data_adesione = get_user_meta($user_id, 'data_adesione', true);
$status = get_user_meta($user_id, 'status_socio', true) ?: 'attivo';
$codice_fiscale = get_user_meta($user_id, 'codice_fiscale', true);
$telefono = get_user_meta($user_id, 'telefono', true);
$citta = get_user_meta($user_id, 'citta', true);
$indirizzo = get_user_meta($user_id, 'indirizzo', true);
$quota_pagata = get_user_meta($user_id, 'quota_pagata', true);

// Calcola anni di anzianità
$anni_anzianita = 0;
if ($data_adesione) {
    $data_inizio = new DateTime($data_adesione);
    $data_oggi = new DateTime();
    $anni_anzianita = $data_inizio->diff($data_oggi)->y;
}
?>

<style>
    .tessera-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }
    
    .tessera-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .tessera-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 4s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.6; }
    }
    
    .tessera-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        position: relative;
        z-index: 1;
    }
    
    .logo {
        font-size: 32px;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .tessera-numero {
        background: rgba(255,255,255,0.2);
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 2px;
        backdrop-filter: blur(10px);
    }
    
    .tessera-body {
        position: relative;
        z-index: 1;
    }
    
    .socio-nome {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .socio-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 30px;
        background: rgba(255,255,255,0.15);
        padding: 20px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .info-value {
        font-size: 16px;
        font-weight: 600;
    }
    
    .status-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.95);
        color: #333;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .status-attivo { background: #4caf50; color: white; }
    .status-sospeso { background: #ff9800; color: white; }
    .status-cessato { background: #f44336; color: white; }
    
    .qr-code-section {
        text-align: center;
        background: white;
        padding: 30px;
        border-radius: 15px;
        margin-top: 20px;
    }
    
    .qr-placeholder {
        width: 200px;
        height: 200px;
        background: #f0f0f0;
        margin: 0 auto 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #999;
        border: 3px dashed #ddd;
    }
    
    .info-panel {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .info-panel h2 {
        margin-top: 0;
        color: #667eea;
        font-size: 24px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn {
        padding: 15px 30px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        text-align: center;
        text-decoration: none;
        display: block;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    @media (max-width: 768px) {
        .socio-info {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            grid-template-columns: 1fr;
        }
        
        .socio-nome {
            font-size: 28px;
        }
    }
    
    @media print {
        .action-buttons,
        .no-print {
            display: none !important;
        }
        
        .tessera-container {
            max-width: 100%;
        }
    }
</style>

<div class="tessera-container">
    
    <!-- Tessera Digitale -->
    <div class="tessera-card">
        <div class="status-badge status-<?php echo esc_attr($status); ?>">
            <?php echo $status === 'attivo' ? '✓ Attivo' : ucfirst($status); ?>
        </div>
        
        <div class="tessera-header">
            <div class="logo">🤝 WECOOP</div>
            <div class="tessera-numero"><?php echo esc_html($numero_tessera); ?></div>
        </div>
        
        <div class="tessera-body">
            <div class="socio-nome">
                <?php echo esc_html($nome . ' ' . $cognome); ?>
            </div>
            
            <div class="socio-info">
                <div class="info-item">
                    <div class="info-label">Socio dal</div>
                    <div class="info-value">
                        <?php echo $data_adesione ? date('d/m/Y', strtotime($data_adesione)) : 'N/D'; ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Anzianità</div>
                    <div class="info-value">
                        <?php echo $anni_anzianita; ?> <?php echo $anni_anzianita == 1 ? 'anno' : 'anni'; ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo esc_html($current_user->user_email); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Telefono</div>
                    <div class="info-value"><?php echo esc_html($telefono ?: 'N/D'); ?></div>
                </div>
                
                <?php if ($citta): ?>
                <div class="info-item">
                    <div class="info-label">Città</div>
                    <div class="info-value"><?php echo esc_html($citta); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="info-item">
                    <div class="info-label">Quota Associativa <?php echo date('Y'); ?></div>
                    <div class="info-value">
                        <?php echo $quota_pagata ? '✓ Pagata' : '⚠ Non pagata'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- QR Code -->
    <div class="qr-code-section">
        <h3 style="color: #333; margin-top: 0;">QR Code Tessera</h3>
        <div class="qr-placeholder" id="qrcode">
            <!-- QR Code verrà generato qui -->
        </div>
        <p style="color: #666; font-size: 14px;">
            Mostra questo QR code per verificare la tua adesione
        </p>
    </div>
    
    <!-- Statistiche -->
    <div class="info-panel">
        <h2>📊 Le Tue Statistiche</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $anni_anzianita; ?></div>
                <div class="stat-label">Anni di Anzianità</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $quota_pagata ? '✓' : '✗'; ?></div>
                <div class="stat-label">Quota <?php echo date('Y'); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo ucfirst($status); ?></div>
                <div class="stat-label">Stato Adesione</div>
            </div>
        </div>
    </div>
    
    <!-- Dati Personali -->
    <div class="info-panel">
        <h2>👤 I Tuoi Dati</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div>
                <strong>Nome Completo:</strong><br>
                <?php echo esc_html($nome . ' ' . $cognome); ?>
            </div>
            
            <div>
                <strong>Codice Fiscale:</strong><br>
                <?php echo esc_html($codice_fiscale ?: 'N/D'); ?>
            </div>
            
            <div>
                <strong>Email:</strong><br>
                <?php echo esc_html($current_user->user_email); ?>
            </div>
            
            <div>
                <strong>Telefono:</strong><br>
                <?php echo esc_html($telefono ?: 'N/D'); ?>
            </div>
            
            <?php if ($indirizzo): ?>
            <div style="grid-column: 1 / -1;">
                <strong>Indirizzo:</strong><br>
                <?php echo esc_html($indirizzo); ?>, <?php echo esc_html($citta); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Azioni -->
    <div class="action-buttons no-print">
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ Stampa Tessera
        </button>
        
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-secondary">
            🚪 Logout
        </a>
        
        <a href="<?php echo get_edit_user_link($user_id); ?>" class="btn btn-secondary">
            ✏️ Modifica Profilo
        </a>
        
        <button class="btn btn-primary" onclick="scaricaTessera()">
            💾 Scarica PDF
        </button>
    </div>
    
</div>

<!-- QR Code Generator -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Genera QR Code con dati socio
const qrData = {
    tessera: '<?php echo esc_js($numero_tessera); ?>',
    nome: '<?php echo esc_js($nome . " " . $cognome); ?>',
    email: '<?php echo esc_js($current_user->user_email); ?>',
    status: '<?php echo esc_js($status); ?>',
    data_adesione: '<?php echo esc_js($data_adesione); ?>'
};

new QRCode(document.getElementById("qrcode"), {
    text: JSON.stringify(qrData),
    width: 200,
    height: 200,
    colorDark: "#667eea",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});

// Funzione scarica PDF (opzionale - richiede libreria)
function scaricaTessera() {
    alert('Funzione in sviluppo. Per ora puoi stampare la tessera con il pulsante "Stampa Tessera".');
    // TODO: Implementare con jsPDF o html2pdf
}
</script>

<?php get_footer(); ?>
