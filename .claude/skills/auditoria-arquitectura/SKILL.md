---
name: auditoria-arquitectura
description: Audita y corrige la arquitectura de código de reports-trimax (Laravel 12). Úsala cuando se pida revisar arquitectura, refactorizar controladores, extraer servicios, mover validación a Form Requests, revisar autorización/Policies, detectar código muerto, o antes de agregar un módulo nuevo para que siga las convenciones correctas. Palabras clave que la disparan - "arquitectura", "refactor", "controlador gordo", "fat controller", "capa de servicio", "code smell", "deuda técnica", "limpiar código", "revisar estructura".
---

# Auditoría de arquitectura — reports-trimax

Aplica a la capa de aplicación PHP (`app/`, `routes/`, `resources/views/`).
Para Docker, red, contenedores y `.env`, usa `auditoria-infraestructura`.

## Contexto del proyecto

Laravel 12 / PHP 8.2. CRM interno de reportes comerciales. ~22k LOC en `app/`,
~44k LOC en `resources/views/`. Sin build de frontend activo: las vistas usan un
template Bootstrap estático servido desde `public/assets/`.

Paquetes clave: `spatie/laravel-permission` (roles), `laravel/horizon` (colas),
`google/apiclient` (Sheets), `maatwebsite/excel`, `barryvdh/laravel-dompdf`.

## Regla de oro: no rompas lo que ya funciona

Este sistema está en uso con datos reales. La deuda técnica documentada es
conocida y tolerada. **No refactorices de forma masiva sin que el usuario lo
pida explícitamente.** El patrón correcto es:

1. Audita y reporta.
2. Propón la corrección con el diff concreto.
3. Aplica solo lo aprobado, módulo por módulo.

Corregir un controlador de 1800 líneas "de una" es la forma más rápida de
romper producción. Si el usuario aprueba un refactor, hazlo por método, no por
archivo.

## Cómo auditar

Corre estas mediciones antes de opinar. Los números concretos son lo que hace
útil el reporte.

### 1. Controladores gordos

```bash
find app/Http/Controllers -name '*.php' -exec wc -l {} + | sort -rn | head -15
```

Umbral del proyecto: **>400 líneas = candidato a extraer servicio**. Los
conocidos son `ComercialController` (~1800), `LeadTimeController` (~980),
`CobranzaSedesController` (~820), `DescuentosEspecialesController` (~780).

### 2. Validación inline vs Form Requests

```bash
grep -rhoE -e 'request->validate|Validator::make' app/Http/Controllers/ | wc -l
ls app/Http/Requests/ app/Http/Requests/*/
```

Cuando un mismo conjunto de reglas aparece en `store()` y `update()`, es Form
Request. Ojo: `app/Http/Requests/Auth/LoginRequest.php` ya está en su namespace
correcto — no lo muevas de vuelta a la raíz (rompe PSR-4).

### 3. Autorización real

```bash
ls app/Policies/
grep -rhoE -e '\$this->authorize|Gate::|->can\(' app/Http/Controllers/ | sort | uniq -c
grep -rnE "middleware\('(role|permission|role_or_permission)" routes/web.php | wc -l
```

Existen `DashboardPolicy`, `FilePolicy`, `UserPolicy` pero casi no se invocan.
La autorización real vive en middleware de rol por ruta. Eso **funciona**, pero
significa que un método llamado desde otro punto no está protegido. Al reportar,
distingue "sin autorización" de "autorizado por middleware de ruta" — no son lo
mismo y confundirlos genera alarmas falsas.

### 4. Código huérfano

```bash
for c in $(find app/Http/Controllers -name '*.php' | sed 's|.*/||;s|\.php||'); do
  grep -rq "$c" routes/ 2>/dev/null || echo "huérfano: $c"
done
```

Antes de declarar algo muerto, confirma que tampoco se referencia desde vistas,
comandos, jobs ni otros controladores:

```bash
grep -rn "NombreControlador" app/ resources/ routes/ --include=*.php
```

### 5. Consultas y N+1

```bash
grep -rhoE -e '->get\(\)' app/Http/Controllers/ | wc -l
grep -rhoE -e '->paginate\(' app/Http/Controllers/ | wc -l
grep -rhoE -e '->with\(' app/Http/Controllers/ | wc -l
```

Un `->get()` sobre una tabla que crece (`ordenes_historico`, `vouchers`,
`ventas`) sin `paginate()` ni `limit()` es un incidente esperando fecha.

### 6. Transacciones

```bash
grep -rn -e 'DB::transaction\|DB::beginTransaction' app/
```

Toda escritura que toque más de una tabla debe ir en transacción. Revisa
especialmente los flujos de vouchers (cabecera + `voucher_facturas`) y
requerimientos (registro + historial).

### 7. Capa de vistas

```bash
find resources/views -name '*.blade.php' -exec wc -l {} + | sort -rn | head -10
grep -rl '<script' resources/views/ | wc -l
```

## Convenciones a exigir en código nuevo

- **Controlador delgado**: recibe request, delega, devuelve respuesta. La lógica
  de negocio va en `app/Services/`.
- **Validación en Form Request**, no inline, cuando la regla se repite o pasa de
  ~5 campos.
- **Nombres en español** para dominio (`AsignacionBasesService`,
  `ReporteCobranza`) — es la convención existente, respétala.
- **SQL crudo permitido** en servicios de reporting (`DB::select` con bindings),
  prohibido en controladores. Nunca concatenes input en el SQL.
- **Paginación obligatoria** en cualquier listado sobre tabla transaccional.
- Sigue el patrón de logging del proyecto al depurar módulos migrados.

## Formato del reporte

Agrupa por severidad y **siempre con el número medido**, no con adjetivos:

| Severidad | Criterio |
|---|---|
| Alta | Riesgo de datos, seguridad o caída (escritura sin transacción, listado sin límite, input sin validar) |
| Media | Deuda que frena el desarrollo (controlador gordo, validación duplicada) |
| Baja | Limpieza (código muerto, inconsistencia de nombres) |

Cierra con las correcciones ordenadas por relación impacto/esfuerzo, y ofrece
aplicarlas. No las apliques sin aprobación.

El diagnóstico vigente está en `docs/auditoria/ARQUITECTURA.md` — léelo primero
para no re-descubrir lo ya documentado, y actualízalo cuando algo se corrija.
