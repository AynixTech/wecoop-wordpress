# WeCoop WhatsApp Integration

Plugin WordPress per l'integrazione WhatsApp Business API con WeCoop.

## Funzionalità

✅ Invio automatico messaggio di benvenuto con credenziali dopo registrazione  
✅ Configurazione API tramite pannello WordPress  
✅ Test invio messaggi  
✅ Logging dettagliato per debug  

## Configurazione

### 1. Attiva il plugin
Vai su **Plugin → Plugin installati** e attiva **WeCoop WhatsApp**.

### 2. Configura WhatsApp Business API

1. Vai su **Impostazioni → WhatsApp**
2. Ottieni le credenziali da [Facebook for Developers](https://developers.facebook.com/apps):
   - Seleziona la tua app WhatsApp Business
   - Vai su **WhatsApp → API Setup**
   - Copia il **Token di accesso** (Access Token)
   - Copia il **Phone Number ID**
3. Inserisci le credenziali nella pagina impostazioni
4. Attiva "Invia messaggio di benvenuto"
5. Clicca **Salva impostazioni**

### 3. Test configurazione

Nella stessa pagina trovi la sezione **Test Invio Messaggio**:
- Inserisci un numero di telefono (es: +393331234567)
- Clicca **Invia messaggio di test**
- Controlla WhatsApp: dovresti ricevere un messaggio di conferma

## Messaggio di Benvenuto

Quando un nuovo utente si registra tramite `/wp-json/wecoop/v1/utenti/primo-accesso`, riceve automaticamente un messaggio WhatsApp:

```
🎉 Benvenuto in WeCoop, [Nome]!

La tua registrazione è stata completata con successo.

📱 Le tue credenziali di accesso:
Username: [numero_telefono]
Password: [password_generata]

Puoi ora accedere all'app WeCoop usando queste credenziali.

💡 Conserva queste informazioni in un luogo sicuro.

Benvenuto nella nostra cooperativa! 🤝
```

## Opzioni

| Opzione | Descrizione | Default |
|---------|-------------|---------|
| **API Key** | Token di accesso WhatsApp Business | - |
| **Phone Number ID** | ID del numero WhatsApp Business | - |
| **Invia messaggio di benvenuto** | Abilita/disabilita invio automatico | ✅ Attivo |

## Logging

Tutti i messaggi sono registrati in `wp-content/debug.log` con prefisso `[WHATSAPP]`:

```
[WHATSAPP] Invio messaggio di benvenuto...
[WHATSAPP] Telefono: +393331234567
[WHATSAPP] Messaggio preparato: 🎉 Benvenuto in WeCoop...
[WHATSAPP] Telefono normalizzato: +393331234567
[WHATSAPP] Status code: 200
[WHATSAPP] ✅ Messaggio inviato con successo
```

## Requisiti

- WordPress 6.0+
- PHP 7.4+
- Plugin **WeCoop Core** attivo
- Account WhatsApp Business API

## Supporto

Per problemi o domande, controlla i log WordPress o contatta il team WeCoop.
