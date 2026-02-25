# 🔐 Guida Implementazione Firma Digitale - wecoop App

**Versione:** 1.1  
**Data aggiornamento:** 25 Febbraio 2026  
**Destinatario:** Team app mobile (React Native/iOS/Android)

---

## 📋 Indice Rapido

1. [⚡ Quick Start](#quick-start)
2. [📊 Flow Completo di Firma](#flow-completo-di-firma)
3. [🔌 API Endpoints](#api-endpoints)
4. [🛡️ Autenticazione](#autenticazione)
5. [💡 Esempi di Implementazione](#esempi-di-implementazione)
6. [⚙️ Gestione Errori](#gestione-errori)
7. [🆕 Novità v1.1 - PDF](#novità-v11---pdf)
8. [❌ Breaking Changes](#breaking-changes)

---

## ⚡ Quick Start

### Il Flow Base (3 step):

```
1. App richiede documento PDF → Backend genera PDF
2. App chiede OTP al backend → Backend invia SMS
3. App firma documento con OTP → Backend salva firma digitale
```

### URL Base
```
https://wecoop.it/wp-json/wecoop/v1
```

### Header Obbligatori
```
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json
```

---

## 📊 Flow Completo di Firma

```
┌─────────────────────────────────────────────────────────────┐
│                    USER FLOW - FIRMA DOCUMENTO               │
└─────────────────────────────────────────────────────────────┘

1️⃣  USER APRE APP → Clicca "Visualizza documento"
    └─> App chiama: GET /documento-unico/{richiesta_id}/send
    └─> Backend torna: PDF URL + contenuto testo + hash

2️⃣  USER VEDE PDF → Clicca "Firma Documento"
    └─> App chiama: POST /firma-digitale/otp/generate
    └─> Backend: genera OTP, invia SMS al numero custodito
    └─> Backend torna: otp_id + scadenza + tentavi rimasti

3️⃣  USER RICEVE SMS CON OTP → Digita codice in App
    └─> App chiama: POST /firma-digitale/otp/verify
    └─> Backend: valida OTP (max 3 tentativi, scadenza 5 min)
    └─> Backend torna: otp_id confermato

4️⃣  USER CONFERMA FIRMA → App crea firma digitale
    └─> App chiama: POST /firma-digitale/sign
    └─> Invia: otp_id + documento_hash + metadata dispositivo
    └─> Backend: valida hash, crea firma FES, salva integrazione
    └─> Backend torna: firma_id + timestamp + status

5️⃣  FIRMA SALVATA → Backend ritorna conferma completa
    └─> App mostra: "✅ Documento firmato il [data/ora]"
    └─> User può visualizzare ricevuta firma
```

---

## 🔌 API Endpoints

### 1️⃣ Scarica Documento Unico (PDF + Testo)
**Novità v1.1**: Restituisce PDF, non più testo puro

```http
GET /documento-unico/{richiesta_id}/send
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "documento": {
    "url": "https://wecoop.it/wp-content/uploads/wecoop-documenti-unici/documento_123_2026-02-25.pdf",
    "contenuto_testo": "Spett.le [NOME]...\nDocumento compilato con i tuoi dati",
    "hash_sha256": "a1b2c3d4e5f6...",
    "nome": "Documento_Unico_123.pdf",
    "data_generazione": "2026-02-25T10:30:00Z"
  }
}
```

**Errori possibili:**
```json
{
  "success": false,
  "error": "Richiesta non trovata",
  "code": "NOT_FOUND"
}
```

---

### 2️⃣ Genera OTP per Firma

```http
POST /firma-digitale/otp/generate
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
Content-Type: application/json
```

**Body:**
```json
{
  "richiesta_id": 123,
  "user_id": 456,
  "telefono": "+39 3XX XXX XXXX"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "otp": {
    "id": "otp_abc123",
    "scadenza": "2026-02-25T10:35:00Z",
    "tentativi_rimasti": 3,
    "metodo_invio": "SMS"
  }
}
```

**Errori possibili:**
```json
{
  "success": false,
  "error": "Troppi tentativi. Riprova tra 1 ora",
  "code": "RATE_LIMITED"
}
```

---

### 3️⃣ Verifica OTP

```http
POST /firma-digitale/otp/verify
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
Content-Type: application/json
```

**Body:**
```json
{
  "otp_id": "otp_abc123",
  "otp_code": "123456"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "otp": {
    "id": "otp_abc123",
    "verified": true,
    "verified_at": "2026-02-25T10:32:00Z"
  }
}
```

**Errori possibili:**
```json
{
  "success": false,
  "error": "OTP non valido - 2 tentativi rimasti",
  "code": "INVALID_OTP",
  "tentativi_rimasti": 2
}
```

---

### 4️⃣ Firma Documento (FES - Firma Elettronica Semplice)

```http
POST /firma-digitale/sign
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
Content-Type: application/json
```

**Body:**
```json
{
  "otp_id": "otp_abc123",
  "richiesta_id": 123,
  "documento_hash": "a1b2c3d4e5f6...",
  "metadata": {
    "device_type": "iOS",
    "device_model": "iPhone 14",
    "app_version": "2.1.0",
    "ip_address": "auto"
  }
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "firma": {
    "id": "firma_xyz789",
    "richiesta_id": 123,
    "firma_timestamp": "2026-02-25T10:33:15Z",
    "metodo_firma": "FES",
    "status": "valida",
    "hash_verificato": true
  }
}
```

**Errori possibili:**
```json
{
  "success": false,
  "error": "Hash documento non corrisponde",
  "code": "HASH_MISMATCH"
}
```

---

### 5️⃣ Stato Firma Documento

```http
GET /firma-digitale/status/{richiesta_id}
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "firma": {
    "id": "firma_xyz789",
    "richiesta_id": 123,
    "status": "valida",
    "data_firma": "2026-02-25T10:33:15Z",
    "metodo": "FES",
    "device_firma": "iPhone 14"
  }
}
```

---

### 6️⃣ Verifica Integrità Firma (Consultabile)

```http
GET /firma-digitale/verifica/{firma_id}
```

**Headers:**
```
Authorization: Bearer JWT_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "verifica": {
    "firma_id": "firma_xyz789",
    "integrita": "verificata",
    "hash_corrisponde": true,
    "otp_validato": true,
    "timestamp_valido": true,
    "metodo_firma": "FES",
    "data_verifica": "2026-02-25T10:45:00Z"
  }
}
```

---

## 🛡️ Autenticazione

### Bearer Token via JWT

Tutti gli endpoint richiedono header:
```
Authorization: Bearer <JWT_TOKEN>
```

**Come ottenere il token:**

```bash
# Usa le credenziali WordPress
curl -X POST https://wecoop.it/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'
```

**Risposta:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user_email": "user@wecoop.it"
  }
}
```

### Token Scadenza
- **Valido per:** 7 giorni
- **Refresh:** Richiedi nuovo token quando scade
- **Salva in:** Secure Storage (iOS) / Keystore (Android)

---

## 💡 Esempi di Implementazione

### React Native - Scarica e Mostra PDF

```javascript
import * as FileSystem from 'expo-file-system';
import { WebView } from 'react-native-webview';

async function scaricaDocumento(richiestaId) {
  try {
    const response = await fetch(
      `https://wecoop.it/wp-json/wecoop/v1/documento-unico/${richiestaId}/send`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${jwtToken}`,
          'Content-Type': 'application/json'
        }
      }
    );

    if (!response.ok) throw new Error('Errore download');

    const data = await response.json();
    
    // Salva URL PDF per visualizzazione
    setPdfUrl(data.documento.url);
    setContenutoPdf(data.documento.contenuto_testo);
    setHashPdf(data.documento.hash_sha256);

  } catch (error) {
    Alert.alert('Errore', error.message);
  }
}

// Rendering
<WebView
  source={{ uri: pdfUrl }}
  style={{ flex: 1 }}
/>
```

### React Native - Flow Completo Firma

```javascript
import React, { useState } from 'react';
import { View, TextInput, Alert, ActivityIndicator } from 'react-native';

function SchermataFirma({ richiestaId, jwtToken, pdfHash }) {
  const [step, setStep] = useState('start'); // start → otp_sent → otp_verify → signed
  const [otpId, setOtpId] = useState(null);
  const [otpCode, setOtpCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [tentativiRimasti, setTentativiRimasti] = useState(3);

  // Step 1: Richiedi OTP
  async function richiestaOTP() {
    setLoading(true);
    try {
      const response = await fetch(
        'https://wecoop.it/wp-json/wecoop/v1/firma-digitale/otp/generate',
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${jwtToken}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            richiesta_id: richiestaId,
            user_id: userId, // Dal login
            telefono: userPhone // Salvato nel profilo
          })
        }
      );

      const data = await response.json();
      if (data.success) {
        setOtpId(data.otp.id);
        setStep('otp_sent');
        Alert.alert('✅ SMS Inviato', 'Controlla il tuo telefono');
      } else {
        Alert.alert('❌ Errore', data.error);
      }
    } catch (error) {
      Alert.alert('❌ Errore', error.message);
    } finally {
      setLoading(false);
    }
  }

  // Step 2: Verifica OTP
  async function verificaOTP() {
    if (otpCode.length !== 6) {
      Alert.alert('⚠️ Errore', 'Inserisci un codice di 6 cifre');
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(
        'https://wecoop.it/wp-json/wecoop/v1/firma-digitale/otp/verify',
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${jwtToken}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            otp_id: otpId,
            otp_code: otpCode
          })
        }
      );

      const data = await response.json();
      if (data.success) {
        setStep('otp_verify');
        // Ora puoi procedere a firmare
      } else {
        setTentativiRimasti(data.tentativi_rimasti || 0);
        Alert.alert('❌ OTP Non Valido', data.error);
      }
    } catch (error) {
      Alert.alert('❌ Errore', error.message);
    } finally {
      setLoading(false);
    }
  }

  // Step 3: Firma Documento
  async function firmaDocumento() {
    setLoading(true);
    try {
      const response = await fetch(
        'https://wecoop.it/wp-json/wecoop/v1/firma-digitale/sign',
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${jwtToken}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            otp_id: otpId,
            richiesta_id: richiestaId,
            documento_hash: pdfHash,
            metadata: {
              device_type: 'iOS', // O 'Android'
              device_model: DeviceInfo.getModel(),
              app_version: '2.1.0',
              ip_address: 'auto'
            }
          })
        }
      );

      const data = await response.json();
      if (data.success) {
        setStep('signed');
        Alert.alert(
          '✅ Documento Firmato',
          `Firma ID: ${data.firma.id}\nTimestamp: ${data.firma.firma_timestamp}`
        );
      } else {
        Alert.alert('❌ Errore Firma', data.error);
      }
    } catch (error) {
      Alert.alert('❌ Errore', error.message);
    } finally {
      setLoading(false);
    }
  }

  // UI Rendering
  return (
    <View style={{ padding: 20 }}>
      {step === 'start' && (
        <>
          <Text>Pronto a firmare il documento?</Text>
          <Button
            title="Firma Documento"
            onPress={richiestaOTP}
            disabled={loading}
          />
        </>
      )}

      {step === 'otp_sent' && (
        <>
          <Text>Inserisci il codice ricevuto via SMS</Text>
          <TextInput
            placeholder="000000"
            value={otpCode}
            onChangeText={setOtpCode}
            maxLength={6}
            keyboardType="number-pad"
          />
          <Text>Tentativi rimasti: {tentativiRimasti}</Text>
          <Button
            title="Verifica OTP"
            onPress={verificaOTP}
            disabled={loading}
          />
        </>
      )}

      {step === 'otp_verify' && (
        <>
          <Text>✅ OTP Verificato - Procedi con la firma</Text>
          <Button
            title="Firma Ora"
            onPress={firmaDocumento}
            disabled={loading}
          />
        </>
      )}

      {step === 'signed' && (
        <Text>✅ Documento firmato con successo!</Text>
      )}

      {loading && <ActivityIndicator size="large" />}
    </View>
  );
}

export default SchermataFirma;
```

### TypeScript - Fetch Helper

```typescript
interface FirmaDigitaleAPI {
  baseUrl: string;
  jwtToken: string;
}

export class FirmaService {
  private api: FirmaDigitaleAPI;

  constructor(baseUrl: string, jwtToken: string) {
    this.api = { baseUrl, jwtToken };
  }

  private headers() {
    return {
      'Authorization': `Bearer ${this.api.jwtToken}`,
      'Content-Type': 'application/json'
    };
  }

  async scaricaDocumento(richiestaId: number): Promise<DocumentoUnico> {
    const res = await fetch(
      `${this.api.baseUrl}/documento-unico/${richiestaId}/send`,
      { headers: this.headers() }
    );
    const data = await res.json();
    return data.documento;
  }

  async richiestaOTP(richiestaId: number, userId: number, telefono: string) {
    const res = await fetch(
      `${this.api.baseUrl}/firma-digitale/otp/generate`,
      {
        method: 'POST',
        headers: this.headers(),
        body: JSON.stringify({ richiesta_id: richiestaId, user_id: userId, telefono })
      }
    );
    return await res.json();
  }

  async verificaOTP(otpId: string, otpCode: string) {
    const res = await fetch(
      `${this.api.baseUrl}/firma-digitale/otp/verify`,
      {
        method: 'POST',
        headers: this.headers(),
        body: JSON.stringify({ otp_id: otpId, otp_code: otpCode })
      }
    );
    return await res.json();
  }

  async firmaDocumento(otpId: string, richiestaId: number, documentoHash: string) {
    const res = await fetch(
      `${this.api.baseUrl}/firma-digitale/sign`,
      {
        method: 'POST',
        headers: this.headers(),
        body: JSON.stringify({
          otp_id: otpId,
          richiesta_id: richiestaId,
          documento_hash: documentoHash,
          metadata: {
            device_type: 'iOS',
            device_model: 'iPhone',
            app_version: '2.1.0',
            ip_address: 'auto'
          }
        })
      }
    );
    return await res.json();
  }
}
```

---

## ⚙️ Gestione Errori

### Errori Comuni e Soluzioni

#### ❌ 401 Unauthorized
```json
{
  "code": "rest_authentication_missing_token",
  "message": "Authorization header missing"
}
```
**Soluzione:** Aggiungi header `Authorization: Bearer YOUR_TOKEN`

#### ❌ 429 Too Many Requests (Rate Limit)
```json
{
  "error": "Troppi tentativi. Riprova tra 1 ora",
  "code": "RATE_LIMITED"
}
```
**Soluzione:** Attendi 1 ora prima di nuovi tentativi; implementa UI warning

#### ❌ Hash Mismatch
```json
{
  "error": "Hash documento non corrisponde",
  "code": "HASH_MISMATCH"
}
```
**Soluzione:** 
1. Ricaricare il documento (GET /documento-unico/{id}/send)
2. Calcolare nuovo hash con stesso algoritmo (SHA-256)
3. Riavviare flow firma

#### ❌ OTP Scaduto (>5 minuti)
```json
{
  "error": "OTP scaduto",
  "code": "OTP_EXPIRED"
}
```
**Soluzione:** Richiedi nuovo OTP (POST /otp/generate)

#### ❌ 3 Tentativi OTP Falliti
```json
{
  "error": "Massimo 3 tentativi OTP raggiunto. Riprova tra 1 ora",
  "code": "OTP_MAX_ATTEMPTS",
  "tentativi_rimasti": 0
}
```
**Soluzione:** Attendi 1 ora, poi richiedi nuovo OTP

---

## 🆕 Novità v1.1 - PDF

### Cosa è Cambiato

#### Prima (v1.0)
```
GET /documento-unico/{id}/send
↓
Ritorna: { contenuto: "testo puro" }
App firma: testo puro
```

#### Adesso (v1.1)
```
GET /documento-unico/{id}/send
↓
Ritorna: { url: "PDF", contenuto_testo: "testo compilato", hash_sha256: "hash" }
App mostra: PDF professionale
App firma: hash del PDF
```

### Perché PDF?

✅ **Più professionale** - Documento formattato con loghi e header  
✅ **Leggale** - Conforme a GDPR/CAD italiano  
✅ **Immutabile** - Hash SHA-256 del PDF (non del testo)  
✅ **Archivio** - Facile da salvare e consultare

### Come Usarlo

```javascript
// 1. Scarica documento
const response = await fetch(`/documento-unico/${richiestaId}/send`);
const doc = (await response.json()).documento;

// 2. Mostra PDF all'utente
<WebView source={{ uri: doc.url }} />

// 3. Usa il contenuto_testo compilato per il display
<Text>{doc.contenuto_testo}</Text>

// 4. Usa l'hash per firmare
await firmaDocumento(doc.hash_sha256);
```

### Test Locale

```bash
# Scarica il PDF generato
curl -H "Authorization: Bearer TOKEN" \
  https://wecoop.it/wp-json/wecoop/v1/documento-unico/123/send | jq

# Verifica file PDF in server
ls -la wp-content/uploads/wecoop-documenti-unici/
```

---

## ❌ Breaking Changes

### Migrazione da v1.0 a v1.1

Se la tua app usa ancora la v1.0, devi aggiornare:

#### 1. Metodo Endpoint (⚠️ BREAKING)
```javascript
// ❌ VECCHIO (v1.0)
POST /documento-unico/{id}/send
Body: {}

// ✅ NUOVO (v1.1)
GET /documento-unico/{id}/send
```

#### 2. Response Format (⚠️ BREAKING)
```javascript
// ❌ VECCHIO (v1.0)
{
  "contenuto": "Testo puro del documento"
}

// ✅ NUOVO (v1.1)
{
  "documento": {
    "url": "https://...pdf",
    "contenuto_testo": "Testo compilato",
    "hash_sha256": "abc123...",
    "nome": "documento_123.pdf"
  }
}
```

#### 3. Firma Hash (⚠️ BREAKING)
```javascript
// ❌ VECCHIO (v1.0)
Hash = SHA-256(testo_puro)

// ✅ NUOVO (v1.1)
Hash = SHA-256(file_pdf_binario)
```

### Checklist Migrazione

- [ ] Aggiorna endpoint da POST a GET
- [ ] Estrai `documento.url` e mostra in WebView
- [ ] Usa `documento.hash_sha256` (non calcolare da testo)
- [ ] Aggiorna parsing della response (new nested structure)
- [ ] Test flow completo in staging
- [ ] Deploy app aggiornata

---

## 📞 Supporto

### Errori o Domande?

1. **Dev Backend**: Contatta team infra per debug SMS/PDF  
2. **API Issues**: Verifica endpoint response con Postman  
3. **JWT Token**: Assicurati sia valido (scadenza 7 giorni)  
4. **iOS/Android**: Usa `Authorization` header non token nel path

### Ambito FES (Non FEA)

Questa implementazione usa **FES** (Firma Elettronica Semplice):
- ✅ OTP SMS + Hash + Timestamp
- ✅ Legale per documenti amministrativi
- ✅ GDPR compliant
- ✅ Costo: ~€0.015/firma

**NON usa FEA** (Firma Elettronica Avanzata - certificati digitali):
- ❌ Richiede certificato HSM
- ❌ Costo: €50+/anno per certificato
- ❌ Processo più complesso
- ❌ Future roadmap, non prioritario

---

**🚀 Sei pronto a integrare! Suggerimenti? Feedback? Contatta via Slack.**
