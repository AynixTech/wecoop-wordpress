# Sistema Automatico Recupero Documenti

## 📋 Panoramica

Il sistema ora recupera **automaticamente** i documenti dell'utente quando crea una richiesta servizio, senza bisogno di inviarli nuovamente.

## 🔄 Flusso Documenti

### 1️⃣ **Caricamento Iniziale** (Una tantum)
L'utente carica i suoi documenti una volta nel profilo:

```bash
POST /wp-json/wecoop/v1/soci/me/upload-documento
Content-Type: multipart/form-data
Authorization: Bearer YOUR_JWT

file: [FILE]
tipo_documento: carta_identita / codice_fiscale / permesso_soggiorno / altro
```

**Storage:**
- Attachment WordPress con `author = user_id`
- Post meta: `documento_socio = 'yes'`, `socio_id`, `tipo_documento`
- User meta: array `documenti_caricati`

---

### 2️⃣ **Verifica Documenti** (Opzionale)
L'app può verificare quali documenti ha già l'utente:

```bash
GET /wp-json/wecoop/v1/soci/me/documenti
Authorization: Bearer YOUR_JWT
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "title": "carta-identita.pdf",
      "filename": "carta-identita.pdf",
      "url": "https://wecoop.org/wp-content/uploads/2025/01/carta-identita.pdf",
      "tipo": "carta_identita",
      "data_upload": "2025-01-15T10:30:00+00:00",
      "data_scadenza": "2028-12-31"
    },
    {
      "id": 124,
      "tipo": "codice_fiscale",
      "url": "...",
      ...
    }
  ]
}
```

---

### 3️⃣ **Creazione Richiesta** (Documenti Automatici)
Quando l'utente crea una richiesta servizio, i documenti vengono **recuperati automaticamente**:

```bash
POST /wp-json/wecoop/v1/richiesta-servizio
Content-Type: application/json
Authorization: Bearer YOUR_JWT

{
  "servizio": "Supporto Contabile per Partite IVA",
  "categoria": "contabilita",
  "dati": {
    "nome": "Mario Rossi",
    "partita_iva": "12345678901"
  }
}
```

**⚙️ Backend Logic (Automatico):**
1. Crea richiesta servizio
2. Cerca documenti utente: `attachment` con `author = user_id` AND `documento_socio = 'yes'`
3. Associa tutti i documenti trovati: `update_post_meta(attachment_id, 'richiesta_id', post_id)`
4. Salva riferimenti: `update_post_meta(post_id, 'documenti_allegati', array)`

**Response:**
```json
{
  "success": true,
  "id": 456,
  "numero_pratica": "WECOOP-2025-00042",
  "documenti_caricati": [
    {
      "tipo": "carta_identita",
      "attachment_id": 123,
      "file_name": "carta-identita.pdf",
      "url": "https://...",
      "data_scadenza": "2028-12-31"
    },
    {
      "tipo": "codice_fiscale",
      "attachment_id": 124,
      ...
    }
  ]
}
```

---

## 🔍 Codice Implementato

### File: `class-servizi-endpoint.php` (linea ~320)

```php
// ⭐ RECUPERA documenti già caricati dall'utente se non inviati con la richiesta
if (empty($documenti_caricati)) {
    error_log("[WECOOP API] Richiesta ##{$post_id} - Nessun documento caricato con la richiesta, cerco documenti utente esistenti");
    
    // Recupera documenti dal profilo utente
    $documenti_utente = get_posts([
        'post_type' => 'attachment',
        'author' => $current_user_id,
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'documento_socio',
            'value' => 'yes'
        ]]
    ]);
    
    if (!empty($documenti_utente)) {
        error_log("[WECOOP API] ✅ Trovati " . count($documenti_utente) . " documenti nel profilo utente");
        
        foreach ($documenti_utente as $doc) {
            $attachment_id = $doc->ID;
            $tipo_documento = get_post_meta($attachment_id, 'tipo_documento', true);
            $data_scadenza = get_post_meta($attachment_id, 'data_scadenza', true);
            
            // Associa documento alla richiesta
            update_post_meta($attachment_id, 'richiesta_id', $post_id);
            
            $documenti_caricati[] = [
                'tipo' => $tipo_documento ?: 'altro',
                'attachment_id' => $attachment_id,
                'file_name' => basename(get_attached_file($attachment_id)),
                'url' => wp_get_attachment_url($attachment_id),
                'data_scadenza' => $data_scadenza,
            ];
            
            error_log("[WECOOP API] 📎 Collegato documento: {$tipo_documento} (ID: {$attachment_id})");
        }
        
        // Salva riferimenti documenti nella richiesta
        update_post_meta($post_id, 'documenti_allegati', $documenti_caricati);
        error_log("[WECOOP API] 📦 Totale documenti collegati dal profilo: " . count($documenti_caricati));
    } else {
        error_log("[WECOOP API] ℹ️ Nessun documento trovato nel profilo utente");
    }
}
```

### File: `class-soci-endpoint.php` (linea ~238)

**Nuovo Endpoint:**
```php
// 17b. READ: Lista documenti dell'utente corrente (self-service)
register_rest_route('wecoop/v1', '/soci/me/documenti', [
    'methods' => 'GET',
    'callback' => [__CLASS__, 'get_miei_documenti'],
    'permission_callback' => [__CLASS__, 'check_jwt_auth']
]);
```

**Implementazione (linea ~1438):**
```php
public static function get_miei_documenti($request) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Utente non autenticato', ['status' => 401]);
    }
    
    // Recupera documenti dal profilo utente
    $documenti = get_posts([
        'post_type' => 'attachment',
        'author' => $user_id,
        'posts_per_page' => -1,
        'meta_query' => [[
            'key' => 'documento_socio',
            'value' => 'yes'
        ]]
    ]);
    
    $result = [];
    foreach ($documenti as $doc) {
        $result[] = [
            'id' => $doc->ID,
            'title' => $doc->post_title,
            'filename' => basename(get_attached_file($doc->ID)),
            'url' => wp_get_attachment_url($doc->ID),
            'tipo' => get_post_meta($doc->ID, 'tipo_documento', true),
            'data_upload' => get_the_date('c', $doc),
            'data_scadenza' => get_post_meta($doc->ID, 'data_scadenza', true)
        ];
    }
    
    return rest_ensure_response([
        'success' => true,
        'data' => $result
    ]);
}
```

---

## 🧪 Test

### Scenario 1: Utente con Documenti Esistenti

```bash
# 1. Login
curl -X POST https://wecoop.org/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{"username":"39123456789","password":"password123"}'

export TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 2. Verifica documenti esistenti
curl https://wecoop.org/wp-json/wecoop/v1/soci/me/documenti \
  -H "Authorization: Bearer $TOKEN"

# Response:
# {
#   "success": true,
#   "data": [
#     {"id": 123, "tipo": "carta_identita", ...},
#     {"id": 124, "tipo": "codice_fiscale", ...}
#   ]
# }

# 3. Crea richiesta servizio (senza inviare documenti)
curl -X POST https://wecoop.org/wp-json/wecoop/v1/richiesta-servizio \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "servizio": "Supporto Contabile per Partite IVA",
    "categoria": "contabilita",
    "dati": {
      "nome": "Mario Rossi",
      "partita_iva": "12345678901"
    }
  }'

# Response:
# {
#   "success": true,
#   "id": 456,
#   "numero_pratica": "WECOOP-2025-00042",
#   "documenti_caricati": [
#     {"tipo": "carta_identita", "attachment_id": 123, ...},
#     {"tipo": "codice_fiscale", "attachment_id": 124, ...}
#   ]
# }
```

### Scenario 2: Utente Nuovo (Senza Documenti)

```bash
# Crea richiesta senza documenti
curl -X POST https://wecoop.org/wp-json/wecoop/v1/richiesta-servizio \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"servizio": "Consulenza Legale", "dati": {}}'

# Response:
# {
#   "success": true,
#   "id": 457,
#   "numero_pratica": "WECOOP-2025-00043",
#   "documenti_caricati": []  ⬅️ Array vuoto
# }
```

---

## 📊 Verifica Database

### Query 1: Documenti Utente

```sql
-- Documenti caricati da utente ID 37
SELECT p.ID, p.post_title, p.post_author,
       pm1.meta_value AS tipo_documento,
       pm2.meta_value AS socio_id
FROM wp57384_posts p
LEFT JOIN wp57384_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'tipo_documento'
LEFT JOIN wp57384_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'socio_id'
WHERE p.post_type = 'attachment'
  AND p.post_author = 37
  AND EXISTS (
      SELECT 1 FROM wp57384_postmeta
      WHERE post_id = p.ID
        AND meta_key = 'documento_socio'
        AND meta_value = 'yes'
  );
```

### Query 2: Documenti Associati a Richiesta

```sql
-- Documenti collegati a richiesta WECOOP-2026-00001
SELECT p.ID, p.post_title,
       pm1.meta_value AS tipo_documento,
       pm2.meta_value AS richiesta_id
FROM wp57384_posts p
LEFT JOIN wp57384_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'tipo_documento'
LEFT JOIN wp57384_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'richiesta_id'
WHERE p.post_type = 'attachment'
  AND pm2.meta_value = (
      SELECT ID FROM wp57384_posts
      WHERE post_type = 'richiesta_servizio'
        AND post_title LIKE '%WECOOP-2026-00001%'
  );
```

### Query 3: Meta `documenti_allegati`

```sql
-- Array documenti salvati nel meta della richiesta
SELECT p.ID, p.post_title, pm.meta_value AS documenti_allegati
FROM wp57384_posts p
LEFT JOIN wp57384_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'documenti_allegati'
WHERE p.post_type = 'richiesta_servizio'
  AND p.post_title LIKE '%WECOOP-2026-00001%';
```

---

## 📝 Log Debug

### Log di Successo

```log
[WECOOP API] Richiesta #456 - Nessun documento caricato con la richiesta, cerco documenti utente esistenti
[WECOOP API] ✅ Trovati 2 documenti nel profilo utente
[WECOOP API] 📎 Collegato documento: carta_identita (ID: 123)
[WECOOP API] 📎 Collegato documento: codice_fiscale (ID: 124)
[WECOOP API] 📦 Totale documenti collegati dal profilo: 2
```

### Log Utente Senza Documenti

```log
[WECOOP API] Richiesta #457 - Nessun documento caricato con la richiesta, cerco documenti utente esistenti
[WECOOP API] ℹ️ Nessun documento trovato nel profilo utente
```

---

## 🎯 Vantaggi

1. **Utente**: Carica documenti **una sola volta**, non ad ogni richiesta
2. **Performance**: Nessun upload ripetuto di file grandi
3. **UX**: Processo più veloce e semplice
4. **Consistenza**: Gli stessi documenti per tutte le richieste

---

## ⚠️ Caso WECOOP-2026-00001

Se la richiesta esistente non mostra documenti:

1. **Verifica utente**: Quale `user_id` ha creato WECOOP-2026-00001?
2. **Controlla documenti**: Ha documenti caricati nel profilo?
3. **Ricollegamento**: Puoi forzare l'associazione manualmente

```sql
-- 1. Trova ID richiesta
SELECT ID, post_title, post_author
FROM wp57384_posts
WHERE post_type = 'richiesta_servizio'
  AND post_title LIKE '%WECOOP-2026-00001%';

-- Risultato: ID = 789, post_author = 37

-- 2. Trova documenti utente 37
SELECT ID, post_title
FROM wp57384_posts
WHERE post_type = 'attachment'
  AND post_author = 37
  AND EXISTS (
      SELECT 1 FROM wp57384_postmeta
      WHERE post_id = wp57384_posts.ID
        AND meta_key = 'documento_socio'
        AND meta_value = 'yes'
  );

-- Risultato: ID = 123 (carta_identita), 124 (codice_fiscale)

-- 3. Associa manualmente
UPDATE wp57384_postmeta
SET meta_value = '789'
WHERE post_id IN (123, 124)
  AND meta_key = 'richiesta_id';

-- 4. Salva array documenti_allegati
-- (Questo va fatto da wp-admin o via codice PHP)
```

---

## 🚀 Deploy

Per applicare le modifiche:

```bash
cd /Users/aynix/Documents/GitHub/wecoop-wordpress
git add wp-content/plugins/wecoop-servizi/includes/api/class-servizi-endpoint.php
git add wp-content/plugins/wecoop-soci/includes/api/class-soci-endpoint.php
git add DOCUMENTI-AUTO-RECOVERY.md
git commit -m "feat: automatic document recovery for service requests

- Added GET /soci/me/documenti endpoint for listing user documents
- Modified crea_richiesta_servizio() to auto-fetch user documents
- Documents are retrieved from user profile and linked to new requests
- No need to upload documents again for each service request"

git push origin main
```

---

## 📚 Riferimenti

- **Storage documenti**: [upload_documento_identita()](wp-content/plugins/wecoop-soci/includes/api/class-soci-endpoint.php#L1373)
- **Recupero documenti**: [get_documenti_socio()](wp-content/plugins/wecoop-soci/includes/api/class-soci-endpoint.php#L1157)
- **Metabox admin**: [class-richiesta-servizio.php](wp-content/plugins/wecoop-servizi/includes/post-types/class-richiesta-servizio.php#L157-L220)
