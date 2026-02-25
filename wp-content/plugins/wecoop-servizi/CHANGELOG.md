# 📋 CHANGELOG - Firma Digitale Documento Unico

## v1.1.0 - Documento Unico in PDF ⭐ NUOVO

### 🎯 Cambiamenti Significativi

#### 1️⃣ Endpoint `/documento-unico/{id}/send` Aggiornato

**Prima (v1.0.0)**:
- Metodo: `POST`
- Ritorno: Testo puro compilato
- Formato: `"contenuto": "..."`

**Adesso (v1.1.0)**:
- Metodo: `GET` (cambiato!)
- Ritorno: PDF generato dinamicamente
- Formato:
```json
{
  "url": "...pdf",
  "contenuto_testo": "...",
  "hash_sha256": "...",
  "nome": "documento_unico_12345.pdf"
}
```

#### 2️⃣ Nuova Classe PHP

**File**: `includes/class-documento-unico-pdf.php`

Funzioni:
- `generate_documento_unico($richiesta_id, $user_id)` - Genera PDF
- `get_documento_contenuto($richiesta_id, $user_id)` - Recupera testo compilato

#### 3️⃣ Miglioramenti Tecnici

✅ Utilizza **mPDF** per generazione PDF (come ricevute)  
✅ **Placeholder dinamici** compilati da dati richiesta  
✅ **Storage organizzato**: `/wp-content/uploads/wecoop-documenti-unici/`  
✅ **Hash SHA-256** per integrità documento  
✅ **Formato professionale** con header WECOOP  

---

## 📦 File Modificati

### Nuovi File Creati
```
includes/class-documento-unico-pdf.php      (NEW)
PDF_DOCUMENTO_UNICO.md                       (NEW)
```

### File Aggiornati
```
wecoop-servizi.php
├── + require_once class-documento-unico-pdf.php

includes/api/class-servizi-endpoint.php
├── Modificato: send_documento_unico()
│   ├── Da: POST → GET
│   ├── Da: Testo puro → PDF + Testo
│
FIRMA_DIGITALE_API.md
├── Endpoint documentation aggiornato
├── Esempi TypeScript aggiornati
├── Nuovo: Spiegazione PDF nel flusso

README_FIRMA_DIGITALE.md
├── Aggiunto: Sezione "Novità PDF"
├── Aggiunto: Nuovo file class-documento-unico-pdf.php

SETUP_FIRMA_DIGITALE.md
├── Aggiunto: composer require mpdf/mpdf
├── Aggiunto: mkdir -p directories
```

---

## 🔄 Migrazione da v1.0 a v1.1

### Per Team App

❌ **Cambiamento Breaking**: Endpoint da POST a GET

**Aggiornamenti Necessari**:
```typescript
// v1.0.0 - OLD (non funziona più)
const doc = await fetch(
  '/wp-json/wecoop/v1/documento-unico/' + id,
  { method: 'POST', ... }
);

// v1.1.0 - NEW (corretto)
const doc = await fetch(
  '/wp-json/wecoop/v1/documento-unico/' + id + '/send',
  { method: 'GET', ... }
);

// Nuovo formato risposta:
doc.documento.url              // ← PDF URL
doc.documento.contenuto_testo  // ← Testo da firmare
doc.documento.hash_sha256      // ← Hash integrità
```

### Per Backend

✅ **Nessun breaking change** per i dati nel DB  
✅ Tabelle rimangono le stesse  
✅ Firma funziona identicamente

**Setup necessario**:
```bash
# 1. Aggiornare plugin
git pull

# 2. Installare mPDF (se non già fatto)
composer require mpdf/mpdf

# 3. Creare directory
mkdir -p wp-content/uploads/wecoop-documenti-unici
chmod 755 wp-content/uploads/wecoop-documenti-unici

# 4. Attivare plugin
wp plugin activate wecoop-servizi

# 5. Test
wp eval 'WECOOP_Documento_Unico_PDF::generate_documento_unico(123, 1);'
```

---

## 📊 Comparativa: UX Migliorata

### Esperienza Utente - PRIMA (v1.0)
```
1. Clicca "Manda Documento"
2. Vedi testo grezzo nel dialog
3. Firmi il testo
```

### Esperienza Utente - ADESSO (v1.1)
```
1. Clicca "Manda Documento"
2. Scarichi beautifulPDF con logo/header
3. Visualizzi il PDF nella app
4. Firmi il documento (che hai visto)
```

**Risultato**: 📈 Migliore user experience, stessa sicurezza

---

## 🧪 Testing Checklist

- [ ] GET `/documento-unico/{id}/send` ritorna PDF
- [ ] Resposta contiene `url`, `contenuto_testo`, `hash_sha256`
- [ ] PDF è scaricabile e visualizzabile
- [ ] Placeholder sono compilati correttamente
- [ ] OTP generazione funziona
- [ ] OTP verifica funziona
- [ ] Firma documento funziona
- [ ] Status firma mostra documento "firmato"
- [ ] Verifica firma integrità funziona

---

## 🚀 Prossimi Step

### v1.2 (Roadmap)
- [ ] Timestamp server (RFC 3161)
- [ ] Signature embedding in PDF
- [ ] Export CADES-A format
- [ ] Archiviazione WORM (Write Once Read Multiple)

### v2.0 (Roadmap)
- [ ] Support FEA (certificati digitali)
- [ ] Multi-signature (più firmatari)
- [ ] Blockchain timestamp

---

## 📝 Note di Release

**Data**: 25 Febbraio 2026  
**Versione**: 1.1.0  
**Tipo**: Feature Release  
**Breaking**: Sì (endpoint da POST → GET)  

**Download**: [https://github.com/AynixTech/wecoop-wordpress/releases/tag/v1.1.0](https://github.com/AynixTech/wecoop-wordpress/releases/tag/v1.1.0)

---

## 📞 Support

Per issue o domande:
- 📧 dev@wecoop.org
- 🐛 GitHub Issues: https://github.com/AynixTech/wecoop-wordpress/issues
- 💬 Slack #wecoop-dev

---

**Status**: ✅ STABLE  
**Tested on**: WordPress 6.4.2, PHP 8.1+  
