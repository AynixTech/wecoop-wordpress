# Verifica Compatibilità API WordPress con App Flutter

## ✅ Endpoint Implementati e Compatibili

### **1. Verifica Socio**
- **App**: `GET /soci/verifica/{email}`
- **Plugin**: ✅ `/soci/verifica/{email}`
- **Response**: `{"success": true, "is_socio": true, "status": "attivo", "data_adesione": "2024-01-10"}`

### **2. Richiesta Adesione Socio**
- **App**: `POST /soci/richiesta`
- **Plugin**: ✅ `/soci/richiesta`
- **Campi obbligatori**: nome, cognome, prefix, telefono, nazionalita, email, privacy_accepted
- **Response**: `{"success": true, "message": "...", "data": {"username": "...", "password": "...", "tessera_url": "..."}}`

### **3. Richiesta Servizio**
- **App**: `POST /richiesta-servizio`
- **Plugin**: ✅ `/richiesta-servizio`
- **Body**: `{"servizio": "...", "categoria": "...", "dati": {...}}`
- **Response**: `{"success": true, "message": "Richiesta ricevuta con successo", "id": 123, "numero_pratica": "WECOOP-2025-00001", "data_richiesta": "..."}`

### **4. Profilo Utente Corrente**
- **App**: `GET /soci/me`
- **Plugin**: ✅ `/soci/me`
- **Response**: `{"success": true, "data": {...tutti i campi socio...}}`

### **5. Lista Soci**
- **App**: `GET /soci?status=attivo&per_page=50&page=1&search=...`
- **Plugin**: ✅ `/soci`
- **Response**: `{"success": true, "data": [...]}`

### **6. Mie Richieste Servizi**
- **App**: `GET /mie-richieste?page=1&per_page=20&stato=...`
- **Plugin**: ✅ `/mie-richieste` (alias aggiunto)
- **Plugin**: ✅ `/richieste-servizi/me` (alternativo)
- **Response**: `{"success": true, "richieste": [...], "pagination": {...}}`

### **7. Dettaglio Richiesta**
- **App**: `GET /richiesta-servizio/{id}`
- **Plugin**: ✅ `/richiesta-servizio/{id}`
- **Response**: `{"success": true, "data": {...}}`

### **8. Completa Profilo**
- **App**: `POST /soci/me/completa-profilo`
- **Plugin**: ✅ `/soci/me/completa-profilo`
- **Campi**: nome, cognome, email, telefono, codice_fiscale, data_nascita, etc.

### **9. Upload Documento**
- **App**: `POST /soci/me/upload-documento`
- **Plugin**: ✅ `/soci/me/upload-documento`
- **Body**: multipart/form-data con file + tipo_documento

### **10. Check Username**
- **App**: `GET /soci/check-username?username={username}`
- **Plugin**: ✅ `/soci/check-username`
- **Response**: `{"esiste": true/false}`

### **11. Reset Password**
- **App**: `POST /soci/reset-password`
- **Plugin**: ✅ `/soci/reset-password`
- **Body**: `{"telefono": "..."} o {"email": "..."}`
- **Response**: `{"success": true, "message": "...", "email_sent_to": "..."}`

### **12. Change Password**
- **App**: `POST /soci/me/change-password`
- **Plugin**: ✅ `/soci/me/change-password`
- **Body**: `{"old_password": "...", "new_password": "..."}`

## 📊 Riepilogo

| Categoria | Endpoint App | Endpoint Plugin | Stato |
|-----------|--------------|-----------------|-------|
| Verifica Socio | GET /soci/verifica/{email} | ✅ | OK |
| Richiesta Adesione | POST /soci/richiesta | ✅ | OK |
| Profilo Corrente | GET /soci/me | ✅ | OK |
| Lista Soci | GET /soci | ✅ | OK |
| Completa Profilo | POST /soci/me/completa-profilo | ✅ | OK |
| Upload Documento | POST /soci/me/upload-documento | ✅ | OK |
| Check Username | GET /soci/check-username | ✅ | OK |
| Reset Password | POST /soci/reset-password | ✅ | OK |
| Change Password | POST /soci/me/change-password | ✅ | OK |
| Crea Richiesta Servizio | POST /richiesta-servizio | ✅ | OK |
| Dettaglio Richiesta | GET /richiesta-servizio/{id} | ✅ | OK |
| Mie Richieste | GET /mie-richieste | ✅ | **AGGIUNTO** |

## 🔧 Modifiche Applicate

1. **Aggiunto endpoint `/mie-richieste`** come alias di `/richieste-servizi/me`
2. **Corretto response di `/mie-richieste`**: cambiato `data` → `richieste`
3. **Corretto response di `/richiesta-servizio`**: aggiunto `id`, `numero_pratica`, `data_richiesta` direttamente

## ✅ Conclusione

**Tutti gli endpoint richiesti dall'app Flutter sono ora implementati e compatibili.**

Le uniche modifiche necessarie erano:
- Aggiungere alias `/mie-richieste`
- Correggere formato response per compatibilità

Il plugin **wecoop-soci** e **wecoop-servizi** sono completamente funzionali per l'app Flutter.
