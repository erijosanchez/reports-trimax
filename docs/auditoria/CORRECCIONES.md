# Listado maestro de correcciones — reports-trimax

**Fecha:** 2026-07-24 (actualizado 2026-07-25 con 2 hallazgos nuevos)
**Origen:** consolidación de las cuatro auditorías
**Total:** 36 correcciones · 16 de severidad alta o crítica

> **2026-07-25 — dos hallazgos nuevos, descubiertos al escribir tests y
> revisar el código de la Ronda 1** (ver [SEGURIDAD.md](SEGURIDAD.md)):
> **[S9](SEGURIDAD.md#s9--blade-no-escapa-nada--severidad-crítica)** —
> `Blade::setEchoFormat('%s')` desactiva el auto-escape de `{{ }}` en las 108
> vistas de la app (crítica, invalida el conteo de "3 salidas XSS" de este
> documento) — y
> **[S10](SEGURIDAD.md#s10--vouchercontroller-sin-control-de-permiso-en-dos-endpoints--severidad-alta)**
> — `VoucherController::servirArchivo()`/`getFacturas()` sin ningún chequeo de
> permiso (alta).
>
> ✅ **Ambos resueltos 2026-07-27** (ver detalle en cada sección de
> SEGURIDAD.md). S9: se auditaron las 108 vistas antes de quitar el override
> — sin editores WYSIWYG, sin HTML armado a mano en PHP ni en Blade — y se
> confirmó que ninguna dependía a propósito del bug. S10: agregado el mismo
> chequeo `puedeVerVouchers()` que ya usan `revisionFile()`/`revisar()`, con 2
> tests de regresión nuevos.

> **2026-07-27 — I4 cerrado del todo.** Se generaron migraciones para las 28
> tablas que llegaron con el backup sin ninguna migración de creación, y se
> aplicó `php artisan migrate --force` sobre la base real tras validarlo en una
> copia descartable (0 diferencias de esquema). Detalle en
> [I4](INFRAESTRUCTURA.md#i4--deriva-de-migraciones--severidad-alta--resuelto).
> La advertencia de "no corras migrate" del `README.md` ya no aplica.

> ✅ **2026-07-27 — Ronda 2 completa: S2 y S4 resueltos.** `composer update
> --with-all-dependencies` corrido tras destrabar un constraint innecesariamente
> fijado (`google/apiclient`); `composer audit` pasó de 49 advisories a
> **0**. Validado a mano: exportación Excel, generación de PDF y envío de
> correo (`Notification::fake()` + render) sin excepciones. 2FA activado por
> rol para `super_admin`/`admin`/`finanzas`, con alta obligatoria si no lo
> tienen configurado, cubierto con 7 tests de regresión. Detalle completo en
> [S2](SEGURIDAD.md#s2--dependencias-vulnerables--severidad-alta) y
> [S4](SEGURIDAD.md#s4--2fa-construido-pero-nunca-activado--severidad-alta).

Todas las correcciones detectadas, **ordenadas de mayor a menor prioridad**, con
el documento del que proviene cada una. El detalle técnico, el porqué y el
código de cada corrección están en su documento de origen — aquí solo está la
lista priorizada.

| Origen | Documento | Hallazgos | Lista |
|---|---|---|---|
| 🔒 Seguridad | [SEGURIDAD.md](SEGURIDAD.md) | 8 | Principal |
| 🏗️ Arquitectura | [ARQUITECTURA.md](ARQUITECTURA.md) | 8 | Principal |
| ⚙️ Infraestructura | [INFRAESTRUCTURA.md](INFRAESTRUCTURA.md) | 10 | Principal |
| 🎨 Frontend | [FRONTEND.md](FRONTEND.md) | 8 | [Tabla aparte](#frontend--tabla-aparte) |

El frontend va en **tabla separada**, al final del documento. Son correcciones
que se ejecutan sobre otra capa —plantillas Blade y assets estáticos—, casi
siempre por otra persona y en otro momento que las de backend e infraestructura.
Mezclarlas en un único ranking haría que ninguna de las dos listas se pudiera
usar como plan de trabajo.

**Criterio de orden:** severidad combinada con explotabilidad real y con el
coste de corregir. Un hallazgo crítico que se arregla en media hora va antes que
uno alto que exige un refactor de semanas. No es orden alfabético ni por
documento.

---

## Resumen por severidad

| Severidad | 🔒 Seguridad | 🏗️ Arquitectura | ⚙️ Infra | Lista principal | 🎨 Frontend | Total |
|---|---|---|---|---|---|---|
| Crítica | 1 | 0 | 0 | **1** | 0 | **1** |
| Alta | 3 | 3 | 4 | **10** | 3 | **13** |
| Media | 4 | 4 | 5 | **13** | 3 | **16** |
| Baja | 0 | 1 | 1 | **2** | 2 | **4** |
| **Total** | **8** | **8** | **10** | **26** | **8** | **34** |

---

## Ganancias rápidas

Seis correcciones que suman **menos de un día de trabajo** y cierran dos
problemas activos, el hueco de exposición más grave y una bomba de relojería en
el despliegue. Si solo se va a hacer una tanda, que sea esta:

| # | Corrección | Origen | Tiempo |
|---|---|---|---|
| 4 | ✅ Middleware de cabeceras de seguridad HTTP | 🔒 S3 | ~30 min |
| 8 | ✅ `CACHE_STORE=redis` — la caché escribe en MySQL | ⚙️ I1 | Minutos |
| 9 | ✅ Sincronizar el registro de `migrations` | ⚙️ I4 | Minutos |
| 12 | ✅ `{!! $extra !!}` → `{{ $extra }}` en el correo de RRHH | 🔒 S6 | Minutos |
| 17 | ✅ `horizon`/`scheduler` reutilizando `image: trimax-app` | ⚙️ I5 | Minutos |
| 24 | Borrar 3 controladores muertos y `routes/auth.php` | 🏗️ A7 | ~30 min |

**Resuelto 2026-07-25** (ronda 1): #1 (S1), #4 (S3), #8 (I1), #9 (I4), #12 (S6), #17
(I5), más un hallazgo de infra no documentado en la auditoría original — drift
de puertos en `docker-compose.yml` (`mysql`/`phpmyadmin` declaraban 3306/8080
pero los contenedores reales corrían en 3307/8090) — corregido junto con I5 al
tocar el mismo archivo. Detalle de cada cambio en `SEGURIDAD.md` e
`INFRAESTRUCTURA.md`.

---

## Listado completo, de mayor a menor

### Prioridad crítica y alta

| # | Corrección | Origen | ID | Severidad | Esfuerzo |
|---|---|---|---|---|---|
| 1 | ✅ Mover los adjuntos de vouchers y desbloqueos al disco `local` y migrar los ya subidos — hoy nginx los sirve sin autenticación | 🔒 Seguridad | [S1](SEGURIDAD.md#s1--adjuntos-financieros-servidos-sin-autenticación--severidad-crítica) | **Crítica** | ~2 h |
| 2 | ✅ `composer update`: 49 vulnerabilidades en 17 paquetes (2 críticas, 10 altas) | 🔒 Seguridad | [S2](SEGURIDAD.md#s2--dependencias-vulnerables--severidad-alta) | Alta | ~3 h |
| 3 | ✅ Despublicar Redis y poner phpMyAdmin y Redis Commander tras perfil — hoy phpMyAdmin entra como root sin pedir credenciales | ⚙️ Infra | [I3](INFRAESTRUCTURA.md#i3--servicios-de-datos-expuestos--severidad-alta) | Alta | ~1 h |
| 4 | ✅ Middleware de cabeceras de seguridad (`X-Frame-Options`, `nosniff`, `Referrer-Policy`) | 🔒 Seguridad | [S3](SEGURIDAD.md#s3--sin-cabeceras-de-seguridad-http--severidad-alta) | Alta | ~30 min |
| 5 | ✅ Sacar las credenciales literales del `docker-compose.yml` a `env_file` | ⚙️ Infra | [I2](INFRAESTRUCTURA.md#i2--credenciales-literales-en-el-compose--severidad-alta) | Alta | ~1 h |
| 6 | ✅ Decidir sobre el 2FA: activarlo por rol o retirarlo — está construido, nunca aplicado, 0 de 65 usuarios | 🔒 Seguridad | [S4](SEGURIDAD.md#s4--2fa-construido-pero-nunca-activado--severidad-alta) | Alta | Decisión + ~4 h |
| 7 | 🟡 Centralizar la frontera de datos por sede en un Global Scope + Gates — hoy son 212 comprobaciones a mano (piloto en Vouchers, resto pendiente) | 🏗️ Arquitectura | [A1](ARQUITECTURA.md#a1--autorización-dispersa--severidad-alta) | Alta | Medio |
| 8 | ✅ `CACHE_STORE=redis`: la clave `CACHE_DRIVER` es de Laravel 10 y se ignora, así que la caché escribe en MySQL | ⚙️ Infra | [I1](INFRAESTRUCTURA.md#i1--la-caché-va-a-mysql-no-a-redis--severidad-alta--resuelto) | Alta | Minutos |
| 9 | ✅ Sincronizar el registro de `migrations` con el esquema real — `php artisan migrate` hoy fallaría | ⚙️ Infra | [I4](INFRAESTRUCTURA.md#i4--deriva-de-migraciones--severidad-alta--resuelto) | Alta | Minutos |
| 10 | ✅ Envolver en `DB::transaction` las escrituras multi-tabla (vouchers, requerimientos) | 🏗️ Arquitectura | [A3](ARQUITECTURA.md#a3--escrituras-sin-transacción--severidad-alta) | Alta | Bajo |
| 11 | 🟡 Paginar los 90 listados que traen la tabla completa a memoria — piloto en las 2 tablas más grandes, 1 hallazgo real documentado sin aplicar | 🏗️ Arquitectura | [A2](ARQUITECTURA.md#a2--listados-sin-paginación--severidad-alta) | Alta | Medio |

### Prioridad media

| # | Corrección | Origen | ID | Severidad | Esfuerzo |
|---|---|---|---|---|---|
| 12 | ✅ Escapar `$extra` en el correo de requerimientos — entra texto de usuario sin filtrar | 🔒 Seguridad | [S6](SEGURIDAD.md#s6--inyección-de-html-en-el-correo-de-requerimientos--severidad-media) | Media | Minutos |
| 13 | ✅ `SESSION_SECURE_COOKIE=true` en entornos con HTTPS | 🔒 Seguridad | [S5](SEGURIDAD.md#s5--cookie-de-sesión-sin-flag-secure--severidad-media) | Media | Minutos |
| 14 | ✅ Checklist de despliegue: `APP_ENV=production`, `APP_DEBUG=false`, caches de config y rutas | ⚙️ Infra | [I8](INFRAESTRUCTURA.md#i8--configuración-de-entorno--severidad-media) | Media | ~1 h |
| 15 | ✅ Endurecer la política de contraseñas (`min:8` sin complejidad; `min:6` en motorizados) | 🔒 Seguridad | [S7](SEGURIDAD.md#s7--política-de-contraseñas-débil--severidad-media) | Media | ~1 h |
| 16 | 🟡 Verificar propiedad en la descarga de adjuntos, no solo el permiso de rol — parcial como efecto lateral de #7 (Vouchers) | 🔒 Seguridad | [S8](SEGURIDAD.md#s8--descarga-de-adjuntos-sin-verificar-propiedad--severidad-media) | Media | Medio |
| 17 | ✅ `horizon` y `scheduler` reutilizando `image: trimax-app` — hoy duplican 2.5 GB y pueden divergir de versión | ⚙️ Infra | [I5](INFRAESTRUCTURA.md#i5--imágenes-duplicadas--severidad-media--resuelto) | Media | Minutos |
| 18 | `healthcheck` en app, nginx y horizon + `depends_on: condition: service_healthy` | ⚙️ Infra | [I7](INFRAESTRUCTURA.md#i7--healthchecks-incompletos--severidad-media) | Media | ~1 h |
| 19 | `composer install` y cacheo de capas en el `Dockerfile` — la imagen no es autónoma | ⚙️ Infra | [I6](INFRAESTRUCTURA.md#i6--la-imagen-no-es-autónoma--severidad-media) | Media | ~2 h |
| 20 | Extraer Form Requests donde las reglas se repiten (75 validaciones inline vs 6 Form Requests) | 🏗️ Arquitectura | [A5](ARQUITECTURA.md#a5--validación-inline--severidad-media) | Media | Medio |
| 21 | Tests de las reglas de negocio, empezando por la frontera de datos por sede | 🏗️ Arquitectura | [A8](ARQUITECTURA.md#a8--cobertura-de-tests--severidad-media) | Media | Alto |
| 22 | 🔴 Decidir el destino del frontend: asumir el template estático o activar Vite — planteado 2026-07-27, se eligió diferir la decisión | 🏗️ Arquitectura | [A6](ARQUITECTURA.md#a6--pipeline-de-frontend-inutilizado--severidad-media) | Media | Medio |
| 23 | Extraer servicios de los 10 controladores que superan 400 LOC | 🏗️ Arquitectura | [A4](ARQUITECTURA.md#a4--controladores-gordos--severidad-media) | Media | Alto |

### Prioridad baja

| # | Corrección | Origen | ID | Severidad | Esfuerzo |
|---|---|---|---|---|---|
| 24 | Borrar 3 controladores muertos y `routes/auth.php` (vacío, importa 5 clases inexistentes) | 🏗️ Arquitectura | [A7](ARQUITECTURA.md#a7--código-huérfano-y-scaffolding-roto--severidad-baja) | Baja | ~30 min |
| 25 | Quitar nginx y supervisor del `Dockerfile` — se instalan y nunca se usan | ⚙️ Infra | [I9](INFRAESTRUCTURA.md#i9--peso-muerto-en-la-imagen--severidad-baja) | Baja | Minutos |
| 26 | Límites de memoria y CPU por contenedor | ⚙️ Infra | [I10](INFRAESTRUCTURA.md#i10--sin-límites-de-recursos--severidad-baja) | Baja | ~30 min |

---

## Frontend — tabla aparte

Ocho correcciones sobre `resources/views/` y `public/assets/`, con su propio
orden interno. Detalle completo en [FRONTEND.md](FRONTEND.md).

Las siete primeras suman **menos de un día** y cierran los dos hallazgos de
seguridad y privacidad de esta capa. La octava es trabajo de fondo sin final
cerrado: se avanza vista a vista.

| # | Corrección | ID | Severidad | Esfuerzo |
|---|---|---|---|---|
| F-1 | Generar los avatares localmente — hoy cada listado envía el nombre completo de cada empleado a `ui-avatars.com` | [F2](FRONTEND.md#f2--nombres-de-empleados-enviados-a-un-servicio-externo--severidad-alta) | Alta | ~1 h |
| F-2 | Servir Chart.js, SweetAlert2 y Leaflet localmente — hoy vienen de CDN sin `integrity` y dos sin versión fijada | [F1](FRONTEND.md#f1--terceros-desde-cdn-sin-integridad-ni-versión--severidad-alta) | Alta | ~2 h |
| F-3 | Unificar Chart.js en una sola versión y retirar el plugin de v2 que corre sobre v4 | [F4](FRONTEND.md#f4--tres-versiones-de-chartjs-conviviendo--severidad-media) | Media | ~1 h |
| F-4 | Borrar los 10 `console.log`, los 214 `.scss` y los 11 sourcemaps servidos públicamente | [F6](FRONTEND.md#f6--accesibilidad-y-consistencia--severidad-media), [F7](FRONTEND.md#f7--fuentes-scss-y-sourcemaps-públicos--severidad-baja) | Baja | ~30 min |
| F-5 | Eliminar las 41 librerías vendor sin usar (34 MB) | [F5](FRONTEND.md#f5--librerías-vendor-sin-usar--severidad-media) | Media | ~1 h |
| F-6 | Añadir los 14 `alt` y 6 `aria-label` que faltan | [F6](FRONTEND.md#f6--accesibilidad-y-consistencia--severidad-media) | Media | ~30 min |
| F-7 | Vistas de error 404 y 500 | [F8](FRONTEND.md#f8--sin-vistas-de-error-404-ni-500--severidad-baja) | Baja | ~30 min |
| F-8 | Extraer el JavaScript de las vistas a módulos compartidos — 12 724 líneas en 52 plantillas | [F3](FRONTEND.md#f3--javascript-dentro-de-las-plantillas--severidad-alta) | Alta | Alto, incremental |

---

## Dependencias entre correcciones

Algunas no conviene abordarlas sueltas:

- **#16 depende de #7.** La propiedad en descargas se resuelve sola cuando el
  `SedeScope` filtre también ese `findOrFail`. Hacerla antes es duplicar
  trabajo.
- **#23 depende de #21.** Refactorizar un controlador de 1799 líneas sin tests
  que avisen si cambió el comportamiento es riesgo puro.
- **CSP depende de F-8 y de #22.** Una política estricta rompe la app mientras
  haya 52 vistas con JavaScript inline y librerías cargadas desde CDN. Por eso
  #4 incluye solo las tres cabeceras que no dependen de esa deuda. El orden real
  para llegar a una CSP es: F-2 (librerías locales) → F-8 (JS fuera de Blade) →
  CSP.
- **F-1 y #4 se refuerzan.** `Referrer-Policy` reduce lo que `ui-avatars.com`
  puede correlacionar mientras F-1 esté pendiente.
- **F-2 resuelve parte de F-3.** Al bajar Chart.js a `vendors/` se elige una
  única versión, así que conviene hacerlas seguidas.
- **#1 y #4 se refuerzan.** `Referrer-Policy` reduce la fuga de URL de adjuntos
  mientras se completa la migración al disco `local`.
- **#2 conviene con #21 delante.** Un `composer update` amplio sin tests obliga
  a validar a mano exportaciones, PDF y correo.

---

## Estado

Ninguna aplicada al 2026-07-24. Marcar aquí conforme se cierren, y actualizar
también el documento de origen.

| Lista | Correcciones | Estado | Fecha |
|---|---|---|---|
| Principal | 1, 4, 8, 9, 12, 17 | ✅ Resuelto | 2026-07-25 |
| Principal | S9, S10 (no numeradas, ver recuadro arriba) | ✅ Resuelto | 2026-07-27 |
| Principal | 2 (S2), 6 (S4) | ✅ Resuelto | 2026-07-27 |
| Principal | 3 (I3), 5 (I2) | ✅ Resuelto | 2026-07-27 |
| Principal | 7 (A1) | 🟡 Piloto (Vouchers) | 2026-07-27 |
| Principal | 10 (A3) | ✅ Resuelto | 2026-07-27 |
| Principal | 11 (A2) | 🟡 Piloto (auditado, 1 hallazgo documentado sin aplicar) | 2026-07-27 |
| Principal | 13 (S5), 14 (I8), 15 (S7) | ✅ Resuelto | 2026-07-27 |
| Principal | 16 (S8) | 🟡 Parcial (efecto lateral de #7) | 2026-07-27 |
| Principal | 22 (A6) | 🔴 Diferido a propósito (decisión de producto) | 2026-07-27 |
| Principal | 18–21, 23–26 | Pendiente | — |
| Frontend | F-1–F-8 | Pendiente | — |
