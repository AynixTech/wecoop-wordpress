# 📝 GUIDA RAPIDA - Modifica Profilo Utente

## ✅ Cosa è cambiato

### Prima (versione precedente):
- ❌ Tutti i campi erano obbligatori
- ❌ Non si poteva salvare parzialmente
- ❌ Nessuna gestione documenti

### Ora (nuova versione):
- ✅ **Salvataggio parziale**: Puoi modificare anche solo 1 campo
- ✅ **Upload documenti**: Carta identità, codice fiscale, patente, ecc.
- ✅ **Validazione smart**: Profilo completo verificato dinamicamente
- ✅ **Approvazione condizionata**: Solo se profilo completo

## 🎯 Come funziona

### 1️⃣ Modifica Profilo (Parziale)
```
📋 Campi disponibili:
- Nome
- Cognome  
- Email
- Codice Fiscale
- Data di Nascita
- Luogo di Nascita
- Indirizzo (via, civico, CAP, città, provincia, nazione)

💡 Puoi compilare solo alcuni campi e salvare!
```

**Esempio - Caso d'uso reale:**
```
Scenario: L'utente ha solo telefono, ma manca tutto il resto

Passo 1: Inserisci solo Nome e Cognome → Salva
Passo 2: Poi torni e aggiungi Codice Fiscale → Salva
Passo 3: Poi aggiungi indirizzo completo → Salva
Passo 4: Quando tutti i campi obbligatori (*) sono compilati → Profilo COMPLETO
```

### 2️⃣ Upload Documenti

**Tipi documento supportati:**
- 🪪 Carta d'Identità
- 🔢 Codice Fiscale
- 🚗 Patente di Guida
- 🛂 Passaporto
- 📋 Permesso di Soggiorno
- 📝 Autocertificazione
- 📄 Altro Documento

**Formati file consentiti:**
- JPG / JPEG
- PNG
- PDF

**Dimensione massima:** 5 MB per file

**Dove vengono salvati:**
```
wp-content/uploads/wecoop-users/{user_id}/
```

### 3️⃣ Approvazione Socio

**Condizione necessaria:**
```
✅ TUTTI i campi obbligatori (*) devono essere compilati

Campi obbligatori:
- Nome
- Cognome
- Codice Fiscale
- Data di Nascita
- Luogo di Nascita
- Indirizzo (via/piazza)
- Numero Civico
- CAP
- Città
- Provincia
- Nazione
```

Se manca anche solo 1 campo → Badge "PROFILO INCOMPLETO" → Approvazione disabilitata

## 📸 Screenshot Workflow

### Pagina Dettaglio Utente
```
┌─────────────────────────────────────────────┐
│ 👤 Nome Utente                              │
│ [← Torna alla lista] [📱 Apri WhatsApp]     │
├─────────────────────────────────────────────┤
│                                              │
│ 📋 INFORMAZIONI UTENTE                       │
│ - ID: #37                                    │
│ - Username: 393891733185                     │
│ - Stato: ⏳ NON SOCIO                        │
│ - Profilo: ❌ INCOMPLETO                     │
│                                              │
│ 📎 DOCUMENTI UTENTE                          │
│ - Carica Nuovo Documento                     │
│   [Tipo] [File] [Carica]                     │
│                                              │
│ - Documenti Caricati                         │
│   🪪 Carta Identità | 29/12/2025 | [Visualizza] [Elimina] │
│   🔢 Codice Fiscale | 29/12/2025 | [Visualizza] [Elimina] │
│                                              │
│ ✏️ MODIFICA PROFILO UTENTE                   │
│ Puoi salvare anche solo alcuni campi...     │
│                                              │
│ Nome:          [Mario              ]         │
│ Cognome:       [Rossi              ]         │
│ Email:         [                   ]         │
│ Codice Fiscale:[                   ]         │
│ ...                                          │
│                                              │
│ [💾 Salva Profilo]                           │
│                                              │
│ ⚙️ AZIONI AMMINISTRATIVE                     │
│ ⚠️ Completa il profilo prima di approvare    │
│ [✅ Approva come SOCIO] (disabilitato)       │
│ [💬 Apri Chat WhatsApp]                      │
│                                              │
└─────────────────────────────────────────────┘
```

## 🔄 Esempi Pratici

### Esempio 1: Utente Appena Registrato
```
Stato iniziale:
- Telefono: ✅ (username)
- Nome: ❌
- Cognome: ❌
- Altri campi: ❌

Step 1: Admin compila Nome + Cognome → Salva
Step 2: Admin carica foto Carta Identità
Step 3: Admin compila CF dalla foto → Salva
Step 4: Admin compila indirizzo → Salva
Step 5: Profilo COMPLETO ✅ → Pulsante "Approva" attivo
```

### Esempio 2: Correzione Dati
```
Utente ha scritto CAP sbagliato: 00138 invece di 00139

Admin:
1. Apre dettaglio utente
2. Modifica solo campo CAP: 00139
3. Salva
4. ✅ Fatto! Altri campi non toccati
```

### Esempio 3: Gestione Documenti
```
1. Seleziona "Carta d'Identità" dal dropdown
2. Clicca "Scegli file" → Seleziona foto/PDF
3. Clicca "Carica Documento"
4. File salvato in: wp-content/uploads/wecoop-users/37/carta_identita_1735488234.jpg
5. Appare in tabella documenti con pulsanti:
   - [👁️ Visualizza] → Apre file in nuova tab
   - [🗑️ Elimina] → Rimuove file (con conferma)
```

## 🛡️ Sicurezza

### Validazioni Upload
```php
✅ Solo formati: JPG, PNG, PDF
✅ Max dimensione: 5 MB
✅ File salvati fuori document root
✅ Nonce verification per upload/eliminazione
✅ Capability check (manage_options)
```

### Permessi Directory
```bash
wp-content/uploads/wecoop-users/  → 755
wp-content/uploads/wecoop-users/37/ → 755
carta_identita_1735488234.jpg → 644
```

## 🐛 Troubleshooting

### Errore "File troppo grande"
```
Soluzione: Comprimi immagine o riduci qualità PDF
Tool online: tinypng.com, ilovepdf.com
```

### Errore "Tipo file non consentito"
```
Soluzione: Converti in JPG o PDF
Windows: Paint, Mac: Anteprima
```

### Il pulsante "Approva Socio" è disabilitato
```
Verifica: Tutti i campi obbligatori (*) compilati?
Controlla badge: deve essere "✅ PROFILO COMPLETO"
```

### I documenti non si caricano
```
1. Verifica permessi directory uploads:
   chmod 755 wp-content/uploads/wecoop-users/

2. Verifica spazio disco server

3. Verifica limite upload PHP:
   php.ini → upload_max_filesize = 10M
            → post_max_size = 10M
```

## 🚀 Deployment

Per deployare le modifiche sul server:

```bash
# 1. Sul server
cd ~/domains/www.wecoop.org/public_html
git pull origin main

# 2. Riavvia PHP (importante!)
sudo systemctl restart php8.3-fpm

# 3. Verifica upload directory
mkdir -p wp-content/uploads/wecoop-users
chmod 755 wp-content/uploads/wecoop-users

# 4. Test
# Apri: https://www.wecoop.org/wp-admin/admin.php?page=wecoop-user-detail&user_id=37
```

## 📊 Database

### User Meta salvati
```sql
-- Dati profilo
first_name
last_name
codice_fiscale
data_nascita
luogo_nascita
indirizzo
civico
cap
citta
provincia
nazione

-- Stato
profilo_completo (boolean)
is_socio (boolean)

-- Documenti (array serializzato)
documenti = [
  {
    "tipo": "carta_identita",
    "filename": "carta_identita_1735488234.jpg",
    "filepath": "/absolute/path/to/file.jpg",
    "url": "https://www.wecoop.org/wp-content/uploads/wecoop-users/37/file.jpg",
    "data_upload": "2025-12-29 15:30:45"
  },
  ...
]
```

## ✅ Checklist Post-Deploy

- [ ] Pull eseguito con successo
- [ ] PHP riavviato
- [ ] Directory uploads creata e permessi ok
- [ ] Aperta pagina dettaglio utente
- [ ] Modificato un campo e salvato
- [ ] Caricato un documento di test
- [ ] Visualizzato documento caricato
- [ ] Eliminato documento di test
- [ ] Verificata validazione profilo completo
- [ ] Testato pulsante approvazione socio

## 🎉 Completato!

Il plugin ora supporta:
✅ Modifica parziale profilo
✅ Upload documenti utente
✅ Gestione file (visualizza/elimina)
✅ Validazione profilo completo dinamica
✅ Approvazione condizionata

Commit: `2b546fc9`
