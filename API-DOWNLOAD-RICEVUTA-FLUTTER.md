# 📱 API Download Ricevuta - Implementazione Flutter

## Endpoint API

### GET Download Ricevuta

```
GET /wp-json/wecoop/v1/pagamento/{payment_id}/ricevuta
```

**Autenticazione:** JWT token richiesto

**Parametri:**
- `payment_id` (int, URL path): ID del pagamento

**Risposta Successo (200):**
```json
{
  "success": true,
  "receipt_url": "https://www.wecoop.org/wp-content/uploads/ricevute/Ricevuta_123_2025.pdf",
  "filename": "Ricevuta_123_2025.pdf",
  "payment_id": 123,
  "numero_ricevuta": "123/2025",
  "importo": 50.00,
  "data_pagamento": "2025-12-27"
}
```

**Errori:**
- `401` - Non autenticato
- `403` - Non proprietario del pagamento
- `404` - Pagamento non trovato o ricevuta non disponibile
- `400` - Pagamento non completato

---

## Backend Implementation (WordPress)

### 1. Aggiungi Endpoint in `class-servizi-endpoint.php`

```php
// In register_routes(), aggiungi:

// GET /pagamento/{id}/ricevuta - Download ricevuta PDF
register_rest_route('wecoop/v1', '/pagamento/(?P<id>\d+)/ricevuta', [
    'methods' => 'GET',
    'callback' => [__CLASS__, 'get_ricevuta_pdf'],
    'permission_callback' => [__CLASS__, 'check_jwt_permission'],
    'args' => [
        'id' => ['required' => true, 'type' => 'integer']
    ]
]);
```

### 2. Implementa Metodo `get_ricevuta_pdf()`

Aggiungi alla fine di `class-servizi-endpoint.php`:

```php
/**
 * GET /pagamento/{id}/ricevuta - Ricevuta PDF
 */
public static function get_ricevuta_pdf($request) {
    global $wpdb;
    
    $payment_id = intval($request->get_param('id'));
    $current_user_id = self::get_user_id_from_jwt($request);
    
    if (!$current_user_id) {
        return new WP_Error('unauthorized', 'Utente non autenticato', ['status' => 401]);
    }
    
    // Recupera pagamento
    $table = $wpdb->prefix . 'wecoop_pagamenti';
    $payment = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE id = %d",
        $payment_id
    ));
    
    if (!$payment) {
        return new WP_Error('not_found', 'Pagamento non trovato', ['status' => 404]);
    }
    
    // Verifica ownership
    $richiesta_id = $payment->richiesta_id;
    $owner_id = get_post_meta($richiesta_id, 'user_id', true);
    
    if (intval($owner_id) !== $current_user_id) {
        return new WP_Error('forbidden', 'Non hai accesso a questa ricevuta', ['status' => 403]);
    }
    
    // Verifica stato pagamento
    if ($payment->status !== 'completed') {
        return new WP_Error('invalid_status', 'Ricevuta disponibile solo per pagamenti completati', ['status' => 400]);
    }
    
    // Se ricevuta non esiste, generala
    if (empty($payment->receipt_url)) {
        if (class_exists('WeCoop_Ricevuta_PDF')) {
            $result = WeCoop_Ricevuta_PDF::genera_ricevuta($payment_id);
            
            if (is_wp_error($result)) {
                return new WP_Error('generation_failed', $result->get_error_message(), ['status' => 500]);
            }
            
            // Aggiorna URL nel database
            $wpdb->update(
                $table,
                ['receipt_url' => $result['url']],
                ['id' => $payment_id],
                ['%s'],
                ['%d']
            );
            
            $payment->receipt_url = $result['url'];
        } else {
            return new WP_Error('service_unavailable', 'Servizio ricevute non disponibile', ['status' => 503]);
        }
    }
    
    // Estrai filename dall'URL
    $filename = basename(parse_url($payment->receipt_url, PHP_URL_PATH));
    
    // Numero ricevuta
    $anno = date('Y', strtotime($payment->paid_at));
    $numero_ricevuta = $payment_id . '/' . $anno;
    
    return new WP_REST_Response([
        'success' => true,
        'receipt_url' => $payment->receipt_url,
        'filename' => $filename,
        'payment_id' => $payment_id,
        'numero_ricevuta' => $numero_ricevuta,
        'importo' => floatval($payment->amount),
        'data_pagamento' => date('Y-m-d', strtotime($payment->paid_at))
    ], 200);
}
```

---

## Frontend Implementation (Flutter)

### 1. Aggiungi Metodo in `socio_service.dart`

```dart
// lib/services/socio_service.dart

/// Scarica ricevuta PDF per un pagamento
Future<Map<String, dynamic>> getRicevutaPdf(int paymentId) async {
  try {
    final response = await dio.get(
      '/wp-json/wecoop/v1/pagamento/$paymentId/ricevuta',
    );

    if (response.statusCode == 200 && response.data['success'] == true) {
      return response.data;
    } else {
      throw Exception('Ricevuta non disponibile');
    }
  } on DioException catch (e) {
    logger.e('Errore download ricevuta: ${e.response?.data}');
    
    final message = e.response?.data['message'] ?? 'Errore durante il download della ricevuta';
    throw Exception(message);
  }
}

/// Scarica e salva ricevuta PDF localmente
Future<String> downloadRicevuta(int paymentId, String filename) async {
  try {
    // 1. Ottieni info ricevuta
    final ricevutaInfo = await getRicevutaPdf(paymentId);
    final url = ricevutaInfo['receipt_url'];

    // 2. Directory download
    final directory = await getApplicationDocumentsDirectory();
    final filepath = '${directory.path}/$filename';

    // 3. Download file
    await dio.download(
      url,
      filepath,
      options: Options(
        responseType: ResponseType.bytes,
        followRedirects: true,
      ),
    );

    logger.i('Ricevuta scaricata: $filepath');
    return filepath;

  } catch (e) {
    logger.e('Errore download file: $e');
    rethrow;
  }
}
```

### 2. Aggiungi Dipendenze in `pubspec.yaml`

```yaml
dependencies:
  # ... esistenti ...
  path_provider: ^2.1.0  # Per directory documenti
  open_file: ^3.3.2      # Per aprire PDF
  permission_handler: ^11.0.1  # Per permessi storage
  share_plus: ^7.2.1     # Per condividere PDF
```

### 3. UI - Pulsante Download nella Lista Richieste

```dart
// lib/screens/richieste/richieste_screen.dart

Widget _buildRichiestaCard(Richiesta richiesta) {
  final hasPayment = richiesta.paymentId != null;
  final isPaid = richiesta.paymentStatus == 'paid' || 
                 richiesta.paymentStatus == 'completed';

  return Card(
    margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ListTile(
          title: Text(
            richiesta.servizio,
            style: TextStyle(fontWeight: FontWeight.bold),
          ),
          subtitle: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SizedBox(height: 4),
              Text('Pratica: ${richiesta.numeroPratica}'),
              Text('Data: ${_formatDate(richiesta.dataRichiesta)}'),
              if (hasPayment && isPaid) ...[
                SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.check_circle, color: Colors.green, size: 16),
                    SizedBox(width: 4),
                    Text(
                      'Pagamento completato',
                      style: TextStyle(
                        color: Colors.green,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
          trailing: _buildStatoBadge(richiesta.stato),
        ),
        
        // Pulsanti azioni
        if (hasPayment && isPaid)
          Padding(
            padding: EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _downloadRicevuta(richiesta),
                    icon: Icon(Icons.download),
                    label: Text('Scarica Ricevuta'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.blue,
                    ),
                  ),
                ),
                SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _condividiRicevuta(richiesta),
                    icon: Icon(Icons.share),
                    label: Text('Condividi'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.blue,
                    ),
                  ),
                ),
              ],
            ),
          ),
      ],
    ),
  );
}
```

### 4. Implementa Funzioni Download e Condivisione

```dart
// lib/screens/richieste/richieste_screen.dart

Future<void> _downloadRicevuta(Richiesta richiesta) async {
  try {
    // Mostra loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Center(
        child: Card(
          child: Padding(
            padding: EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Download ricevuta in corso...'),
              ],
            ),
          ),
        ),
      ),
    );

    // Verifica permessi storage (solo Android)
    if (Platform.isAndroid) {
      final status = await Permission.storage.request();
      if (!status.isGranted) {
        Navigator.pop(context); // Chiudi loading
        _showError('Permesso storage negato');
        return;
      }
    }

    // Download
    final filename = 'Ricevuta_${richiesta.numeroPratica}.pdf';
    final filepath = await _socioService.downloadRicevuta(
      richiesta.paymentId!,
      filename,
    );

    // Chiudi loading
    Navigator.pop(context);

    // Mostra successo con opzione apri
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Row(
          children: [
            Icon(Icons.check_circle, color: Colors.green),
            SizedBox(width: 8),
            Text('Ricevuta Scaricata'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('La ricevuta è stata salvata con successo'),
            SizedBox(height: 8),
            Text(
              filename,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: Colors.grey[700],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Chiudi'),
          ),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              _apriRicevuta(filepath);
            },
            icon: Icon(Icons.open_in_new),
            label: Text('Apri PDF'),
          ),
        ],
      ),
    );

  } catch (e) {
    // Chiudi loading se aperto
    Navigator.of(context, rootNavigator: true).pop();
    _showError('Errore download: $e');
  }
}

Future<void> _apriRicevuta(String filepath) async {
  try {
    final result = await OpenFile.open(filepath);
    
    if (result.type != ResultType.done) {
      _showError('Impossibile aprire il file: ${result.message}');
    }
  } catch (e) {
    _showError('Errore apertura file: $e');
  }
}

Future<void> _condividiRicevuta(Richiesta richiesta) async {
  try {
    // Mostra loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Center(child: CircularProgressIndicator()),
    );

    // Download temporaneo
    final filename = 'Ricevuta_${richiesta.numeroPratica}.pdf';
    final filepath = await _socioService.downloadRicevuta(
      richiesta.paymentId!,
      filename,
    );

    // Chiudi loading
    Navigator.pop(context);

    // Condividi
    await Share.shareXFiles(
      [XFile(filepath)],
      subject: 'Ricevuta Donazione - ${richiesta.numeroPratica}',
      text: 'Ricevuta per erogazione liberale a WeCoop APS',
    );

  } catch (e) {
    Navigator.of(context, rootNavigator: true).pop();
    _showError('Errore condivisione: $e');
  }
}

void _showError(String message) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(message),
      backgroundColor: Colors.red,
      duration: Duration(seconds: 4),
    ),
  );
}
```

### 5. Aggiungi Permessi Android

```xml
<!-- android/app/src/main/AndroidManifest.xml -->

<manifest>
    <!-- Aggiungi questi permessi -->
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"
        android:maxSdkVersion="32" />
    <uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"
        android:maxSdkVersion="32" />
    
    <application>
        <!-- FileProvider per condivisione file -->
        <provider
            android:name="androidx.core.content.FileProvider"
            android:authorities="${applicationId}.fileprovider"
            android:exported="false"
            android:grantUriPermissions="true">
            <meta-data
                android:name="android.support.FILE_PROVIDER_PATHS"
                android:resource="@xml/file_paths" />
        </provider>
    </application>
</manifest>
```

### 6. FileProvider Config Android

```xml
<!-- android/app/src/main/res/xml/file_paths.xml -->
<?xml version="1.0" encoding="utf-8"?>
<paths>
    <external-files-path name="external_files" path="." />
    <cache-path name="cache" path="." />
    <files-path name="files" path="." />
</paths>
```

### 7. Aggiungi Info.plist iOS

```xml
<!-- ios/Runner/Info.plist -->
<dict>
    <!-- ... esistenti ... -->
    
    <!-- Per salvare file -->
    <key>UIFileSharingEnabled</key>
    <true/>
    <key>LSSupportsOpeningDocumentsInPlace</key>
    <true/>
</dict>
```

---

## 📧 Integrazione Email (Opzionale)

### Includi Link Ricevuta nell'Email di Conferma Pagamento

Modifica `class-payment-system.php`:

```php
// In send_payment_email(), aggiungi dopo generazione deep links:

// URL ricevuta
$receipt_url = '';
if ($payment && !empty($payment->receipt_url)) {
    $receipt_url = $payment->receipt_url;
}

// Aggiungi ai dati email:
WeCoop_Multilingual_Email::send(
    $user->user_email,
    'service_payment_required',
    [
        'nome' => $nome,
        'servizio' => $servizio,
        'numero_pratica' => $numero_pratica,
        'importo' => '€' . number_format($importo, 2, ',', '.'),
        'button_url' => $redirect_pagamento,
        'web_url' => $web_payment_url,
        'deep_link' => $deep_link_pagamento,
        'receipt_url' => $receipt_url  // ← AGGIUNGI QUESTO
    ],
    $user_id
);
```

Modifica template email in `class-multilingual-email.php`:

```php
// In build_service_payment_required_content(), dopo il box pagamento:

<?php if (!empty($data['receipt_url'])): ?>
    <table style="width: 100%; max-width: 600px; margin: 20px 0;">
        <tr>
            <td style="text-align: center; padding: 15px; background: #e3f2fd; border-radius: 5px;">
                <p style="margin: 0 0 10px 0; font-size: 14px;">
                    <strong>📄 La tua ricevuta è pronta!</strong>
                </p>
                <a href="<?php echo esc_url($data['receipt_url']); ?>" 
                   style="display: inline-block; padding: 12px 24px; background: #2196f3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Scarica Ricevuta PDF
                </a>
                <p style="margin: 10px 0 0 0; font-size: 11px; color: #666;">
                    Valida per detrazioni fiscali
                </p>
            </td>
        </tr>
    </table>
<?php endif; ?>
```

---

## 📊 Model Dart - Estendi Richiesta

```dart
// lib/models/richiesta.dart

class Richiesta {
  final int id;
  final String numeroPratica;
  final String servizio;
  final String stato;
  final DateTime dataRichiesta;
  
  // Pagamento
  final int? paymentId;           // ← Aggiungi
  final String? paymentStatus;    // ← Aggiungi
  final double? importo;          // ← Aggiungi
  final String? receiptUrl;       // ← Aggiungi (opzionale)

  Richiesta({
    required this.id,
    required this.numeroPratica,
    required this.servizio,
    required this.stato,
    required this.dataRichiesta,
    this.paymentId,
    this.paymentStatus,
    this.importo,
    this.receiptUrl,
  });

  factory Richiesta.fromJson(Map<String, dynamic> json) {
    return Richiesta(
      id: json['id'] as int,
      numeroPratica: json['numero_pratica'] as String,
      servizio: json['servizio'] as String,
      stato: json['stato'] as String,
      dataRichiesta: DateTime.parse(json['data_richiesta']),
      
      // Pagamento
      paymentId: json['pagamento']?['id'] as int?,
      paymentStatus: json['pagamento']?['ricevuto'] == true ? 'paid' : null,
      importo: json['pagamento']?['importo'] != null 
          ? double.parse(json['pagamento']['importo'].toString())
          : null,
      receiptUrl: json['pagamento']?['receipt_url'] as String?,
    );
  }
}
```

---

## 🧪 Testing

### Test Backend

```bash
# Con JWT token valido
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  https://www.wecoop.org/wp-json/wecoop/v1/pagamento/123/ricevuta
```

Risposta attesa:
```json
{
  "success": true,
  "receipt_url": "https://www.wecoop.org/wp-content/uploads/ricevute/Ricevuta_123_2025.pdf",
  "filename": "Ricevuta_123_2025.pdf",
  "payment_id": 123,
  "numero_ricevuta": "123/2025",
  "importo": 50.00,
  "data_pagamento": "2025-12-27"
}
```

### Test Flutter

1. **Login** utente con pagamento completato
2. **Vai** a lista richieste
3. **Trova** richiesta pagata (badge verde)
4. **Click** "Scarica Ricevuta"
5. **Verifica** download completato
6. **Click** "Apri PDF" → Dovrebbe aprire con viewer PDF
7. **Test** "Condividi" → Dovrebbe aprire share sheet

---

## ✅ Checklist Implementazione

### Backend
- [ ] Aggiungi endpoint `/pagamento/{id}/ricevuta` in `class-servizi-endpoint.php`
- [ ] Implementa metodo `get_ricevuta_pdf()`
- [ ] Test con cURL (verifica ownership, errori)

### Frontend
- [ ] Aggiungi dipendenze in `pubspec.yaml`
- [ ] Implementa `getRicevutaPdf()` in `socio_service.dart`
- [ ] Implementa `downloadRicevuta()` in `socio_service.dart`
- [ ] Aggiungi pulsanti UI in card richiesta
- [ ] Implementa `_downloadRicevuta()` con loading
- [ ] Implementa `_apriRicevuta()` con OpenFile
- [ ] Implementa `_condividiRicevuta()` con Share
- [ ] Aggiungi permessi Android (AndroidManifest.xml)
- [ ] Aggiungi FileProvider config (file_paths.xml)
- [ ] Aggiungi permessi iOS (Info.plist)
- [ ] Estendi model `Richiesta` con campi pagamento

### Email (Opzionale)
- [ ] Modifica `send_payment_email()` per includere receipt_url
- [ ] Aggiorna template email con pulsante download
- [ ] Test email con link ricevuta

### Test
- [ ] Test download su Android
- [ ] Test download su iOS
- [ ] Test apertura PDF
- [ ] Test condivisione
- [ ] Test permessi storage
- [ ] Test errori (non autenticato, non proprietario, pagamento pending)

---

## 🎨 UI Variants

### Variant 1: Floating Action Button

```dart
// In richiesta detail screen
floatingActionButton: richiesta.paymentStatus == 'paid' 
    ? FloatingActionButton.extended(
        onPressed: () => _downloadRicevuta(richiesta),
        icon: Icon(Icons.download),
        label: Text('Scarica Ricevuta'),
      )
    : null,
```

### Variant 2: Bottom Sheet Menu

```dart
void _showRicevutaMenu(Richiesta richiesta) {
  showModalBottomSheet(
    context: context,
    builder: (context) => Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        ListTile(
          leading: Icon(Icons.download, color: Colors.blue),
          title: Text('Scarica Ricevuta'),
          onTap: () {
            Navigator.pop(context);
            _downloadRicevuta(richiesta);
          },
        ),
        ListTile(
          leading: Icon(Icons.share, color: Colors.blue),
          title: Text('Condividi Ricevuta'),
          onTap: () {
            Navigator.pop(context);
            _condividiRicevuta(richiesta);
          },
        ),
        ListTile(
          leading: Icon(Icons.preview, color: Colors.blue),
          title: Text('Visualizza Online'),
          onTap: () {
            Navigator.pop(context);
            _apriRicevutaWeb(richiesta);
          },
        ),
      ],
    ),
  );
}
```

### Variant 3: Badge con Info

```dart
Widget _buildPaymentBadge(Richiesta richiesta) {
  if (richiesta.paymentStatus != 'paid') return SizedBox.shrink();
  
  return Container(
    margin: EdgeInsets.only(top: 8),
    padding: EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: Colors.green[50],
      borderRadius: BorderRadius.circular(8),
      border: Border.all(color: Colors.green, width: 1),
    ),
    child: Row(
      children: [
        Icon(Icons.check_circle, color: Colors.green, size: 20),
        SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Pagamento completato',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: Colors.green[900],
                ),
              ),
              Text(
                'Ricevuta fiscale disponibile',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.green[700],
                ),
              ),
            ],
          ),
        ),
        IconButton(
          icon: Icon(Icons.download, color: Colors.green),
          onPressed: () => _downloadRicevuta(richiesta),
          tooltip: 'Scarica ricevuta',
        ),
      ],
    ),
  );
}
```

---

## 🚀 Deploy

1. **Backend**: Push codice WordPress → Pull su server
2. **Frontend**: 
   ```bash
   flutter pub get
   flutter build apk --release  # Android
   flutter build ios --release  # iOS
   ```
3. **Test** su dispositivi reali
4. **Deploy** su Play Store / App Store

---

## 📞 Supporto

Problemi comuni:

1. **Permessi negati**: Verifica AndroidManifest.xml e Info.plist
2. **File non si apre**: Installa PDF viewer sul dispositivo
3. **Download fallisce**: Verifica JWT token valido
4. **Ricevuta non generata**: Controlla log backend webhook
