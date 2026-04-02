# WECOOP BFFE - Supporto `soggetto` per API Documenti

Data: 2026-04-01
Stato: Implementato (backend WordPress)

## Obiettivo
Distinguere in modo affidabile i documenti per soggetto:
- `richiedente`
- `familiare`

Questo evita ambiguita su documenti con stesso `tipo_documento` (es. doppia `carta_identita`).

## Cosa e stato implementato

### 1) Upload documento self-service
Endpoint:
- `POST /wp-json/wecoop/v1/soci/me/upload-documento`

Payload multipart supportato:
- `file` (obbligatorio)
- `tipo_documento` (gia esistente)
- `soggetto` (nuovo, consigliato per client aggiornati)

Comportamento:
- Se `soggetto` manca: fallback legacy a `richiedente`
- Se `soggetto` e invalido: `400 Bad Request`

Valori ammessi `soggetto`:
- `richiedente`
- `familiare`

Response include ora anche:
- `tipo_documento`
- `soggetto`

Esempio:

```http
POST /wp-json/wecoop/v1/soci/me/upload-documento
Authorization: Bearer <jwt>
Content-Type: multipart/form-data

file: <binary>
tipo_documento: carta_identita
soggetto: familiare
```

Response esempio:

```json
{
  "success": true,
  "message": "Documento caricato con successo",
  "data": {
    "id": 123,
    "url": "https://.../file.pdf",
    "tipo_documento": "carta_identita",
    "tipo": "carta_identita",
    "soggetto": "familiare",
    "filename": "file.pdf"
  }
}
```

---

### 2) Lettura documenti self-service
Endpoint:
- `GET /wp-json/wecoop/v1/soci/me/documenti`

Ogni item documento include ora sempre:
- `tipo_documento`
- `soggetto`

Per record legacy senza soggetto salvato:
- `soggetto` valorizzato a `richiedente`

Response item esempio:

```json
{
  "id": 123,
  "title": "Documento",
  "filename": "file.pdf",
  "url": "https://.../file.pdf",
  "tipo_documento": "carta_identita",
  "tipo": "carta_identita",
  "soggetto": "familiare",
  "data_upload": "2026-04-01T10:20:30+00:00",
  "data_scadenza": ""
}
```

---

### 3) Filtro per soggetto in lettura
Implementato su:
- `GET /wp-json/wecoop/v1/soci/me/documenti?soggetto=richiedente`
- `GET /wp-json/wecoop/v1/soci/me/documenti?soggetto=familiare`

Comportamento filtro:
- Se `soggetto` presente e valido: filtra
- Se `soggetto` assente: ritorna tutti i documenti
- Se `soggetto` invalido: `400 Bad Request`

Errore esempio:

```json
{
  "code": "invalid_soggetto",
  "message": "Valore soggetto non valido. Valori ammessi: richiedente, familiare",
  "data": {
    "status": 400
  }
}
```

---

### 4) Endpoint admin allineati
Allineati anche gli endpoint admin documenti:
- `GET /wp-json/wecoop/v1/soci/{id}/documenti`
- `POST /wp-json/wecoop/v1/soci/{id}/documenti`

Anche qui:
- supporto `soggetto` in upload
- `soggetto` incluso in lettura
- filtro opzionale `?soggetto=...` in GET

---

### 5) Retrocompatibilita
Confermata:
- Client legacy che non inviano `soggetto` continuano a funzionare
- Default automatico: `richiedente`

---

## Note di integrazione app
1. Inviare sempre `soggetto` sui nuovi upload.
2. In lettura usare `soggetto` come fonte dati affidabile (non filename).
3. Se serve UI separata, usare:
   - `GET .../documenti?soggetto=richiedente`
   - `GET .../documenti?soggetto=familiare`
4. Mantenere compatibilita con campo `tipo` (preservato), ma preferire `tipo_documento` lato nuovo client.

---

## Test minimo consigliato (QA)
1. Upload `carta_identita` con `soggetto=richiedente`.
2. Upload `carta_identita` con `soggetto=familiare`.
3. Verifica lettura completa: presenti entrambi con soggetto corretto.
4. Verifica filtro per soggetto su endpoint `GET /soci/me/documenti`.
5. Verifica fallback legacy: upload senza `soggetto` -> documento letto come `richiedente`.
6. Verifica errore validazione: `soggetto=altro` -> HTTP 400.

---

## Impatto atteso
- Nessuna regressione sui client non aggiornati.
- Distinzione certa tra documenti del richiedente e del familiare.
- Riduzione rischio sovrascritture logiche e mismatch UI.
