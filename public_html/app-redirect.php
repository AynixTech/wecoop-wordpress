<?php
/**
 * Redirect per Deep Link WeCoop
 * 
 * Questa pagina gestisce il redirect dai link email all'app mobile
 * URL: https://www.wecoop.org/app-redirect.php?link=wecoop://app/pagamento/123
 */

// Carica WordPress
require_once(__DIR__ . '/wp-load.php');

// Ottieni deep link
$deep_link = isset($_GET['link']) ? sanitize_text_field($_GET['link']) : '';
$fallback = isset($_GET['fallback']) ? esc_url_raw($_GET['fallback']) : home_url();

// Valida che sia un link wecoop://
if (!$deep_link || strpos($deep_link, 'wecoop://') !== 0) {
    wp_redirect($fallback);
    exit;
}

// Estrai il path (es. da wecoop://app/pagamento/123 → app/pagamento/123)
$path = str_replace('wecoop://', '', $deep_link);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apertura App WeCoop...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 40px;
            max-width: 500px;
        }
        .logo {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: scale(1.05);
        }
        .manual-link {
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.8;
        }
        .manual-link a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🤝</div>
        <h1>Apertura App WeCoop...</h1>
        <p id="status">Sto aprendo l'app sul tuo dispositivo</p>
        <div class="spinner"></div>
        
        <div id="fallback-message" style="display: none;">
            <p>L'app non si è aperta?</p>
            <a href="<?php echo esc_url($fallback); ?>" class="button">
                Continua sul Web
            </a>
        </div>
        
        <div class="manual-link">
            <p>
                Link diretto: <a href="<?php echo esc_attr($deep_link); ?>"><?php echo esc_html($deep_link); ?></a>
            </p>
        </div>
    </div>

    <script>
        // Tentativo di aprire deep link
        const deepLink = <?php echo json_encode($deep_link); ?>;
        const fallbackUrl = <?php echo json_encode($fallback); ?>;
        
        // Prova ad aprire l'app
        window.location.href = deepLink;
        
        // Dopo 2 secondi, mostra opzione fallback
        setTimeout(() => {
            document.getElementById('status').textContent = "L'app dovrebbe aprirsi automaticamente";
            document.getElementById('fallback-message').style.display = 'block';
        }, 2000);
        
        // Dopo 5 secondi, se ancora qui, redirect a fallback
        setTimeout(() => {
            // Verifica se l'utente è ancora sulla pagina (non ha aperto l'app)
            if (document.hasFocus()) {
                document.getElementById('status').textContent = "Sembra che l'app non sia installata";
                // Non fare auto-redirect, lascia scegliere all'utente
            }
        }, 5000);
    </script>
</body>
</html>
