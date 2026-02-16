# Test Upload Documenti - Endpoint API

## 📋 Panoramica

L'endpoint `/wp-json/wecoop/v1/richiesta-servizio` è stato aggiornato per supportare l'upload di documenti in formato multipart/form-data.

## 🔧 Modifiche Implementate

### Backend (WordPress)

✅ **Endpoint modificato:** `/wp-content/plugins/wecoop-servizi/includes/api/class-servizi-endpoint.php`
- Supporto multipart/form-data
- Validazione file (tipo, dimensione)
- Upload in Media Library
- Collegamento documenti a richiesta
- Metadati documento (tipo, scadenza)

✅ **Metabox Admin:** `/wp-content/plugins/wecoop-servizi/includes/post-types/class-richiesta-servizio.php`
- Visualizzazione documenti allegati
- Link per visualizzare/modificare documenti
- Icone per tipo file

### Validazioni Implementate

| Validazione | Valore | Comportamento |
|-------------|--------|---------------|
| Tipi file consentiti | PDF, JPG, PNG | File ignorato se tipo non valido |
| Dimensione massima | 10 MB | File ignorato se troppo grande |
| Prefisso campo | `documento_*` | Solo campi con questo prefisso |

---

## 🧪 Test con cURL

### 1️⃣ Test Base (senza documenti)

```bash
curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "servizio": "caf_tax_assistance",
    "categoria": "tax_mediation",
    "dati": {
      "nome_completo": "Mario Rossi",
      "email": "mario@example.com",
      "telefono": "+39 123 456 789"
    }
  }'
```

**Response attesa:**
```json
{
  "success": true,
  "message": "Richiesta ricevuta con successo",
  "id": 12345,
  "numero_pratica": "WC-20260216-12345",
  "data_richiesta": "2026-02-16 14:30:25",
  "documenti_caricati": [],
  "requires_payment": false,
  "payment_id": null,
  "importo": null
}
```

---

### 2️⃣ Test con 1 Documento PDF

```bash
curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "servizio=immigration_desk" \
  -F "categoria=residence_permit" \
  -F 'dati={"nome_completo":"Mario Rossi","email":"mario@example.com","paese_provenienza":"Ecuador"}' \
  -F "documento_passaporto=@/percorso/locale/passaporto.pdf" \
  -F "scadenza_passaporto=2028-12-31T00:00:00.000Z"
```

**Response attesa:**
```json
{
  "success": true,
  "id": 12346,
  "numero_pratica": "WC-20260216-12346",
  "documenti_caricati": [
    {
      "tipo": "passaporto",
      "attachment_id": 9876,
      "file_name": "passaporto.pdf",
      "url": "https://wecoop.org/wp-content/uploads/2026/02/passaporto.pdf",
      "data_scadenza": "2028-12-31T00:00:00.000Z"
    }
  ],
  "requires_payment": false
}
```

---

### 3️⃣ Test con 3 Documenti (Multi-upload)

```bash
curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "servizio=immigration_desk" \
  -F "categoria=family_reunification" \
  -F 'dati={"nome_completo":"Maria Garcia","email":"maria@example.com"}' \
  -F "documento_passaporto=@/path/to/passaporto.pdf" \
  -F "scadenza_passaporto=2027-06-15T00:00:00.000Z" \
  -F "documento_permesso_soggiorno=@/path/to/permesso.jpg" \
  -F "scadenza_permesso_soggiorno=2025-12-31T00:00:00.000Z" \
  -F "documento_codice_fiscale=@/path/to/cf.pdf"
```

**Response attesa:**
```json
{
  "success": true,
  "id": 12347,
  "numero_pratica": "WC-20260216-12347",
  "documenti_caricati": [
    {
      "tipo": "passaporto",
      "attachment_id": 9877,
      "file_name": "passaporto.pdf",
      "url": "https://wecoop.org/.../passaporto.pdf",
      "data_scadenza": "2027-06-15T00:00:00.000Z"
    },
    {
      "tipo": "permesso_soggiorno",
      "attachment_id": 9878,
      "file_name": "permesso.jpg",
      "url": "https://wecoop.org/.../permesso.jpg",
      "data_scadenza": "2025-12-31T00:00:00.000Z"
    },
    {
      "tipo": "codice_fiscale",
      "attachment_id": 9879,
      "file_name": "cf.pdf",
      "url": "https://wecoop.org/.../cf.pdf",
      "data_scadenza": null
    }
  ]
}
```

---

### 4️⃣ Test File Troppo Grande (dovrebbe fallire)

```bash
# Crea un file di test da 15MB
dd if=/dev/zero of=test_15mb.pdf bs=1M count=15

curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "servizio=test_servizio" \
  -F "categoria=test" \
  -F 'dati={"test":"test"}' \
  -F "documento_test=@test_15mb.pdf"
```

**Comportamento atteso:**
- File viene ignorato (log: "File troppo grande")
- Richiesta creata comunque
- `documenti_caricati = []`

---

### 5️⃣ Test Tipo File Non Consentito

```bash
curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "servizio=test_servizio" \
  -F "categoria=test" \
  -F 'dati={"test":"test"}' \
  -F "documento_test=@documento.docx"
```

**Comportamento atteso:**
- File viene ignorato (log: "Tipo file non consentito")
- Richiesta creata
- `documenti_caricati = []`

---

## 🔍 Verifica Upload

### Da WordPress Admin

1. Vai su **wp-admin → Richieste Servizi**
2. Apri una richiesta che ha documenti
3. Verifica la sezione **"📎 Documenti Allegati"**
4. Clicca su **"Visualizza"** per aprire il file
5. Clicca su **"Modifica"** per vedere dettagli in Media Library

### Da Database (phpMyAdmin)

```sql
-- Verifica documenti allegati
SELECT post_id, meta_key, meta_value 
FROM wp57384_postmeta 
WHERE meta_key = 'documenti_allegati' 
ORDER BY post_id DESC 
LIMIT 5;

-- Verifica documenti in Media Library
SELECT p.ID, p.post_title, pm.meta_value as tipo_documento
FROM wp57384_posts p
LEFT JOIN wp57384_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'tipo_documento'
WHERE p.post_type = 'attachment'
AND pm.meta_value IS NOT NULL
ORDER BY p.ID DESC
LIMIT 10;
```

---

## 📊 Log di Debug

I log sono scritti in `/wp-content/debug.log` (se `WP_DEBUG_LOG` è attivo).

### Log di successo

```
[16-Feb-2026 14:30:25] [WECOOP API] Richiesta #12345 - Trovati 2 file da caricare
[16-Feb-2026 14:30:25] [WECOOP API] 📎 Elaborazione documento: passaporto - passaporto.pdf
[16-Feb-2026 14:30:26] [WECOOP API] ✅ Caricato documento: passaporto (ID: 9876) - https://...
[16-Feb-2026 14:30:26] [WECOOP API] 📎 Elaborazione documento: permesso_soggiorno - permesso.jpg
[16-Feb-2026 14:30:27] [WECOOP API] ✅ Caricato documento: permesso_soggiorno (ID: 9877) - https://...
[16-Feb-2026 14:30:27] [WECOOP API] 📦 Totale documenti caricati: 2
```

### Log di errore

```
[16-Feb-2026 14:35:10] [WECOOP API] ⚠️ File troppo grande: 15.5MB per test_documento
[16-Feb-2026 14:35:15] [WECOOP API] ⚠️ Tipo file non consentito: application/vnd.ms-word per documento_word
[16-Feb-2026 14:35:20] [WECOOP API] ❌ Errore upload documento test: File upload error
```

---

## 🎯 Tipi di Documenti Supportati

| Tipo Documento | Nome Campo | Scadenza? |
|----------------|------------|-----------|
| Passaporto | `documento_passaporto` | Sì |
| Permesso Soggiorno | `documento_permesso_soggiorno` | Sì |
| Codice Fiscale | `documento_codice_fiscale` | No |
| Carta Identità | `documento_carta_identita` | Sì |
| Certificato Residenza | `documento_certificato_residenza` | No |
| Contratto Lavoro | `documento_contratto_lavoro` | No |
| Busta Paga | `documento_busta_paga` | No |

---

## ⚠️ Troubleshooting

### Problema: File non viene caricato

**Possibili cause:**
1. Nome campo non inizia con `documento_`
2. Tipo file non consentito (solo PDF, JPG, PNG)
3. File troppo grande (> 10 MB)
4. Permessi cartella upload mancanti

**Soluzione:**
```bash
# Verifica permessi cartella upload
ls -la wp-content/uploads/

# Se necessario, imposta permessi
chmod 755 wp-content/uploads/
```

### Problema: Token JWT non valido

**Soluzione:**
```bash
# Ottieni nuovo token
curl -X POST "https://www.wecoop.org/wp-json/wecoop/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'
```

### Problema: Multipart non funziona

**Causa:** Server non supporta multipart su endpoint REST

**Soluzione:** Verifica configurazione nginx/Apache:

```nginx
# nginx.conf
client_max_body_size 20M;
```

```apache
# .htaccess
php_value upload_max_filesize 20M
php_value post_max_size 20M
```

---

## 📌 Next Steps

- [ ] Testare upload da app Flutter (dopo deploy)
- [ ] Verificare email notifiche con documenti allegati
- [ ] Implementare antivirus scan (opzionale)
- [ ] Aggiungere compressione automatica immagini
- [ ] Implementare preview documenti in admin

---

## 🔗 Riferimenti

- **Endpoint:** `/wp-json/wecoop/v1/richiesta-servizio`
- **Metodo:** `POST`
- **Auth:** JWT Bearer Token
- **Content-Type:** `multipart/form-data`
- **File modificati:**
  - `wp-content/plugins/wecoop-servizi/includes/api/class-servizi-endpoint.php`
  - `wp-content/plugins/wecoop-servizi/includes/post-types/class-richiesta-servizio.php`

---

*Ultimo aggiornamento: 16 Febbraio 2026*
