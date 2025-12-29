# 🚀 DEPLOYMENT WECOOP USERS PLUGIN

## 📋 Pre-requisiti
- [x] Plugin WeCoop Soci già installato e attivo
- [x] Accesso SSH al server Hostinger
- [x] Git configurato sul server

## 📦 Installazione

### 1. Pull sul Server
```bash
cd ~/domains/www.wecoop.org/public_html
git pull origin main
```

### 2. Verifica File Plugin
```bash
ls -la wp-content/plugins/wecoop-users/
```

Dovresti vedere:
```
wecoop-users.php
assets/css/admin.css
includes/admin/class-users-list-page.php
includes/admin/class-user-detail-page.php
templates/user-detail.php
templates/partials/user-info.php
templates/partials/user-form.php
templates/partials/user-actions.php
README.md
```

### 3. Riavvia PHP (IMPORTANTE!)
```bash
# Hostinger richiede restart PHP per aggiornare OPcache
sudo systemctl restart php8.3-fpm
# oppure dal pannello Hostinger
```

### 4. Attiva Plugin
1. Login admin WordPress: https://www.wecoop.org/wp-admin
2. Vai su **Plugin**
3. Cerca **WeCoop Users**
4. Clicca **Attiva**

### 5. Verifica Menu
Controlla sidebar admin:
- Dovresti vedere nuovo menu **👥 Utenti Registrati**

## ✅ Test Funzionalità

### Test 1: Visualizza Lista
1. Vai su **Utenti Registrati**
2. Verifica statistiche dashboard:
   - Totale utenti
   - Soci attivi
   - Non soci
   - Profili completi

### Test 2: Filtri
1. Prova ricerca per nome
2. Filtra per "Solo Soci"
3. Filtra per ruolo
4. Resetta filtri

### Test 3: Dettaglio Utente
1. Clicca **Dettagli** su un utente
2. Verifica caricamento dati
3. Compila campi mancanti
4. Salva profilo
5. Verifica messaggio successo

### Test 4: Approvazione Socio
1. Seleziona utente con profilo completo
2. Clicca **Approva come SOCIO**
3. Conferma
4. Verifica badge "SOCIO ATTIVO"

### Test 5: Bulk Approval
1. Seleziona checkbox multipli
2. Clicca **Approva Soci Selezionati**
3. Conferma
4. Verifica tutti approvati

### Test 6: WhatsApp Integration
1. Clicca bottone WhatsApp (verde)
2. Verifica apertura WhatsApp Web
3. Controlla messaggio pre-compilato
4. Chiudi senza inviare

## 🐛 Troubleshooting

### Plugin non appare nel menu
```bash
# Verifica permessi file
chmod -R 755 wp-content/plugins/wecoop-users/

# Riattiva plugin
wp plugin deactivate wecoop-users
wp plugin activate wecoop-users
```

### CSS non caricato
```bash
# Svuota cache WordPress
wp cache flush

# Riavvia PHP
sudo systemctl restart php8.3-fpm
```

### Errore "richiesta_socio non trovata"
- Verifica che il plugin **WeCoop Soci** sia attivo
- Controlla che l'utente abbia una `richiesta_socio` associata

### Statistiche a zero
```bash
# Verifica user_meta
wp db query "SELECT * FROM wp_usermeta WHERE meta_key='has_richiesta_socio' LIMIT 5;"
```

## 🔍 Debug Mode

Attiva debug se serve:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Log in: `wp-content/debug.log`

## 📊 Database Queries Utili

### Vedi tutti gli utenti con richiesta_socio
```sql
SELECT u.ID, u.user_login, u.user_email, 
       um1.meta_value as is_socio,
       um2.meta_value as profilo_completo
FROM wp_users u
LEFT JOIN wp_usermeta um1 ON u.ID = um1.user_id AND um1.meta_key = 'is_socio'
LEFT JOIN wp_usermeta um2 ON u.ID = um2.user_id AND um2.meta_key = 'profilo_completo'
WHERE EXISTS (
    SELECT 1 FROM wp_usermeta um3 
    WHERE um3.user_id = u.ID 
    AND um3.meta_key = 'has_richiesta_socio'
)
ORDER BY u.user_registered DESC;
```

### Vedi tutte le richieste_socio
```sql
SELECT p.ID, p.post_author, p.post_date,
       pm1.meta_value as nome,
       pm2.meta_value as cognome,
       pm3.meta_value as telefono
FROM wp_posts p
LEFT JOIN wp_postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'nome'
LEFT JOIN wp_postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'cognome'
LEFT JOIN wp_postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'telefono_completo'
WHERE p.post_type = 'richiesta_socio'
ORDER BY p.post_date DESC;
```

## 🎯 Workflow Post-Deploy

1. ✅ Verifica lista carica correttamente
2. ✅ Testa approvazione singola
3. ✅ Testa approvazione massiva
4. ✅ Testa WhatsApp link
5. ✅ Verifica responsive su mobile
6. ✅ Informa il team del rilascio

## 📞 Contatti

In caso di problemi:
- **GitHub Issues**: https://github.com/AynixTech/wecoop-wordpress/issues
- **Email**: [indirizzo email del team]

## 🎉 Deploy Completato!

Il plugin **WeCoop Users** è ora live su www.wecoop.org!

Funzionalità disponibili:
- 📊 Dashboard statistiche
- 🔍 Ricerca e filtri avanzati
- ✅ Approvazione soci (singola e massiva)
- 💬 Integrazione WhatsApp
- 📱 Responsive design
