# 📋 Guida ai Log Sistema Documenti

## 🎯 Panoramica

Il sistema di gestione documenti ora include log dettagliati per tracciare ogni fase del processo:

1. **Upload Documenti** (`/soci/me/upload-documento`)
2. **Lista Documenti** (`/soci/me/documenti`)
3. **Creazione Richiesta Servizio** (`/richiesta-servizio`)
4. **Auto-Recovery Documenti** (sistema automatico)

---

## 📍 Dove Trovare i Log

### WordPress Debug Log
**Percorso:** `wp-content/debug.log`

**Abilitare debug logging in `wp-config.php`:**
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Server Log (se disponibile)
- **Apache:** `/var/log/apache2/error.log`
- **Nginx:** `/var/log/nginx/error.log`

---

## 🔍 Log Pattern & Esempi

### 1. Upload Documento

#### Pattern Log:
```
[WECOOP UPLOAD] 📤 Inizio upload documento per user_id: {ID}
[WECOOP UPLOAD] 📎 Tipo documento: {tipo}, File: {nome}, Size: {KB} KB
[WECOOP UPLOAD] ✅ File caricato - Attachment ID: {ID}
[WECOOP UPLOAD] 💾 Meta salvati: documento_socio=yes, socio_id={ID}, tipo_documento={tipo}
[WECOOP UPLOAD] 🎉 Upload completato con successo! URL: {url}
```

#### Esempio Reale:
```
[17-Feb-2026 10:30:15] [WECOOP UPLOAD] 📤 Inizio upload documento per user_id: 37
[17-Feb-2026 10:30:15] [WECOOP UPLOAD] 📎 Tipo documento: carta_identita, File: carta_identita.pdf, Size: 245.67 KB
[17-Feb-2026 10:30:16] [WECOOP UPLOAD] ✅ File caricato - Attachment ID: 127
[17-Feb-2026 10:30:16] [WECOOP UPLOAD] 💾 Meta salvati: documento_socio=yes, socio_id=37, tipo_documento=carta_identita
[17-Feb-2026 10:30:16] [WECOOP UPLOAD] 🎉 Upload completato con successo! URL: https://wecoop.org/wp-content/uploads/2026/02/carta_identita.pdf
```

#### Log di Errore:
```
[17-Feb-2026 10:30:15] [WECOOP UPLOAD] ❌ Utente non autenticato
// oppure
[17-Feb-2026 10:30:15] [WECOOP UPLOAD] ❌ Nessun file nel payload
// oppure
[17-Feb-2026 10:30:16] [WECOOP UPLOAD] ❌ Errore upload: File type not allowed
```

---

### 2. Lista Documenti

#### Pattern Log:
```
[WECOOP DOCUMENTI] 🔍 Richiesta lista documenti per user_id: {ID}
[WECOOP DOCUMENTI] 📊 Trovati {N} documenti per user {ID}
[WECOOP DOCUMENTI] 📄 ID: {ID}, Tipo: {tipo}, File: {nome}
[WECOOP DOCUMENTI] ✅ Response inviata con {N} documenti
```

#### Esempio Reale:
```
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 🔍 Richiesta lista documenti per user_id: 37
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 📊 Trovati 2 documenti per user 37
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 📄 ID: 127, Tipo: carta_identita, File: carta_identita.pdf
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 📄 ID: 128, Tipo: codice_fiscale, File: codice_fiscale.jpg
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] ✅ Response inviata con 2 documenti
```

#### Log Utente Senza Documenti:
```
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 🔍 Richiesta lista documenti per user_id: 37
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] 📊 Trovati 0 documenti per user 37
[17-Feb-2026 10:35:20] [WECOOP DOCUMENTI] ✅ Response inviata con 0 documenti
```

---

### 3. Creazione Richiesta Servizio

#### Pattern Log Completo:
```
[WECOOP API] 🎉 ========== NUOVA RICHIESTA SERVIZIO ==========
[WECOOP API] 📝 Servizio: {nome}, Categoria: {categoria}
[WECOOP API] 👤 User ID: {ID}
[WECOOP API] 🎫 Socio ID: {ID o "non impostato"}
[WECOOP API] ✅ Richiesta creata con ID: {ID}
[WECOOP API] 💾 Metadati salvati per richiesta #{ID}
```

#### Esempio Reale:
```
[17-Feb-2026 10:40:00] [WECOOP API] 🎉 ========== NUOVA RICHIESTA SERVIZIO ==========
[17-Feb-2026 10:40:00] [WECOOP API] 📝 Servizio: Permesso di Soggiorno, Categoria: new_application
[17-Feb-2026 10:40:00] [WECOOP API] 👤 User ID: 37
[17-Feb-2026 10:40:00] [WECOOP API] 🎫 Socio ID: non impostato
[17-Feb-2026 10:40:01] [WECOOP API] ✅ Richiesta creata con ID: 446
[17-Feb-2026 10:40:01] [WECOOP API] 💾 Metadati salvati per richiesta #446
```

---

### 4. Auto-Recovery Documenti

#### Pattern Log (Caso Successo):
```
[WECOOP API] 🔍 AUTO-RECOVERY: Nessun documento nel payload, cerco documenti esistenti per user {ID}
[WECOOP API] ✅ AUTO-RECOVERY: Trovati {N} documenti nel profilo utente
[WECOOP API] 📄 AUTO-RECOVERY: Documento #{ID} - Tipo: {tipo}, File: {nome}
[WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id={ID} per attachment #{ID}
[WECOOP API] ✅ AUTO-RECOVERY: Collegato documento {tipo} (ID: {ID}) alla richiesta #{ID}
[WECOOP API] 📦 AUTO-RECOVERY: Salvato meta 'documenti_allegati' con {N} documenti
[WECOOP API] 🎉 AUTO-RECOVERY: Totale documenti collegati dal profilo: {N}
```

#### Esempio Reale (Successo):
```
[17-Feb-2026 10:40:01] [WECOOP API] 🔍 AUTO-RECOVERY: Nessun documento nel payload, cerco documenti esistenti per user 37
[17-Feb-2026 10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Trovati 2 documenti nel profilo utente
[17-Feb-2026 10:40:01] [WECOOP API] 📄 AUTO-RECOVERY: Documento #127 - Tipo: carta_identita, File: carta_identita.pdf
[17-Feb-2026 10:40:01] [WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id=446 per attachment #127
[17-Feb-2026 10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Collegato documento carta_identita (ID: 127) alla richiesta #446
[17-Feb-2026 10:40:01] [WECOOP API] 📄 AUTO-RECOVERY: Documento #128 - Tipo: codice_fiscale, File: codice_fiscale.jpg
[17-Feb-2026 10:40:01] [WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id=446 per attachment #128
[17-Feb-2026 10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Collegato documento codice_fiscale (ID: 128) alla richiesta #446
[17-Feb-2026 10:40:02] [WECOOP API] 📦 AUTO-RECOVERY: Salvato meta 'documenti_allegati' con 2 documenti per richiesta #446
[17-Feb-2026 10:40:02] [WECOOP API] 🎉 AUTO-RECOVERY: Totale documenti collegati dal profilo: 2
```

#### Esempio Reale (Nessun Documento):
```
[17-Feb-2026 10:40:01] [WECOOP API] 🔍 AUTO-RECOVERY: Nessun documento nel payload, cerco documenti esistenti per user 37
[17-Feb-2026 10:40:01] [WECOOP API] ⚠️ AUTO-RECOVERY: Nessun documento trovato nel profilo utente 37
[17-Feb-2026 10:40:01] [WECOOP API] 💡 AUTO-RECOVERY: L'utente deve caricare documenti via /soci/me/upload-documento prima di creare richieste
```

---

### 5. Riepilogo Finale

#### Pattern Log:
```
[WECOOP API] ========== RIEPILOGO RICHIESTA #{ID} ==========
[WECOOP API] 📋 Numero Pratica: {numero}
[WECOOP API] 🎫 Servizio: {nome}
[WECOOP API] 👤 User ID: {ID}
[WECOOP API] 📎 Documenti collegati: {N}
[WECOOP API] 💰 Importo suggerito: {importo o "Non definito"}
[WECOOP API] ✅ Richiesta creata con successo!
[WECOOP API] ================================================
```

#### Esempio Reale:
```
[17-Feb-2026 10:40:02] [WECOOP API] ========== RIEPILOGO RICHIESTA #446 ==========
[17-Feb-2026 10:40:02] [WECOOP API] 📋 Numero Pratica: WECOOP-2026-00003
[17-Feb-2026 10:40:02] [WECOOP API] 🎫 Servizio: Permesso di Soggiorno
[17-Feb-2026 10:40:02] [WECOOP API] 👤 User ID: 37
[17-Feb-2026 10:40:02] [WECOOP API] 📎 Documenti collegati: 2
[17-Feb-2026 10:40:02] [WECOOP API] 💰 Importo suggerito: Non definito
[17-Feb-2026 10:40:02] [WECOOP API] ✅ Richiesta creata con successo!
[17-Feb-2026 10:40:02] [WECOOP API] ================================================
```

---

## 🔄 Flusso Completo con Log

### Scenario: Utente Carica Documenti e Crea Richiesta

```
# 1. Upload primo documento
[10:30:15] [WECOOP UPLOAD] 📤 Inizio upload documento per user_id: 37
[10:30:15] [WECOOP UPLOAD] 📎 Tipo documento: carta_identita, File: carta_identita.pdf, Size: 245.67 KB
[10:30:16] [WECOOP UPLOAD] ✅ File caricato - Attachment ID: 127
[10:30:16] [WECOOP UPLOAD] 💾 Meta salvati: documento_socio=yes, socio_id=37, tipo_documento=carta_identita
[10:30:16] [WECOOP UPLOAD] 🎉 Upload completato con successo!

# 2. Upload secondo documento
[10:32:10] [WECOOP UPLOAD] 📤 Inizio upload documento per user_id: 37
[10:32:10] [WECOOP UPLOAD] 📎 Tipo documento: codice_fiscale, File: codice_fiscale.jpg, Size: 180.23 KB
[10:32:11] [WECOOP UPLOAD] ✅ File caricato - Attachment ID: 128
[10:32:11] [WECOOP UPLOAD] 💾 Meta salvati: documento_socio=yes, socio_id=37, tipo_documento=codice_fiscale
[10:32:11] [WECOOP UPLOAD] 🎉 Upload completato con successo!

# 3. Lista documenti (verifica)
[10:35:20] [WECOOP DOCUMENTI] 🔍 Richiesta lista documenti per user_id: 37
[10:35:20] [WECOOP DOCUMENTI] 📊 Trovati 2 documenti per user 37
[10:35:20] [WECOOP DOCUMENTI] 📄 ID: 127, Tipo: carta_identita, File: carta_identita.pdf
[10:35:20] [WECOOP DOCUMENTI] 📄 ID: 128, Tipo: codice_fiscale, File: codice_fiscale.jpg
[10:35:20] [WECOOP DOCUMENTI] ✅ Response inviata con 2 documenti

# 4. Creazione richiesta servizio
[10:40:00] [WECOOP API] 🎉 ========== NUOVA RICHIESTA SERVIZIO ==========
[10:40:00] [WECOOP API] 📝 Servizio: Permesso di Soggiorno, Categoria: new_application
[10:40:00] [WECOOP API] 👤 User ID: 37
[10:40:01] [WECOOP API] ✅ Richiesta creata con ID: 446
[10:40:01] [WECOOP API] 💾 Metadati salvati per richiesta #446

# 5. Auto-recovery documenti
[10:40:01] [WECOOP API] 🔍 AUTO-RECOVERY: Cerco documenti esistenti per user 37
[10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Trovati 2 documenti nel profilo utente
[10:40:01] [WECOOP API] 📄 AUTO-RECOVERY: Documento #127 - Tipo: carta_identita
[10:40:01] [WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id=446 per attachment #127
[10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Collegato documento carta_identita alla richiesta #446
[10:40:01] [WECOOP API] 📄 AUTO-RECOVERY: Documento #128 - Tipo: codice_fiscale
[10:40:01] [WECOOP API] 🔗 AUTO-RECOVERY: Impostato richiesta_id=446 per attachment #128
[10:40:01] [WECOOP API] ✅ AUTO-RECOVERY: Collegato documento codice_fiscale alla richiesta #446
[10:40:02] [WECOOP API] 📦 AUTO-RECOVERY: Salvato meta 'documenti_allegati' con 2 documenti
[10:40:02] [WECOOP API] 🎉 AUTO-RECOVERY: Totale documenti collegati: 2

# 6. Riepilogo
[10:40:02] [WECOOP API] ========== RIEPILOGO RICHIESTA #446 ==========
[10:40:02] [WECOOP API] 📋 Numero Pratica: WECOOP-2026-00003
[10:40:02] [WECOOP API] 🎫 Servizio: Permesso di Soggiorno
[10:40:02] [WECOOP API] 👤 User ID: 37
[10:40:02] [WECOOP API] 📎 Documenti collegati: 2
[10:40:02] [WECOOP API] ✅ Richiesta creata con successo!
[10:40:02] [WECOOP API] ================================================
```

---

## 🚨 Log di Errore Comuni

### 1. Upload Fallito - File Troppo Grande
```
[WECOOP UPLOAD] 📤 Inizio upload documento per user_id: 37
[WECOOP UPLOAD] 📎 Tipo documento: carta_identita, File: documento_grande.pdf, Size: 15240.50 KB
[WECOOP UPLOAD] ❌ Errore upload: File size exceeds the maximum allowed size
```

### 2. Upload Fallito - Tipo File Non Consentito
```
[WECOOP UPLOAD] 📤 Inizio upload documento per user_id: 37
[WECOOP UPLOAD] 📎 Tipo documento: carta_identita, File: documento.exe, Size: 120.00 KB
[WECOOP UPLOAD] ❌ Errore upload: Sorry, this file type is not permitted for security reasons
```

### 3. Utente Non Autenticato
```
[WECOOP UPLOAD] ❌ Utente non autenticato
// oppure
[WECOOP DOCUMENTI] ❌ Utente non autenticato
// oppure
[WECOOP API] ❌ Utente non autenticato
```

### 4. Nessun Documento Disponibile
```
[WECOOP API] 🔍 AUTO-RECOVERY: Cerco documenti esistenti per user 37
[WECOOP API] ⚠️ AUTO-RECOVERY: Nessun documento trovato nel profilo utente 37
[WECOOP API] 💡 AUTO-RECOVERY: L'utente deve caricare documenti via /soci/me/upload-documento
```

---

## 🛠️ Troubleshooting con i Log

### Problema: "Documenti non visibili in admin"

**Cerca nei log:**
```bash
grep "AUTO-RECOVERY.*user 37" wp-content/debug.log
```

**Interpretazione:**
- ✅ Se vedi `Trovati N documenti` → Auto-recovery ha funzionato
- ❌ Se vedi `Nessun documento trovato` → Utente non ha caricato documenti
- ⚠️ Se non vedi nessun log AUTO-RECOVERY → Endpoint non è stato chiamato

### Problema: "Upload documento fallisce"

**Cerca nei log:**
```bash
grep "WECOOP UPLOAD.*user 37" wp-content/debug.log
```

**Interpretazione:**
- Se vedi `❌ Errore upload: [messaggio]` → Problema specifico nell'upload
- Se vedi solo `Inizio upload` senza `Upload completato` → Script interrotto

### Problema: "Lista documenti vuota"

**Cerca nei log:**
```bash
grep "WECOOP DOCUMENTI.*user 37" wp-content/debug.log
```

**Interpretazione:**
- Se vedi `Trovati 0 documenti` → Utente non ha mai caricato documenti
- Se non vedi log → Endpoint non è stato chiamato

---

## 📊 Monitoraggio

### Comandi Utili

**Monitoraggio real-time:**
```bash
tail -f wp-content/debug.log | grep "WECOOP"
```

**Filtra solo upload:**
```bash
tail -f wp-content/debug.log | grep "WECOOP UPLOAD"
```

**Filtra solo auto-recovery:**
```bash
tail -f wp-content/debug.log | grep "AUTO-RECOVERY"
```

**Conta documenti caricati oggi:**
```bash
grep "$(date +%d-%b-%Y)" wp-content/debug.log | grep "Upload completato" | wc -l
```

**Trova errori upload:**
```bash
grep "WECOOP UPLOAD.*❌" wp-content/debug.log
```

---

## 📝 Note

- Tutti i log includono emoji per facilitare la lettura visiva
- I log sono organizzati gerarchicamente con indentazione logica
- Ogni operazione include timestamp automatico di WordPress
- Log sensibili (URL completi, etc.) sono registrati ma non esposti all'utente
- In produzione, considera di disabilitare `WP_DEBUG_DISPLAY` per non mostrare errori agli utenti

---

**Data documento:** 17 Febbraio 2026  
**Versione:** 1.0  
**Ultimo aggiornamento:** Implementazione logging completo sistema documenti
