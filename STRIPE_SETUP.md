# 🚨 Stripe Setup - LEGGIMI

## ⚠️ File Secrets Bloccati da GitHub

GitHub ha bloccato il push perché rilevava chiavi Stripe nei file:
1. Documentazione conteneva chiavi esempio
2. Directory `vendor/` di Stripe conteneva chiavi test nel README

## ✅ Soluzione Implementata

### 1. Secrets Rimossi dal Repository

- ❌ Chiavi Stripe NON più in `wp-config.php`
- ✅ Chiavi ora in `wp-config-stripe.php` (escluso da Git)
- ✅ File template `wp-config-stripe.php.example` per riferimento

### 2. Vendor Escluso

- ✅ `vendor/` aggiunto a `.gitignore`
- ✅ Directory esiste localmente ma non viene pushata
- ✅ Stripe SDK funziona localmente

### 3. Come Configurare su Nuovo Server

1. **Clona repository**
```bash
git clone https://github.com/AynixTech/wecoop-wordpress.git
cd wecoop-wordpress
```

2. **Installa Stripe SDK**
```bash
cd wp-content/plugins/wecoop-servizi
composer install  # Installa stripe/stripe-php
```

3. **Configura Chiavi Stripe**
```bash
cp wp-config-stripe.php.example wp-config-stripe.php
nano wp-config-stripe.php  # Inserisci le tue chiavi
```

4. **Chiavi richieste**:
   - `WECOOP_STRIPE_SECRET_KEY` - Da Stripe Dashboard → Developers → API Keys
   - `WECOOP_STRIPE_WEBHOOK_SECRET` - Da Stripe Dashboard → Developers → Webhooks (opzionale)

### 4. File Implementati (Localmente)

✅ `/wp-content/plugins/wecoop-servizi/includes/api/class-stripe-payment-intent.php`
✅ `/wp-content/plugins/wecoop-servizi/includes/class-payment-system.php` (aggiornato)
✅ `/wp-content/plugins/wecoop-servizi/wecoop-servizi.php` (aggiornato)

### 5. Endpoint API Disponibili

```
POST /wp-json/wecoop/v1/create-payment-intent
POST /wp-json/wecoop/v1/stripe-webhook
```

Vedere `API_PAGAMENTI_WECOOP.md` per documentazione completa.

---

## 📝 Note

- Il sistema funziona localmente anche se non committato (vendor locale)
- Su produzione: `composer install` dentro `/wp-content/plugins/wecoop-servizi/`
- Non committare MAI chiavi reali su Git
- GitHub Push Protection è attivo e blocca secrets

---

## 🔧 Quick Setup

```bash
# Sul server
cd /path/to/wordpress/wp-content/plugins/wecoop-servizi
composer require stripe/stripe-php

# Configura secrets
nano ../../wp-config-stripe.php
```

Contenuto `wp-config-stripe.php`:
```php
<?php
define('WECOOP_STRIPE_SECRET_KEY', 'sk_test_YOUR_KEY');
define('WECOOP_STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK');
```

---

Fatto! Il sistema funziona. Non committare vendor né secrets. ✅
