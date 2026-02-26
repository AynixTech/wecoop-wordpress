# Download Documento Unico + Allegato Firma (PDF Unito)

Questa guida descrive come usare il nuovo endpoint API che scarica **un solo PDF** contenente:
- Documento Unico
- Allegato/attestato firma digitale (se documento firmato)

## Endpoint

**Method:** `GET`  
**URL:** `/wp-json/wecoop/v1/documento-unico/{id}/download-merged`

Esempio completo:

`https://www.wecoop.org/wp-json/wecoop/v1/documento-unico/123/download-merged`

## Autenticazione

Richiede JWT Bearer token (stesso meccanismo degli altri endpoint `wecoop/v1`).

Header obbligatorio:

`Authorization: Bearer <TOKEN_JWT>`

## Query Params

- `force_merge` (opzionale, boolean, default `false`)
  - `true`: forza il merge al momento della richiesta (se disponibile attestato firma)
  - `false`: usa il merged già salvato, se presente

Esempio:

`/wp-json/wecoop/v1/documento-unico/123/download-merged?force_merge=true`

## Risposta

Risposta binaria PDF:

- `Content-Type: application/pdf`
- `Content-Disposition: attachment; filename="Documento_Unico_<id>_Firmato.pdf"`

## Esempio cURL

```bash
curl -L \
  -H "Authorization: Bearer <TOKEN_JWT>" \
  "https://www.wecoop.org/wp-json/wecoop/v1/documento-unico/123/download-merged" \
  --output Documento_Unico_123.pdf
```

## Esempio Flutter (Dio)

```dart
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';

Future<File> downloadDocumentoUnicoMerged({
  required int richiestaId,
  required String jwt,
  bool forceMerge = false,
}) async {
  final dio = Dio();
  final dir = await getApplicationDocumentsDirectory();
  final filePath = '${dir.path}/Documento_Unico_$richiestaId.pdf';

  final url =
      'https://www.wecoop.org/wp-json/wecoop/v1/documento-unico/$richiestaId/download-merged'
      '${forceMerge ? '?force_merge=true' : ''}';

  await dio.download(
    url,
    filePath,
    options: Options(
      headers: {
        'Authorization': 'Bearer $jwt',
      },
      responseType: ResponseType.bytes,
      followRedirects: true,
      validateStatus: (status) => status != null && status < 500,
    ),
  );

  return File(filePath);
}
```

## Errori principali

- `401 unauthorized` → utente non autenticato
- `403 forbidden` → utente non proprietario della richiesta
- `404 invalid_request` → richiesta non valida/non trovata
- `404 document_not_found` → documento unico non ancora disponibile
- `404 attestato_missing` → documento firmato ma attestato firma mancante
- `500 merge_failed` → errore tecnico durante merge PDF

## Note operative

1. Se il documento **non è firmato**, viene scaricato il Documento Unico corrente.
2. Se il documento **è firmato** e attestato disponibile, endpoint restituisce il PDF unito.
3. Con `force_merge=true`, l'API rigenera il merge prima del download.
