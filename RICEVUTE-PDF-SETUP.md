# 📄 Sistema Ricevute PDF - Istruzioni Attivazione

## 🚀 Setup Iniziale

### 1. Esegui Migrazione Database

La prima volta, esegui lo script per aggiungere la colonna `receipt_url` alla tabella pagamenti:

```bash
# SSH nel server
ssh u703617904@156.67.218.194

# Naviga alla cartella migrations
cd /home/u703617904/domains/wecoop.org/public_html/wp-content/plugins/wecoop-servizi/migrations/

# Esegui migrazione
/opt/alt/php83/usr/bin/php add-receipt-url-column.php
```

Dovresti vedere:
```
✅ Colonna receipt_url aggiunta con successo!
```

### 2. Configura Dati Associazione

Vai su **wp-admin → Richieste Servizi → ⚙️ Impostazioni**

Compila i campi obbligatori:

- **Nome Associazione**: `WeCoop APS` (o nome completo della cooperativa)
- **Rappresentante Legale**: Nome e cognome del presidente/legale rappresentante
- **Data Iscrizione RUNTS**: Formato `gg/mm/aaaa` (es: `15/03/2023`)

Clicca **💾 Salva Impostazioni**

### 3. Verifica Prerequisiti

Il sistema usa **mPDF** fornito dal plugin **Complianz GDPR**.

Verifica che sia installato e attivo:
```
wp-admin → Plugin → Complianz GDPR/Terms & Conditions
```

Se non presente, installa uno dei due:
- Complianz - GDPR/CCPA Cookie Consent
- Complianz - Terms & Conditions

## 📋 Come Funziona

### Generazione Automatica

1. **Utente completa pagamento** con Stripe
2. **Webhook Stripe** invia `payment_intent.succeeded`
3. **Sistema genera automaticamente** ricevuta PDF
4. **Salva** in `/wp-content/uploads/ricevute/`
5. **URL salvato** in database (`wp_wecoop_pagamenti.receipt_url`)

### Nomenclatura File

```
Ricevuta_{payment_id}_{anno}.pdf

Esempi:
- Ricevuta_123_2025.pdf
- Ricevuta_456_2025.pdf
```

### Numero Ricevuta

```
{payment_id}/{anno}

Esempi:
- 123/2025
- 456/2025
```

## 👀 Visualizzazione

### Admin WordPress

Vai su **Richieste Servizi → 📋 Tutte le Richieste**

Per richieste con pagamento completato (`✅ Pagato`), appare:

```
✅ Pagato
📄 Scarica Ricevuta  [pulsante]
```

Click sul pulsante → Download PDF immediato

### Email Utente

*(Implementazione futura)*

L'email di conferma pagamento includerà:
- Link diretto alla ricevuta
- Istruzioni detraibilità fiscale

## 📄 Contenuto Ricevuta

### Header
```
RICEVUTA PER EROGAZIONI LIBERALI A APS E ETS
da persone fisiche, aziende o enti
```

### Campi Compilati Automaticamente

**Dati Ricevuta:**
- Data pagamento
- Numero ricevuta / Anno

**Dati Associazione:**
- Nome associazione (da impostazioni)
- Rappresentante legale (da impostazioni)
- Data iscrizione RUNTS (da impostazioni)

**Importo:**
- In cifre: `€ 50,00`
- In lettere: `Cinquanta euro`

**Metodo Pagamento:**
- ✓ Carta di credito (se Stripe card)
- ✓ Bonifico bancario (se SEPA)
- (Checkbox precompilati automaticamente)

**Dati Donatore:**
- Nominativo completo
- Indirizzo completo
- CAP, Comune, Provincia
- Codice Fiscale / P.IVA

**Riferimenti:**
- Numero pratica
- Servizio richiesto
- ID Transazione Stripe

**Normativa:**
- Art. 83 D.Lgs. 117/2017 (detraibilità/deducibilità)
- Art. 82 co. 5 D.Lgs. 117/2017 (esenzione bollo)
- GDPR 679/2016 (privacy)

### Info Fiscali Incluse

**Per persone fisiche:**
- Detraibile al 30% fino a € 30.000
- Oppure deducibile al 10% del reddito

**Per enti/aziende:**
- Deducibile al 10% del reddito

**Requisito:**
- Pagamento tracciabile (carta, bonifico, ecc.) ✅

**Esenzione:**
- Esente da imposta di bollo

## 🔧 Troubleshooting

### Ricevuta non generata

**Controlla log Stripe webhook:**
```bash
tail -f /home/u703617904/domains/wecoop.org/public_html/wp-content/debug.log | grep "WECOOP STRIPE"
```

Dovresti vedere:
```
[WECOOP STRIPE] Success: PI pi_xxx, Payment #123
[WECOOP STRIPE] Ricevuta generata: https://www.wecoop.org/wp-content/uploads/ricevute/Ricevuta_123_2025.pdf
```

Se vedi errore:
```
[WECOOP STRIPE] Errore generazione ricevuta: mPDF not found
```

→ Installa plugin Complianz GDPR

### Permessi file

Verifica permessi cartella ricevute:
```bash
ls -la /home/u703617904/domains/wecoop.org/public_html/wp-content/uploads/

# Dovrebbe esistere cartella "ricevute" con permessi 755
```

Se non esiste o permessi errati:
```bash
mkdir -p /home/u703617904/domains/wecoop.org/public_html/wp-content/uploads/ricevute
chmod 755 /home/u703617904/domains/wecoop.org/public_html/wp-content/uploads/ricevute
chown u703617904:u703617904 /home/u703617904/domains/wecoop.org/public_html/wp-content/uploads/ricevute
```

### Dati mancanti nella ricevuta

**Campi utente vuoti?**

Gli utenti devono avere compilato:
- `nome`, `cognome`
- `indirizzo`, `cap`, `comune`, `provincia`
- `codice_fiscale`

Verifica in **wp-admin → Utenti → {utente} → Meta campi**

## 📊 Query Database

### Verifica colonna receipt_url

```sql
DESCRIBE wp57384_wecoop_pagamenti;
```

Dovrebbe includere:
```
receipt_url | varchar(500) | YES | | NULL
```

### Trova pagamenti con ricevuta

```sql
SELECT id, richiesta_id, amount, receipt_url, paid_at
FROM wp57384_wecoop_pagamenti
WHERE status = 'completed'
AND receipt_url IS NOT NULL
ORDER BY paid_at DESC
LIMIT 10;
```

### Rigenera ricevuta manualmente

```php
// In wp-admin → Strumenti → Editor di file temi
$payment_id = 123; // ID pagamento
$result = WeCoop_Ricevuta_PDF::genera_ricevuta($payment_id);

if (is_wp_error($result)) {
    echo 'Errore: ' . $result->get_error_message();
} else {
    echo 'Ricevuta generata: ' . $result['url'];
}
```

## 🎨 Personalizzazione Template

File da modificare:
```
wp-content/plugins/wecoop-servizi/includes/class-ricevuta-pdf.php
```

Metodo `genera_html_ricevuta()` contiene il template HTML.

Dopo modifiche, rigenera ricevute esistenti o testa con nuovo pagamento.

## 📧 Integrazione Email (TODO)

Per includere link ricevuta nell'email di conferma pagamento:

1. Modifica `class-payment-system.php` metodo `send_payment_email()`
2. Aggiungi variabile `receipt_url` ai dati email
3. Modifica template in `class-multilingual-email.php`
4. Aggiungi pulsante "📄 Scarica Ricevuta"

## ✅ Checklist Attivazione

- [ ] Eseguita migrazione database (colonna `receipt_url`)
- [ ] Plugin Complianz GDPR installato e attivo
- [ ] Impostazioni associazione compilate
- [ ] Testato pagamento completo
- [ ] Verificata generazione ricevuta
- [ ] Controllato PDF scaricabile da admin
- [ ] Verificati permessi cartella `/uploads/ricevute/`
- [ ] Testati tutti i campi compilati correttamente

## 📞 Supporto

In caso di problemi, verifica:
1. Log WordPress debug
2. Log Stripe webhook
3. Permessi file system
4. Presenza mPDF
5. Configurazione impostazioni

Per problemi persistenti, contatta sviluppatore con:
- Screenshot errore
- Log completo
- ID payment/richiesta problematico
