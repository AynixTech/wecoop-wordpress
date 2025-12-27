# API Download Ricevuta PDF - Flutter

## Endpoint

```
GET /wp-json/wecoop/v1/pagamento/{payment_id}/ricevuta
```

## Autenticazione

**Header richiesto:**
```
Authorization: Bearer {JWT_TOKEN}
```

## Parametri

- `payment_id` (int, required): ID del pagamento

## Response

### Successo (200)

**Headers:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="Ricevuta_123_2024.pdf"
Content-Length: {file_size}
```

**Body:** Binary PDF file

### Errori

**404 - Ricevuta non trovata**
```json
{
  "code": "receipt_not_found",
  "message": "Ricevuta non disponibile per questo pagamento",
  "data": {
    "status": 404
  }
}
```

**403 - Non autorizzato**
```json
{
  "code": "forbidden",
  "message": "Non hai i permessi per scaricare questa ricevuta",
  "data": {
    "status": 403
  }
}
```

**401 - Token non valido**
```json
{
  "code": "jwt_auth_invalid_token",
  "message": "Token JWT non valido",
  "data": {
    "status": 401
  }
}
```

## Esempio Flutter - Service Method

```dart
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import 'dart:io';

class RicevuteService {
  final Dio _dio;
  final String baseUrl = 'https://www.wecoop.org/wp-json/wecoop/v1';
  
  RicevuteService(this._dio);
  
  /// Scarica la ricevuta PDF per un pagamento
  Future<File> downloadRicevuta({
    required int paymentId,
    required String jwtToken,
    Function(double)? onProgress,
  }) async {
    try {
      // Directory per salvare il PDF
      final dir = await getApplicationDocumentsDirectory();
      final filepath = '${dir.path}/ricevuta_$paymentId.pdf';
      
      // Download con progress
      await _dio.download(
        '$baseUrl/pagamento/$paymentId/ricevuta',
        filepath,
        options: Options(
          headers: {
            'Authorization': 'Bearer $jwtToken',
          },
          responseType: ResponseType.bytes,
        ),
        onReceiveProgress: (received, total) {
          if (total != -1 && onProgress != null) {
            onProgress(received / total);
          }
        },
      );
      
      return File(filepath);
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('Ricevuta non disponibile');
      } else if (e.response?.statusCode == 403) {
        throw Exception('Non autorizzato a scaricare questa ricevuta');
      } else if (e.response?.statusCode == 401) {
        throw Exception('Sessione scaduta, effettua di nuovo il login');
      } else {
        throw Exception('Errore download: ${e.message}');
      }
    }
  }
  
  /// Verifica se una ricevuta esiste per un pagamento
  Future<bool> hasRicevuta(int paymentId, String jwtToken) async {
    try {
      final response = await _dio.head(
        '$baseUrl/pagamento/$paymentId/ricevuta',
        options: Options(
          headers: {'Authorization': 'Bearer $jwtToken'},
        ),
      );
      return response.statusCode == 200;
    } catch (e) {
      return false;
    }
  }
}
```

## Esempio Flutter - UI con Download Progress

```dart
import 'package:flutter/material.dart';
import 'package:open_file/open_file.dart';

class RichiestaDetailPage extends StatefulWidget {
  final Richiesta richiesta;
  
  const RichiestaDetailPage({required this.richiesta});
  
  @override
  State<RichiestaDetailPage> createState() => _RichiestaDetailPageState();
}

class _RichiestaDetailPageState extends State<RichiestaDetailPage> {
  bool _isDownloading = false;
  double _downloadProgress = 0.0;
  
  Future<void> _downloadRicevuta() async {
    if (widget.richiesta.paymentId == null) return;
    
    setState(() {
      _isDownloading = true;
      _downloadProgress = 0.0;
    });
    
    try {
      final jwtToken = await AuthService().getToken();
      final file = await RicevuteService(_dio).downloadRicevuta(
        paymentId: widget.richiesta.paymentId!,
        jwtToken: jwtToken,
        onProgress: (progress) {
          setState(() {
            _downloadProgress = progress;
          });
        },
      );
      
      setState(() {
        _isDownloading = false;
      });
      
      // Apri il PDF
      await OpenFile.open(file.path);
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('✅ Ricevuta scaricata'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      setState(() {
        _isDownloading = false;
      });
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ Errore: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Dettaglio Richiesta')),
      body: Column(
        children: [
          // ... altri widget ...
          
          // Sezione Pagamento
          if (widget.richiesta.paymentStatus == 'completed' || 
              widget.richiesta.paymentStatus == 'paid')
            Card(
              child: ListTile(
                leading: Icon(Icons.check_circle, color: Colors.green),
                title: Text('Pagamento completato'),
                subtitle: Text('€${widget.richiesta.importo}'),
                trailing: _isDownloading
                    ? SizedBox(
                        width: 60,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            CircularProgressIndicator(
                              value: _downloadProgress,
                              strokeWidth: 2,
                            ),
                            SizedBox(height: 4),
                            Text(
                              '${(_downloadProgress * 100).toInt()}%',
                              style: TextStyle(fontSize: 10),
                            ),
                          ],
                        ),
                      )
                    : IconButton(
                        icon: Icon(Icons.download, color: Colors.blue),
                        tooltip: 'Scarica ricevuta',
                        onPressed: _downloadRicevuta,
                      ),
              ),
            ),
        ],
      ),
    );
  }
}
```

## Esempio con Share (condivisione)

```dart
import 'package:share_plus/share_plus.dart';

Future<void> _shareRicevuta() async {
  try {
    final jwtToken = await AuthService().getToken();
    final file = await RicevuteService(_dio).downloadRicevuta(
      paymentId: widget.richiesta.paymentId!,
      jwtToken: jwtToken,
    );
    
    await Share.shareXFiles(
      [XFile(file.path)],
      subject: 'Ricevuta Pagamento WeCoop',
      text: 'Ecco la ricevuta del tuo pagamento',
    );
  } catch (e) {
    // Gestisci errore
  }
}
```

## Dipendenze Required

```yaml
dependencies:
  dio: ^5.4.0
  path_provider: ^2.1.0
  open_file: ^3.3.2
  share_plus: ^7.2.1  # opzionale, per condivisione
```

## Note Implementazione Backend

L'endpoint deve essere creato in `class-servizi-endpoint.php`:

```php
// Registra route
add_action('rest_api_init', function() {
    register_rest_route('wecoop/v1', '/pagamento/(?P<id>\d+)/ricevuta', [
        'methods' => 'GET',
        'callback' => [__CLASS__, 'get_ricevuta_pdf'],
        'permission_callback' => [__CLASS__, 'check_jwt_auth'],
        'args' => [
            'id' => [
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ],
        ],
    ]);
});
```

## Testing

```bash
# Test con curl
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
     https://www.wecoop.org/wp-json/wecoop/v1/pagamento/123/ricevuta \
     --output ricevuta.pdf
```
