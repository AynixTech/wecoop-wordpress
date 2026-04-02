# UTF-8 Charset Issues - Diagnostic Guide

## 🔍 Problema

Caratteri accentati (é, à, ù, etc.) vengono visualizzati come `\uXXXX` anziché come caratteri leggibili.
Esempio: `Documento Identitu00e0 Familiare` al posto di `Documento Identità Familiare`

---

## 🎯 Possibili Cause

### 1️⃣ **HTTP Response Header Mancante**
Se la response REST API non dichiara `charset=utf-8`, il browser/app non sa come decodificare.

**Verificare:**
```bash
curl -i https://www.wecoop.org/wp-json/wecoop/v1/test-endpoint
# Cercare: Content-Type: application/json; charset=utf-8
```

**Soluzione:** ✅ La nuova classe `WeCoop_REST_Charset_Handler` forza questo header

### 2️⃣ **Database Charset Non UTF-8**
Se il database MySQL non è UTF-8mb4, i caratteri speciali viene corrotti quando salvati/letti.

**Verificare nel database:**
```sql
SHOW CREATE TABLE wp_posts;
-- Cercare: CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Verificare connessione PHP:**
```sql
SHOW VARIABLES LIKE 'character_set_database';
SHOW VARIABLES LIKE 'collation_database';
```

**Soluzione:**
```sql
-- Converti tutto il database a UTF-8mb4
ALTER DATABASE nomedelta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Per ogni tabella
ALTER TABLE wp_posts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wp_users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wp_postmeta CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wp_usermeta CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**In wp-config.php aggiungi:**
```php
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
```

### 3️⃣ **JSON Encoding Errato**
Se `json_encode()` viene usato senza flag `JSON_UNESCAPED_UNICODE`, produce `\u00e0` che la app non decodifica.

**Verificare in PHP:**
```php
// SBAGLIATO (produce \u00e0):
json_encode(['text' => 'Identità']);

// CORRETTO (produce Identità):
json_encode(['text' => 'Identità'], JSON_UNESCAPED_UNICODE);
```

**Soluzione:** Usare `wp_json_encode()` di WordPress che gestisce UTF-8 automaticamente

### 4️⃣ **App Non Decodifica JSON Correttamente**
Se l'app riceve JSON con `Content-Type: application/json; charset=utf-8` ma non lo decodifica come UTF-8.

**Flutter/Dart:**
```dart
// CORRETTO:
final jsonResponse = utf8.decode(response.bodyBytes);
final data = jsonDecode(jsonResponse);

// SBAGLIATO:
final data = jsonDecode(response.body); // Potrebbe perdere encoding
```

**React Native:**
```javascript
// CORRETTO - il JSON è già corretto se i server lo invia con charset
const data = await response.json();

// Se hai il problema, verifica che response.headers['content-type'] contenga charset=utf-8
```

### 5️⃣ **Meta Fields Non UTF-8**
Se i dati memorizzati in `wp_postmeta` e `wp_usermeta` per documenti/profilelo non sono UTF-8.

**Verificare:**
```php
$nome = get_user_meta($user_id, 'nome', true);
$check = mb_detect_encoding($nome);
error_log("Encoding rilevato: $check");
```

---

## ✅ Soluzioni Implementate

### ✔️ Server-Side (WordPress)

1. **Nuova classe `WeCoop_REST_Charset_Handler`** in `/wp-content/plugins/wecoop-core/includes/`:
   - Forza `Content-Type: application/json; charset=utf-8` su tutte le risposte REST
   - Valida UTF-8 ricorsivamente nei dati prima di rispondere
   - Usa `JSON_UNESCAPED_UNICODE` per evitare escape sequences

2. **Configurazione Database** (da verificare/aggiornare):
   ```php
   // Verifica in wp-config.php
   define('DB_CHARSET', 'utf8mb4');
   define('DB_COLLATE', 'utf8mb4_unicode_ci');
   ```

### ✔️ App-Side (Flutter/React-Native)

**Se vedi ancora il problema:**

1. **Verifica Response Headers:**
   ```dart
   // Flutter
   final response = await http.get(...);
   print(response.headers); // Deve contenere application/json; charset=utf-8
   ```

2. **Decodifica Corretta JSON:**
   ```dart
   // Flutter - CORRETTO
   final jsonString = utf8.decode(response.bodyBytes);
   final decoded = jsonDecode(jsonString);
   
   // React/TypeScript - CORRETTO  
   const response = await fetch(url);
   const contentType = response.headers.get('content-type');
   console.log('Content-Type:', contentType); // Verificare charset
   const data = await response.json();
   ```

3. **Se il server NON invia charset corretto:**
   ```dart
   // Fallback in Flutter
   try {
     final data = jsonDecode(response.body);
   } catch (e) {
     // Se fallisce, prova con conversione UTF-8 forzata
     final jsonString = utf8.decode(response.bodyBytes);
     final data = jsonDecode(jsonString);
   }
   ```

---

## 🧪 Test

### Test Endpoint REST
```bash
# Testa GET mie-richieste
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json; charset=utf-8" \
  https://www.wecoop.org/wp-json/wecoop/v1/mie-richieste

# Verifica charset nella response
# Cercare header: Content-Type: application/json; charset=utf-8
```

### Test Locale PHP
```php
<?php
$dati = ['documento' => 'Documento Identità Familiare'];

// Test 1: json_encode standard 
echo json_encode($dati);
// Output: {"documento":"Documento Identit\u00e0 Familiare"}

// Test 2: con JSON_UNESCAPED_UNICODE
echo json_encode($dati, JSON_UNESCAPED_UNICODE);
// Output: {"documento":"Documento Identità Familiare"} ✅

// Test 3: wp_json_encode
echo wp_json_encode($dati);
// Output: {"documento":"Documento Identità Familiare"} ✅
```

---

## 📋 Checklist di Verifica

- [ ] Database charset è `utf8mb4` su tutte le tabelle
- [ ] `wp-config.php` ha `define('DB_CHARSET', 'utf8mb4')`
- [ ] Nuovo handler charset è caricato nel plugin core
- [ ] Teste endpoint REST con `curl` e verifica header `charset=utf-8`
- [ ] App riceve Response Header con `Content-Type: application/json; charset=utf-8`
- [ ] App decodifica JSON con UTF-8 esplicito (Flutter: `utf8.decode()`)
- [ ] Character visualizzati correttamente dopo le modifiche

---

## 📞 Se Il Problema Persiste

1. **Verifica errori in PHP logs:**
   ```
   tail -100 wp-content/debug.log | grep "charset\|encoding\|utf"
   ```

2. **Prendi screenshot del network tab del browser** per vedere esattamente quali header e data ricevi

3. **Test diretto nel database:**
   ```php
   $result = $wpdb->get_var("SELECT COUNT(*) FROM wp_users WHERE display_name LIKE '%à%'");
   // Se 0, il database non ha nulla con accenti, controllare i tuoi dati
   ```
