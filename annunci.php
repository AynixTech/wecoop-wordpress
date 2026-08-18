<?php
/**
 * Pagina pubblica di anteprima annuncio WeCoop (per condivisione su WhatsApp,
 * social, ecc.).
 *
 * URL amichevole (via rewrite in .htaccess / functions):
 *     https://www.wecoop.org/annunci/123
 * URL diretto (sempre funzionante):
 *     https://www.wecoop.org/annunci.php?id=123
 *
 * Comportamento:
 *  - Legge l'annuncio dal backend Node (GET /api/annunci/:id).
 *  - Genera i meta tag Open Graph così WhatsApp mostra titolo, descrizione e
 *    immagine nell'anteprima del messaggio.
 *  - Se l'utente ha l'app installata, il sistema operativo apre direttamente
 *    l'app grazie all'Universal Link / App Link (file in /.well-known/).
 *  - Se non ha l'app, vede questa pagina con un bottone per scaricarla.
 */

// Backend Node di WeCoop (stesso usato dall'app mobile).
const WECOOP_BACKEND_URL = 'https://wecoop-backend-s9gl.onrender.com/api';

// Link agli store (da aggiornare con gli ID reali quando disponibili).
const WECOOP_APPSTORE_URL = 'https://apps.apple.com/us/app/wecoop/id6766248437';
const WECOOP_PLAYSTORE_URL = 'https://play.google.com/store/apps/details?id=org.wecoop.app';

/** Ricava l'id annuncio da ?id= oppure dal path /annunci/123. */
function wecoop_get_annuncio_id(): int {
    if (isset($_GET['id'])) {
        return (int) $_GET['id'];
    }
    // Fallback: estrae l'ultimo segmento numerico dal path.
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    if (preg_match('#/annunci/(\d+)#', $path, $m)) {
        return (int) $m[1];
    }
    return 0;
}

/** Scarica il dettaglio annuncio dal backend Node. Ritorna array o null. */
function wecoop_fetch_annuncio(int $id): ?array {
    if ($id <= 0) {
        return null;
    }
    $url = WECOOP_BACKEND_URL . '/annunci/' . $id;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || $body === false) {
            return null;
        }
    } else {
        $body = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 8, 'header' => "Accept: application/json\r\n"],
        ]));
        if ($body === false) {
            return null;
        }
    }

    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

/** Escape HTML abbreviato. */
function h($v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$id = wecoop_get_annuncio_id();
$annuncio = wecoop_fetch_annuncio($id);

// Store consigliato in base allo user agent (per il bottone principale).
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$isIos = (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false);
$storeUrl = $isIos ? WECOOP_APPSTORE_URL : WECOOP_PLAYSTORE_URL;

$pageUrl = 'https://www.wecoop.org/annunci/' . $id;

if (!$annuncio) {
    http_response_code(404);
    $titolo = 'Annuncio non disponibile';
    $descrizione = "Questo annuncio non è più disponibile o è stato rimosso.";
    $immagine = 'https://www.wecoop.org/wp-content/uploads/wecoop-share-default.jpg';
} else {
    $titolo = $annuncio['titolo'] ?? 'Annuncio WeCoop';
    $descrizioneRaw = $annuncio['descrizione'] ?? '';
    // Descrizione breve per l'anteprima (max ~200 caratteri, senza tag).
    $descrizione = trim(strip_tags((string) $descrizioneRaw));
    if (mb_strlen($descrizione) > 200) {
        $descrizione = mb_substr($descrizione, 0, 197) . '…';
    }
    if ($descrizione === '') {
        $descrizione = 'Scopri questo annuncio sulla community WeCoop.';
    }
    $immagine = $annuncio['immagine_url']
        ?? $annuncio['copertina_url']
        ?? 'https://www.wecoop.org/wp-content/uploads/wecoop-share-default.jpg';

    $citta = $annuncio['citta'] ?? '';
    $prezzo = $annuncio['prezzo'] ?? $annuncio['prezzo_ingresso'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($titolo); ?> · WeCoop</title>

    <!-- Open Graph: anteprima in WhatsApp / Facebook / Telegram -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="WeCoop">
    <meta property="og:title" content="<?php echo h($titolo); ?>">
    <meta property="og:description" content="<?php echo h($descrizione); ?>">
    <meta property="og:image" content="<?php echo h($immagine); ?>">
    <meta property="og:url" content="<?php echo h($pageUrl); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($titolo); ?>">
    <meta name="twitter:description" content="<?php echo h($descrizione); ?>">
    <meta name="twitter:image" content="<?php echo h($immagine); ?>">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            background: #f4f6f8;
            color: #1f2933;
        }
        .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px 48px; }
        .card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .cover {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            background: #e4e7eb;
            display: block;
        }
        .body { padding: 20px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .meta { color: #52606d; font-size: 14px; margin-bottom: 4px; }
        .price { color: #1282a8; font-weight: 700; font-size: 18px; margin: 8px 0 16px; }
        .desc { font-size: 15px; line-height: 1.6; color: #3e4c59; white-space: pre-line; }
        .cta {
            display: block;
            text-align: center;
            margin-top: 24px;
            padding: 16px;
            background: #1282a8;
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
        }
        .cta--secondary {
            background: transparent;
            color: #1282a8;
            border: 1.5px solid #1282a8;
            margin-top: 12px;
        }
        .brand { text-align: center; margin-bottom: 20px; font-weight: 700; color: #1282a8; font-size: 20px; }
        .note { text-align: center; color: #7b8794; font-size: 13px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">WeCoop</div>
        <div class="card">
            <img class="cover" src="<?php echo h($immagine); ?>" alt="<?php echo h($titolo); ?>">
            <div class="body">
                <h1><?php echo h($titolo); ?></h1>
                <?php if (!empty($citta)): ?>
                    <div class="meta">📍 <?php echo h($citta); ?></div>
                <?php endif; ?>
                <?php if (isset($prezzo) && $prezzo !== null && $prezzo !== ''): ?>
                    <div class="price"><?php echo h(number_format((float) $prezzo, 2, ',', '.')); ?> €</div>
                <?php endif; ?>
                <div class="desc"><?php echo h($descrizione); ?></div>

                <a class="cta" href="<?php echo h($storeUrl); ?>">Apri nell'app WeCoop</a>
                <a class="cta cta--secondary" href="https://www.wecoop.org">Vai al sito WeCoop</a>
            </div>
        </div>
        <p class="note">
            Hai l'app installata? Questo link si apre automaticamente nell'app WeCoop.
        </p>
    </div>
</body>
</html>
