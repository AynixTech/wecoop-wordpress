# WECOOP CV API - Quickstart

Documentazione rapida degli endpoint per creare, salvare, recuperare e visualizzare i curriculum.

## Base URL

`/wp-json/wecoop/v1`

## Endpoint disponibili

### 1) Genera e salva CV

**POST** `/wp-json/wecoop/v1/cv/generate`

Usalo per creare il CV e ottenere i file finali (PDF/DOCX) o uno stato `processing`.

Body minimo esempio:

```json
{
  "personalInfo": {
    "firstName": "Mario",
    "lastName": "Rossi",
    "email": "mario.rossi@email.com"
  },
  "experience": [
    {
      "role": "Operatore",
      "company": "ABC"
    }
  ],
  "config": {
    "template": "formal",
    "cvLanguage": "it"
  }
}
```

Risposta tipica:

```json
{
  "ok": true,
  "cvId": "cv_01HXYZ",
  "status": "generated",
  "files": {
    "pdfUrl": "https://.../cv_01HXYZ.pdf",
    "docxUrl": "https://.../cv_01HXYZ.docx"
  }
}
```

### 2) Recupera un CV per ID

**GET** `/wp-json/wecoop/v1/cv/{cv_id}`

Usalo per:
- polling se lo stato e `processing`
- riaprire un CV gia creato

Esempio:

`GET /wp-json/wecoop/v1/cv/cv_01HXYZ`

### 3) Lista CV salvati

**GET** `/wp-json/wecoop/v1/cv`

Query param supportati:
- `page` (default 1)
- `limit` (default 10, max 50)
- `status` (`generated|processing|failed`)
- `language` (`it|es|en|fr|de|pt|nl|pl|ro|uk|ru|el|sv|cs|hu|tr`)

Esempio:

`GET /wp-json/wecoop/v1/cv?page=1&limit=10&status=generated&language=it`

### 4) Catalogo template

**GET** `/wp-json/wecoop/v1/cv/templates`

Query opzionale:
- `default=<template>`

Template attuali:
- `vibrant`
- `formal`
- `matrix`
- `peach`

### 5) Preview HTML del CV

**POST** `/wp-json/wecoop/v1/cv/preview?template=formal`

Accetta un payload CV anche parziale e ritorna HTML di anteprima.

---

## Flusso consigliato in app

1. `GET /cv/templates`
2. `POST /cv/preview` nell'ultimo step di scelta template
3. `POST /cv/generate` per creare/salvare
4. Se `processing`, polling su `GET /cv/{cv_id}` ogni 2-3 secondi
5. Storico documenti con `GET /cv`

---

## Validazioni principali su generate

- Almeno nome: `personalInfo.firstName` o `personalInfo.lastName`
- Email valida obbligatoria: `personalInfo.email`
- Almeno 1 voce tra `experience` o `education`
- `config.template` deve essere uno dei template consentiti
- `config.cvLanguage` deve essere tra le lingue supportate
- payload massimo ~300KB

In caso errore validazione, risposta tipica:
- status HTTP `422`
- `error.code = VALIDATION_ERROR`

---

## Rate limit

- Rate limit attivo per endpoint CV
- Soglia: circa 30 richieste / 10 minuti per IP
- In caso di superamento: HTTP `429` con `RATE_LIMITED`

---

## Note operative

- Gli endpoint sono pubblici a livello REST route (`permission_callback` permissivo), quindi valuta protezione lato infrastruttura/app (token, WAF, gateway).
- Se il servizio upstream non risponde correttamente, il plugin puo attivare fallback locale in alcuni casi (es. generate).
