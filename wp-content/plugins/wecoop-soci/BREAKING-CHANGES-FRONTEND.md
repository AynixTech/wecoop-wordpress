# 🔥 BREAKING CHANGES - Aggiornamento Frontend Flutter Obbligatorio

## ⚠️ ATTENZIONE: API COMPLETAMENTE CAMBIATA

Il flusso di registrazione è stato **completamente modificato**. L'app Flutter deve essere aggiornata **IMMEDIATAMENTE**.

---

## 📋 COSA È CAMBIATO

### ❌ PRIMA (Versione Vecchia)

**Flusso:**
1. Utente compila form (nome, cognome, prefix, telefono)
2. Backend salva solo una "richiesta" (status: pending)
3. Utente **NON può fare login**
4. Admin deve approvare manualmente
5. Admin crea utente e invia credenziali via email

**Response API:**
```json
{
  "success": true,
  "message": "Richiesta di adesione inviata con successo! Ti contatteremo presto.",
  "data": {
    "id": 123,
    "numero_pratica": "RS-2025-00123",
    "status": "pending",
    "nome": "Mario",
    "cognome": "Rossi",
    "prefix": "+39",
    "telefono": "3331234567",
    "telefono_completo": "+393331234567"
  }
}
```

### ✅ ORA (Versione Nuova)

**Flusso:**
1. Utente compila form (nome, cognome, prefix, telefono)
2. Backend **crea subito utente WordPress**
3. Backend genera credenziali di accesso
4. Utente **può fare login immediatamente**
5. Admin può promuovere a "socio" in seguito

**Response API:**
```json
{
  "success": true,
  "message": "Registrazione completata! Usa queste credenziali per accedere.",
  "data": {
    "id": 123,
    "user_id": 456,
    "numero_pratica": "RS-2025-00123",
    "username": "+393331234567",
    "password": "SoleLuna2025",
    "nome": "Mario",
    "cognome": "Rossi",
    "prefix": "+39",
    "telefono": "3331234567",
    "telefono_completo": "+393331234567",
    "is_socio": false,
    "profilo_completo": false
  }
}
```

---

## 🔄 MODIFICHE OBBLIGATORIE FRONTEND

### 1. ✅ Aggiornare Model `PrimoAccessoResponse`

**File:** `lib/models/primo_accesso_response.dart` (o simile)

```dart
class PrimoAccessoResponse {
  final bool success;
  final String message;
  final PrimoAccessoData data;

  PrimoAccessoResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory PrimoAccessoResponse.fromJson(Map<String, dynamic> json) {
    return PrimoAccessoResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: PrimoAccessoData.fromJson(json['data']),
    );
  }
}

class PrimoAccessoData {
  final int id;
  final int userId;                    // ✅ NUOVO
  final String numeroPratica;
  final String username;               // ✅ NUOVO - telefono completo
  final String password;               // ✅ NUOVO - password generata
  final String nome;
  final String cognome;
  final String prefix;
  final String telefono;
  final String telefonoCompleto;
  final bool isSocio;                  // ✅ NUOVO - sempre false
  final bool profiloCompleto;          // ✅ NUOVO - sempre false
  // ❌ RIMOSSO: String? status;

  PrimoAccessoData({
    required this.id,
    required this.userId,
    required this.numeroPratica,
    required this.username,
    required this.password,
    required this.nome,
    required this.cognome,
    required this.prefix,
    required this.telefono,
    required this.telefonoCompleto,
    required this.isSocio,
    required this.profiloCompleto,
  });

  factory PrimoAccessoData.fromJson(Map<String, dynamic> json) {
    return PrimoAccessoData(
      id: json['id'],
      userId: json['user_id'],                          // ✅ NUOVO
      numeroPratica: json['numero_pratica'] ?? '',
      username: json['username'] ?? '',                 // ✅ NUOVO
      password: json['password'] ?? '',                 // ✅ NUOVO
      nome: json['nome'] ?? '',
      cognome: json['cognome'] ?? '',
      prefix: json['prefix'] ?? '',
      telefono: json['telefono'] ?? '',
      telefonoCompleto: json['telefono_completo'] ?? '',
      isSocio: json['is_socio'] ?? false,               // ✅ NUOVO
      profiloCompleto: json['profilo_completo'] ?? false, // ✅ NUOVO
    );
  }
}
```

---

### 2. ✅ Mostrare Credenziali all'Utente

**CRITICO:** L'utente DEVE vedere le sue credenziali dopo la registrazione!

**Schermata di Successo (esempio):**

```dart
// lib/screens/registrazione_successo_screen.dart

class RegistrazioneSuccessoScreen extends StatelessWidget {
  final PrimoAccessoData userData;

  const RegistrazioneSuccessoScreen({
    Key? key,
    required this.userData,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Registrazione Completata'),
        automaticallyImplyLeading: false, // Non permettere di tornare indietro
      ),
      body: Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.check_circle,
              color: Colors.green,
              size: 80,
            ),
            SizedBox(height: 24),
            Text(
              'Benvenuto ${userData.nome}!',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            SizedBox(height: 16),
            Text(
              'Il tuo account è stato creato con successo.',
              textAlign: TextAlign.center,
            ),
            SizedBox(height: 32),
            
            // Box con credenziali
            Container(
              padding: EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '🔐 Le tue credenziali di accesso:',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(height: 16),
                  
                  // Username
                  Row(
                    children: [
                      Icon(Icons.person, size: 20, color: Colors.blue),
                      SizedBox(width: 8),
                      Text('Username:', style: TextStyle(fontWeight: FontWeight.w600)),
                    ],
                  ),
                  SizedBox(height: 4),
                  SelectableText(
                    userData.username,
                    style: TextStyle(fontSize: 18, fontFamily: 'monospace'),
                  ),
                  SizedBox(height: 16),
                  
                  // Password
                  Row(
                    children: [
                      Icon(Icons.lock, size: 20, color: Colors.blue),
                      SizedBox(width: 8),
                      Text('Password:', style: TextStyle(fontWeight: FontWeight.w600)),
                    ],
                  ),
                  SizedBox(height: 4),
                  SelectableText(
                    userData.password,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      fontFamily: 'monospace',
                      color: Colors.red.shade700,
                    ),
                  ),
                ],
              ),
            ),
            
            SizedBox(height: 24),
            
            // Avviso importante
            Container(
              padding: EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.orange.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.orange.shade200),
              ),
              child: Row(
                children: [
                  Icon(Icons.warning_amber, color: Colors.orange),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Salva queste credenziali! Ti serviranno per accedere.',
                      style: TextStyle(fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
            
            SizedBox(height: 32),
            
            // Pulsanti azione
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      // Copia credenziali negli appunti
                      Clipboard.setData(ClipboardData(
                        text: 'Username: ${userData.username}\nPassword: ${userData.password}',
                      ));
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Credenziali copiate negli appunti')),
                      );
                    },
                    icon: Icon(Icons.copy),
                    label: Text('Copia'),
                  ),
                ),
                SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      // Vai alla schermata di login
                      Navigator.of(context).pushReplacementNamed('/login');
                    },
                    icon: Icon(Icons.login),
                    label: Text('Accedi Ora'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
```

---

### 3. ✅ Opzionale: Login Automatico

Puoi fare login automatico dopo la registrazione:

```dart
// Nel service dopo la registrazione
Future<void> registraEAccedi({
  required String nome,
  required String cognome,
  required String prefix,
  required String telefono,
}) async {
  try {
    // 1. Registrazione
    final response = await dio.post(
      '$baseUrl/utenti/primo-accesso',
      data: {
        'nome': nome,
        'cognome': cognome,
        'prefix': prefix,
        'telefono': telefono,
      },
    );

    final data = PrimoAccessoResponse.fromJson(response.data);

    // 2. Login automatico con credenziali ricevute
    final loginResponse = await dio.post(
      '$baseUrl/auth/login',
      data: {
        'username': data.data.username,
        'password': data.data.password,
      },
    );

    // 3. Salva token JWT
    final token = loginResponse.data['token'];
    await storage.write(key: 'jwt_token', value: token);
    
    // 4. Salva dati utente
    await storage.write(key: 'user_id', value: data.data.userId.toString());
    await storage.write(key: 'username', value: data.data.username);
    await storage.write(key: 'is_socio', value: data.data.isSocio.toString());

    // 5. Vai alla home
    Navigator.of(context).pushReplacementNamed('/home');

  } catch (e) {
    // Gestione errori
  }
}
```

---

### 4. ✅ Salvare Credenziali Localmente

**IMPORTANTE:** Salva le credenziali per permettere recupero password:

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class AuthService {
  final storage = FlutterSecureStorage();

  Future<void> salvaCredenziali(PrimoAccessoData data) async {
    await storage.write(key: 'user_id', value: data.userId.toString());
    await storage.write(key: 'username', value: data.username);
    await storage.write(key: 'password', value: data.password); // Opzionale
    await storage.write(key: 'nome', value: data.nome);
    await storage.write(key: 'cognome', value: data.cognome);
    await storage.write(key: 'telefono_completo', value: data.telefonoCompleto);
    await storage.write(key: 'is_socio', value: data.isSocio.toString());
    await storage.write(key: 'profilo_completo', value: data.profiloCompleto.toString());
  }

  Future<Map<String, String>> leggiCredenziali() async {
    return {
      'user_id': await storage.read(key: 'user_id') ?? '',
      'username': await storage.read(key: 'username') ?? '',
      'password': await storage.read(key: 'password') ?? '',
      'nome': await storage.read(key: 'nome') ?? '',
      'cognome': await storage.read(key: 'cognome') ?? '',
      'is_socio': await storage.read(key: 'is_socio') ?? 'false',
    };
  }
}
```

---

### 5. ✅ Gestire Flag `is_socio`

Usa questo flag per mostrare/nascondere funzionalità:

```dart
class HomeScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return FutureBuilder<bool>(
      future: _checkIsSocio(),
      builder: (context, snapshot) {
        final isSocio = snapshot.data ?? false;

        return Scaffold(
          body: Column(
            children: [
              // Mostra sempre (utenti base)
              ListTile(
                title: Text('Richiedi Servizio'),
                onTap: () => Navigator.pushNamed(context, '/servizi'),
              ),

              // Mostra solo se è socio
              if (isSocio) ...[
                ListTile(
                  title: Text('Tessera Socio'),
                  onTap: () => Navigator.pushNamed(context, '/tessera'),
                ),
                ListTile(
                  title: Text('Assemblee'),
                  onTap: () => Navigator.pushNamed(context, '/assemblee'),
                ),
              ],

              // Mostra solo se NON è socio
              if (!isSocio) ...[
                Card(
                  color: Colors.blue.shade50,
                  child: ListTile(
                    leading: Icon(Icons.card_membership, color: Colors.blue),
                    title: Text('Diventa Socio'),
                    subtitle: Text('Accedi a vantaggi esclusivi'),
                    trailing: Icon(Icons.arrow_forward),
                    onTap: () => Navigator.pushNamed(context, '/diventa-socio'),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Future<bool> _checkIsSocio() async {
    final storage = FlutterSecureStorage();
    final isSocioStr = await storage.read(key: 'is_socio');
    return isSocioStr == 'true';
  }
}
```

---

## 🔐 CREDENZIALI GENERATE

### Username

**Formato:** Telefono completo con prefisso

**Esempi:**
- `+393331234567` (Italia)
- `+12025551234` (USA)
- `+447911123456` (UK)

### Password

**Formato:** 2 parole italiane + numeri

**Esempi:**
- `SoleLuna2025`
- `MareCielo123`
- `CasaVerde99`
- `FioreStella456`

**Caratteristiche:**
- ✅ Facile da ricordare
- ✅ Facile da digitare
- ✅ Abbastanza sicura per primo accesso
- ⚠️ Utente può cambiarla dopo

---

## ❌ ERRORI DA GESTIRE

### Errore: `duplicate_phone`

**Quando:** Telefono già registrato

```dart
if (error.response?.data['code'] == 'duplicate_phone') {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: Text('Numero già registrato'),
      content: Text(
        'Questo numero è già associato a un account. '
        'Hai dimenticato la password? Usa il recupero password.',
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('Annulla'),
        ),
        ElevatedButton(
          onPressed: () {
            Navigator.pop(context);
            Navigator.pushNamed(context, '/recupera-password');
          },
          child: Text('Recupera Password'),
        ),
      ],
    ),
  );
}
```

### Errore: `invalid_data`

**Quando:** Campi mancanti o non validi

```dart
if (error.response?.data['code'] == 'invalid_data') {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text('Controlla i dati inseriti e riprova'),
      backgroundColor: Colors.red,
    ),
  );
}
```

### Errore: `user_creation_failed`

**Quando:** Errore nella creazione utente WordPress

```dart
if (error.response?.data['code'] == 'user_creation_failed') {
  showDialog(
    context: context,
    builder: (context) => AlertDialog(
      title: Text('Errore del Server'),
      content: Text(
        'Si è verificato un errore durante la creazione dell\'account. '
        'Riprova tra qualche minuto.',
      ),
      actions: [
        ElevatedButton(
          onPressed: () => Navigator.pop(context),
          child: Text('OK'),
        ),
      ],
    ),
  );
}
```

---

## 📝 CHECKLIST AGGIORNAMENTO FRONTEND

### Obbligatorio (MUST HAVE)

- [ ] ✅ Aggiornare model `PrimoAccessoResponse` con nuovi campi
- [ ] ✅ Aggiungere campi: `user_id`, `username`, `password`, `is_socio`, `profilo_completo`
- [ ] ✅ Rimuovere campo: `status`
- [ ] ✅ Creare schermata "Registrazione Successo" che mostra credenziali
- [ ] ✅ Permettere copia credenziali negli appunti
- [ ] ✅ Salvare credenziali in secure storage
- [ ] ✅ Aggiornare gestione errori (`duplicate_phone`, `invalid_data`, `user_creation_failed`)
- [ ] ✅ Testare flusso completo di registrazione

### Consigliato (SHOULD HAVE)

- [ ] 🔄 Implementare login automatico dopo registrazione
- [ ] 🔄 Mostrare/nascondere funzionalità in base a `is_socio`
- [ ] 🔄 Aggiungere pulsante "Diventa Socio" se `is_socio == false`
- [ ] 🔄 Permettere condivisione credenziali (WhatsApp, Email)
- [ ] 🔄 Inviare promemoria credenziali via WhatsApp (opzionale)

### Opzionale (NICE TO HAVE)

- [ ] 💡 Screenshot credenziali per salvare
- [ ] 💡 Tutorial primo accesso
- [ ] 💡 Cambio password al primo login
- [ ] 💡 Validazione password (forza, sicurezza)

---

## 🧪 TESTING

### Test Case 1: Registrazione Nuova

**Input:**
- Nome: Mario
- Cognome: Rossi
- Prefix: +39
- Telefono: 3331234567

**Output Atteso:**
```json
{
  "success": true,
  "message": "Registrazione completata! Usa queste credenziali per accedere.",
  "data": {
    "user_id": 123,
    "username": "+393331234567",
    "password": "SoleLuna2025",
    "is_socio": false,
    "profilo_completo": false
  }
}
```

**Verifiche:**
- ✅ Schermata successo mostrata
- ✅ Credenziali visibili e copiabili
- ✅ Pulsante "Accedi Ora" funzionante
- ✅ Login con credenziali funziona

### Test Case 2: Telefono Duplicato

**Input:**
- Stesso telefono di Test Case 1

**Output Atteso:**
```json
{
  "code": "duplicate_phone",
  "message": "Telefono già registrato",
  "data": {"status": 400}
}
```

**Verifiche:**
- ✅ Dialog errore mostrato
- ✅ Suggerimento recupero password
- ✅ Nessuna navigazione

### Test Case 3: Login con Credenziali Generate

**Input:**
- Username: `+393331234567`
- Password: `SoleLuna2025`

**Output Atteso:**
- ✅ Login riuscito
- ✅ Token JWT salvato
- ✅ Navigazione alla home

---

## 🚀 DEPLOYMENT

### Ordine Implementazione

1. **Backend** ✅ GIÀ FATTO
   - Endpoint aggiornato
   - Response modificata
   - Credenziali generate

2. **Frontend** ⏳ DA FARE
   - Aggiornare models
   - Creare schermata successo
   - Implementare storage credenziali
   - Gestire errori
   - Testare end-to-end

3. **Go Live** 🚀
   - Deploy backend (git pull su server)
   - Deploy app Flutter (Play Store + App Store)
   - Comunicazione utenti esistenti

---

## 📞 SUPPORTO

**Dubbi su implementazione?** Contatta il team backend

**Issue/Bug?** Apri issue su GitHub con tag `frontend`

**Testing?** Usa ambiente di staging prima di produzione

---

**Versione Backend:** 2.0.0  
**Data Modifica:** 28 Dicembre 2025  
**Compatibilità:** ❌ NON retrocompatibile con versione precedente  
**Priorità:** 🔴 CRITICA - Aggiornamento obbligatorio
