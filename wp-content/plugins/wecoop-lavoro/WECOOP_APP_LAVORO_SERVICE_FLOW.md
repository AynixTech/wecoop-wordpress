# WECOOP_APP_LAVORO_SERVICE_FLOW.md

## Servicio
**Servicio Lavoro - WECOOP APP**

## Objetivo
Permitir a los usuarios crear su perfil laboral, generar un CV con AI y activar un servicio de acompanamiento para acceso a oportunidades laborales, con gestion interna y comunicacion via WhatsApp (Wachatbot).

---

# 1. Entrada al area Lavoro

## Seccion APP
**Lavoro e Formazione**

## Opciones:
- Crear CV con AI
- Activar servicio trabajo
- Ver estado candidatura
- Ver formacion

---

# 2. Creacion del perfil laboral

## 2.1 Datos personales
- Nombre
- Apellido
- Fecha nacimiento
- Nacionalidad
- Direccion
- Telefono
- Email
- Foto CV (opcional)

## 2.2 Formacion
Campos:
- Titulo
- Instituto
- Pais
- Fecha inicio/fin
- Descripcion

Upload:
- diploma
- certificados

## 2.3 Experiencia laboral
Campos:
- Cargo
- Empresa
- Pais
- Fecha inicio/fin
- Descripcion

Upload:
- referencias
- contratos
- certificados

## 2.4 Lenguas
- Idioma
- Nivel (A1-C2)
- Certificado (opcional)

## 2.5 Competencias
- digitales
- tecnicas
- relacionales
- licencia conducir
- otras

## 2.6 Objetivo laboral
- trabajo buscado
- pais
- disponibilidad
- sector
- interes formacion

---

# 3. Generacion CV con AI

## Output:
- CV formato Europass-style
- CV moderno
- CV simple
- Perfil profesional resumen

## Opciones:
- con/sin foto
- idioma (IT, ES, EN)

---

# 4. Vista previa y guardado

Acciones:
- editar
- descargar PDF
- descargar Word
- guardar perfil

Resultado:
**Perfil Lavoro WECOOP creado**

---

# 5. Activacion servicio trabajo

Pantalla:
**Quieres que WECOOP te ayude a encontrar trabajo?**

CTA:
**Activar servicio**

---

# 6. Consentimiento y documento unico

Usuario acepta:
- tratamiento datos (GDPR)
- envio CV a terceros
- contacto via WhatsApp
- condiciones servicio

Accion:
- firma digital
- generacion PDF mandato

---

# 7. Validacion final del perfil

Backend verifica:
- perfil completo
- documentos
- coherencia CV

Si falta info:
- solicitud via Wachatbot

Estado:
- listo / incompleto

---

# 8. Integracion Wachatbot

## Trigger:
Activacion servicio

## Mensajes automaticos:
- confirmacion recepcion
- solicitud documentos
- recordatorios
- actualizacion estado

## Uso:
- chatbot
- operador humano

---

# 9. Gestion backend (core system)

Estados:
- perfil creado
- CV generado
- servicio activado
- consentimiento firmado
- en revision
- listo envio
- enviado
- en evaluacion
- entrevista
- cerrado
- no seleccionado

IMPORTANTE:
Backend = sistema principal (source of truth)

---

# 10. Envio a partners

Tipos:
- agencias trabajo
- empresas
- cooperativas

Ejemplos:
- Manpower
- Adecco

Modo inicial:
- manual

Modo futuro:
- automatico (API/email)

---

# 11. Tracking usuario

APP muestra:
- CV enviado
- en revision
- entrevista
- resultado

Wachatbot:
- notificaciones paralelas

---

# 12. Feedback

Acciones:
- sugerencias mejora CV
- orientacion laboral
- recomendacion formacion

---

# Arquitectura tecnica

## APP
- UI/UX
- formularios
- CV
- tracking

## BACKEND
- DB usuarios
- CV
- estados
- documentos
- consents
- logica negocio

## WACHATBOT
- messaging
- chatbot
- reminders
- operator interface

## STORAGE
- documentos
- CV
- PDFs

---

# APIs implementadas (WordPress proxy)

Base URL:
`/wp-json/wecoop/v1`

## Profile
- POST `/lavoro/profile`
- GET `/lavoro/profile/{id}`
- PUT/PATCH `/lavoro/profile/{id}`

Aliases de compatibilidad:
- POST `/profile`
- GET `/profile/{id}`
- PUT/PATCH `/profile/{id}`

## CV
- POST `/cv/generate`
- GET `/cv/{cv_id}`
- GET `/cv`
- GET `/cv/templates`
- POST `/cv/preview`

## Consent
- POST `/lavoro/consent`
- Alias: POST `/consent`

## Job Service
- POST `/lavoro/job/activate`
- GET `/lavoro/job/status/{id}`
- PUT/PATCH `/lavoro/job/status/{id}`

Aliases:
- POST `/job/activate`
- GET `/job/status/{id}`
- PUT/PATCH `/job/status/{id}`

## Wachatbot
- POST `/lavoro/wachatbot/send`
- POST `/lavoro/wachatbot/trigger`

Aliases:
- POST `/wachatbot/send`
- POST `/wachatbot/trigger`

---

# Consideraciones clave

- No es agencia de trabajo
- Es plataforma de orientacion
- Datos ingresados una sola vez
- Perfil reutilizable

---

# Valor estrategico

El sistema crea un:
**Database de perfiles profesionales**

Utilizable para:
- empleo
- formacion
- emprendimiento
- microcredito

---

# Nota tecnica de naming plugin

El plugin fue renombrado a carpeta/slug:
`wp-content/plugins/wecoop-lavoro`

Archivo principal:
`wecoop-lavoro.php`
