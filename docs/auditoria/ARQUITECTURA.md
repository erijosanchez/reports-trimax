# Auditoría de arquitectura — reports-trimax

**Fecha:** 2026-07-24
**Alcance:** capa de aplicación PHP (`app/`, `routes/`, `resources/views/`)
**Stack:** Laravel 12 · PHP 8.2 · Blade · MySQL 8 · Redis
**Estado del sistema:** en producción con datos reales (65 usuarios, 874 vouchers)

> Documento vivo. Actualízalo cuando se corrija un hallazgo.
> Para Docker, red y `.env`, ver [INFRAESTRUCTURA.md](INFRAESTRUCTURA.md).

---

## Resumen ejecutivo

El sistema **funciona y está bien estructurado en lo esencial**: hay capa de
servicios, Policies, Form Requests, jobs, comandos, middleware propio y uso
correcto de Redis para sesiones y colas. No es un proyecto improvisado.

La deuda se concentra en **tres puntos**, y todos son del mismo tipo: lógica que
debería estar centralizada y está copiada a mano en decenas de sitios.

| # | Hallazgo | Severidad | Esfuerzo |
|---|---|---|---|
| A1 | 212 decisiones de autorización dispersas en controladores y vistas | **Alta** | 🟡 Piloto 2026-07-27 (Vouchers) |
| A2 | 90 listados sin paginación sobre tablas transaccionales | **Alta** | 🟡 Piloto 2026-07-27 (2 tablas más grandes auditadas) |
| A3 | Solo 5 transacciones para escrituras multi-tabla | **Alta** | ✅ Resuelto 2026-07-27 (Voucher, Desbloqueo, Requerimientos) |
| A4 | 10 controladores >400 LOC (el mayor, 1799) | Media | Alto |
| A5 | 75 validaciones inline vs 6 Form Requests | Media | Medio |
| A6 | Pipeline Vite configurado pero inutilizado; 46 MB de assets en git | Media | Diferido a propósito 2026-07-27 |
| A7 | 3 controladores muertos + `routes/auth.php` vacío importando 5 clases inexistentes | Baja | ✅ Resuelto 2026-07-27 |
| A8 | Suite de tests solo cubre el scaffolding de Breeze | Media | 🟡 Piloto 2026-07-27 |

**Métricas base**

| Métrica | Valor |
|---|---|
| LOC en `app/` | 21 985 |
| LOC en `resources/views/` | 44 045 |
| Controladores | 41 |
| Modelos | 30 |
| Servicios | 10 |
| Rutas web | 219 |
| Tests | 9 archivos (todos scaffolding) |

La proporción llama la atención: **el doble de código en vistas que en toda la
aplicación**. Es consecuencia de A6.

---

## A1 · Autorización dispersa — Severidad alta

> 🟡 **Piloto aplicado 2026-07-27, no migración completa** — el enfoque
> incremental que ya recomendaba esta sección. No se tocaron los 212 sitios;
> se construyó la base y se probó en un solo módulo (Vouchers, el sugerido
> aquí mismo).
>
> **Hallazgo adicional al implementar esto:** `app/Providers/AuthServiceProvider.php`
> existía completo — las 3 Policies, un `Gate::before` que da acceso total a
> `super_admin` — pero **nunca estuvo registrado en `bootstrap/providers.php`**,
> así que nada de eso corría nunca. Pasó desapercibido porque los únicos 6
> `$this->authorize()` del código son sobre `Dashboard`, que resuelve su
> Policy por convención de nombre sin necesitar el provider; `FilePolicy`
> (para `UploadedFile`, no sigue la convención de nombre) y `UserPolicy` no
> los invoca nada hoy, así que su ausencia nunca causó un síntoma visible. Se
> registró el provider — sin riesgo, porque no cambia ningún comportamiento
> ya observable (Dashboard seguía funcionando igual, File/User Policies no
> las usa nadie todavía).
>
> **1. `SedeScope`** (`app/Models/Scopes/SedeScope.php`) — exactamente el
> código propuesto más abajo. Aplicado a `Voucher` vía `booted()`. Verificado
> contra los 874 vouchers reales: un usuario de sede (Huánuco) pasó de ver
> 874 a ver solo sus 8; admin/finanzas siguen viendo los 874. Escape hatch
> (`Voucher::withoutGlobalScope(SedeScope::class)`) probado y funcionando.
>
> **2. Gates** en el `AuthServiceProvider` ya activo:
> `Gate::define('ver-vouchers', ...)` y `Gate::define('revisar-vouchers', ...)`,
> envolviendo los helpers existentes sin cambiar su lógica. Un único punto de
> `VoucherController::index()` migrado de `$user->puedeVerVouchers()` a
> `Gate::denies('ver-vouchers')` como demostración del patrón — el resto del
> controlador (y los otros 40) se queda como está, a migrar módulo por
> módulo cuando se toque cada uno por otra razón, tal como recomienda esta
> sección.
>
> Cubierto con `tests/Feature/SedeScopeTest.php` (6 casos): sede solo ve lo
> suyo, `findOrFail` de otra sede da 404, finanzas/super_admin ven todo, el
> escape hatch funciona, y el Gate refleja el helper. Es el test que "hace
> que la frontera de datos deje de depender de que nadie olvide una
> comprobación" que pide A8.
>
> **2026-07-27 (continuación) — los 4 modelos candidatos, migrados.**
> `SolicitudDesbloqueo`, `ReporteCobranza`, `ReporteCajaChica` y
> `ReporteComentarios` ya aplican `SedeScope` vía `booted()`, mismo patrón que
> `Voucher`. Se verificó antes que ningún controlador usa `DB::table()` sobre
> esas 4 tablas (el scope solo actúa sobre Eloquent) y que los filtros
> manuales existentes (`->where('sede', $user->sede)` en
> `CobranzaSedesController`, `CajaChicaSedesController`,
> `ComentariosSedesController`, `DesbloqueoController`) quedan redundantes
> pero no rotos — se dejan como están, a limpiar cuando se toque cada
> controlador por otra razón. De paso, `ProductivyController::index()` traía
> las 3 tablas de reportes completas (todas las sedes) a memoria y filtraba
> recién al armar la tabla; con el scope activo un usuario de sede ahora deja
> de traer filas ajenas incluso en ese paso intermedio.
>
> Cubierto con 9 casos nuevos en `tests/Feature/SedeScopeTest.php` (mismo
> archivo, misma estructura que el piloto de Voucher): por modelo, lista
> restringida a la sede propia, `findOrFail` de otra sede da 404, y
> finanzas ve todas las sedes. No se repitió el test del escape hatch
> (`withoutGlobalScope`) por modelo — es un mecanismo de `SedeScope` en sí,
> ya cubierto una vez, no algo que varíe por modelo.
>
> **Mismo efecto secundario que en Vouchers:** `DesbloqueoController::servirArchivo()`
> usa `SolicitudDesbloqueo::findOrFail()`, así que el scope ahora intercepta
> antes que el chequeo de permiso del controlador — un usuario de otra sede
> pasa de recibir 403 a recibir 404 al pedir el adjunto ajeno. Se actualizó
> `DesbloqueoAttachmentDiskTest::test_a_different_sede_cannot_download_the_attachment`
> para reflejarlo (igual que ya se había hecho con `VoucherAttachmentDiskTest`).
> Suite completa verificada tras el cambio: 69 tests en verde, el único
> fallo (`ExampleTest`) es preexistente y no relacionado (la ruta `/`
> redirige a login, no es un 200 — scaffolding por defecto de Laravel).
>
> Quedan sin migrar los ~200 sitios restantes de `isSede()`/`isAdmin()`/
> `isSuperAdmin()` fuera de la frontera de datos (permisos de acción, no de
> lectura) — eso es lo que cubre el paso 2 de la corrección propuesta
> (Gates), aplicado hasta ahora solo a Vouchers.
>
> ✅ **2026-07-27 (cierre) — paso 2 completo: un Gate por cada uno de los
> 20 `puedeX()` de `User`**, registrados en `AuthServiceProvider` (antes
> solo existían 2, para Vouchers). Los helpers siguen existiendo — el Gate
> es la fachada, no un reemplazo. Migrado un punto de entrada por
> controlador en los 4 módulos que ya tenían `SedeScope`
> (`CobranzaSedesController`, `CajaChicaSedesController`,
> `ComentariosSedesController`, `DesbloqueoController`), mismo criterio
> demostrativo que Vouchers: el `index()` de cada uno pasa de llamar al
> helper directamente a `Gate::denies('ver-cobranza-sedes')` /
> `Gate::denies('ver-desbloqueo')`. El resto de las ~30 llamadas al mismo
> helper dentro de esos controladores (y los ~35 controladores restantes
> de la app) se quedan como están, a migrar módulo por módulo cuando se
> toquen por otra razón — esto **no** fue un reemplazo mecánico de los 212
> sitios originales, que seguiría siendo el enfoque equivocado que esta
> misma sección descarta más abajo.
>
> Cubierto con 12 tests nuevos en `tests/Feature/GatesTest.php`: los 20
> Gates responden igual que su helper (super_admin pasa los 20 vía
> `Gate::before`; un usuario sin rol ni flags falla los gates de solo
> columna; un flag explícito los habilita; sede/finanzas/rrhh pasan los
> gates de su dominio) y los 4 endpoints migrados devuelven 403/200 según
> corresponda. Suite completa: 87 tests en verde, único fallo preexistente
> y no relacionado (`ExampleTest`).
>
> Con esto, A1 pasa de piloto a su alcance completo tal como lo definió
> esta sección desde el inicio: **no** migrar los 212 sitios de una vez —
> centralizar el mecanismo (Global Scope + Gates) y dejar la migración de
> cada controlador para cuando se toque por otra razón. Ese mecanismo ya
> existe y está probado en 5 modelos y 5 controladores.

### Qué pasa

La autenticación está bien: casi todas las rutas viven dentro de un grupo
`Route::middleware(['auth', ...])` en `routes/web.php:76`. No hay rutas de
negocio abiertas.

El problema es la **autorización**. `User` expone ~20 helpers ad-hoc
(`app/Models/User.php:149-286`): `isAdmin()`, `isSede()`, `isSuperAdmin()`,
`puedeVerCobranzaSedes()`, `puedeRevisarReportesSedes()`, etc. Esos helpers se
invocan a mano por todas partes:

| Lugar | Ocurrencias |
|---|---|
| `->isSede()` en controladores | 51 |
| `->isSuperAdmin()` en controladores | 48 |
| `->isAdmin()` en controladores | 47 |
| `->isConsultor()` en controladores | 2 |
| Helpers de rol en vistas Blade | 64 |
| **Total** | **212** |

Mientras tanto existen tres Policies (`DashboardPolicy`, `FilePolicy`,
`UserPolicy`) que **solo se invocan 6 veces** en 41 controladores. Y solo 3 de
219 rutas usan `middleware('role:...')` — las dos secciones de admin.

### Por qué importa

El filtro por sede es una **frontera de datos**, no cosmética. En
`CobranzaSedesController.php:43-64` se decide con `$user->isSede() && $user->sede`
qué sede ve el usuario. Ese mismo criterio está reescrito a mano en 18
controladores. Basta que un método nuevo olvide la comprobación para que un
usuario de una sede vea datos de otra, y nada en el código lo impide: no hay un
punto único que falle en voz alta.

Es deuda de tipo silencioso — no rompe nada hoy, y por eso crece.

### Corrección propuesta

**No** reescribir los 212 sitios. El camino incremental:

1. **Centralizar el filtro de sede en un Global Scope**, que es donde debe vivir:

```php
// app/Models/Scopes/SedeScope.php
class SedeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if ($user && $user->isSede() && $user->sede) {
            $builder->where($model->getTable().'.sede', $user->sede);
        }
    }
}
```

Aplicado en los modelos con datos por sede (`ReporteCobranza`, `Voucher`,
`ReporteCajaChica`, `ReporteComentarios`), el filtro pasa a ser el
comportamiento por defecto y hay que pedir explícitamente saltárselo
(`withoutGlobalScope`) — el fallo se vuelve visible en vez de silencioso.

2. **Mover los `puedeVerX()` a Gates**, registrados en un solo sitio:

```php
// app/Providers/AuthServiceProvider.php
Gate::define('ver-cobranza-sedes', fn (User $u) => $u->puedeVerCobranzaSedes());
```

Así las vistas usan `@can('ver-cobranza-sedes')` y los controladores
`$this->authorize('ver-cobranza-sedes')`. Los helpers de `User` siguen
existiendo — se convierten en el detalle de implementación del Gate, no en la
API que consume todo el sistema.

3. **Migrar módulo por módulo**, empezando por el de mayor exposición
   (Vouchers o Cobranza). No hay que tocar los 41 controladores para obtener el
   beneficio.

---

## A2 · Listados sin paginación — Severidad alta

> 🟡 **Piloto auditado 2026-07-27** en las dos tablas más grandes de la base
> (`asignacion_bases`, 159 429 filas; `ordenes_historico`, 143 573 filas),
> siguiendo el criterio de esta misma sección: "priorizar por tamaño de
> tabla, no por orden alfabético". No se auditaron los otros ~80 sitios en
> los 21 controladores restantes — quedan pendientes, con menor riesgo por
> tratarse de tablas más chicas.
>
> **Resultado, con más matices de lo que sugiere el conteo de "90 `->get()`":**
>
> - **`asignacion_bases`** (`AsignacionBasesService.php`, 9 usos) — **sin
>   problema real**. Los 9 son agregaciones `GROUP BY`/`SUM` filtradas por
>   `anio`/`mes` y cacheadas 30 min con `Cache::remember()`. El resultado que
>   llega a PHP está acotado por días/meses/productos, no por las 159K filas
>   de la tabla. No hay nada que paginar: son datasets para gráficos, no
>   listados.
> - **`OrdenesXUsuarioController`** — **sin problema real**. Mismo patrón:
>   agregaciones acotadas, y el único listado fila-por-fila
>   (`data()`, tabla detallada) ya está paginado a mano
>   (`->offset()->limit()->get()` + `count()` aparte, línea 133-140) — el
>   patrón correcto, solo que sin usar el helper `->paginate()` de Laravel.
> - **`ComercialController::exportarExcel()`** — **sin problema real**. Ya
>   usa `->chunk(1000, ...)` con `response()->stream()`, exactamente el
>   patrón que recomienda esta sección para exportaciones.
> - **`ComercialController::obtenerOrdenesRecientes()`** — acotado a 2000
>   filas máximo pase lo que pase (`min($limite, 2000)`), no es paginación
>   real pero tampoco es un riesgo de memoria — no se tocó.
> - **`ComercialController::obtenerOrdenes()` — el único hallazgo real.**
>   `if ($limite > 0) { $query->limit($limite); }` dispara con `limite=0`
>   como un *unbounded query* a propósito: `resources/views/comercial/consulta-orden.blade.php:598-604`
>   manda `limite: 0` deliberadamente (comentario en el propio JS: "sin
>   límite, trae TODOS los registros"), con un timeout de AJAX de 120
>   segundos y un contador de "tiempo de carga" visible para el usuario — el
>   equipo ya sabe que es lento y lo viene tolerando. El resultado completo
>   se carga en un array de JavaScript y se pagina **del lado del cliente**
>   en el navegador (`ordenesData`, `currentPage`, `renderizarTabla()`).
>
>   **No se aplicó ningún cambio aquí** — se decidió explícitamente dejarlo
>   documentado en vez de tocarlo, porque:
>   1. Es una función deliberada (no un bug accidental) que el equipo ya
>      usa y tolera en su forma actual.
>   2. El arreglo correcto no es solo de backend: hay que mover la
>      paginación al servidor (`page`/`per_page` en `obtenerOrdenes()`,
>      mismo patrón que ya usa bien `OrdenesXUsuarioController::data()`) *y*
>      cambiar el JS de `consulta-orden.blade.php` para pedir página por
>      página en vez de todo de una vez — un cambio de UX que no se puede
>      verificar sin un navegador real.
>   3. Acotar solo el backend sin avisar al frontend (ej. tope duro de 5000)
>      haría que el botón "cargar histórico completo" mostrara datos
>      incompletos sin que el usuario se entere — peor que el problema
>      actual.
>
>   **Recomendación para cuando se aborde:** convertir `obtenerOrdenes()` a
>   paginación real (`page`/`per_page`, igual que `OrdenesXUsuarioController::data()`)
>   y cambiar el botón "Cargar histórico completo" por scroll infinito o
>   "cargar más", probando en navegador antes de desplegar.

### Qué pasa

```
->get()       90 ocurrencias en controladores
->paginate()  14 ocurrencias
```

Seis de cada siete listados traen la tabla completa a memoria de PHP.

### Por qué importa

Sobre tablas que crecen sin techo (`ordenes_historico`, `vouchers` — ya con 874
filas y 1830 facturas asociadas —, `ventas`, `user_activity_logs`) esto degrada
de forma no lineal. El `memory_limit` está en **1024 MB**
(`docker/php/uploads.ini`), un valor que sugiere que ya se chocó contra este
problema y se subió el límite en vez de paginar. Eso compra tiempo, no lo
resuelve.

### Corrección propuesta

Auditar los 90 `->get()` y clasificarlos:

- **Catálogo corto y acotado** (sedes, roles, tipos) → dejar como está.
- **Tabla transaccional** → `->paginate(50)`, o `->cursor()` / `->chunk()` si
  alimenta una exportación.
- **Exportaciones Excel/PDF** → `maatwebsite/excel` soporta
  `FromQuery` + `WithChunkReading`; es el cambio de mayor impacto y menor riesgo.

Priorizar por tamaño de tabla, no por orden alfabético.

---

## A3 · Escrituras sin transacción — Severidad alta

> ✅ **Resuelto 2026-07-27** para los tres flujos nombrados abajo. Envueltos
> tal como sugiere esta sección — "sin reestructurar nada" — salvo un ajuste
> puntual: donde el método hacía `update()/create() → notificar por correo →
> ActivityLogService::log()`, el `log()` se movió antes de la notificación y
> ambos quedaron dentro de la misma transacción; la notificación (I/O de red)
> se dejó fuera para no sostener la conexión de BD abierta durante el envío.
>
> - **`VoucherController`**: `store()` (voucher + N facturas),
>   `addFactura()`/`removeFactura()` (factura + recálculo de `total` +
>   reseteo de revisión), `revisar()` (`save()` + registro de actividad).
> - **`DesbloqueoController`**: `store()` (`create()` + registro de
>   actividad), `revisar()` (`save()` + registro de actividad).
> - **`RequerimientoPersonalController`**: `actualizarEstado()`,
>   `asignarResponsable()`, `registrarEtapa()`, `actualizarInfoRrhh()` y
>   `firmar()` — todos con el mismo patrón `update()`/`create()` +
>   `registrarHistorial()` (+ `ActivityLogService::log()` donde aplica). Su
>   `store()` ya tenía `DB::transaction` desde antes (una de las 5 originales).
>
> No se tocó `UserMarketingController` (usa `DB::beginTransaction()` manual,
> ya envuelto, fuera del alcance de esta ronda) ni el resto de controladores
> con escrituras multi-tabla que no se nombraban aquí.
>
> Suite completa corrida después: mismo resultado de siempre (las pruebas de
> Vouchers/Desbloqueo pasan por el mismo camino que estos métodos, así que
> ejercitan la reordenación sin que se rompiera nada). No se forzó un fallo a
> mitad de transacción para probar el rollback en sí (`DB::transaction` es
> comportamiento estándar de Laravel, no un mecanismo nuevo que necesite
> reprobarse) — la cobertura nueva confirma que el camino feliz sigue
> produciendo el mismo resultado tras envolver y reordenar.

### Qué pasa

Solo **5** usos de `DB::transaction` / `DB::beginTransaction` en todo `app/`,
frente a flujos que claramente escriben en más de una tabla:

- Vouchers: cabecera `vouchers` + N filas en `voucher_facturas` (1830 registros
  actuales, ~2 facturas por voucher).
- Requerimientos: `requerimientos_personal` + `requerimiento_historial`.
- Revisiones de finanzas: actualización de estado + registro de actividad.

### Por qué importa

Un fallo a mitad de operación deja el registro cabecera sin sus detalles, o al
revés. No hay error visible: queda un dato inconsistente que aparece semanas
después en un reporte que no cuadra.

### Corrección propuesta

Es el hallazgo de **mejor relación impacto/esfuerzo** de todo el documento.
Envolver el método, sin reestructurar nada:

```php
DB::transaction(function () use ($request, $voucher) {
    $voucher->save();
    $voucher->facturas()->createMany($request->validated()['facturas']);
    ActivityLogService::registrar(...);
});
```

Empezar por `VoucherController` y `RequerimientoPersonalController`.

---

## A4 · Controladores gordos — Severidad media

**10 controladores superan 400 LOC:**

| Controlador | LOC |
|---|---|
| `ComercialController` | 1799 |
| `LeadTimeController` | 976 |
| `CobranzaSedesController` | 816 |
| `DescuentosEspecialesController` | 776 |
| `VoucherController` | 667 |
| `RequerimientoPersonalController` | 660 |
| `ComentariosSedesController` | 572 |
| `CajaChicaSedesController` | 570 |
| `DesbloqueoController` | 457 |
| `UserMarketingController` | 426 |

`ComercialController` concentra por sí solo el 8 % del código de `app/`.

La capa de servicios existe (10 servicios, `AsignacionBasesService` con 522 LOC
bien encapsuladas) — el patrón está establecido, simplemente no se aplicó de
forma consistente.

### Corrección propuesta

**No refactorizar en bloque.** Regla de oro para este proyecto: se extrae
servicio cuando ya se va a tocar el módulo por otra razón. Un refactor de 1799
líneas sin tests que lo respalden (ver A8) es riesgo puro.

Orden sugerido, y solo cuando toque trabajar en cada uno:
`ComercialController` → `ComercialReportService` + `AcuerdosService`; luego
`LeadTimeController` → `LeadTimeService`.

---

## A5 · Validación inline — Severidad media

> 🟡 **Piloto aplicado 2026-07-27** en `DescuentosEspecialesController` (9 de
> las 75 validaciones inline), el segundo controlador de mayor concentración
> después de `ComercialController`. No se tocaron los otros 66 sitios —
> quedan pendientes, mismo criterio incremental que A1/A2.
>
> **5 Form Requests nuevos** (`app/Http/Requests/`), cada uno cubriendo un
> ruleset que aparecía repetido letra por letra en 2+ métodos:
> `AccionDescuentoRequest` (`aplicarDescuento`/`aprobarDescuento`),
> `MotivoDescuentoRequest` (`deshabilitarDescuento`/`rehabilitarDescuento`),
> `NuevoEstadoDescuentoRequest` (`cambiarAplicacion`/`cambiarAprobacion`) y
> `StoreDescuentoEspecialRequest`/`UpdateDescuentoEspecialRequest` (el
> ruleset de 15 campos de `crearDescuento`/`editarDescuento`). Se preservó
> la única divergencia real que ya existía entre create y update
> (`comentarios` obligatorio al crear, opcional al editar) documentándola en
> el propio `UpdateDescuentoEspecialRequest` en vez de decidir cuál de las
> dos reglas es la correcta — no era el alcance de este cambio.
>
> `editarDescuento()` tiene una rama que solo corre para usuarios con rol
> `sede` (edita 2 campos, no 15) resuelta en tiempo de ejecución — no se le
> puede poner un Form Request por type-hint sin romper esa rama, porque
> Laravel resuelve y valida el Form Request antes de que el cuerpo del
> método decida qué rama tomar. Se dejó con `Request` genérico y
> `$request->validate((new UpdateDescuentoEspecialRequest())->rules())` en la
> rama no-sede, matando la duplicación literal sin arriesgar el flujo de
> sede.
>
> **Efecto colateral real, verificado con tests:** las 9 validaciones vivían
> dentro de un `try { ... } catch (\Exception $e) { return 500 }` en cada
> método. Como `$request->validate()` lanza `ValidationException` (subclase
> de `Exception`), una petición inválida cascaba con **500** y el mensaje
> genérico del catch, en vez del 422 con errores por campo que da Laravel
> por defecto — nadie lo había notado porque no había ningún test sobre este
> controlador. Con el Form Request, la validación se resuelve *antes* de
> entrar al método (fuera del try/catch), así que ahora sí es un 422
> estándar. Cubierto con 6 tests nuevos en
> `tests/Feature/DescuentoEspecialFormRequestTest.php` (controlador sin
> tests previos), incluyendo uno que fija explícitamente el 422 en vez del
> 500 anterior.
>
> Nota de orden: en los métodos que además chequean permiso por email
> (`aplicarDescuento`, `deshabilitarDescuento`, etc.), antes el permiso se
> comprobaba primero y la validación después (ambos dentro del mismo
> método); ahora la validación del Form Request corre primero. Si una
> petición llega inválida *y* de un usuario sin permiso, antes daba 403 y
> ahora da 422 — mismo tipo de cambio de precedencia ya aceptado en A1 para
> `findOrFail` (403→404), sin fuga de datos en ningún caso.
>
> Suite completa verificada: 75 tests en verde, el único fallo
> (`ExampleTest`) es preexistente y no relacionado.

**75** `$request->validate()` / `Validator::make()` en controladores, contra
**6** Form Requests existentes (`ProfileUpdateRequest`, `FileUploadRequest`,
`StoreUserRequest`, `StoreDashboardRequest`, `UpdateUserRequest`,
`Auth/LoginRequest`).

Concentración: `ComercialController` (10), `DescuentosEspecialesController` (9),
`RequerimientoPersonalController` (6), `CobranzaSedesController` (4).

El costo real es la duplicación entre `store()` y `update()`: las reglas
divergen con el tiempo y aparecen bugs donde crear acepta algo que editar
rechaza.

### Corrección propuesta

Extraer Form Request solo donde las reglas **se repiten** o pasan de ~5 campos.

> **Corrección 2026-07-27 a esta misma nota:** la afirmación anterior ("`LoginRequest`
> ya fue movido a `app/Http/Requests/Auth/`, no revertir") era incorrecta —
> nunca se movió. El archivo declaraba `namespace App\Http\Requests\Auth`
> pero vivía físicamente en `app/Http/Requests/LoginRequest.php` desde su
> creación (commit `3314eef`, PSR-4 roto desde el día uno) y **no lo
> referenciaba nada en toda la app**: `Auth/LoginController::login()` usa
> `Illuminate\Http\Request` con `validate()` inline, el real. Es el mismo
> scaffolding muerto de Breeze que ya se limpió en
> [A7](#a7--código-huérfano-y-scaffolding-roto--severidad-baja) — se coló
> porque A7 auditó controladores y rutas, no Form Requests. Se borró el
> archivo (adenda a A7, no a A5) en vez de moverlo: no había nada que
> preservar.

---

## A6 · Pipeline de frontend inutilizado — Severidad media

> 🔴 **Diferido a propósito — 2026-07-27.** Se confirmó que el diagnóstico
> sigue exacto (`public/build/` sigue sin existir, 46 MB / 1644 archivos en
> `public/assets/`, solo 2 de 108 vistas usan `@vite`) y se planteó la
> decisión al equipo: Opción A (asumir estático, bajo esfuerzo) vs. Opción B
> (activar Vite, alto esfuerzo pero mejor destino) vs. dejarlo pendiente. Se
> eligió **dejarlo pendiente** — no se tomó la decisión de producto todavía,
> y tampoco se adelantó la extracción del JS inline (que serviría para
> cualquiera de los dos caminos), para no invertir esfuerzo en un trabajo de
> alcance grande (52 vistas, ~12 724 líneas) sin la decisión de fondo tomada.
> Queda exactamente como estaba: sin tocar.

### Qué pasa

El proyecto tiene `vite.config.js`, `tailwind.config.js`, `postcss.config.js`,
`package.json` y `resources/css/app.css` + `resources/js/app.js`. Pero:

- `@vite(...)` se usa en **2 vistas** de 108: `welcome.blade.php` y
  `layouts/guest.blade.php` — ninguna del sistema real.
- `public/build/` **no existe**: nunca se ejecutó `npm run build`.
- Las 106 vistas restantes cargan un template Bootstrap estático desde
  `public/assets/`: **46 MB en 1644 archivos, todos versionados en git**.
- **53 vistas** llevan JavaScript inline. Las mayores:
  `comercial/acuerdos.blade.php` (2362 LOC),
  `comercial/descuentos-especiales.blade.php` (2091 LOC).

### Por qué importa

Explica los 44 045 LOC de vistas. Sin build no hay minificación, ni cache
busting real (se usa `?v=1784912239` a mano), ni forma de compartir código JS
entre pantallas: se copia y pega. Y los 46 MB inflan cada `clone` y cada
contexto de build de Docker.

### Corrección propuesta

Hay dos caminos legítimos y conviene **elegir uno explícitamente**, porque el
estado actual es el peor de los dos mundos: se paga el mantenimiento de la
config de Vite sin recibir ningún beneficio.

**Opción A — asumir el template estático (bajo esfuerzo).**
Eliminar `vite.config.js`, `tailwind.config.js`, `postcss.config.js`,
`package.json` y `resources/js|css`. Extraer el JS repetido de las vistas a
`public/assets/js/modules/*.js`. Documentar que el frontend es estático.
Recomendada si no hay plan de modernizar el frontend.

**Opción B — activar Vite (alto esfuerzo, mejor destino).**
Mover `public/assets` fuera de git, migrar `layouts/app.blade.php` a `@vite`,
e ir moviendo el JS inline a módulos. Requiere agregar `npm ci && npm run build`
al `Dockerfile` (ver INFRAESTRUCTURA.md, I2).

Sea cual sea, **el JS inline de las 53 vistas debe salir de Blade**. Ese trabajo
sirve en ambos caminos y puede empezar ya.

---

## A7 · Código huérfano y scaffolding roto — Severidad baja

### Controladores sin referencia

Verificado contra `app/`, `routes/` y `resources/`:

| Archivo | Diagnóstico |
|---|---|
| `app/Http/Controllers/AuthController.php` | **Muerto.** Cero referencias. Duplica a `Auth/LoginController`, que es el que las rutas usan (`web.php:48-54`). |
| `app/Http/Controllers/ProfileController.php` | **Muerto.** Cero referencias. |
| `app/Http/Controllers/Admin/UserAccessController.php` | **Muerto.** No aparece en `routes/`. |

Dos controladores de login conviviendo es el tipo de ambigüedad que hace que
alguien parchee el archivo equivocado durante un incidente de seguridad.

### `routes/auth.php` — scaffolding vacío que apunta a clases inexistentes

`routes/web.php:446` hace `require __DIR__ . '/auth.php'`. Ese archivo:

- Define **0 rutas**. Son 14 líneas de `use` y nada más.
- Importa **5 controladores que no existen** en el proyecto:
  `AuthenticatedSessionController`, `EmailVerificationNotificationController`,
  `EmailVerificationPromptController`, `NewPasswordController`,
  `VerifyEmailController`.

No provoca error porque en PHP un `use` no dispara el autoload si la clase nunca
se instancia. Es residuo de Laravel Breeze: la autenticación real se resolvió a
mano en `web.php:43-54` con `Auth/LoginController`, y el archivo de Breeze quedó
vaciado de rutas pero todavía enlazado.

El riesgo es de lectura, no de ejecución: quien abra `routes/auth.php` buscando
las rutas de autenticación concluirá que el proyecto usa Breeze estándar, y no
es así.

### Corrección propuesta

1. Eliminar `AuthController.php`, `ProfileController.php` y
   `Admin/UserAccessController.php`.
2. Eliminar `routes/auth.php` y su `require` en `web.php:446`, o dejarlo con un
   comentario que explique que la autenticación vive en `web.php`.
3. Revisar si `tests/Feature/Auth/*` y `tests/Feature/ProfileTest.php` siguen
   teniendo sentido: prueban el flujo de Breeze, no el real (ver A8).

> ✅ **Resuelto 2026-07-27**, las tres acciones. Verificado antes de borrar
> que los 3 controladores seguían sin ninguna referencia externa (`grep` solo
> encontraba su propia declaración de clase). Eliminado `routes/auth.php` y
> su `require` en `web.php:446`, reemplazado por un comentario que explica
> dónde vive la autenticación real. `php artisan route:list` sigue
> funcionando (268 rutas) sin errores.
>
> Punto 3 — y esto fue lo más valioso del hallazgo —: `tests/Feature/Auth/*`
> (6 archivos) y `tests/Feature/ProfileTest.php` **no tenían sentido y se
> borraron**. No es una zona gris: prueban rutas (`/register`,
> `/forgot-password`, `/profile`, verificación de email) que literalmente no
> existen en esta app, porque `routes/auth.php` — el archivo que las
> registraría — definía **0 rutas**. No eran tests fallando por un bug; eran
> tests de un flujo que nunca se implementó. Esto explica **los 22 tests que
> fallaban desde el arranque de esta sesión** en cada ronda anterior (S1, S2,
> A1, etc.) — no eran regresiones de nada de lo tocado hoy, sino este mismo
> scaffolding muerto. Tras borrarlos: 22 fallos → 1 (`Tests\Feature\ExampleTest`,
> el scaffolding genérico de Laravel que espera `GET /` → 200 pero la app
> redirige a `/login` por diseño — no está en el alcance de A7, se dejó sin
> tocar).
>
> **Adenda 2026-07-27** (encontrado auditando [A5](#a5--validación-inline--severidad-media)):
> `app/Http/Requests/LoginRequest.php` era el mismo residuo de Breeze, pasado
> por alto porque el barrido original de A7 cubrió controladores y rutas, no
> Form Requests. Cero referencias en toda la app. Borrado.
>
> **Segunda adenda 2026-07-27** (encontrado auditando
> [F1 en FRONTEND.md](FRONTEND.md#f1--terceros-desde-cdn-sin-integridad-ni-versión--severidad-alta)):
> `resources/views/welcome.blade.php`, `resources/views/layouts/guest.blade.php`
> y `app/View/Components/GuestLayout.php` — mismo scaffolding de Breeze, cero
> rutas ni vistas que los referencien (`/` redirige directo a `/login`,
> nunca renderiza `welcome`). Borrados los 3.
>
> **Tercera adenda 2026-07-27** (encontrado auditando
> [F6 en FRONTEND.md](FRONTEND.md#f6--accesibilidad-y-consistencia--severidad-media)):
> `resources/views/layouts/navigation.blade.php` — mismo scaffolding de
> Breeze, cero vistas lo incluyen. Borrado.

---

## A8 · Cobertura de tests — Severidad media

9 archivos de test, **todos scaffolding de Laravel Breeze**
(`AuthenticationTest`, `RegistrationTest`, `PasswordResetTest`,
`ProfileTest`, `ExampleTest`…).

**Cero tests** sobre la lógica de negocio: cálculo de KPI de cobranza, ventanas
de hora límite por sede, flujo de revisión de vouchers, sincronización con
Google Sheets, asignación de bases.

Esto es lo que bloquea A4: no se puede refactorizar con confianza un controlador
de 1799 líneas sin una red que avise si algo cambió de comportamiento.

### Corrección propuesta

No perseguir un porcentaje de cobertura. Escribir tests **de característica**
sobre las reglas de negocio que ya causaron incidentes:

- `ReporteCobranza::horaLimitePara($sede)` — hubo un bug de hora límite en la
  sede Huánuco (commit `9ea05b5`).
- Cálculo de `kpi_porcentaje` y `editado_tarde`.
- Filtro por sede: **un usuario de sede A no debe ver datos de sede B** — es el
  test que convierte A1 en algo verificable.

Ese último es el más valioso del documento: hace que la frontera de datos deje
de depender de que nadie olvide una comprobación.

> 🟡 **Piloto aplicado 2026-07-27** — los tres puntos exactos que nombra esta
> sección, no cobertura general (sigue sin perseguirse un %).
>
> - **`tests/Unit/Models/ReporteCobranzaTest.php`** — `horaLimitePara()`,
>   `esSedeLimite11()`, `normalizarSede()` y `calcularKpi()`. Cubre
>   explícitamente el caso del bug real: `esSedeLimite11('HUÁNUCO')` (con
>   tilde, como se guarda en la BD) debe dar `true` — antes de `9ea05b5` esa
>   comparación fallaba porque `SEDES_LIMITE_11` no lleva tildes.
> - **`tests/Feature/ReporteCobranzaRecalcularKpiTest.php`** — `recalcularKpi()`:
>   sin envío → `no_enviado`; usa `fecha_envio_original` o
>   `fecha_ultimo_envio` según `editado_tarde`; `revision_estado=rechazado`
>   fuerza el KPI a 0; `conforme_observado` aplica la penalidad proporcional.
>   Al escribirlo se encontró que `recalcularKpi()` corta a `kpi=0` /
>   `no_enviado` si `fecha_ultimo_envio` es nula, **sin importar** si
>   `fecha_envio_original` tiene valor — dos de los tests fallaron al
>   principio por no setear ese campo, no por un bug del código. Quedó
>   documentado en los propios tests (comentario en la línea que lo setea)
>   para que no vuelva a sorprender.
> - **`tests/Feature/CobranzaSedesFronteraDeSedeTest.php`** — el test que
>   "convierte A1 en algo verificable", pero para un módulo **sin** `SedeScope`
>   todavía: `CobranzaSedesController::historial()` sigue filtrando a mano
>   (`$query->where('sede', $user->sede)`), y este test protege que ese
>   filtro no se rompa sin que nadie lo note. Confirma que sede solo ve lo
>   suyo y finanzas ve todo.
>
> **Hallazgo al escribir el tercero, no corregido:** `CobranzaSedesController::download()`,
> `show()` y `preview()` comprueban `puedeVerCobranzaSedes()` (permiso de
> rol) pero no `$reporte->sede === $user->sede` — mismo patrón que S8
> (`SEGURIDAD.md`), en un controlador distinto al que ya se investigó ahí.
> Un usuario de sede con el permiso genérico podría leer el adjunto de
> depósito de otra sede cambiando el `{reporte}` de la URL. No se corrigió
> en esta ronda — es una decisión de la misma familia que S8 (¿el permiso es
> "ve su sede" o "ve todas"?), no un fix mecánico.
>
> Suite completa después de esto: 1 fallo (`Tests\Feature\ExampleTest`,
> scaffolding genérico sin relación — ver A7), 49 pasan.
>
> **Sigue pendiente, sin tocar:** cobertura del flujo de revisión de
> vouchers y de la sincronización con Google Sheets — A8 no se agotó, sigue
> siendo trabajo abierto tal como aclara esta sección.

---

## Plan sugerido

Ordenado por relación impacto/esfuerzo, no por severidad:

| Orden | Acción | Hallazgo | Esfuerzo |
|---|---|---|---|
| 1 | Envolver escrituras multi-tabla en `DB::transaction` | A3 | Bajo |
| 2 | Eliminar `AuthController` muerto | A7 | Bajo |
| 3 | Test de frontera de datos por sede | A8 | Bajo |
| 4 | Paginar exportaciones y listados de tablas grandes | A2 | Medio |
| 5 | `SedeScope` + Gates en un módulo piloto | A1 | Medio |
| 6 | Decidir Opción A u B de frontend | A6 | Medio |
| 7 | Extraer servicios al tocar cada módulo | A4, A5 | Alto |

Los tres primeros se pueden hacer en una sesión y reducen el riesgo real.
Del 5 en adelante conviene un módulo piloto antes de generalizar.
