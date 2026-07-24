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

| # | Hallazgo | Severidad | Explotable hoy |
|---|---|---|---|
| S1 | Adjuntos financieros servidos sin autenticación | **Crítica** | Sí |
| S2 | 49 vulnerabilidades en dependencias (2 críticas, 10 altas) | **Alta** | Parcial |
| S3 | Ninguna cabecera de seguridad HTTP | **Alta** | Sí |
| S4 | 2FA implementado pero nunca aplicado; 0 de 65 usuarios | **Alta** | — |
| S5 | Cookie de sesión sin flag `Secure` | Media | Al desplegar con HTTPS |
| S6 | Inyección de HTML en correo de requerimientos | Media | Sí (usuario privilegiado) |
| S7 | Política de contraseñas débil | Media | Sí |
| S8 | Descarga de adjuntos sin verificar propiedad | Media | Sí |

---

## S1 · Adjuntos financieros servidos sin autenticación — Severidad crítica

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
| **XSS en vistas** | Solo 3 salidas sin escapar en 108 vistas; 2 verificadas seguras, la tercera es S6. |
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
