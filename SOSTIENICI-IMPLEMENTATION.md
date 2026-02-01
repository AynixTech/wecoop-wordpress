# Pagina "Sostienici" - Guida Implementazione

## ✅ File Creati

### 1. Template PHP
- **File:** `wp-content/themes/wecoop/page-sostienici.php`
- **Template Name:** Sostienici
- Include tutte le sezioni richieste con traduzioni multilingua

### 2. Stili CSS
- **File:** `wp-content/themes/wecoop/assets/css/sostienici.css`
- Design responsive mobile-first
- Stili per tutte le sezioni e componenti

### 3. Traduzioni
Aggiornati i seguenti file:
- `wp-content/themes/wecoop/languages/it.json`
- `wp-content/themes/wecoop/languages/en.json`
- `wp-content/themes/wecoop/languages/es.json`

Con le chiavi di traduzione per:
- Hero section
- Sezione 5×1000
- A cosa serve il sostegno
- Altri modi per sostenerci
- Blocco trasparenza

### 4. Header
- **File:** `wp-content/themes/wecoop/header.php`
- Aggiunto link "5×1000" nel top menu
- Link punta a `/sostienici#5x1000`

### 5. Stili Header
- **File:** `wp-content/themes/wecoop/assets/css/header.css`
- Aggiunto stile evidenziato per il link 5×1000

---

## 📋 Passi per Attivare la Pagina

### 1. Creare la Pagina in WordPress

1. Accedi al pannello admin di WordPress
2. Vai su **Pagine → Aggiungi Nuova**
3. Inserisci il titolo: **Sostienici**
4. Imposta lo slug: **sostienici**
5. Nel pannello di destra "Attributi Pagina", seleziona il template: **Sostienici**
6. Pubblica la pagina

### 2. Verificare le Traduzioni

Le traduzioni sono già configurate nei file JSON. Verifica che il sistema di traduzione `theme_translate()` funzioni correttamente.

### 3. Testare la Pagina

Verifica i seguenti elementi:

#### Funzionalità
- [ ] Il bottone "Dona il tuo 5×1000" fa scroll alla sezione #5x1000
- [ ] Il bottone "Copia codice fiscale" copia 97977210158 negli appunti
- [ ] Appare il toast "Codice copiato!" quando si copia
- [ ] Il link "5×1000" nel top menu porta alla sezione corretta

#### Design
- [ ] Layout mobile responsive
- [ ] Codice fiscale ben visibile e grande
- [ ] Sezione 5×1000 è prominente
- [ ] Altri modi per sostenerci è visivamente secondario
- [ ] Tutti i colori e spaziature sono corretti

#### Contenuti
- [ ] Codice Fiscale: **97977210158**
- [ ] IBAN: **IT96O0569601614000008698X43**
- [ ] Email: info@wecoop.org
- [ ] Sito: www.wecoop.org

---

## 🎨 Caratteristiche Implementate

### Hero Section
- Background gradient blu
- Titolo e sottotitolo
- CTA principale con microcopy "È gratis per te"

### Sezione 5×1000
- Codice fiscale in grande evidenza (font monospace)
- Bottone "Copia codice fiscale" con icona
- Toast notification al copy
- Nota "Valido per 730, CU e Modello Redditi"
- Smooth scroll dall'anchor #5x1000

### A Cosa Serve il Tuo Sostegno
- Lista con icone check verdi
- 4 punti chiave

### Altri Modi per Sostenerci
- Visivamente secondario (come richiesto)
- Intestatario, IBAN e causale
- Design più sobrio rispetto al 5×1000

### Blocco Trasparenza
- Informazioni su RUNTS
- Codice fiscale
- Contatti con link

### Menu Header
- Link "5×1000" nel top menu
- Stile evidenziato (blu con padding)
- Link diretto a `/sostienici#5x1000`

---

## 🔧 Personalizzazioni Future

Se necessario modificare:

### Colori
Modificare in `sostienici.css`:
- Blu principale: `#2c5f8d`
- Blu scuro: `#1a3d5c`
- Verde check: `#28a745`

### Testi
Modificare i file JSON in `languages/`:
- `it.json` per italiano
- `en.json` per inglese
- `es.json` per spagnolo

Tutte le chiavi iniziano con `support_page.*`

### Layout
I breakpoint responsive sono:
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

---

## 📱 Note Mobile-First

Il design è ottimizzato per mobile con:
- Font scalabili
- Padding e margini ridotti su mobile
- Codice fiscale facilmente selezionabile
- Bottoni touch-friendly (min 44px altezza)
- Toast notification visibile in basso

---

## ✨ JavaScript Incluso

Il JavaScript per la funzionalità di copia è già incluso nel template PHP `page-sostienici.php`:
- Funzione `copyCF()` per copiare il codice
- Clipboard API con fallback
- Toast notification temporizzata
- Smooth scroll per anchor links

---

## 🎯 Priorità CTA

Come richiesto nelle specifiche:
1. **Primaria:** 5×1000 (prominente, colorata, grande)
2. **Secondaria:** Donazione IBAN (più sobria, meno evidenziata)

Il codice fiscale 5×1000 è sempre la CTA principale in ogni sezione.

---

## 📞 Supporto

Per modifiche o problemi:
1. Verificare che tutti i file siano stati caricati
2. Controllare i permessi dei file
3. Svuotare la cache di WordPress
4. Verificare che il tema sia attivo

---

**Data implementazione:** 1 febbraio 2026  
**Versione:** 1.0
