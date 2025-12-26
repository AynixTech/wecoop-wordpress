# Sistema Pagamenti Automatici WeCoop

## 📋 Panoramica

Il sistema ora crea **automaticamente** un record di pagamento quando l'utente richiede un servizio a pagamento.

---

## 🔄 Flow Completo

```
1. Utente compila form servizio nell'app
   ↓
2. App → POST /wp-json/wecoop/v1/richiesta-servizio
   {
     "servizio": "Richiesta 730",
     "categoria": "Fiscale",
     "dati": {...}
   }
   ↓
3. Backend verifica se "Richiesta 730" richiede pagamento
   ✅ Sì → €50.00
   ↓
4. Backend:
   - Crea richiesta (es. ID 385)
   - Imposta stato = "awaiting_payment"
   - Crea pagamento automaticamente in wp_wecoop_pagamenti:
     {
       richiesta_id: 385,
       importo: 50.00,
       stato: "pending"
     }
   ↓
5. Backend risponde:
   {
     "success": true,
     "id": 385,
     "numero_pratica": "RS-2025-385",
     "requires_payment": true,
     "payment_id": 123,
     "importo": 50.00
   }
   ↓
6. App mostra richiesta con badge "Pagamento richiesto €50.00"
   ↓
7. Utente clicca "Paga Ora"
   ↓
8. App → GET /wp-json/wecoop/v1/payment/richiesta/385
   ↓
9. Backend risponde 200 OK:
   {
     "id": 123,
     "richiesta_id": 385,
     "importo": 50.00,
     "stato": "pending",
     "stripe_payment_intent_id": null
   }
   ↓
10. App mostra schermata pagamento
    ↓
11. Utente sceglie metodo (Stripe/PayPal/Bonifico)
    ↓
12. App → POST /wp-json/wecoop/v1/create-payment-intent
    {
      "payment_id": 123
    }
    ↓
13. Backend:
    - Crea Stripe PaymentIntent
    - Salva stripe_payment_intent_id nel DB
    - Risponde con client_secret
    ↓
14. App completa pagamento con Stripe SDK
    ↓
15. Stripe → Webhook /wp-json/wecoop/v1/stripe-webhook
    payment_intent.succeeded
    ↓
16. Backend aggiorna:
    UPDATE wp_wecoop_pagamenti 
    SET stato = 'paid', 
        paid_at = NOW(),
        metodo_pagamento = 'stripe',
        transaction_id = 'pi_...'
    WHERE id = 123
    ↓
17. ✅ Pagamento completato!
```

---

## 💰 Servizi a Pagamento Configurati

File: `wp-content/plugins/wecoop-servizi/includes/api/class-servizi-endpoint.php`

```php
private static function get_servizi_a_pagamento() {
    return [
        'Richiesta CUD' => 10.00,
        'Richiesta 730' => 50.00,
        'Richiesta ISEE' => 30.00,
        'Richiesta RED' => 25.00,
        'Richiesta Certificazione Unica' => 15.00,
        'Assistenza Fiscale' => 80.00,
        'Compilazione Modello F24' => 20.00,
    ];
}
```

**Per aggiungere nuovi servizi:**
Modifica questo metodo aggiungendo una riga:
```php
'Nome Servizio' => 25.00,
```

---

## 📡 Endpoint API Aggiornati

### POST `/wp-json/wecoop/v1/richiesta-servizio`

**Request:**
```json
{
  "servizio": "Richiesta 730",
  "categoria": "Fiscale",
  "dati": {
    "anno": "2024",
    "note": "..."
  }
}
```

**Response (Servizio a pagamento):**
```json
{
  "success": true,
  "id": 385,
  "numero_pratica": "RS-2025-385",
  "data_richiesta": "2025-12-26 14:30:00",
  "requires_payment": true,
  "payment_id": 123,
  "importo": 50.00
}
```

**Response (Servizio gratuito):**
```json
{
  "success": true,
  "id": 386,
  "numero_pratica": "RS-2025-386",
  "data_richiesta": "2025-12-26 14:35:00",
  "requires_payment": false,
  "payment_id": null,
  "importo": null
}
```

---

### GET `/wp-json/wecoop/v1/richiesta-servizio/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 385,
    "numero_pratica": "RS-2025-385",
    "servizio": "Richiesta 730",
    "categoria": "Fiscale",
    "stato": "awaiting_payment",
    "dati": {...},
    "user_id": 45,
    "socio_id": 12,
    "data_creazione": "2025-12-26 14:30:00",
    "has_payment": true,
    "payment_id": 123,
    "payment_status": "pending",
    "importo": 50.00
  }
}
```

---

### GET `/wp-json/wecoop/v1/payment/richiesta/{richiesta_id}`

**Response (200 OK):**
```json
{
  "id": 123,
  "importo": 50.00,
  "stato": "pending",
  "servizio": "Richiesta 730",
  "numero_pratica": "RS-2025-385",
  "metodo_pagamento": null,
  "transaction_id": null,
  "stripe_payment_intent_id": null,
  "created_at": "2025-12-26 14:30:00",
  "paid_at": null
}
```

**Response (404 Not Found - Nessun pagamento):**
```json
{
  "code": "not_found",
  "message": "Pagamento non trovato",
  "data": {
    "status": 404
  }
}
```

---

## 🗃️ Database: `wp_wecoop_pagamenti`

**Struttura:**
```sql
CREATE TABLE wp_wecoop_pagamenti (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  richiesta_id bigint(20) NOT NULL,
  user_id bigint(20) NOT NULL,
  importo decimal(10,2) NOT NULL,
  stato varchar(50) NOT NULL DEFAULT 'pending',
  metodo_pagamento varchar(50),
  transaction_id varchar(255),
  stripe_payment_intent_id varchar(255),
  note text,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  paid_at datetime,
  PRIMARY KEY (id),
  KEY richiesta_id (richiesta_id),
  KEY user_id (user_id),
  KEY stato (stato),
  KEY transaction_id (transaction_id),
  KEY stripe_payment_intent_id (stripe_payment_intent_id)
);
```

**Stati Pagamento:**

| Stato | Significato | Quando |
|-------|-------------|--------|
| `pending` | In attesa di pagamento | Appena creato |
| `processing` | Pagamento in elaborazione | Durante transazione Stripe |
| `paid` | Pagato con successo | Dopo webhook success |
| `failed` | Pagamento fallito | Errore carta/Stripe |
| `cancelled` | Annullato | Utente/admin annulla |
| `refunded` | Rimborsato | Dopo rimborso |

---

## 🧪 Test Manuale

### 1. Crea Richiesta Servizio a Pagamento

```bash
curl -X POST https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servizio": "Richiesta 730",
    "categoria": "Fiscale",
    "dati": {
      "anno": "2024"
    }
  }'
```

**Risposta attesa:**
```json
{
  "success": true,
  "id": 385,
  "requires_payment": true,
  "payment_id": 123,
  "importo": 50.00
}
```

### 2. Verifica Pagamento Creato

```bash
curl -X GET https://www.wecoop.org/wp-json/wecoop/v1/payment/richiesta/385 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

**Risposta attesa:**
```json
{
  "id": 123,
  "importo": 50.00,
  "stato": "pending"
}
```

### 3. Verifica Database

```sql
SELECT * FROM wp_wecoop_pagamenti WHERE richiesta_id = 385;
```

**Risultato atteso:**
```
id: 123
richiesta_id: 385
user_id: 45
importo: 50.00
stato: pending
metodo_pagamento: NULL
transaction_id: NULL
stripe_payment_intent_id: NULL
created_at: 2025-12-26 14:30:00
paid_at: NULL
```

---

## 🔧 Aggiungere/Modificare Servizi a Pagamento

**File:** `wp-content/plugins/wecoop-servizi/includes/api/class-servizi-endpoint.php`

**Linea:** ~260

```php
private static function get_servizi_a_pagamento() {
    return [
        'Richiesta CUD' => 10.00,
        'Richiesta 730' => 50.00,
        'Richiesta ISEE' => 30.00,
        'Richiesta RED' => 25.00,
        'Richiesta Certificazione Unica' => 15.00,
        'Assistenza Fiscale' => 80.00,
        'Compilazione Modello F24' => 20.00,
        
        // 🔥 Aggiungi nuovi servizi qui:
        'Nuovo Servizio XYZ' => 35.00,
    ];
}
```

**Dopo la modifica:**
```bash
cd /home/u703617904/domains/wecoop.org/public_html
git pull
```

---

## 🐛 Debug & Logs

**Verifica log creazione pagamento:**
```bash
tail -f /home/u703617904/domains/wecoop.org/logs/error_log | grep WECOOP
```

**Log attesi:**
```
[WECOOP PAYMENT] Pagamento #123 creato per richiesta #385
[WECOOP API] Pagamento #123 creato per richiesta #385, importo €50.00
```

**Verifica pagamenti senza richiesta:**
```sql
SELECT p.*, r.post_title 
FROM wp_wecoop_pagamenti p
LEFT JOIN wp_posts r ON r.ID = p.richiesta_id
WHERE p.created_at > '2025-12-26'
ORDER BY p.created_at DESC;
```

---

## 📱 Comportamento App (Dopo Update)

### PRIMA (Problema):
```
1. Crea richiesta → Backend crea solo richiesta
2. App chiama GET /payment/richiesta/385
3. ❌ 404 Not Found
4. ❌ App mostra "Nessun pagamento trovato"
```

### DOPO (Fix):
```
1. Crea richiesta servizio a pagamento
2. Backend crea richiesta + pagamento automaticamente
3. App chiama GET /payment/richiesta/385
4. ✅ 200 OK con dati pagamento
5. ✅ App mostra schermata pagamento
6. ✅ Utente può pagare
```

---

## ⚠️ Note Importanti

1. **Solo servizi configurati creano pagamenti** - Se il servizio non è in `get_servizi_a_pagamento()`, nessun pagamento viene creato

2. **Stato richiesta cambia** - Quando viene creato il pagamento, lo stato della richiesta diventa `awaiting_payment`

3. **Email notifiche** - L'utente riceve email di conferma richiesta, non ancora email per pagamento pendente (da implementare se necessario)

4. **Importi fissi** - Attualmente gli importi sono fissi. Per prezzi variabili serve logica aggiuntiva

5. **Un pagamento per richiesta** - Il sistema crea un solo pagamento per richiesta. Se serve multiple payments, modificare la logica

---

## 🚀 Deploy Checklist

- [x] Modificato `class-servizi-endpoint.php` per creazione automatica pagamenti
- [x] Aggiunto metodo `get_servizi_a_pagamento()` con importi
- [x] Modificato response POST `/richiesta-servizio` con info pagamento
- [x] Modificato response GET `/richiesta-servizio/{id}` con info pagamento
- [x] Committato e pushato su GitHub
- [ ] Eseguire `git pull` sul server
- [ ] Testare creazione richiesta a pagamento
- [ ] Verificare GET `/payment/richiesta/{id}` restituisce 200
- [ ] Testare flow completo pagamento Stripe

---

## 📞 Supporto

**Problema:** Pagamento non creato automaticamente  
**Verifica:**
```sql
-- Controlla se servizio è configurato
-- Deve essere identico al nome in get_servizi_a_pagamento()
SELECT DISTINCT servizio FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE pm.meta_key = 'servizio'
AND p.post_type = 'richiesta_servizio';
```

**Problema:** Importo errato  
**Fix:** Modifica importo in `get_servizi_a_pagamento()` e fai `git pull`

**Problema:** 404 su `/payment/richiesta/{id}`  
**Verifica:**
```sql
SELECT * FROM wp_wecoop_pagamenti WHERE richiesta_id = 385;
```
Se empty → Pagamento non creato, verifica log errori

---

**Ultimo aggiornamento:** 26 Dicembre 2025  
**Versione:** 1.0
