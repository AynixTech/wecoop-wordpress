# App Integration: Endpoint per Creare e Scaricare PDF CV

Questa guida descrive gli endpoint da chiamare dall'app per generare un CV con AI e ottenere il PDF.

## Base URL consigliata (via WordPress proxy)

Usare il proxy WordPress per centralizzare sicurezza, rate limit e configurazione token BFFE.

- Base: `/wp-json/wecoop/v1`
- Header richiesti:
  - `Content-Type: application/json`
  - `X-WP-Nonce: <nonce>` se la richiesta passa da frontend WordPress
  - `Authorization: Bearer <token-app>` solo se il tuo client usa auth applicativa

## Flusso minimo per ottenere il PDF

1. Chiamare `GET /cv/templates` per ottenere i template disponibili.
2. Quando l'utente arriva all'ultimo step (scelta template), chiamare `POST /cv/preview` con i dati gia compilati per ottenere la preview valorizzata.
3. Chiamare `POST /cv/generate` includendo `config.template` con uno dei template ricevuti.
4. Se `status=generated` e `files.pdfUrl` presente, mostrare subito pulsante download PDF.
5. Se `status=processing`, fare polling su `GET /cv/{cv_id}` ogni 2-3 secondi fino a `generated` (max 60-90s).
6. In alternativa, mostrare storico da `GET /cv` e permettere apertura PDF dai risultati.

---

## 1) Generare CV (crea PDF/DOCX)

**Endpoint**

`POST /wp-json/wecoop/v1/cv/generate`

**Body esempio**

```json
{
  "personalInfo": {
    "firstName": "Mario",
    "lastName": "Rossi",
    "birthDate": "1993-10-12",
    "nationality": "Italian",
    "phone": "+39 3331234567",
    "email": "mario.rossi@email.com",
    "address": "Milano, Italia"
  },
  "education": [
    {
      "title": "Laurea in Economia",
      "institution": "Universita di Milano",
      "country": "Italia",
      "startDate": "2012-09-01",
      "endDate": "2016-07-01",
      "description": "Especializacion en administracion empresarial"
    }
  ],
  "experience": [
    {
      "role": "Contable Junior",
      "company": "ABC SRL",
      "country": "Italia",
      "startDate": "2017-01-01",
      "endDate": "2020-12-31",
      "description": "Gestion contable, conciliaciones y reportes"
    }
  ],
  "languages": [
    {"language": "Italiano", "level": "Madrelingua"},
    {"language": "English", "level": "Buono"}
  ],
  "skills": ["Excel", "SAP", "Comunicacion", "Trabajo en equipo"],
  "jobGoal": {
    "position": "Contable Senior",
    "country": "Italia",
    "availability": "Full time",
    "industry": "Finanzas"
  },
  "config": {
    "template": "formal",
    "cvLanguage": "it",
    "includePhoto": true
  }
}
```

**Response OK (200)**

```json
{
  "ok": true,
  "cvId": "cv_01HXYZ...",
  "status": "generated",
  "previewText": "Resumen del CV generado...",
  "files": {
    "pdfUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.pdf",
    "docxUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.docx"
  },
  "createdAt": "2026-04-07T14:35:00Z"
}
```

**Response validazione (422)**

```json
{
  "ok": false,
  "requestId": "req_xxx",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid required fields",
    "fields": {
      "personalInfo.email": "Invalid email format"
    }
  }
}
```

---

## 0) Lista template disponibili

**Endpoint**

`GET /wp-json/wecoop/v1/cv/templates`

**Query params opzionali**

- `default` (string): forza quale template marcare come predefinito nella risposta.

**Response esempio**

```json
{
  "ok": true,
  "defaultTemplate": "formal",
  "items": [
    {
      "id": "vibrant",
      "name": "Vibrant",
      "htmlUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/vibrant.html",
      "previewEndpoint": "https://www.wecoop.org/wp-json/wecoop/v1/cv/preview?template=vibrant",
      "cssUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/vibrant.css",
      "isDefault": false
    },
    {
      "id": "formal",
      "name": "Formal",
      "htmlUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/formal.html",
      "previewEndpoint": "https://www.wecoop.org/wp-json/wecoop/v1/cv/preview?template=formal",
      "cssUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/formal.css",
      "isDefault": true
    },
    {
      "id": "matrix",
      "name": "Matrix",
      "htmlUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/matrix.html",
      "previewEndpoint": "https://www.wecoop.org/wp-json/wecoop/v1/cv/preview?template=matrix",
      "cssUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/matrix.css",
      "isDefault": false
    },
    {
      "id": "peach",
      "name": "Peach",
      "htmlUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/peach.html",
      "previewEndpoint": "https://www.wecoop.org/wp-json/wecoop/v1/cv/preview?template=peach",
      "cssUrl": "https://www.wecoop.org/wp-content/plugins/wecoop-cv-ai/template_cv/peach.css",
      "isDefault": false
    }
  ]
}
```

Nota:

- `htmlUrl` punta al file template sorgente.
- `previewEndpoint` genera una preview dinamica con i dati correnti del form e il template selezionato.

---

## 0.b) Preview dinamica template (ultimo step)

**Endpoint**

`POST /wp-json/wecoop/v1/cv/preview?template=peach`

**Body**

Stessa struttura del payload CV (anche parziale), usando i dati gia compilati in app.

**Response esempio**

```json
{
  "ok": true,
  "requestId": "req_xxx",
  "template": "peach",
  "html": "<!doctype html><html>...preview valorizzata...</html>"
}
```

---

## 2) Stato/Dettaglio CV

**Endpoint**

`GET /wp-json/wecoop/v1/cv/{cv_id}`

**Uso**

- Polling quando la generazione e asincrona (`status=processing`).
- Mostrare PDF quando `files.pdfUrl` non e null.

**Response esempio**

```json
{
  "ok": true,
  "cvId": "cv_01HXYZ...",
  "status": "generated",
  "files": {
    "pdfUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.pdf",
    "docxUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.docx"
  }
}
```

---

## 3) Lista CV generati

**Endpoint**

`GET /wp-json/wecoop/v1/cv`

**Query params supportati**

- `page` default `1`
- `limit` default `10`, max `50`
- `status` `generated|processing|failed`
- `language` `it|es|en|fr|de|pt|nl|pl|ro|uk|ru|el|sv|cs|hu|tr`

**Esempio**

`GET /wp-json/wecoop/v1/cv?page=1&limit=10&status=generated&language=it`

**Response esempio**

```json
{
  "ok": true,
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 2,
    "totalPages": 1
  },
  "items": [
    {
      "cvId": "cv_01HXYZ...",
      "status": "generated",
      "template": "formal",
      "cvLanguage": "it",
      "createdAt": "2026-04-07T14:35:00Z",
      "files": {
        "pdfUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.pdf",
        "docxUrl": "https://cdn.midominio.com/cv/cv_01HXYZ.docx"
      }
    }
  ]
}
```

---

## Regole client consigliate nell'app

- Considerare riuscita solo se `ok=true`.
- Usare `requestId` (se presente) nei log client e ticket di supporto.
- Gestire codici:
  - `422`: mostrare errori campo per campo (`error.fields`).
  - `429`: mostrare messaggio di rate limit e retry con backoff.
  - `502/5xx`: errore temporaneo, consentire retry.
- Per download PDF usare direttamente `files.pdfUrl` (URL firmata lato BFFE).

## Note sicurezza

- Non loggare in chiaro email, telefono, indirizzo lato app.
- Non esporre chiavi server-side nel client.
- Se usi auth utente, inviare token utente nell'header Authorization.

## Endpoint BFFE diretto (solo se necessario)

Se l'app non passa dal proxy WordPress, gli endpoint equivalenti sono:

- `POST /api/v1/cv/generate`
- `GET /api/v1/cv/{cv_id}`
- `GET /api/v1/cv`

Consigliato comunque il proxy WordPress per uniformita e governance.
