# 🔔 Push Notifications - Guida Implementazione Backend WordPress

## ✅ Cosa è stato implementato

### 1. **Tabelle Database**

#### `wp_wecoop_push_tokens`
Memorizza i token FCM degli utenti per l'app Flutter.

| Colonna | Tipo | Descrizione |
|---------|------|-------------|
| `id` | bigint(20) | ID univoco |
| `user_id` | bigint(20) | ID utente WordPress (UNIQUE) |
| `token` | text | Token FCM dall'app Flutter |
| `device_info` | varchar(255) | Info dispositivo (es: "Flutter App - Android") |
| `created_at` | datetime | Data prima registrazione |
| `updated_at` | datetime | Data ultimo aggiornamento |

#### `wp_wecoop_push_logs`
Storico di tutte le notifiche inviate (già esistente).

---

## 📡 API Endpoints Implementati

### 1. POST `/wp-json/push/v1/token`

**Descrizione:** Salva o aggiorna il token FCM dell'utente autenticato.

**Autenticazione:** JWT Bearer Token (dall'app Flutter)

**Headers:**
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**Body:**
```json
{
  "token": "fZ8xK9mN2pL5qR7sT1vW3yA6bC8dE0fG2hI4jK6lM8nO0pQ2rS4tU6vX8yZ0",
  "device_info": "Flutter App - Android 13"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Token salvato con successo",
  "user_id": 123
}
```

**Errori:**
- `401`: Utente non autenticato
- `400`: Token FCM mancante
- `500`: Errore database

---

### 2. DELETE `/wp-json/push/v1/token`

**Descrizione:** Rimuove il token FCM dell'utente (al logout dall'app).

**Autenticazione:** JWT Bearer Token

**Headers:**
```
Authorization: Bearer {jwt_token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Token rimosso con successo",
  "user_id": 123
}
```

**Errori:**
- `401`: Utente non autenticato
- `500`: Errore database

---

### 3. GET `/wp-json/push/v1/tokens` (Admin Only)

**Descrizione:** Lista di tutti i token FCM registrati.

**Autenticazione:** WordPress session (admin)

**Response (200 OK):**
```json
{
  "success": true,
  "count": 45,
  "tokens": [
    {
      "id": "1",
      "user_id": "123",
      "token": "fZ8xK9mN2pL5qR...",
      "device_info": "Flutter App - Android",
      "created_at": "2025-12-20 10:30:00",
      "updated_at": "2025-12-23 08:15:00",
      "user_login": "mario.rossi",
      "user_email": "mario@example.com",
      "display_name": "Mario Rossi"
    }
  ]
}
```

---

## 🔧 Helper Functions Disponibili

### `wecoop_send_push_notification()`

Invia notifica push a uno o più utenti.

```php
<?php
/**
 * @param int|array $user_ids ID utente o array di ID
 * @param string $title Titolo notifica
 * @param string $body Corpo notifica
 * @param string|null $screen Nome schermata Flutter (EventDetail, ServiceDetail, etc.)
 * @param string|null $id ID risorsa (evento_id, servizio_id, etc.)
 * @param array $extra_data Dati extra payload
 * @return array ['success' => bool, 'sent' => int, 'failed' => int]
 */
wecoop_send_push_notification($user_ids, $title, $body, $screen, $id, $extra_data);

// Esempio: Notifica nuovo evento
wecoop_send_push_notification(
    [123, 456, 789],
    'Nuovo Evento: Workshop WordPress',
    'Il 30 dicembre alle 18:00',
    'EventDetail',
    '456'
);
?>
```

### `wecoop_send_push_to_role()`

Invia notifica a tutti gli utenti con un ruolo specifico.

```php
<?php
// Notifica a tutti i soci
wecoop_send_push_to_role(
    'socio',
    'Assemblea Annuale',
    'Assemblea dei soci il 15 gennaio 2026',
    'EventDetail',
    '789'
);
?>
```

### `wecoop_send_push_to_all()`

Invia notifica broadcast a tutti gli utenti.

```php
<?php
wecoop_send_push_to_all(
    'Manutenzione Programmata',
    'Il sito sarà offline domani dalle 02:00 alle 04:00'
);
?>
```

### `wecoop_get_user_fcm_token()`

Ottieni il token FCM di un utente.

```php
<?php
$token = wecoop_get_user_fcm_token(123);
if ($token) {
    echo "Token trovato: " . $token;
}
?>
```

### `wecoop_user_has_fcm_token()`

Verifica se un utente ha un token FCM registrato.

```php
<?php
if (wecoop_user_has_fcm_token(123)) {
    echo "Utente ha l'app installata";
}
?>
```

### `wecoop_count_users_with_tokens()`

Conta quanti utenti hanno l'app installata.

```php
<?php
$count = wecoop_count_users_with_tokens();
echo "Utenti con app installata: " . $count;
?>
```

---

## 🎣 Hooks Automatici Implementati

### 1. Nuovo Evento Pubblicato

**Hook:** `publish_evento`

**Azione:** Invia notifica a tutti i soci quando viene pubblicato un nuovo evento.

```php
<?php
// Automatico quando pubblichi un evento
// Notifica inviata a: tutti gli utenti con ruolo 'socio'
// Screen: EventDetail
// ID: evento_id
?>
```

### 2. Socio Approvato

**Hook:** `wecoop_socio_approved`

**Azione:** Notifica all'utente quando la sua richiesta di adesione viene approvata.

```php
<?php
// Trigger hook nel tuo codice:
do_action('wecoop_socio_approved', $user_id, $socio_data);
?>
```

### 3. Richiesta Servizio - Cambio Stato

**Hook:** `wecoop_richiesta_servizio_status_changed`

**Azione:** Notifica quando cambia lo stato di una richiesta servizio.

```php
<?php
// Trigger hook:
do_action('wecoop_richiesta_servizio_status_changed', $richiesta_id, $old_status, $new_status);

// Stati supportati:
// - approvata
// - rifiutata
// - completata
// - in_lavorazione
?>
```

### 4. Reminder Evento (24h prima)

**Hook:** `wecoop_evento_reminder_24h`

**Azione:** Promemoria ai partecipanti iscritti 24h prima dell'evento.

```php
<?php
// Schedula con WP Cron:
$evento_date = get_post_meta($evento_id, 'data_evento', true);
$reminder_time = strtotime($evento_date) - (24 * 3600); // 24h prima

wp_schedule_single_event($reminder_time, 'wecoop_evento_reminder_24h', [$evento_id]);
?>
```

### 5. Conferma Iscrizione Evento

**Hook:** `wecoop_evento_iscrizione_confermata`

**Azione:** Conferma all'utente l'iscrizione all'evento.

```php
<?php
// Trigger hook quando utente si iscrive:
do_action('wecoop_evento_iscrizione_confermata', $user_id, $evento_id);
?>
```

---

## 📱 Payload Notifiche per Flutter App

### Struttura Base

```json
{
  "notification": {
    "title": "Titolo Notifica",
    "body": "Corpo del messaggio"
  },
  "data": {
    "click_action": "FLUTTER_NOTIFICATION_CLICK",
    "screen": "EventDetail",
    "id": "456"
  }
}
```

### Schermate Flutter Supportate

| `screen` | `id` Required | Descrizione |
|----------|---------------|-------------|
| `EventDetail` | ✅ Yes | Dettaglio evento (id = evento_id) |
| `ServiceDetail` | ✅ Yes | Dettaglio servizio (id = servizio_id) |
| `Profile` | ❌ No | Schermata profilo utente |
| `Notifications` | ❌ No | Lista notifiche |
| `null` | - | Home screen (default) |

---

## 🚀 Esempi Pratici

### Esempio 1: Notifica Manuale

```php
<?php
// Invia notifica manuale da codice WordPress
add_action('init', function() {
    if (isset($_GET['test_push'])) {
        $result = wecoop_send_push_notification(
            123, // User ID
            'Test Notifica',
            'Questa è una notifica di test',
            'Profile'
        );
        
        var_dump($result);
        die();
    }
});
?>
```

### Esempio 2: Notifica Nuovo Commento Evento

```php
<?php
add_action('comment_post', function($comment_id, $comment_approved, $commentdata) {
    if ($comment_approved !== 1) return;
    
    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);
    
    // Solo per eventi
    if ($post->post_type !== 'evento') return;
    
    // Notifica all'autore dell'evento
    $evento_author = $post->post_author;
    
    wecoop_send_push_notification(
        $evento_author,
        'Nuovo Commento',
        $comment->comment_author . ' ha commentato: ' . wp_trim_words($comment->comment_content, 10),
        'EventDetail',
        $post->ID
    );
}, 10, 3);
?>
```

### Esempio 3: Notifica Quote in Scadenza

```php
<?php
// Cron giornaliero per verificare quote in scadenza
add_action('wecoop_daily_quota_check', function() {
    global $wpdb;
    
    // Trova soci con quota in scadenza tra 7 giorni
    $scadenza = date('Y-m-d', strtotime('+7 days'));
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} 
        WHERE meta_key = 'quota_scadenza' 
        AND meta_value = %s",
        $scadenza
    ));
    
    foreach ($results as $row) {
        wecoop_send_push_notification(
            $row->user_id,
            'Quota Associativa in Scadenza',
            'La tua quota associativa scade tra 7 giorni. Rinnova ora!',
            'Profile'
        );
    }
});

// Schedula cron
if (!wp_next_scheduled('wecoop_daily_quota_check')) {
    wp_schedule_event(time(), 'daily', 'wecoop_daily_quota_check');
}
?>
```

---

## 🔐 Sicurezza

### Autenticazione JWT

Il sistema usa JWT Bearer tokens per autenticare le richieste dall'app Flutter:

```php
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

Il token viene validato usando `WeCoop_Auth_Handler::validate_token()`.

### Permessi

- **POST/DELETE `/token`**: Utenti autenticati (JWT o WordPress session)
- **GET `/tokens`**: Solo amministratori (`manage_options`)

---

## 📊 Monitoraggio

### Dashboard Admin

Vai su **Push Notifications → Log** per vedere:
- Totale notifiche inviate
- Notifiche riuscite/fallite
- Utenti con token FCM registrato
- Storico dettagliato invii

### Query Database

```sql
-- Conta utenti con app installata
SELECT COUNT(*) FROM wp_wecoop_push_tokens;

-- Lista utenti con app e ultima connessione
SELECT 
    u.display_name,
    u.user_email,
    t.device_info,
    t.updated_at as last_seen
FROM wp_wecoop_push_tokens t
JOIN wp_users u ON t.user_id = u.ID
ORDER BY t.updated_at DESC;

-- Notifiche inviate oggi
SELECT COUNT(*) 
FROM wp_wecoop_push_logs 
WHERE DATE(sent_at) = CURDATE() 
AND status = 'sent';
```

---

## 🐛 Troubleshooting

### Problema: Token non viene salvato

**Soluzione:**
1. Verifica che la tabella `wp_wecoop_push_tokens` esista
2. Controlla log errori PHP: `wp-content/debug.log`
3. Verifica JWT token valido: usa plugin REST API per testare

### Problema: Notifiche non arrivano

**Soluzione:**
1. Verifica configurazione FCM in **Push Notifications → Impostazioni**
2. Testa connessione FCM: clicca "Testa Connessione"
3. Verifica che l'utente abbia token FCM: `wecoop_user_has_fcm_token($user_id)`
4. Controlla log: **Push Notifications → Log**

### Problema: Errore "Service Account JSON non valido"

**Soluzione:**
1. Scarica nuovo Service Account JSON da Firebase Console
2. Verifica che contenga: `project_id`, `client_email`, `private_key`
3. Copia/incolla senza modifiche in **Impostazioni → Service Account JSON**

---

## ✅ Checklist Installazione

- [x] Plugin attivato
- [x] Tabelle database create (`wp_wecoop_push_tokens`, `wp_wecoop_push_logs`)
- [x] Endpoint API funzionanti (`/push/v1/token`)
- [ ] Configurazione FCM inserita (Server Key o Service Account JSON)
- [ ] Test connessione FCM riuscito
- [ ] App Flutter configurata e testata
- [ ] Almeno un token FCM salvato nel database
- [ ] Test notifica manuale inviata con successo

---

**Versione Backend:** 1.0.0  
**Data:** 23 Dicembre 2025  
**Stato:** ✅ Backend COMPLETO - Pronto per integrazione Flutter
