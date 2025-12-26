# API - Elimina Richiesta Servizio

## Endpoint

```
DELETE /wp-json/wecoop/v1/richiesta-servizio/{id}
```

## Autenticazione

Richiede JWT token nell'header:

```
Authorization: Bearer {jwt_token}
```

## Parametri

| Parametro | Tipo | Posizione | Obbligatorio | Descrizione |
|-----------|------|-----------|--------------|-------------|
| `id` | integer | URL path | ✅ Sì | ID della richiesta da eliminare |

## Condizioni per Eliminazione

L'utente può eliminare una richiesta **SOLO SE**:

1. ✅ È il **proprietario** della richiesta (`user_id` corrisponde)
2. ✅ La richiesta è in stato **`pending`** (in attesa)
3. ✅ **NON** ha pagamenti associati (`payment_status` != 'paid' e != 'pending')

## Esempio Richiesta

```dart
// Flutter/Dart
Future<Map<String, dynamic>> deleteRichiesta(int richiestaId) async {
  try {
    final response = await dio.delete(
      '/wp-json/wecoop/v1/richiesta-servizio/$richiestaId',
      options: Options(
        headers: {
          'Authorization': 'Bearer $jwtToken',
        },
      ),
    );
    
    return response.data;
  } on DioException catch (e) {
    if (e.response != null) {
      throw Exception(e.response!.data['message'] ?? 'Errore eliminazione');
    }
    rethrow;
  }
}
```

```bash
# cURL
curl -X DELETE \
  'https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio/123' \
  -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...'
```

## Risposte

### ✅ Successo (200 OK)

```json
{
  "success": true,
  "message": "Richiesta eliminata con successo",
  "id": 123,
  "numero_pratica": "REQ-2025-001",
  "servizio": "Certificato di Residenza"
}
```

### ❌ Errori

#### 401 Unauthorized - Non autenticato

```json
{
  "code": "unauthorized",
  "message": "Utente non autenticato",
  "data": {
    "status": 401
  }
}
```

#### 403 Forbidden - Non proprietario

```json
{
  "code": "forbidden",
  "message": "Non hai il permesso di eliminare questa richiesta",
  "data": {
    "status": 403
  }
}
```

#### 404 Not Found - Richiesta inesistente

```json
{
  "code": "not_found",
  "message": "Richiesta non trovata",
  "data": {
    "status": 404
  }
}
```

#### 400 Bad Request - Stato non valido

```json
{
  "code": "invalid_status",
  "message": "Puoi eliminare solo richieste in attesa. Questa richiesta è in stato: processing",
  "data": {
    "status": 400
  }
}
```

#### 400 Bad Request - Ha pagamento associato

```json
{
  "code": "has_payment",
  "message": "Non puoi eliminare una richiesta con un pagamento associato",
  "data": {
    "status": 400
  }
}
```

#### 500 Internal Server Error

```json
{
  "code": "delete_failed",
  "message": "Impossibile eliminare la richiesta",
  "data": {
    "status": 500
  }
}
```

## Implementazione Flutter

### Service Method

```dart
// lib/services/socio_service.dart

Future<void> deleteRichiesta(int richiestaId) async {
  try {
    final response = await dio.delete(
      '/wp-json/wecoop/v1/richiesta-servizio/$richiestaId',
    );

    if (response.statusCode == 200 && response.data['success'] == true) {
      logger.i('Richiesta eliminata: ${response.data['numero_pratica']}');
    } else {
      throw Exception('Eliminazione fallita');
    }
  } on DioException catch (e) {
    logger.e('Errore eliminazione richiesta: ${e.response?.data}');
    
    final message = e.response?.data['message'] ?? 'Errore durante l\'eliminazione';
    throw Exception(message);
  }
}
```

### UI - Dialog Conferma

```dart
// lib/screens/richieste/richieste_screen.dart

Future<void> _showDeleteConfirmDialog(Richiesta richiesta) async {
  // Verifica se eliminabile
  if (richiesta.stato != 'pending') {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Puoi eliminare solo richieste in attesa'),
        backgroundColor: Colors.orange,
      ),
    );
    return;
  }

  final confirm = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: Text('Elimina Richiesta'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Sei sicuro di voler eliminare questa richiesta?'),
          SizedBox(height: 16),
          Text(
            'Pratica: ${richiesta.numeroPratica}',
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          Text('Servizio: ${richiesta.servizio}'),
          SizedBox(height: 16),
          Text(
            '⚠️ Questa azione non può essere annullata',
            style: TextStyle(color: Colors.orange, fontSize: 12),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context, false),
          child: Text('Annulla'),
        ),
        ElevatedButton(
          onPressed: () => Navigator.pop(context, true),
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.red,
          ),
          child: Text('Elimina'),
        ),
      ],
    ),
  );

  if (confirm == true) {
    _deleteRichiesta(richiesta.id);
  }
}

Future<void> _deleteRichiesta(int richiestaId) async {
  try {
    // Mostra loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Center(child: CircularProgressIndicator()),
    );

    await _socioService.deleteRichiesta(richiestaId);

    // Chiudi loading
    Navigator.pop(context);

    // Mostra successo
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('✅ Richiesta eliminata con successo'),
        backgroundColor: Colors.green,
      ),
    );

    // Ricarica lista
    _loadRichieste();

  } catch (e) {
    // Chiudi loading
    Navigator.pop(context);

    // Mostra errore
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('❌ $e'),
        backgroundColor: Colors.red,
        duration: Duration(seconds: 4),
      ),
    );
  }
}
```

### UI - Pulsante Elimina (Swipe/Dismissible)

```dart
// In ListView.builder o similar

Widget buildRichiestaCard(Richiesta richiesta) {
  // Solo richieste pending sono eliminabili
  final canDelete = richiesta.stato == 'pending';

  if (canDelete) {
    return Dismissible(
      key: Key('richiesta-${richiesta.id}'),
      direction: DismissDirection.endToStart,
      confirmDismiss: (direction) async {
        return await _showDeleteConfirmDialog(richiesta);
      },
      onDismissed: (direction) {
        _deleteRichiesta(richiesta.id);
      },
      background: Container(
        color: Colors.red,
        alignment: Alignment.centerRight,
        padding: EdgeInsets.only(right: 20),
        child: Icon(Icons.delete, color: Colors.white),
      ),
      child: _buildRichiestaContent(richiesta),
    );
  } else {
    return _buildRichiestaContent(richiesta);
  }
}
```

### UI - Menu Contestuale

```dart
Widget _buildRichiestaContent(Richiesta richiesta) {
  return Card(
    child: ListTile(
      title: Text(richiesta.servizio),
      subtitle: Text(richiesta.numeroPratica),
      trailing: PopupMenuButton(
        itemBuilder: (context) => [
          PopupMenuItem(
            child: ListTile(
              leading: Icon(Icons.info),
              title: Text('Dettagli'),
            ),
            onTap: () => _showDetails(richiesta),
          ),
          if (richiesta.stato == 'pending')
            PopupMenuItem(
              child: ListTile(
                leading: Icon(Icons.delete, color: Colors.red),
                title: Text('Elimina', style: TextStyle(color: Colors.red)),
              ),
              onTap: () => _showDeleteConfirmDialog(richiesta),
            ),
        ],
      ),
    ),
  );
}
```

## Note Implementative

### Backend (WordPress)

- ✅ Usa `wp_trash_post()` - **soft delete**, recuperabile da admin
- ✅ Log completo dell'operazione in `error_log`
- ✅ Verifica rigorosa ownership
- ✅ Validazione stato e payment_status

### Frontend (Flutter)

- 🔴 Mostra pulsante "Elimina" **SOLO** per richieste `pending`
- 🟡 Dialog di conferma obbligatorio
- 🟢 Feedback visivo (loading, successo, errore)
- 🔵 Ricarica lista dopo eliminazione
- 🟣 Swipe-to-delete opzionale (migliore UX)

## Stati Richiesta

| Stato | Eliminabile? | Motivo |
|-------|--------------|--------|
| `pending` | ✅ Sì | In attesa, nessun processo avviato |
| `awaiting_payment` | ❌ No | Ha pagamento associato |
| `processing` | ❌ No | Già in lavorazione |
| `completed` | ❌ No | Già completata |
| `cancelled` | ❌ No | Già annullata |

## Sicurezza

1. **Autenticazione JWT** - Token valido richiesto
2. **Ownership Check** - Solo il proprietario può eliminare
3. **State Validation** - Solo stato `pending` consentito
4. **Payment Check** - Blocca se pagamento presente
5. **Soft Delete** - Admin può recuperare dal cestino
6. **Audit Log** - Operazione tracciata nei log

## Test API

```bash
# Test successo (richiesta pending, proprietario)
curl -X DELETE 'https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio/123' \
  -H 'Authorization: Bearer YOUR_TOKEN'

# Test errore 403 (non proprietario)
curl -X DELETE 'https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio/456' \
  -H 'Authorization: Bearer OTHER_USER_TOKEN'

# Test errore 400 (stato non pending)
curl -X DELETE 'https://www.wecoop.org/wp-json/wecoop/v1/richiesta-servizio/789' \
  -H 'Authorization: Bearer YOUR_TOKEN'
```
