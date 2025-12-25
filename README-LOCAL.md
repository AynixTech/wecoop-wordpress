# WeCoop WordPress - Ambiente Locale

## 🚀 Quick Start

### 1. Avvia l'ambiente locale
```bash
docker-compose up -d
```

### 2. Accedi a WordPress
- **Sito**: http://localhost:8080
- **Admin**: http://localhost:8080/wp-admin
- **phpMyAdmin**: http://localhost:8081

### 3. Configurazione iniziale
Al primo avvio, WordPress ti chiederà di:
- Selezionare la lingua
- Creare un utente admin
- Configurare il sito

**Credenziali database** (se richieste):
- Database: `wordpress`
- User: `wordpress`
- Password: `wordpress`
- Host: `db`

---

## 🔧 Comandi utili

### Avvia ambiente
```bash
docker-compose up -d
```

### Ferma ambiente
```bash
docker-compose down
```

### Riavvia ambiente
```bash
docker-compose restart
```

### Vedi i logs
```bash
docker-compose logs -f wordpress
```

### Accedi al container WordPress
```bash
docker exec -it wecoop-local bash
```

---

## 📝 Workflow di sviluppo

1. **Sviluppa in locale**
   - Modifica i file nel tuo editor
   - Le modifiche si riflettono subito su http://localhost:8080

2. **Testa le modifiche**
   - Verifica che tutto funzioni

3. **Commit e Push**
   ```bash
   git add .
   git commit -m "Descrizione modifiche"
   git push origin main
   ```

4. **Deploy automatico**
   - Il webhook su Hostinger deploya automaticamente

---

## ⚠️ Note importanti

- I file WordPress core sono nel container Docker
- Solo `/wp-content` è sincronizzato con il tuo repository
- Il database è persistente (volume Docker `db_data`)
- NON committare `wp-config.php` con credenziali reali

---

## 🗄️ Importare database di produzione (opzionale)

Se vuoi lavorare con i dati di produzione:

1. Esporta DB da Hostinger (phpMyAdmin)
2. Importa in locale tramite http://localhost:8081
3. Aggiorna le URL nel database:
   ```sql
   UPDATE wp_options SET option_value = 'http://localhost:8080' WHERE option_name = 'siteurl';
   UPDATE wp_options SET option_value = 'http://localhost:8080' WHERE option_name = 'home';
   ```

---

## 🧹 Pulizia completa

Per rimuovere tutto (incluso database):
```bash
docker-compose down -v
```
