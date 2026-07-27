# Auditoría de seguridad — reports-trimax

**Fecha:** 2026-07-24
**Alcance:** OWASP Top 10 aplicado al código, dependencias, sesión, subida de archivos y cabeceras HTTP
**Método:** revisión estática de `app/`, `routes/`, `resources/views/`, `config/`, más `composer audit` y verificación de cabeceras contra la instancia local
**No cubre:** pentest activo, revisión de la red corporativa, ingeniería social

> Documento vivo. Actualízalo cuando se corrija un hallazgo.
> Ver también [ARQUITECTURA.md](ARQUITECTURA.md) e [INFRAESTRUCTURA.md](INFRAESTRUCTURA.md).

---

## Resumen ejecutivo

La aplicación tiene **buenos fundamentos de seguridad**: sin inyección SQL,
protección de asignación masiva completa, CSRF sin excepciones, throttling en
login y subidas, contraseñas hasheadas y sesión cifrada. La sección
"[Verificado y correcto](#verificado-y-correcto)" no es relleno: son ocho
vectores clásicos que se revisaron y están bien resueltos.

Los problemas se concentran en **exposición**, no en lógica: archivos que
deberían requerir autenticación y no la requieren, dependencias sin actualizar,
y mecanismos de defensa construidos pero nunca activados.

| # | Hallazgo | Severidad | Explotable hoy | Estado |
|---|---|---|---|---|
| S1 | Adjuntos financieros servidos sin autenticación | **Crítica** | Sí | ✅ Resuelto 2026-07-25 |
| S2 | 49 vulnerabilidades en dependencias (2 críticas, 10 altas) | **Alta** | Parcial | ✅ Resuelto 2026-07-27 |
| S3 | Ninguna cabecera de seguridad HTTP | **Alta** | Sí | ✅ Resuelto 2026-07-25 |
| S4 | 2FA implementado pero nunca aplicado; 0 de 65 usuarios | **Alta** | — | ✅ Resuelto 2026-07-27 (activado por rol) |
| S5 | Cookie de sesión sin flag `Secure` | Media | Al desplegar con HTTPS | ✅ Resuelto 2026-07-27 |
| S6 | Inyección de HTML en correo de requerimientos | Media | Sí (usuario privilegiado) | ✅ Resuelto 2026-07-25 |
| S7 | Política de contraseñas débil | Media | Sí | Pendiente |
| S8 | Descarga de adjuntos sin verificar propiedad | Media | Sí | 🟡 Parcial 2026-07-27 (ver nota) |
| S9 | `Blade::setEchoFormat('%s')` desactiva el auto-escape de `{{ }}` en toda la app | **Crítica** | Sí | ✅ Resuelto 2026-07-27 |
| S10 | `VoucherController::servirArchivo()` y `getFacturas()` sin ningún control de permiso | Alta | Sí | ✅ Resuelto 2026-07-27 |

---

## S1 · Adjuntos financieros servidos sin autenticación — Severidad crítica

> ✅ **Resuelto 2026-07-25.** `VoucherController.php` y `DesbloqueoController.php`
> ahora usan `Storage::disk('local')` en los 11 puntos donde antes decían
> `'public'` (`storeAs`, `delete`, `exists`, `path`, `download`). Verificado:
> `grep -n "disk('public')"` sobre ambos archivos ya no devuelve resultados, y
> `/login`, `/` y `/up` siguen respondiendo 200/302 tras el cambio. Corrección
> del propio documento: en Laravel 12 el disco `local` escribe en
> `storage_path('app/private')`, no en `storage/app/` como decía este
> documento — sigue fuera de la raíz web, la corrección no cambia. En este
> entorno local `storage/app/public/` estaba vacío, así que no hubo archivos
> que migrar; **en producción sigue pendiente migrar los adjuntos ya subidos**
> antes de considerar este hallazgo cerrado allí.

### Qué pasa

`VoucherController` y `DesbloqueoController` guardan los adjuntos en el disco
**`public`**:

```php
// VoucherController.php:623 y DesbloqueoController.php:414
$path = $file->storeAs($dir, Str::uuid() . '.' . $ext, 'public');
```

El disco `public` escribe en `storage/app/public/`, que está enlazado a
`public/storage/`. **nginx sirve ese directorio directamente**, sin pasar por
Laravel y por tanto sin pasar por `auth`.

Ambos controladores exponen además una ruta de descarga *sí* protegida
(`revisionFile()` comprueba `puedeVerVouchers()`). Es decir: existe la intención
de controlar el acceso, pero el archivo es alcanzable por una segunda vía que
esquiva ese control.

```
Ruta controlada:  GET /vouchers/{id}/revision-file/{i}  → verifica permiso ✓
Ruta directa:     GET /storage/vouchers/2026/<uuid>.pdf → nginx, sin auth ✗
```

### Por qué importa

Son documentos financieros: facturas con RUC, comprobantes de depósito,
solicitudes de desbloqueo. Hay **874 vouchers y 1830 facturas asociadas**, con
860 vouchers que tienen archivos adjuntos.

La única protección es que el nombre es un UUID v4, lo que hace inviable
adivinarlo. Pero eso es **seguridad por oscuridad**, y las URL se filtran solas:
historial del navegador, cabecera `Referer` al hacer clic hacia otro dominio,
enlaces reenviados por chat o correo, logs de proxy corporativo. Cualquiera de
esos caminos convierte la URL en pública y permanente, sin forma de revocarla.

### La corrección ya existe en el propio código

`ComentariosSedesController` resuelve exactamente el mismo problema **bien**:

```php
// ComentariosSedesController.php:487 — patrón correcto
'path' => $file->storeAs($dir, Str::uuid().'.'.$ext, 'local'),
```

El disco `local` escribe en `storage/app/`, fuera de la raíz web. El archivo
solo se puede obtener a través de la ruta de Laravel, que sí comprueba permisos.

### Corrección propuesta

1. Cambiar `'public'` por `'local'` en los dos `storeAs()`
   (`VoucherController.php:623`, `DesbloqueoController.php:414`).
2. Cambiar los `Storage::disk('public')` restantes de ambos controladores a
   `'local'` — son 10 ocurrencias en total, todas de lectura/borrado.
3. **Migrar los archivos ya subidos** de `storage/app/public/` a
   `storage/app/`. Sin este paso los adjuntos históricos siguen expuestos.
4. Verificar que ningún Blade construya URLs con `Storage::url()` o
   `asset('storage/...')` hacia esos adjuntos.

Es un cambio pequeño y de bajo riesgo: la ruta de descarga protegida ya existe
y ya funciona.

---

## S2 · Dependencias vulnerables — Severidad alta

> ✅ **Resuelto 2026-07-27.** `google/apiclient` estaba fijado a la versión
> exacta `2.16` (sin caret) en `composer.json`, y esa versión exige
> `firebase/php-jwt ~6.0` — un rango que cae **completo** dentro de la
> advisory de baja severidad `PKSA-y2cr-5h3j-g3ys` (`<7.0.0`). Composer ya no
> resuelve a una versión con advisory conocida por defecto, así que
> `composer update` se negaba a correr. Se relajó el constraint a
> `^2.16` (mismo criterio que el resto de dependencias del proyecto — no es
> un downgrade de rigor, simplemente estaba innecesariamente fijado a un
> patch exacto), lo que permitió a Composer subir a `google/apiclient
> v2.19.4`, que ya pide `firebase/php-jwt ^7.0` (fuera de la advisory).
>
> Con ese único cambio de constraint, `composer update --with-all-dependencies`
> corrió limpio y actualizó 60 paquetes dentro de sus majors ya declarados
> (`laravel/framework` v12.40.1 → v12.64.0, `dompdf` v3.1.5 → v3.1.6,
> `phpoffice/phpspreadsheet` 1.30.2 → 1.30.6, `guzzlehttp/guzzle` 7.10 →
> 7.15.2, `symfony/mime` y el resto de symfony/* a sus últimos patch/minor,
> etc.). `composer audit` después del update:
> **"No security vulnerability advisories found."** — 49 → 0.
>
> Apareció una dependencia nueva no pedida directamente:
> `laravel/sentinel v1.1.0`, requerida por `laravel/horizon v5.48.1`
> (`composer why laravel/sentinel` lo confirma). Viene del repositorio
> oficial `github.com/laravel/sentinel`, MIT, primera release 2026-03-24 —
> se investigó porque el nombre no es de un paquete conocido, pero al venir
> del propio org de Laravel como dependencia directa de Horizon no hay nada
> irregular.
>
> **Validación manual (checklist de esta misma sección) antes de dar por
> cerrado:**
> - **Excel**: `AcuerdosComercialesExport` generado con `Excel::store()` —
>   archivo válido de 86 KB, sin excepciones.
> - **PDF**: `Pdf::loadView('rrhh.requerimientos.pdf', ...)` con un
>   requerimiento real — PDF válido generado, sin excepciones.
> - **Correo**: `RequerimientoEstadoActualizado` con `Notification::fake()`,
>   `toMail()->render()` — HTML de ~13 KB renderizado sin excepciones, mismo
>   pipeline que usan `symfony/mailer`/`symfony/mime` recién actualizados.
>
> Se corrió la suite completa después: mismos resultados que antes del
> update (los tests propios en verde, las fallas de `Auth/*`/`ProfileTest`
> son las mismas de siempre, sin relación — ver A7).

### Qué pasa

`composer audit` reporta **49 advisories sobre 17 paquetes**:

| Severidad | Cantidad |
|---|---|
| Crítica | 2 |
| Alta | 10 |
| Media | 28 |
| Baja | 8 |

Los paquetes más afectados: `phpoffice/phpspreadsheet` (9),
`guzzlehttp/guzzle` (7), `dompdf/dompdf` (6), `phpseclib/phpseclib` (4),
`guzzlehttp/psr7` (4), `laravel/framework` (3), `symfony/yaml` (3).

### Las dos críticas — y por qué hoy no son explotables

| CVE | Paquete | Descripción |
|---|---|---|
| CVE-2026-34084 | phpoffice/phpspreadsheet | SSRF/RCE en `IOFactory::load` cuando `$filename` es controlado por el usuario |
| CVE-2026-45034 | phpoffice/phpspreadsheet | Bypass del parche del anterior |

Verificado: **la aplicación no lee hojas de cálculo**. No hay
`Excel::import`, `Excel::toArray`, `IOFactory::load` ni ninguna clase `Import`
en el proyecto; `app/Exports/` contiene solo exportadores. PhpSpreadsheet entra
como dependencia de `maatwebsite/excel` y se usa únicamente para **generar**
archivos.

Es decir: severidad crítica en el paquete, **superficie de ataque nula hoy**.
Pero el proyecto acepta subidas `.xlsx/.xls/.csv` y la primera funcionalidad de
importación que alguien agregue activa el vector de inmediato. Actualizar ahora
es barato; descubrirlo después, no.

### Las altas que sí tocan funcionalidad en uso

| CVE | Paquete | Por qué importa aquí |
|---|---|---|
| CVE-2026-45067 | symfony/mime | Inyección de comandos SMTP vía CRLF en cabeceras. La app **envía correos** (notificaciones de requerimientos, cobranza, vouchers). |
| — | laravel/framework | Inyección CRLF en la regla de validación `email` por defecto. |
| CVE-2026-32935 | phpseclib | Oracle de padding por temporización en AES-CBC. |
| CVE-2026-44167 | phpseclib | DoS por amplificación de OID. |

El de `symfony/mime` es el más relevante: se combina con S6, donde ya entra
texto de usuario en un correo.

### Corrección propuesta

```bash
docker compose run --rm --no-deps app composer update --with-all-dependencies
docker compose run --rm --no-deps app composer audit
```

La mayoría son dependencias transitivas de Laravel y Symfony, así que un
`composer update` resuelve el grueso sin tocar código. Conviene hacerlo con la
suite de tests delante — aunque hoy esa suite cubre poco (ver ARQUITECTURA.md,
A8), así que toca validar a mano los flujos de exportación, PDF y correo.

Recomendación de proceso: agregar `composer audit` al flujo de trabajo. Es el
tipo de deuda que se acumula en silencio.

---

## S3 · Sin cabeceras de seguridad HTTP — Severidad alta

> ✅ **Resuelto 2026-07-25.** Creado `app/Http/Middleware/SecurityHeaders.php`
> y registrado en `bootstrap/app.php` dentro de `$middleware->web(append: [...])`.
> Verificado: `curl -sI http://localhost:8000/login` devuelve las tres
> cabeceras (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`,
> `Referrer-Policy: strict-origin-when-cross-origin`). `Content-Security-Policy`
> y `Strict-Transport-Security` siguen fuera de alcance por lo ya explicado
> aquí (dependen de sacar el JS inline de Blade y de tener HTTPS).

### Qué pasa

Verificado contra la instancia local:

```
$ curl -sI http://localhost:8000/login | grep -iE "x-frame|x-content|content-security|referrer|strict-transport"
(ninguna coincidencia)
```

No se emite **ninguna** de estas:

| Cabecera | Protege contra |
|---|---|
| `X-Frame-Options` / `frame-ancestors` | Clickjacking |
| `X-Content-Type-Options: nosniff` | MIME sniffing en adjuntos servidos inline |
| `Content-Security-Policy` | XSS y carga de recursos externos |
| `Referrer-Policy` | Fuga de URL internas a terceros |
| `Strict-Transport-Security` | Degradación a HTTP |

### Por qué importa

La falta de `X-Frame-Options` es la más seria en este sistema concreto: hay
acciones de aprobación financiera con un clic (revisar voucher como
"conforme", aprobar desbloqueo). Un atacante puede embeber la app en un iframe
invisible y engañar a un usuario autenticado para que apruebe algo sin saberlo.
CSRF no protege de esto — la petición es legítima y el token es válido.

`X-Content-Type-Options` se conecta con S1: los adjuntos se sirven con
`Content-Disposition: inline` y el `mime` que reportó el navegador del que subió
el archivo (`VoucherController.php:444`). Sin `nosniff`, un archivo subido puede
interpretarse como HTML y ejecutarse en el origen de la aplicación.

### Corrección propuesta

Un middleware global es lo más simple y no depende del servidor web:

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    $response->headers->add([
        'X-Frame-Options'        => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
    ]);
    return $response;
}
```

Registrarlo en `bootstrap/app.php` junto a los otros globales.

`Referrer-Policy` además reduce el impacto de S1 mientras se corrige: evita que
la URL del adjunto viaje en la cabecera `Referer` hacia dominios externos.

CSP merece paso aparte: con 53 vistas que llevan JavaScript inline
(ARQUITECTURA.md, A6) una política estricta rompería la aplicación. Es
consecuencia de esa deuda, y se resuelve cuando el JS salga de Blade.
`Strict-Transport-Security` solo tiene sentido una vez haya HTTPS.

---

## S4 · 2FA construido pero nunca activado — Severidad alta

> ✅ **Resuelto 2026-07-27 — activado por rol** (decisión tomada: solo
> `super_admin`, `admin` y `finanzas`, quienes aprueban movimientos de
> dinero). `TwoFactorVerifiedMiddleware` ahora exige 2FA únicamente a esos
> tres roles:
>
> ```php
> private function requiere2fa($user): bool
> {
>     return $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas();
> }
> ```
>
> Si el usuario de esos roles no tiene 2FA habilitado (`two_factor_confirmed_at`
> nulo), se le redirige a `/2fa/setup` (alta obligatoria) en vez de dejarlo
> pasar — antes la falta de este paso era exactamente el hueco: la
> funcionalidad existía pero nunca forzaba el alta. Si ya lo tiene habilitado
> pero no verificó la sesión actual, va a `/2fa/verify`. El resto de roles no
> se ve afectado. Se agregó `2fa.verified` al grupo principal de rutas
> autenticadas (`routes/web.php:76`); las rutas `/2fa/*` quedan fuera de ese
> grupo para no generar un loop de redirecciones.
>
> Cubierto con `tests/Feature/TwoFactorEnforcementTest.php` (7 casos): los
> tres roles obligados sin 2FA van a `setup`, con 2FA sin verificar en sesión
> van a `verify`, con sesión verificada pasan, un rol no obligado (`sede`)
> pasa sin 2FA, y no hay loop al acceder a `/2fa/setup` directamente.

### Qué pasa

El segundo factor está **completamente implementado**: `pragmarx/google2fa-laravel`
en `composer.json`, `Auth/TwoFactorController` con alta, verificación y baja,
`TwoFactorVerifiedMiddleware`, columnas `two_factor_secret`,
`two_factor_recovery_codes` y `two_factor_confirmed_at` en `users`, y rutas bajo
`/2fa` (`routes/web.php:59-65`).

Y sin embargo:

- El middleware `2fa.verified` se registra como alias en `bootstrap/app.php:47`
  y **no se aplica a ninguna ruta**. El grupo autenticado principal
  (`web.php:76`) no lo incluye.
- En base de datos: **0 de 65 usuarios** tienen `two_factor_confirmed_at`.

```
total  con_2fa
65     0
```

El flujo de login nunca pide segundo factor. La funcionalidad está ahí, apagada.

### Por qué importa

El riesgo no es solo técnico. Un sistema que "tiene 2FA" en el inventario de
controles pero no lo aplica da una garantía falsa: se contabiliza como mitigación
de robo de credenciales en cualquier evaluación de riesgo, y no mitiga nada.

Para un CRM con datos comerciales de 20+ sedes, una contraseña de 8 caracteres
sin complejidad (S7) es hoy el único factor.

### Corrección propuesta

Es una decisión de negocio antes que técnica, porque afecta a 65 personas. Dos
caminos:

1. **Activarlo por rol** — empezar por `super_admin`, `admin` y `finanzas`, que
   son quienes aprueban movimientos de dinero:

```php
Route::middleware(['auth', '2fa.verified', ...])->group(...);
```

   Requiere un flujo de alta obligatoria: si `two_factor_confirmed_at` es nulo,
   redirigir a `/2fa/setup` en lugar de bloquear.

2. **Retirarlo** si no se va a usar. Dejar código de seguridad muerto es peor
   que no tenerlo: aparenta un control inexistente.

Lo que no conviene es dejarlo como está.

---

## S5 · Cookie de sesión sin flag `Secure` — Severidad media

> ✅ **Resuelto 2026-07-27.** `SESSION_SECURE_COOKIE=false` explícito en
> `.env` (antes indefinida) — esta máquina sirve por HTTP, así que `true`
> aquí rompería la sesión. Documentado en `docs/DEPLOYMENT.md` como parte
> del checklist de despliegue (junto con I8): pasar a `true` únicamente en
> el entorno que ya sirva por HTTPS. Agregado también a `.env.example` con
> el mismo comentario. Verificado con `curl -sI`: la cookie sigue sin
> `Secure` en local, como corresponde.

`SESSION_SECURE_COOKIE` no está definida en `.env`, así que `config/session.php:172`
resuelve a `null` y la cookie viaja sin el flag:

```
Set-Cookie: laravel-session=...; path=/; httponly; samesite=lax
                                            ^^^^^^^^ falta "secure"
```

Correcto en local, donde `APP_URL=http://localhost` y no hay TLS. **En cualquier
despliegue con HTTPS**, sin ese flag el navegador envía la cookie también por
HTTP, y basta un enlace `http://` para capturar la sesión en una red compartida.

Lo demás de la sesión está bien: `SESSION_ENCRYPT=true`, `http_only` activo,
`same_site=lax` y `SESSION_LIFETIME=120` minutos.

**Corrección:** `SESSION_SECURE_COOKIE=true` en el `.env` de cualquier entorno
con TLS. Va en el mismo checklist de despliegue que `APP_DEBUG` (INFRAESTRUCTURA.md, I8).

---

## S6 · Inyección de HTML en el correo de requerimientos — Severidad media

> ✅ **Resuelto 2026-07-25.** `requerimiento_estado.blade.php:94` cambiado de
> `{!! $extra !!}` a `{{ $extra }}`.

### Qué pasa

`resources/views/emails/rrhh/requerimiento_estado.blade.php:94` imprime sin
escapar:

```blade
📌 {!! $extra !!}
```

Y `$extra` es texto del usuario, trazado hasta su origen:

```php
// RequerimientoPersonalController.php — registrarEtapa()
$request->validate(['descripcion' => 'required|string|max:2000']);
...
new RequerimientoEstadoActualizado(
    ..., 'etapa', "[{$etiqueta}] " . $request->descripcion
)
```

`string|max:2000` no filtra HTML. Lo que se escriba en el campo "descripción"
llega literal al correo del solicitante.

### Alcance real

Acotado, y conviene decirlo con precisión:

- Requiere `puedeGestionarRequerimientos()` — es un usuario privilegiado, no
  anónimo.
- El destino es un cliente de correo, no el navegador dentro de la sesión de la
  app. Los clientes modernos bloquean `<script>`, así que **no es XSS clásico**.

Lo que sí permite es **inyectar enlaces y marcado con apariencia legítima** en
un correo que sale del sistema, firmado por el dominio de la empresa. Es un
vector de phishing interno con una credibilidad que un correo externo no tiene.
Combinado con el CVE de `symfony/mime` (S2), el riesgo sube.

### Corrección propuesta

```diff
-📌 {!! $extra !!}
+📌 {{ $extra }}
```

Las otras dos salidas sin escapar del proyecto se revisaron y **son seguras**:
`$alertText` sale de un `match` con textos fijos en la misma plantilla, y
`{!! json_encode($responsableMap) !!}` es serialización de datos propios.

---

## S7 · Política de contraseñas débil — Severidad media

| Punto de creación | Regla |
|---|---|
| `StoreUserRequest:19` (alta de usuarios reales) | `min:8`, sin complejidad |
| `TrackingAdminController:123` (motorizados) | `min:6` |
| `Auth/RegisteredUserController:35` | `Rules\Password::defaults()` — ruta de Breeze, sin usar |

Con 2FA apagado (S4), la contraseña es el único factor. Ocho caracteres sin
requisitos admite `trimax12`, y seis admite `123456`.

Curiosidad reveladora: el único sitio que usa la regla robusta de Laravel es la
ruta de registro de Breeze, que no está activa.

**Corrección:**

```php
'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()->uncompromised()],
```

`uncompromised()` contrasta contra la base de HaveIBeenPwned por k-anonimato
(no envía la contraseña). Aplicarlo en `StoreUserRequest` y en
`TrackingAdminController`. No fuerza rotación de las existentes; para eso hace
falta una campaña aparte.

---

## S8 · Descarga de adjuntos sin verificar propiedad — Severidad media

> 🟡 **Parcialmente resuelto 2026-07-27, como efecto lateral de A1.** Al
> aplicar `SedeScope` a `Voucher` (piloto de A1), `Voucher::findOrFail($id)`
> en `revisionFile()` (línea de abajo) ahora filtra automáticamente por sede
> — un usuario con rol `sede` de una sede distinta a la del voucher recibe
> 404 en vez de leer el adjunto. Verificado con
> `tests/Feature/VoucherAttachmentDiskTest.php::test_usuario_de_otra_sede_no_puede_leer_el_adjunto_de_revision`.
>
> Sigue **sin resolver** el resto del hallazgo: finanzas/admin/super_admin no
> están restringidos por `SedeScope` (a propósito — sí deben ver todas las
> sedes), así que dentro de ese grupo de roles cualquiera con
> `puedeVerVouchers()` sigue pudiendo leer el adjunto de cualquier voucher
> sin más verificación de propiedad. Eso requeriría una regla de negocio
> distinta (ej. "finanzas solo ve lo que le fue asignado a revisar"), que no
> estaba definida ni se inventó aquí.

`VoucherController::revisionFile()` comprueba permiso de rol, pero no relación
con el recurso:

```php
if (!auth()->user()->puedeVerVouchers()) { abort(403); }
$voucher = Voucher::findOrFail($id);   // ← cualquier id
```

Cualquier usuario con el permiso genérico puede leer el adjunto de **cualquier**
voucher cambiando el `{id}` de la URL, incluidos los de otras sedes.

Puede ser intencionado — finanzas revisa todas las sedes —, pero entonces el
permiso debería llamarse así, y los usuarios de sede no deberían tenerlo. Hoy no
hay forma de distinguir "puede ver los vouchers de su sede" de "puede ver todos".

Es la misma raíz que ARQUITECTURA.md, A1: la frontera por sede se comprueba a
mano y aquí no se comprobó. Se corrige con el `SedeScope` propuesto allí, que
aplicaría el filtro también a este `findOrFail`.

---

## S9 · Blade no escapa nada — Severidad crítica

> ✅ **Resuelto 2026-07-27.** Antes de quitar la línea se auditó el codebase
> buscando usos que dependieran a propósito de este comportamiento para
> imprimir HTML: sin editores WYSIWYG ni columnas de HTML enriquecido en la
> base, sin PHP que arme strings HTML para pasarlos a una vista, sin bloques
> `@php` dentro de Blade que construyan HTML. Los `{{ }}` dentro de
> `<script>` (rutas, `csrf_token()`, `Auth::id()`, `auth()->user()->sede`)
> solo llevan datos escalares sin caracteres especiales — no se rompen con el
> escape por defecto.
>
> Se eliminó `Blade::setEchoFormat('%s')` de `AppServiceProvider.php:33`, se
> limpió la caché de vistas compiladas (`view:clear`) y se compilaron las 108
> vistas de una sola vez con `view:cache` para confirmar que ninguna tiene un
> error de sintaxis — 0 errores. `RequerimientoEstadoEmailEscapingTest` (el
> test que documentó este hallazgo) pasa ahora en verde. `/login` y `/up`
> siguen respondiendo 200 sin nada en los logs.
>
> Con esto los `{!! !!}` explícitos vuelven a ser la única forma de imprimir
> HTML sin escapar en la app — que es como se supone que debe funcionar
> Blade. Reemplaza la fila "XSS en vistas" de
> [Verificado y correcto](#verificado-y-correcto): ya es cierto que solo 3
> salidas (ahora ninguna, con S6 también resuelto) usan `{!! !!}`.

> **Descubierto 2026-07-25** al escribir un test de regresión para S6
> (`tests/Unit/Emails/RequerimientoEstadoEmailEscapingTest.php`). El test
> falló pese a que el código fuente ya usa `{{ $extra }}` — la investigación
> llevó a esto:

```php
// app/Providers/AppServiceProvider.php:33
\Illuminate\Support\Facades\Blade::setEchoFormat('%s');
```

Esta línea existe desde el primer commit del proyecto (`d1278e6`, sin
comentario que explique por qué). Cambia el formato de compilación de
`{{ $var }}` de `htmlspecialchars($var, ...)` (el default de Laravel) a un
`sprintf('%s', $var)` sin escapar — es decir, **`{{ }}` pasó a comportarse
exactamente igual que `{!! !!}` en las 108 vistas de la aplicación**, sin
excepción, en los tres entornos (local/testing/producción, sin condición de
`config('app.env')` alrededor).

Verificado en runtime:

```php
// {{ $extra }} en emails/rrhh/requerimiento_estado.blade.php:94 compila a:
<?php echo $extra; ?>
// no a:
<?php echo e($extra); ?>
```

### Por qué invalida parte de este documento

La sección "[Verificado y correcto](#verificado-y-correcto)" de este mismo
documento decía "XSS en vistas: solo 3 salidas sin escapar en 108 vistas" —
ese conteo se hizo buscando usos literales de `{!! !!}`, sin saber que `{{ }}`
tampoco protege aquí. **El universo real de salidas potencialmente inyectables
es todo `{{ $variable }}` que imprima un valor con origen en input de
usuario**, no solo los 3 casos de `{!! !!}` documentados. La corrección de S6
(cambiar `{!! $extra !!}` por `{{ $extra }}`) **no protege nada mientras esta
línea siga activa** — quedó demostrado con el test que falla en
`tests/Unit/Emails/RequerimientoEstadoEmailEscapingTest.php`.

### Corrección propuesta

1. Eliminar `Blade::setEchoFormat('%s')` de `AppServiceProvider.php:33` para
   volver al comportamiento por defecto de Laravel (`{{ }}` escapa,
   `{!! !!}` no).
2. **Antes de quitarla**, auditar las 108 vistas buscando `{{ }}` que hoy
   dependan implícitamente de este override para imprimir HTML a propósito
   (ej. texto enriquecido guardado ya como HTML) — esos casos deben pasar a
   `{!! !!}` explícito para no romperse.
3. Re-ejecutar `tests/Unit/Emails/RequerimientoEstadoEmailEscapingTest.php`
   (debe pasar a verde) y revisar visualmente las vistas con más JS/HTML
   inline (`comercial/acuerdos.blade.php`, `descuentos-especiales.blade.php`,
   ver ARQUITECTURA.md A6) tras el cambio.

Dado el alcance (toca las 108 vistas a la vez, cambia el comportamiento de
seguridad de renderizado en todo el sistema), se auditó primero y se aplicó
después de confirmar que ningún lugar dependía a propósito del bug — ver el
recuadro de arriba.

---

## S10 · `VoucherController` sin control de permiso en dos endpoints — Severidad alta

> ✅ **Resuelto 2026-07-27.** Agregado `if (!auth()->user()->puedeVerVouchers())
> { abort(403); }` en `servirArchivo()` (antes del `Voucher::findOrFail()`) y
> el equivalente en JSON en `getFacturas()`, igual patrón que ya usan
> `revisionFile()`, `revisar()` y `destroy()` en el mismo archivo. Cubierto
> con 2 tests de regresión nuevos en `VoucherAttachmentDiskTest.php`: un
> usuario sin rol relevante (`consultor`, sin `puede_ver_vouchers`) recibe 403
> tanto al intentar descargar el archivo de otro como al pedir sus facturas.
> No cierra la frontera por sede (sigue siendo S8/A1) — solo cierra el acceso
> a usuarios sin ningún permiso sobre vouchers.

> **Descubierto 2026-07-25** en la revisión de los cambios de S1.

`servirArchivo()` y `getFacturas()` no llaman a `puedeVerVouchers()` ni a
ningún otro chequeo de rol o sede — a diferencia de `index()`, `historial()`,
`revisar()`, `revisionFile()` y `destroy()`, que sí lo hacen:

```php
// VoucherController.php — servirArchivo()
public function servirArchivo($id, $index)
{
    $voucher  = Voucher::findOrFail($id);   // ← sin chequeo de permiso
    ...
}

// VoucherController.php — getFacturas()
public function getFacturas($id)
{
    $voucher = Voucher::with(['facturas', 'revisor'])->findOrFail($id);
    $user    = auth()->user();               // se lee pero no se usa para autorizar
    ...
}
```

Ambas rutas están detrás de `auth` (`web.php:76`), así que el ataque no es
anónimo — pero **cualquier usuario autenticado del sistema, sin importar su
rol**, puede:

- Leer las facturas (RUC, montos) de cualquier voucher vía
  `GET /vouchers/{id}/facturas`.
- Descargar el archivo adjunto de cualquier voucher vía
  `GET /vouchers/{id}/archivo/{index}`, moviendo el disco de `public` a
  `local` (S1) protege de nginx, pero no de este endpoint autenticado.

Esto significa que **la corrección de S1 reduce la exposición pero no la
cierra del todo**: antes cualquiera con la URL (ni sesión) leía el archivo;
ahora hace falta sesión, pero cualquier usuario logueado —vendedor, sede,
consultor— puede leer vouchers de otras sedes igual, solo que autenticado.

Nótese además que la afirmación de S1 ("Ambos controladores exponen además
una ruta de descarga *sí* protegida") es cierta solo para `revisionFile()`
(adjuntos de la revisión de finanzas), no para `servirArchivo()` (adjuntos
originales del voucher).

### Corrección propuesta

Añadir el mismo chequeo que ya usan los demás métodos del controlador:

```php
public function servirArchivo($id, $index)
{
    if (!auth()->user()->puedeVerVouchers()) {
        abort(403);
    }
    ...
}

public function getFacturas($id)
{
    if (!auth()->user()->puedeVerVouchers()) {
        return response()->json(['error' => 'Sin permiso.'], 403);
    }
    ...
}
```

Es del mismo tamaño y riesgo que S1 (una condición al principio del método,
patrón ya usado en el resto del archivo). No cierra la frontera por sede
(eso sigue siendo S8/A1), pero sí cierra el acceso a usuarios sin ningún
permiso sobre vouchers.

---

## Verificado y correcto

Vectores revisados que **no** presentan problema. Se listan porque un informe de
seguridad sin esta sección invita a asumir lo peor de lo que no menciona:

| Vector | Estado |
|---|---|
| **Inyección SQL** | Sin concatenación de input. El único `DB::raw` con variable (`TriMaxAIAssistant.php:796`) recibe `$groupColumn` de un `match` con lista blanca y `default`. Los `selectRaw` usan literales. |
| **Asignación masiva** | Los 30 modelos declaran `$fillable` o `$guarded`. Ninguno con `$guarded = []`. Cero usos de `$request->all()` en `create`/`fill`/`update`. |
| **CSRF** | Sin exclusiones en `bootstrap/app.php` ni middleware propio. Token presente y cookie emitida. |
| **Rate limiting** | `throttle:login` en el POST de login, `throttle:dashboard` en el grupo autenticado, `throttle:uploads` en subidas. |
| **Secretos en el repositorio** | `.env` en `.gitignore` y no rastreado. Sin claves, certificados ni credenciales versionados. (Las del `docker-compose.yml` sí: INFRAESTRUCTURA.md, I2.) |
| **Hash de contraseñas** | `Hash::make` / bcrypt. Sin hashes propios ni MD5/SHA1. |
| **XSS en vistas** | Era incorrecta hasta el 2026-07-27: se basaba en contar `{!! !!}` sin saber que `{{ }}` tampoco escapaba (ver [S9](#s9--blade-no-escapa-nada--severidad-crítica), ya resuelto). Ahora sí es cierta: 108 vistas auditadas, sin editores WYSIWYG ni HTML construido a mano, `{{ }}` vuelve a escapar por defecto y los únicos `{!! !!}` que quedan (`$alertText` con textos fijos, `json_encode($responsableMap)`) ya estaban verificados como seguros. |
| **Validación de subidas** | Todas las rutas de subida declaran `mimes:` y `max:`. Nombres reescritos a UUID, sin usar el nombre original en disco. |
| **Cifrado de sesión** | `SESSION_ENCRYPT=true`, `http_only`, `same_site=lax`. |
| **Bloqueo por IP** | `CheckIpBlacklistMiddleware` global en web y api; registro de intentos fallidos con motivo y usuario. |

---

## Plan sugerido

| Orden | Acción | Hallazgo | Esfuerzo |
|---|---|---|---|
| 1 | Adjuntos al disco `local` + migrar los ya subidos | S1 | ~2 h |
| 2 | Middleware de cabeceras de seguridad | S3 | ~30 min |
| 3 | `{!! $extra !!}` → `{{ $extra }}` | S6 | Minutos |
| 4 | `composer update` + verificar exportación, PDF y correo | S2 | ~3 h |
| 5 | `SESSION_SECURE_COOKIE=true` en el checklist de despliegue | S5 | Minutos |
| 6 | Endurecer política de contraseñas | S7 | ~1 h |
| 7 | Decidir sobre 2FA: activar por rol o retirar | S4 | Decisión + ~4 h |
| 8 | Propiedad en descargas (vía `SedeScope`) | S8, A1 | Medio |

Los tres primeros suman menos de tres horas y cierran el hueco de exposición más
grave.
