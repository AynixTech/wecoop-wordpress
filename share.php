<?php
/**
 * Pagina pubblica di anteprima contenuti WeCoop (per condivisione su WhatsApp,
 * social, ecc.), generica per tutte le sezioni condivisibili.
 *
 * Sezioni supportate: eventi, offerte-lavoro, offerte-formative.
 * (Per gli annunci resta la pagina dedicata annunci.php.)
 *
 * URL amichevoli (via rewrite in .htaccess):
 *     https://www.wecoop.org/eventi/123
 *     https://www.wecoop.org/offerte-lavoro/123
 *     https://www.wecoop.org/offerte-formative/123
 * URL diretto (sempre funzionante):
 *     https://www.wecoop.org/share.php?tipo=eventi&id=123
 *
 * Comportamento:
 *  - Legge il contenuto dal backend Node.
 *  - Genera i meta tag Open Graph per l'anteprima nel messaggio.
 *  - Se l'utente ha l'app installata, il sistema apre l'app (Universal/App Link).
 *  - Se non ha l'app, vede questa pagina con un bottone per scaricarla.
 */

// Backend Node di WeCoop (stesso usato dall'app mobile).
const WECOOP_BACKEND_URL = 'https://wecoop-backend-s9gl.onrender.com/api';

// Link agli store (da aggiornare con gli ID reali quando disponibili).
const WECOOP_APPSTORE_URL = 'https://apps.apple.com/us/app/wecoop/id6766248437';
const WECOOP_PLAYSTORE_URL = 'https://play.google.com/store/apps/details?id=org.wecoop.app';
const WECOOP_SHARE_DEFAULT_IMG = 'https://www.wecoop.org/wp-content/uploads/wecoop-share-default.jpg';

/**
 * Configurazione per sezione: endpoint backend, mappatura campi e label.
 * `wrap` indica se la risposta è annidata in { data: {...} }.
 */
function wecoop_share_config(): array {
    return [
        'eventi' => [
            'endpoint' => '/eventi/',
            'wrap'     => false,
            'titolo'   => ['titolo'],
            'descr'    => ['descrizione'],
            'img'      => ['immagine_copertina', 'copertina_url', 'immagine_url'],
            'citta'    => ['citta', 'luogo'],
            'label'    => 'Evento',
        ],
        'offerte-lavoro' => [
            'endpoint' => '/lavoro/offerte/',
            'wrap'     => true, // risposta { success, data: {...} }
            'titolo'   => ['title', 'titolo'],
            'descr'    => ['description', 'descrizione'],
            'img'      => ['image_url'],
            'citta'    => ['city', 'citta'],
            'label'    => 'Offerta di lavoro',
        ],
        'offerte-formative' => [
            'endpoint' => '/offerte-formative/',
            'wrap'     => false,
            'titolo'   => ['titolo'],
            'descr'    => ['descrizione'],
            'img'      => ['image_url'],
            'citta'    => ['categoria'],
            'label'    => 'Offerta formativa',
        ],
    ];
}

/** Ricava tipo + id da ?tipo=&id= oppure dal path /<tipo>/<id>. */
function wecoop_get_tipo_id(): array {
    $config = wecoop_share_config();
    $tipo = isset($_GET['tipo']) ? preg_replace('/[^a-z\-]/', '', strtolower((string) $_GET['tipo'])) : '';
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if (!$tipo || !$id) {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        foreach (array_keys($config) as $sezione) {
            if (preg_match('#/' . preg_quote($sezione, '#') . '/(\d+)#', $path, $m)) {
                $tipo = $sezione;
                $id = (int) $m[1];
                break;
            }
        }
    }
    return [$tipo, $id];
}

/** Scarica un contenuto dal backend Node. Ritorna array o null. */
function wecoop_fetch(string $url): ?array {
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

/** Primo valore non vuoto tra le chiavi candidate. */
function wecoop_pick(array $data, array $keys): string {
    foreach ($keys as $k) {
        if (isset($data[$k]) && trim((string) $data[$k]) !== '') {
            return (string) $data[$k];
        }
    }
    return '';
}

/** Escape HTML. */
function h($v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$config = wecoop_share_config();
[$tipo, $id] = wecoop_get_tipo_id();

$item = null;
$cfg = $config[$tipo] ?? null;
if ($cfg && $id > 0) {
    $raw = wecoop_fetch(WECOOP_BACKEND_URL . $cfg['endpoint'] . $id);
    if ($raw !== null) {
        $item = ($cfg['wrap'] && isset($raw['data']) && is_array($raw['data'])) ? $raw['data'] : $raw;
    }
}

// Store consigliato in base allo user agent.
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$isIos = (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false);
$storeUrl = $isIos ? WECOOP_APPSTORE_URL : WECOOP_PLAYSTORE_URL;

$pageUrl = 'https://www.wecoop.org/' . $tipo . '/' . $id;

if (!$cfg || !$item) {
    http_response_code(404);
    $titolo = 'Contenuto non disponibile';
    $descrizione = 'Questo contenuto non è più disponibile o è stato rimosso.';
    $immagine = WECOOP_SHARE_DEFAULT_IMG;
    $citta = '';
    $label = 'WeCoop';
} else {
    $titolo = wecoop_pick($item, $cfg['titolo']) ?: 'WeCoop';
    $descrizioneRaw = wecoop_pick($item, $cfg['descr']);
    $descrizione = trim(strip_tags($descrizioneRaw));
    if (mb_strlen($descrizione) > 200) {
        $descrizione = mb_substr($descrizione, 0, 197) . '…';
    }
    if ($descrizione === '') {
        $descrizione = 'Scopri questo contenuto sulla community WeCoop.';
    }
    $immagine = wecoop_pick($item, $cfg['img']) ?: WECOOP_SHARE_DEFAULT_IMG;
    $citta = wecoop_pick($item, $cfg['citta']);
    $label = $cfg['label'];
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
            margin: 0; background: #f4f6f8; color: #1f2933;
        }
        .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px 48px; }
        .card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .cover { width: 100%; aspect-ratio: 16 / 9; object-fit: cover; background: #e4e7eb; display: block; }
        .body { padding: 20px; }
        .kicker { display: inline-block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #1282a8; background: #e6f4f8; padding: 4px 10px; border-radius: 99px; margin-bottom: 12px; }
        h1 { font-size: 22px; margin: 0 0 8px; }
        .meta { color: #52606d; font-size: 14px; margin-bottom: 4px; }
        .desc { font-size: 15px; line-height: 1.6; color: #3e4c59; white-space: pre-line; }
        .cta { display: block; text-align: center; margin-top: 24px; padding: 16px; background: #1282a8; color: #fff; text-decoration: none; border-radius: 12px; font-weight: 700; font-size: 16px; }
        .cta--secondary { background: transparent; color: #1282a8; border: 1.5px solid #1282a8; margin-top: 12px; }
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
                <span class="kicker"><?php echo h($label); ?></span>
                <h1><?php echo h($titolo); ?></h1>
                <?php if (!empty($citta)): ?>
                    <div class="meta">📍 <?php echo h($citta); ?></div>
                <?php endif; ?>
                <div class="desc"><?php echo h($descrizione); ?></div>

                <a class="cta" href="<?php echo h($storeUrl); ?>">Apri nell'app WeCoop</a>
                <a class="cta cta--secondary" href="https://www.wecoop.org">Vai al sito WeCoop</a>
            </div>
        </div>
        <p class="note">Hai l'app installata? Questo link si apre automaticamente nell'app WeCoop.</p>
    </div>
</body>
</html>
