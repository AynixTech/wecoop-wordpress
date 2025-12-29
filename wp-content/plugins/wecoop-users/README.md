# WeCoop Users Plugin

Plugin WordPress per la gestione completa degli utenti registrati con funzionalità di approvazione soci e integrazione WhatsApp.

## 📋 Funzionalità

### Lista Utenti
- **Dashboard statistiche** con 4 card:
  - 👥 Totale utenti registrati
  - ✅ Soci attivi
  - ⏳ Non soci
  - 📝 Profili completi

### Filtri e Ricerca
- 🔍 Ricerca per nome, cognome, email, telefono
- 📊 Filtro per stato socio (Tutti / Soci / Non Soci)
- 🎯 Filtro per ruolo WordPress
- 🔄 Reset rapido filtri

### Approvazione Massiva
- ☑️ Selezione multipla utenti
- ✅ Approvazione bulk come soci
- 🎯 Seleziona tutti con un click

### Pagina Dettaglio Utente
- 📋 Info complete utente (ID, username, email, telefono, ruolo)
- ✏️ Form completo profilo con validazione
- ✅ Approvazione singola come socio
- 🚫 Revoca stato socio
- 💬 Bottone WhatsApp diretto

### Integrazione WhatsApp
- 💬 Pulsante per ogni utente nella lista
- 📱 Messaggio personalizzato pre-compilato
- 🔗 Apertura diretta chat WhatsApp

## 🏗️ Struttura

```
wecoop-users/
├── wecoop-users.php              # Main plugin file
├── assets/
│   └── css/
│       └── admin.css             # Stili admin interface
├── includes/
│   └── admin/
│       ├── class-users-list-page.php     # Lista utenti
│       └── class-user-detail-page.php    # Dettaglio utente
└── templates/
    ├── user-detail.php           # Template principale dettaglio
    └── partials/
        ├── user-info.php         # Card info utente
        ├── user-form.php         # Form completa profilo
        └── user-actions.php      # Azioni amministrative
```

## 🔧 Requisiti

- WordPress 6.0+
- PHP 7.4+
- **Plugin WeCoop Soci** (dipendenza per Custom Post Type `richiesta_socio`)

## 📦 Installazione

1. Carica la cartella `wecoop-users` in `/wp-content/plugins/`
2. Attiva il plugin dal menu Plugin di WordPress
3. Trovi il menu **Utenti Registrati** nella sidebar admin

## 🎯 Utilizzo

### Visualizza Lista Utenti
1. Vai su **Utenti Registrati** nel menu admin
2. Vedi statistiche in tempo reale
3. Usa i filtri per trovare utenti specifici

### Approva Socio Singolo
1. Clicca **Dettagli** su un utente
2. Completa il profilo (se necessario)
3. Clicca **Approva come SOCIO**
4. L'utente diventa ruolo `socio` con `is_socio=true`

### Approvazione Massiva
1. Seleziona checkbox degli utenti da approvare
2. Clicca **Approva Soci Selezionati**
3. Conferma azione
4. Tutti gli utenti selezionati diventano soci

### Contatto WhatsApp
1. Clicca pulsante WhatsApp (verde) su ogni utente
2. Si apre WhatsApp con messaggio pre-compilato:
   ```
   Ciao [Nome], sono [Nome Admin] di WeCoop.
   Ti contatto per...
   ```

## 🔐 Campi Profilo

### Obbligatori per Approvazione Socio
- ✅ Nome
- ✅ Cognome
- ✅ Codice Fiscale (16 caratteri)
- ✅ Data di Nascita
- ✅ Luogo di Nascita
- ✅ Indirizzo (via/piazza)
- ✅ Numero Civico
- ✅ CAP
- ✅ Città
- ✅ Provincia (sigla)
- ✅ Nazione

### Validazioni
- **CF**: 16 caratteri alfanumerici maiuscoli
- **CAP**: 5 cifre numeriche
- **Provincia**: 2 lettere maiuscole

## 🎨 Design

- **Responsive**: Funziona su desktop, tablet, mobile
- **Card moderne**: Design pulito con ombre e hover effects
- **Badge colorati**: Status visivo immediato
  - 🟢 Verde: Socio/Completo
  - 🟡 Giallo: Non socio
  - 🔴 Rosso: Incompleto
- **WhatsApp verde**: Colore ufficiale WhatsApp (#25D366)

## 🔄 Workflow Utente

```
1. Registrazione App → Crea utente WordPress
2. Admin vede utente in lista → Profilo INCOMPLETO
3. Admin completa profilo → Profilo COMPLETO
4. Admin approva socio → Diventa SOCIO ATTIVO
```

## 🛠️ Sviluppo

### Costanti Definite
```php
WECOOP_USERS_VERSION         // 1.0.0
WECOOP_USERS_PLUGIN_DIR      // Path assoluto plugin
WECOOP_USERS_PLUGIN_URL      // URL pubblico plugin
WECOOP_USERS_INCLUDES_DIR    // Path includes/
```

### Classi Principali
```php
WeCoop_Users                 // Classe principale singleton
WeCoop_Users_List_Page       // Gestione lista utenti
WeCoop_User_Detail_Page      // Gestione dettaglio utente
```

### Hook WordPress Usati
- `admin_menu`: Aggiunge voci menu
- `admin_init`: Handler form submissions
- `admin_enqueue_scripts`: Carica CSS

## 📊 Query Database

### Statistiche
```php
// Totale utenti con richiesta_socio
$users = get_users(['meta_key' => 'has_richiesta_socio', 'meta_value' => '1']);

// Soci attivi
$soci = get_users(['meta_key' => 'is_socio', 'meta_value' => '1']);

// Profili completi
$completi = get_users(['meta_key' => 'profilo_completo', 'meta_value' => '1']);
```

## 🐛 Debug

Attiva logging WordPress:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Controlla log in: `/wp-content/debug.log`

## 📝 Changelog

### 1.0.0 - 2025-01-XX
- ✨ Release iniziale
- ✅ Lista utenti con statistiche
- ✅ Filtri e ricerca
- ✅ Approvazione massiva
- ✅ Dettaglio utente
- ✅ Integrazione WhatsApp
- ✅ Template modulari
- ✅ CSS responsive

## 👥 Autori

**WeCoop Team**
- Website: https://www.wecoop.org
- GitHub: https://github.com/AynixTech/wecoop-wordpress

## 📄 Licenza

Proprietario - WeCoop © 2025
