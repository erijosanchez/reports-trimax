# Documentación: Módulo Acuerdos Comerciales & Descuentos Especiales

## Índice
1. [Estructura de Archivos](#1-estructura-de-archivos)
2. [Acuerdos Comerciales](#2-acuerdos-comerciales)
3. [Descuentos Especiales](#3-descuentos-especiales)
4. [Permisos por Rol](#4-permisos-por-rol)
5. [Flujo de Notificaciones](#5-flujo-de-notificaciones)
6. [Exportación y Estadísticas](#6-exportación-y-estadísticas)
7. [Estructura de Base de Datos](#7-estructura-de-base-de-datos)

---

## 1. Estructura de Archivos

```
app/
├── Http/Controllers/
│   ├── ComercialController.php              (Acuerdos — 1,735 líneas)
│   └── DescuentosEspecialesController.php   (Descuentos — 691 líneas)
├── Models/
│   ├── AcuerdoComercial.php
│   └── DescuentoEspecial.php
├── Exports/
│   └── AcuerdosComercialesExport.php
└── Notifications/
    ├── AcuerdoCreado.php
    ├── AcuerdoAprobado.php
    ├── AcuerdoVigente.php
    ├── AcuerdoDeshabilitado.php
    ├── AcuerdoRehabilitado.php
    ├── AcuerdoExtendido.php
    ├── AcuerdosExtendidosMasivo.php
    ├── DescuentoEspecialCreado.php
    ├── DescuentoEspecialAprobado.php
    ├── DescuentoEspecialDeshabilitado.php
    └── DescuentoEspecialRehabilitado.php

resources/views/comercial/
├── acuerdos.blade.php               (2,395 líneas)
└── descuentos-especiales.blade.php
```

---

## 2. Acuerdos Comerciales

### Acceso
**URL:** `/comercial/acuerdos`

### ¿Qué es un Acuerdo Comercial?
Documento formal que registra un acuerdo entre Trimax y una sede/cliente, especificando condiciones de promoción, marcas, materiales y vigencia en el tiempo. Tiene un proceso de doble aprobación antes de activarse.

---

### 2.1 Creación de un Acuerdo

**Quién puede crear:** Cualquier usuario autenticado con acceso al módulo.

**Datos del formulario:**

| Campo | Descripción | Requerido |
|---|---|---|
| Sede | Nombre de la sede del cliente | Sí |
| RUC | RUC del cliente | Sí |
| Razón Social | Nombre legal del cliente | Sí |
| Consultor | Nombre del consultor asignado | Sí |
| Ciudad | Ciudad del cliente | Sí |
| Acuerdo Comercial | Descripción detallada del acuerdo | Sí |
| Tipo de Promoción | Tipo (ej. descuento, bonificación, etc.) | Sí |
| Marca | Marca involucrada | Sí |
| AR | Número de AR | No |
| Diseños | Diseños acordados | No |
| Material | Material incluido en el acuerdo | No |
| Fecha Inicio | Inicio de vigencia | Sí |
| Fecha Fin | Fin de vigencia | Sí |
| Archivos Adjuntos | PDFs, imágenes, documentos de soporte | No (múltiples) |

**Proceso de creación (flujo técnico):**
1. Usuario completa formulario en modal y presiona "Guardar"
2. Se envía vía AJAX con `FormData` (multipart) a `POST /comercial/acuerdos/crear`
3. El controlador genera el número único: `AC-2026-0001` (formato `AC-AÑO-SECUENCIA`)
4. Los archivos se guardan en: `storage/app/public/acuerdos/{numero_acuerdo}/`
5. Los metadatos de archivos se guardan como JSON en el campo `archivos_adjuntos`
6. El acuerdo se crea con estados iniciales:
   - `estado` = **Solicitado**
   - `validado` = **Pendiente**
   - `aprobado` = **Pendiente**
7. Se envían notificaciones por email a: Planeamiento, Aprobador (Sergio), Creador y usuarios de la sede
8. La tabla se recarga automáticamente

---

### 2.2 Flujo de Aprobación

```
CREACIÓN
    ↓
 [Estado: Solicitado | Validado: Pendiente | Aprobado: Pendiente]
    ↓
PASO 1 — VALIDACIÓN (solo planeamiento.comercial@trimaxperu.com)
    ↓ Aprueba                         ↓ Rechaza
 Validado: Aprobado              Validado: Rechazado
    ↓
PASO 2 — APROBACIÓN (solo smonopoli@trimaxperu.com)
    ↓ Aprueba                         ↓ Rechaza
 Aprobado: Aprobado              Aprobado: Rechazado
    ↓
AMBOS APROBADOS + DENTRO DE FECHA
    ↓
 [Estado: VIGENTE]
    ↓ (después de fecha_fin)
 [Estado: VENCIDO] ← calculado automáticamente
```

**Validación:**
- Endpoint: `POST /comercial/acuerdos/{id}/validar`
- Solo disponible para `planeamiento.comercial@trimaxperu.com`
- Puede aprobar o rechazar con mensaje opcional
- Registra: `validado_por`, `validado_at`

**Aprobación:**
- Endpoint: `POST /comercial/acuerdos/{id}/aprobar`
- Solo disponible para `smonopoli@trimaxperu.com`
- Puede aprobar o rechazar con mensaje opcional
- Registra: `aprobado_por`, `aprobado_at`
- Al aprobar se ejecuta `actualizarEstado()` que calcula si pasa a **Vigente**

**Estado Calculado (lógica automática):**
El modelo tiene el accessor `getEstadoCalculadoAttribute()` que se ejecuta en cada carga:
- Si `validado = Aprobado` Y `aprobado = Aprobado` Y `fecha_inicio <= hoy <= fecha_fin` → **Vigente**
- Si `validado = Aprobado` Y `aprobado = Aprobado` Y `fecha_fin < hoy` → **Vencido**
- En cualquier otro caso → **Solicitado**

---

### 2.3 Edición de un Acuerdo

- Endpoint: `PUT /comercial/acuerdos/{id}/editar`
- Al editar, **se resetean las validaciones** (vuelven a Pendiente)
- Permite modificar todos los campos del formulario original
- Se pueden agregar o reemplazar archivos adjuntos

---

### 2.4 Extensión de Fechas

**Extensión Individual:**
- Endpoint: `POST /comercial/acuerdos/{id}/extender`
- Modal con campo de nueva fecha fin + motivo obligatorio
- Registra: `motivo_extension`, `extendido_at`, `extendido_por`
- Disponible para: Sergio y Planeamiento

**Extensión Masiva:**
- Endpoint: `POST /comercial/acuerdos/extender-masivo`
- Interfaz estilo Gmail: checkboxes en cada fila para seleccionar múltiples acuerdos
- Modal único con nueva fecha fin + motivo
- Aplica la misma extensión a todos los seleccionados
- Se envía notificación individual a cada creador y un resumen a los admins

---

### 2.5 Deshabilitar y Rehabilitar

**Deshabilitar:**
- Endpoint: `POST /comercial/acuerdos/{id}/deshabilitar`
- Requiere motivo obligatorio
- Establece `habilitado = false`
- Registra: `motivo_deshabilitacion`, `deshabilitado_at`, `deshabilitado_por`

**Rehabilitar:**
- Endpoint: `POST /comercial/acuerdos/{id}/rehabilitar`
- Requiere motivo obligatorio
- Establece `habilitado = true`
- Registra: `motivo_rehabilitacion`, `rehabilitado_at`, `rehabilitado_por`

---

### 2.6 Cambios Manuales de Estado (Admin)

Para correcciones administrativas sin pasar por el flujo normal:
- `POST /comercial/acuerdos/{id}/cambiar-validacion` → Cambia `validado` manualmente
- `POST /comercial/acuerdos/{id}/cambiar-aprobacion` → Cambia `aprobado` manualmente

---

### 2.7 Vista y Filtros

**La pantalla principal tiene dos pestañas:**

**Pestaña "Acuerdos":**
- 4 tarjetas de resumen: Total | Vigentes | Pendientes | Vencidos
- Tabla paginada (20 registros por página)
- Filtros disponibles:
  - Por usuario creador (dropdown dinámico cargado desde la BD)
  - Por sede
  - Por estado (Todos / Solicitado / Vigente / Vencido / Deshabilitado)
  - Búsqueda libre por número de acuerdo o RUC
- Botón "Exportar Excel"
- Selección múltiple para extensión masiva (solo admins)

**Pestaña "Estadísticas":**
- 6 gráficas Chart.js cargadas de forma diferida (al hacer clic en la pestaña)
- Visualizaciones: por estado, por sede, por tipo de promoción, por marca, por mes, por consultor

---

### 2.8 Rutas Completas — Acuerdos

| Método | URL | Controlador@Método | Nombre |
|---|---|---|---|
| GET | `/comercial/acuerdos` | `ComercialController@acuerdos` | `acuerdos` |
| GET | `/comercial/acuerdos/obtener` | `ComercialController@obtenerAcuerdos` | `acuerdos.obtener` |
| GET | `/comercial/acuerdos/usuarios` | `ComercialController@obtenerUsuariosCreadores` | `acuerdos.usuarios` |
| GET | `/comercial/acuerdos/exportar` | `ComercialController@exportarAcuerdos` | `acuerdos.exportar` |
| POST | `/comercial/acuerdos/crear` | `ComercialController@crearAcuerdo` | `acuerdos.crear` |
| PUT | `/comercial/acuerdos/{id}/editar` | `ComercialController@editarAcuerdo` | `acuerdos.editar` |
| POST | `/comercial/acuerdos/{id}/validar` | `ComercialController@validarAcuerdo` | `acuerdos.validar` |
| POST | `/comercial/acuerdos/{id}/aprobar` | `ComercialController@aprobarAcuerdo` | `acuerdos.aprobar` |
| POST | `/comercial/acuerdos/{id}/deshabilitar` | `ComercialController@deshabilitarAcuerdo` | `acuerdos.deshabilitar` |
| POST | `/comercial/acuerdos/{id}/rehabilitar` | `ComercialController@rehabilitarAcuerdo` | `acuerdos.rehabilitar` |
| POST | `/comercial/acuerdos/{id}/extender` | `ComercialController@extenderAcuerdo` | `acuerdos.extender` |
| POST | `/comercial/acuerdos/extender-masivo` | `ComercialController@extenderMasivoAcuerdos` | `acuerdos.extender-masivo` |
| POST | `/comercial/acuerdos/{id}/cambiar-validacion` | `ComercialController@cambiarValidacion` | `acuerdos.cambiar-validacion` |
| POST | `/comercial/acuerdos/{id}/cambiar-aprobacion` | `ComercialController@cambiarAprobacion` | `acuerdos.cambiar-aprobacion` |
| GET | `/comercial/acuerdos/{id}/archivo/{index}` | `ComercialController@descargarArchivo` | `acuerdos.descargar` |

---

## 3. Descuentos Especiales

### Acceso
**URL:** `/comercial/descuentos-especiales`

### ¿Qué es un Descuento Especial?
Solicitud puntual de descuento vinculada a una factura u orden específica. A diferencia de los acuerdos (que tienen vigencia temporal), los descuentos son transacciones individuales con un proceso de dos pasos: aplicación + aprobación.

---

### 3.1 Tipos de Descuento

| Tipo | Descripción |
|---|---|
| ANULACION | Anulación del cobro |
| CORTESIA | Descuento por cortesía comercial |
| DESCUENTO ADICIONAL | Descuento sobre el precio acordado |
| DESCUENTO TOTAL | Descuento del 100% |
| OTROS | Otros casos no categorizados |

---

### 3.2 Creación de un Descuento

**Datos del formulario:**

| Campo | Descripción | Requerido |
|---|---|---|
| Número de Factura | Factura relacionada | No |
| Número de Orden | Orden relacionada | No |
| Sede | Sede del cliente | Sí |
| RUC | RUC del cliente | Sí |
| Razón Social | Nombre legal del cliente | Sí |
| Consultor | Consultor asignado | Sí |
| Ciudad | Ciudad del cliente | Sí |
| Descuento Especial | Descripción del descuento solicitado | Sí |
| Tipo | Tipo de descuento (ENUM) | Sí |
| Marca | Marca involucrada | No |
| AR | Número de AR | No |
| Diseños | Diseños involucrados | No |
| Material | Material involucrado | No |
| Archivos Adjuntos | Documentos de soporte | No |

**Número auto-generado:** `DE-2026-0001` (formato `DE-AÑO-SECUENCIA`)

**Archivos guardados en:** `storage/app/public/descuentos/{numero_descuento}/`

**Estados iniciales al crear:**
- `aplicado` = **Pendiente**
- `aprobado` = **Pendiente**

---

### 3.3 Flujo de Aprobación

```
CREACIÓN
    ↓
 [Aplicado: Pendiente | Aprobado: Pendiente]
    ↓
PASO 1 — APLICACIÓN (solo auditor.junior@trimaxperu.com)
    ↓ Aprueba                         ↓ Rechaza
 Aplicado: Aprobado             Aplicado: Rechazado
    ↓
PASO 2 — APROBACIÓN (Sergio o Planeamiento)
    ↓ Aprueba                         ↓ Rechaza
 Aprobado: Aprobado             Aprobado: Rechazado
    ↓
 [DESCUENTO APROBADO — completado]
```

**Aplicación:**
- Endpoint: `POST /comercial/descuentos-especiales/{id}/aplicar`
- Solo disponible para `auditor.junior@trimaxperu.com`
- Registra: `aplicado_por`, `aplicado_at`

**Aprobación:**
- Endpoint: `POST /comercial/descuentos-especiales/{id}/aprobar`
- Disponible para `smonopoli@trimaxperu.com` y `planeamiento.comercial@trimaxperu.com`
- Registra: `aprobado_por`, `aprobado_at`
- Al aprobar se envían notificaciones a todos los involucrados

---

### 3.4 Edición, Deshabilitar y Rehabilitar

**Edición:**
- Endpoint: `PUT /comercial/descuentos-especiales/{id}/editar`
- Al editar se resetean las aprobaciones (vuelven a Pendiente)

**Deshabilitar:**
- Endpoint: `POST /comercial/descuentos-especiales/{id}/deshabilitar`
- Motivo obligatorio, registra quién y cuándo

**Rehabilitar:**
- Endpoint: `POST /comercial/descuentos-especiales/{id}/rehabilitar`
- Motivo obligatorio, registra quién y cuándo

---

### 3.5 Cambios Manuales de Estado (Admin)

- `POST /comercial/descuentos-especiales/{id}/cambiar-aplicacion` → Cambia `aplicado` manualmente
- `POST /comercial/descuentos-especiales/{id}/cambiar-aprobacion` → Cambia `aprobado` manualmente

---

### 3.6 Rutas Completas — Descuentos

| Método | URL | Controlador@Método | Nombre |
|---|---|---|---|
| GET | `/comercial/descuentos-especiales` | `DescuentosEspecialesController@index` | `descuentos.index` |
| GET | `/comercial/descuentos-especiales/obtener` | `DescuentosEspecialesController@obtenerDescuentos` | `descuentos.obtener` |
| GET | `/comercial/descuentos-especiales/usuarios` | `DescuentosEspecialesController@obtenerUsuariosCreadores` | `descuentos.usuarios` |
| POST | `/comercial/descuentos-especiales/crear` | `DescuentosEspecialesController@crearDescuento` | `descuentos.crear` |
| PUT | `/comercial/descuentos-especiales/{id}/editar` | `DescuentosEspecialesController@editarDescuento` | `descuentos.editar` |
| POST | `/comercial/descuentos-especiales/{id}/aplicar` | `DescuentosEspecialesController@aplicarDescuento` | `descuentos.aplicar` |
| POST | `/comercial/descuentos-especiales/{id}/aprobar` | `DescuentosEspecialesController@aprobarDescuento` | `descuentos.aprobar` |
| POST | `/comercial/descuentos-especiales/{id}/cambiar-aplicacion` | `DescuentosEspecialesController@cambiarAplicacion` | `descuentos.cambiar-aplicacion` |
| POST | `/comercial/descuentos-especiales/{id}/cambiar-aprobacion` | `DescuentosEspecialesController@cambiarAprobacion` | `descuentos.cambiar-aprobacion` |
| POST | `/comercial/descuentos-especiales/{id}/deshabilitar` | `DescuentosEspecialesController@deshabilitarDescuento` | `descuentos.deshabilitar` |
| POST | `/comercial/descuentos-especiales/{id}/rehabilitar` | `DescuentosEspecialesController@rehabilitarDescuento` | `descuentos.rehabilitar` |
| GET | `/comercial/descuentos-especiales/{id}/archivo/{index}` | `DescuentosEspecialesController@descargarArchivo` | `descuentos.archivo` |

---

## 4. Permisos por Rol

| Acción | Email / Rol |
|---|---|
| **Validar** acuerdos | `planeamiento.comercial@trimaxperu.com` |
| **Aprobar** acuerdos | `smonopoli@trimaxperu.com` |
| **Extender** acuerdos (individual y masivo) | Sergio + Planeamiento |
| **Deshabilitar / Rehabilitar** acuerdos | Sergio + Planeamiento |
| **Aplicar** descuentos | `auditor.junior@trimaxperu.com` |
| **Aprobar** descuentos | Sergio + Planeamiento |
| **Deshabilitar / Rehabilitar** descuentos | Sergio + Planeamiento |
| **Ver solo su sede** | Usuarios de sede (filtrado automático) |
| **Ver todos los registros** | Admins (Sergio, Planeamiento, Auditor Junior) |

---

## 5. Flujo de Notificaciones

### Acuerdos

| Evento | Destinatarios |
|---|---|
| Nuevo acuerdo creado | Planeamiento, Sergio, Creador, usuarios de la sede |
| Acuerdo aprobado (ambas validaciones OK) | Creador, usuarios de la sede |
| Acuerdo pasa a Vigente | `auditor.junior@trimaxperu.com` |
| Acuerdo deshabilitado | Sergio, Planeamiento, Creador |
| Acuerdo rehabilitado | Sergio, Planeamiento, Creador |
| Acuerdo extendido (individual) | Sergio, Planeamiento, Creador |
| Extensión masiva | Creadores individualmente + resumen a admins |

### Descuentos

| Evento | Destinatarios |
|---|---|
| Nuevo descuento creado | Auditor Junior, Sergio, Planeamiento, Creador, usuarios de la sede |
| Descuento aprobado | Auditor Junior, Sergio, Planeamiento, Creador, usuarios de la sede |
| Descuento deshabilitado | Sergio, Planeamiento, Auditor Junior, Creador, usuarios de la sede |
| Descuento rehabilitado | Sergio, Planeamiento, Auditor Junior, Creador, usuarios de la sede |

---

## 6. Exportación y Estadísticas

### Exportar a Excel (Acuerdos)
- Botón en la parte superior de la tabla
- Endpoint: `GET /comercial/acuerdos/exportar`
- Exporta **37 columnas**: datos del acuerdo, fechas, estados, quién validó/aprobó, fechas de cada acción, motivos de extensión y deshabilitación
- Cabecera con fondo azul corporativo (`#1e3a8a`)
- Auto-ajuste de columnas activado
- Título de hoja: "Acuerdos Comerciales"
- Librería utilizada: `Maatwebsite/Excel`

### Estadísticas (ambos módulos)
Las gráficas se cargan de forma diferida — solo al hacer clic en la pestaña "Estadísticas" para no afectar el tiempo de carga inicial.

| # | Gráfica |
|---|---|
| 1 | Distribución por estado |
| 2 | Volumen por sede |
| 3 | Por tipo de promoción / tipo de descuento |
| 4 | Por marca |
| 5 | Evolución mensual (línea de tiempo) |
| 6 | Por consultor |

---

## 7. Estructura de Base de Datos

### Tabla `acuerdos_comerciales`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | ID auto-incremental |
| `numero_acuerdo` | VARCHAR UNIQUE | Ej: `AC-2026-0001` |
| `user_id` | FK INT | ID del usuario creador |
| `sede` | VARCHAR | Nombre de la sede |
| `ruc` | VARCHAR | RUC del cliente |
| `razon_social` | VARCHAR | Nombre legal |
| `consultor` | VARCHAR | Consultor asignado |
| `ciudad` | VARCHAR | Ciudad |
| `acuerdo_comercial` | TEXT | Descripción del acuerdo |
| `tipo_promocion` | VARCHAR | Tipo de promoción |
| `marca` | VARCHAR | Marca involucrada |
| `ar` | VARCHAR | Número AR |
| `disenos` | VARCHAR | Diseños acordados |
| `material` | VARCHAR | Material incluido |
| `fecha_inicio` | DATE | Inicio de vigencia |
| `fecha_fin` | DATE | Fin de vigencia |
| `estado` | ENUM | `Solicitado / Vigente / Vencido / Deshabilitado` |
| `validado` | ENUM | `Pendiente / Aprobado / Rechazado` |
| `aprobado` | ENUM | `Pendiente / Aprobado / Rechazado` |
| `validado_por` | VARCHAR | Email del validador |
| `validado_at` | TIMESTAMP | Fecha de validación |
| `aprobado_por` | VARCHAR | Email del aprobador |
| `aprobado_at` | TIMESTAMP | Fecha de aprobación |
| `habilitado` | BOOLEAN | `true / false` |
| `motivo_deshabilitacion` | TEXT | Razón del deshabilitado |
| `deshabilitado_at` | TIMESTAMP | Fecha de deshabilitación |
| `deshabilitado_por` | VARCHAR | Email de quien deshabilitó |
| `motivo_rehabilitacion` | TEXT | Razón de la rehabilitación |
| `rehabilitado_at` | TIMESTAMP | Fecha de rehabilitación |
| `rehabilitado_por` | VARCHAR | Email de quien rehabilitó |
| `motivo_extension` | TEXT | Razón de la extensión |
| `extendido_at` | TIMESTAMP | Fecha de extensión |
| `extendido_por` | VARCHAR | Email de quien extendió |
| `archivos_adjuntos` | JSON | Array con metadatos de archivos adjuntos |
| `deleted_at` | TIMESTAMP | Soft delete (eliminación lógica) |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Fecha de última modificación |

---

### Tabla `descuentos_especiales`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | ID auto-incremental |
| `numero_descuento` | VARCHAR UNIQUE | Ej: `DE-2026-0001` |
| `user_id` | FK INT | ID del creador |
| `numero_factura` | VARCHAR | Factura relacionada |
| `numero_orden` | VARCHAR | Orden relacionada |
| `sede` | VARCHAR | Sede del cliente |
| `ruc` | VARCHAR | RUC del cliente |
| `razon_social` | VARCHAR | Nombre legal |
| `consultor` | VARCHAR | Consultor asignado |
| `ciudad` | VARCHAR | Ciudad |
| `descuento_especial` | TEXT | Descripción del descuento |
| `tipo` | ENUM | `ANULACION / CORTESIA / DESCUENTO ADICIONAL / DESCUENTO TOTAL / OTROS` |
| `marca` | VARCHAR | Marca involucrada |
| `ar` | VARCHAR | Número AR |
| `disenos` | VARCHAR | Diseños involucrados |
| `material` | VARCHAR | Material involucrado |
| `aplicado` | ENUM | `Pendiente / Aprobado / Rechazado` |
| `aprobado` | ENUM | `Pendiente / Aprobado / Rechazado` |
| `aplicado_por` | VARCHAR | Email de quien aplicó |
| `aplicado_at` | TIMESTAMP | Fecha de aplicación |
| `aprobado_por` | VARCHAR | Email de quien aprobó |
| `aprobado_at` | TIMESTAMP | Fecha de aprobación |
| `habilitado` | BOOLEAN | Estado activo del registro |
| `motivo_deshabilitacion` | TEXT | Razón del deshabilitado |
| `deshabilitado_at` | TIMESTAMP | Fecha de deshabilitación |
| `deshabilitado_por` | VARCHAR | Email de quien deshabilitó |
| `motivo_rehabilitacion` | TEXT | Razón de la rehabilitación |
| `rehabilitado_at` | TIMESTAMP | Fecha de rehabilitación |
| `rehabilitado_por` | VARCHAR | Email de quien rehabilitó |
| `archivos_adjuntos` | JSON | Array con metadatos de archivos adjuntos |
| `deleted_at` | TIMESTAMP | Soft delete (eliminación lógica) |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Fecha de última modificación |

---

## Diagrama de Flujo General

```
USUARIO REGULAR               PLANEAMIENTO                  SERGIO (smonopoli)
      |                            |                               |
      | Crea Acuerdo               |                               |
      | → Estado: Solicitado       |                               |
      | → Notif email →→→→→→→→→→→ |                               |
      |                            | Valida (Aprueba/Rechaza)      |
      |                            | → validado: Aprobado          |
      |                            | → Notif email →→→→→→→→→→→→→ |
      |                            |                               | Aprueba/Rechaza
      |                            |                               | → aprobado: Aprobado
      |                            |                               | → Estado: VIGENTE
      | ←←← Notif: Acuerdo Vigente ←←←←←←←←←←←←←←←←←←←←←←←←←← |
      |                            |                               |
      | (fecha_fin supera hoy)     |                               |
      | → Estado: VENCIDO          |                               |
      |   (calculado automático)   |                               |


USUARIO REGULAR               AUDITOR JUNIOR                SERGIO o PLANEAMIENTO
      |                            |                               |
      | Crea Descuento             |                               |
      | → Aplicado: Pendiente      |                               |
      | → Notif email →→→→→→→→→→→ |                               |
      |                            | Aplica (Aprueba/Rechaza)      |
      |                            | → aplicado: Aprobado          |
      |                            | → Notif email →→→→→→→→→→→→→ |
      |                            |                               | Aprueba/Rechaza
      |                            |                               | → aprobado: Aprobado
      | ←←← Notif: Descuento Aprobado ←←←←←←←←←←←←←←←←←←←←←←← |
```

---

*Documentación generada el 2026-04-20 — Proyecto Reports Trimax*
