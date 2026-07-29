# Auditoría de frontend — reports-trimax

**Fecha:** 2026-07-24
**Alcance:** `resources/views/`, `public/assets/`, carga de recursos, accesibilidad
**Stack real:** Blade + Bootstrap 5.0.0 + jQuery 3.5.1 + plantilla de administración estática
**Verificado contra:** la instancia local en `http://localhost:8000`

> Documento vivo. Actualízalo cuando se corrija un hallazgo.
> Complementa a [ARQUITECTURA.md](ARQUITECTURA.md) (A6, decisión sobre Vite) y a
> [SEGURIDAD.md](SEGURIDAD.md) (S3, cabeceras).

---

## Resumen ejecutivo

El frontend es una **plantilla de administración Bootstrap servida estáticamente**,
con la lógica de cada pantalla escrita directamente dentro de su vista Blade. No
hay proceso de build activo, ni paquetes, ni módulos compartidos.

Funciona. Pero tiene dos consecuencias medibles: **12 724 líneas de JavaScript
viven dentro de plantillas**, donde no se pueden lintar, minificar, probar ni
reutilizar; y **la aplicación depende de cuatro servicios externos en tiempo de
ejecución**, sin fijar versiones ni verificar integridad.

| # | Hallazgo | Severidad | Tipo |
|---|---|---|---|
| F1 | Librerías de terceros desde CDN sin SRI ni versión fija | **Alta** | Seguridad |
| F2 | Nombres de empleados enviados a un servicio externo en cada avatar | **Alta** | Privacidad |
| F3 | 12 724 líneas de JavaScript dentro de las vistas | **Alta** | Mantenibilidad |
| F4 | Tres versiones de Chart.js conviviendo, con un plugin incompatible | Media | Corrección |
| F5 | 41 de 50 librerías vendor no se usan: 34 MB muertos | Media | Rendimiento |
| F6 | Accesibilidad: imágenes sin `alt`, `alert()` nativos, CSS disperso | Media | Accesibilidad |
| F7 | Fuentes SCSS y sourcemaps servidos públicamente | Baja | Exposición |
| F8 | Sin vistas de error 404 ni 500 | Baja | Experiencia |

**Métricas base**

| Métrica | Valor |
|---|---|
| Vistas Blade | 108 |
| LOC totales en vistas | 44 045 |
| — de las cuales, JavaScript | **12 724** (29 %) |
| Vistas con `<script>` embebido | 52 |
| Vistas con `<style>` embebido | 57 |
| Atributos `style="` inline | 891 |
| Peso de `public/assets/` | 46 MB (1644 archivos) |
| Layouts | 3 (`app` usado por 58 vistas) |

---

## F1 · Terceros desde CDN sin integridad ni versión — Severidad alta

> ✅ **Resuelto 2026-07-27** (junto con F4, que comparte causa y arreglo).
> Chart.js 4.4.0, SweetAlert2 y Leaflet 1.9.4 descargados a
> `public/assets/vendors/{chart.js,sweetalert2,leaflet}/` (JS + CSS +
> imágenes de marcador de Leaflet) y servidos localmente. No se vendorizaron
> los `.map` de sourcemap — F7 pide justo lo contrario, exponerlos.
>
> **Chart.js, unificado a una sola versión y un solo punto de carga**
> (resuelve F4 de paso): había en realidad **4** variantes conviviendo, no 3
> — el inventario original no había detectado que `admin/dashboard.blade.php`
> cargaba además `chart.js@3.9.1`. De los 13 `<script>` de Chart.js
> encontrados (5 sin fijar + 7 en `@4.4.0` + 1 en `@3.9.1`), **12 eran carga
> duplicada**: las 12 vistas que los tenían ya extienden `layouts/app.blade.php`,
> que carga Chart.js una vez para las 58 vistas del layout. Se borraron los
> 12 `<script>` sueltos y se dejó un único `<script>` en el layout, apuntando
> al Chart.js local. Se verificó antes que ninguna de las 12 vistas dependía
> de una API específica de su versión pineada (todas usan la sintaxis
> `scales: { x: {...}, y: {...} }` de v3/v4, ninguna la `xAxes/yAxes` de v2).
>
> `Chart.roundedBarCharts.js` (el plugin de F4, escrito para v2) se retiró
> del layout y se borró el archivo: `grep` confirmó que su única propiedad
> (`cornerRadius`) que aparecía en el resto del código era una opción nativa
> de *tooltip* de Chart.js v4 en `lead-time-semanal.blade.php`, sin relación
> con el plugin — ningún gráfico dependía de él.
>
> **SweetAlert2**: 6 referencias, todas por-vista (no en el layout, correcto
> porque no todas las páginas lo usan) — cambiadas a la copia local.
>
> **Leaflet**: 4 vistas (CSS + JS cada una) — cambiadas a la copia local,
> con las 5 imágenes de marcador (`marker-icon(-2x)?.png`,
> `marker-shadow.png`, `layers(-2x)?.png`) que su propio CSS/JS referencian
> por ruta relativa.
>
> **Hallazgo adicional, mismo patrón**: `auth/login.blade.php` —la página
> de login real, no `welcome.blade.php`— cargaba la fuente de iconos MDI
> desde `cdn.jsdelivr.net/npm/@mdi/font@7.2.96`, una versión distinta a la ya
> vendorizada en `assets/vendors/mdi/` (v3.9.97) que usa el resto de la app.
> Verificados los 10 íconos que usa la página contra el CSS local antes de
> cambiar: los 10 existen en v3.9.97. Cambiado a la copia local.
>
> **Efecto colateral encontrado investigando `fonts.bunny.net`** (2 vistas
> según el inventario original): ambas — `resources/views/welcome.blade.php`
> y `resources/views/layouts/guest.blade.php` (más
> `app/View/Components/GuestLayout.php`, la clase que lo respalda) — son
> **scaffolding muerto de Breeze**, sin ninguna ruta ni vista que las
> referencie (`/` redirige directo a `/login`, nunca renderiza `welcome`).
> Mismo patrón que `LoginRequest.php` (ver adenda de A7 en ARQUITECTURA.md).
> Se borraron los 3 archivos en vez de arreglar su CDN — no había nada que
> preservar. Con esto, F1 queda en 0 referencias a CDN en todo
> `resources/views/`.
>
> Verificado en navegador (Puppeteer + Chrome headless, usuario de prueba
> creado y borrado después): `window.Chart.version === '4.4.0'`,
> `window.Swal` y `window.L` definidos, mapa Leaflet con tiles y controles
> renderizando, gráficos de Chart.js renderizando — **cero errores de
> consola y cero requests fallidos** en las 3 páginas probadas (antes había
> el error de F4 en cada carga). Suite completa: 76 tests en verde salvo el
> fallo preexistente de `ExampleTest`.

### Qué pasa

Las vistas cargan código ejecutable desde cuatro dominios externos, y
**ninguna de las referencias usa `integrity=`** (verificado: cero ocurrencias de
SRI en todo `resources/views/`).

| Recurso | Vistas | Versión |
|---|---|---|
| `cdn.jsdelivr.net/npm/chart.js` | 5 | **sin fijar** — sirve la última |
| `cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js` | 7 | fijada |
| `cdn.jsdelivr.net/npm/sweetalert2@11` | 6 | **major sin fijar** |
| `unpkg.com/leaflet@1.9.4` (js + css) | 4 | fijada |
| `fonts.bunny.net` | 2 | — |

La referencia de `layouts/app.blade.php:26` es la más expuesta, porque se carga
en **las 58 vistas** que extienden ese layout:

```blade
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### Por qué importa

Ese script se ejecuta con acceso completo al DOM y a la sesión autenticada de un
usuario que está aprobando movimientos de dinero. Sin `integrity`, el navegador
acepta cualquier cosa que el CDN devuelva. Sin versión fijada, el contenido
puede cambiar entre dos cargas de página sin que nadie despliegue nada.

No hace falta que jsDelivr sea malicioso: basta un incidente en la cadena de
suministro del paquete publicado. Es el tipo de dependencia que no aparece en
`composer audit` ni en ningún inventario, porque no está declarada en ninguna
parte — solo en una etiqueta `<script>`.

Hay además un efecto de disponibilidad: si la red corporativa bloquea jsDelivr
o unpkg, los gráficos y los mapas dejan de funcionar sin ningún error visible en
el servidor.

### Corrección propuesta

1. **Descargar las cuatro librerías a `public/assets/vendors/`** y servirlas
   localmente. Es coherente con el resto del proyecto, que ya sirve 50
   librerías así, y elimina de golpe el riesgo de terceros y el de red.
2. Si se decide mantener el CDN, como mínimo **fijar versión exacta y añadir
   SRI**:

```blade
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
        integrity="sha384-…" crossorigin="anonymous"></script>
```

La opción 1 es la recomendada: resuelve F1 y F4 a la vez, y es requisito para
poder aplicar una CSP estricta más adelante.

---

## F2 · Nombres de empleados enviados a un servicio externo — Severidad alta

> ✅ **Resuelto 2026-07-27.** Nuevo componente Blade
> `resources/views/components/avatar-iniciales.blade.php` (iniciales
> calculadas con `mb_substr`/`mb_strtoupper`, sin librería, sin red) usado en
> las **24 ocurrencias** de `ui-avatars.com` encontradas en 14 vistas (más
> que las "12 lugares" originales — el conteo inicial no incluía las
> reconstrucciones dinámicas por JavaScript). De esas 24: 20 eran `<img>`
> renderizadas en Blade (migradas al componente) y 4 eran URLs construidas
> en `<script>` client-side dentro de `admin/users.blade.php`,
> `admin/dashboard.blade.php` y `marketing/dashboard/index.blade.php` (para
> actualizar avatares por polling de presencia, o al pintar un modal de
> encuesta) — se les escribió el equivalente JS (`avatarInicialesHtml()` en
> el dashboard de marketing) o se adaptó el código de actualización para
> tocar el nuevo `<span>` en vez de un `<img src>` inexistente.
>
> **Detalle que casi se pasa por alto:** `admin/users.blade.php` y
> `admin/dashboard.blade.php` tienen polling de presencia en vivo que hacía
> `item.querySelector('img').src = avatarUrl(...)` cada pocos segundos. Al
> cambiar el `<img>` por un `<span>`, ese `querySelector('img')` habría
> devuelto `null` y roto el polling con un error de JS silencioso. Corregido
> actualizando `background-color`/`textContent` del `<span>` en vez del
> `src` del `<img>`.
>
> Verificado en navegador (Puppeteer + Chrome headless, usuario de prueba
> creado y borrado después): navbar y su dropdown (visibles en las 58 vistas
> del layout) y la tabla de usuarios del dashboard de Marketing con datos
> reales — 74 avatares con iniciales correctas, incluida una con tilde
> (`ÓR`), tamaño y color consistentes con el original. Suite completa: 76
> tests en verde salvo el fallo preexistente de `ExampleTest`. Un error de
> consola sí apareció (`Cannot read properties of undefined (reading
> 'prototype')`) pero es **F4** (el plugin de Chart.js v2 corriendo contra
> v4), confirmado que existe también sin este cambio — no relacionado a F2,
> queda para cuando se aborde F1/F4.

### Qué pasa

Los avatares se generan con `ui-avatars.com`, pasando el nombre real del usuario
en la URL. Aparece en **12 lugares** distintos:

```blade
<img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff">
```

Y también sobre datos de terceros:

```blade
https://ui-avatars.com/api/?name={{ urlencode($sv->client_name) }}&background=10b981&color=fff
```

### Por qué importa

Cada vez que se pinta un listado de usuarios, el navegador hace una petición a
un servidor de terceros con el **nombre completo del empleado** en la
query string. En una tabla de 65 usuarios son 65 peticiones, cada una revelando
un nombre, más la IP del empleado y —al no haber `Referrer-Policy`, ver
[S3](SEGURIDAD.md#s3--sin-cabeceras-de-seguridad-http--severidad-alta)— la URL
interna desde la que se pidió.

Ese tercero acaba pudiendo reconstruir la plantilla de la empresa, quién la
consulta y desde dónde. En `client_name` el dato ya no es ni siquiera de
empleados, sino de clientes.

No es un fallo explotable por un atacante; es una fuga continua de datos
personales hacia un proveedor con el que probablemente no hay contrato ni
encargo de tratamiento.

### Corrección propuesta

Generar las iniciales localmente. No hace falta librería:

```blade
@php($ini = collect(explode(' ', $user->name))->take(2)->map(fn($p) => mb_substr($p,0,1))->implode(''))
<span class="avatar-initials" style="background:{{ $user->color ?? '#6366f1' }}">{{ $ini }}</span>
```

Es menos código que la URL que reemplaza, no depende de la red, no falla si el
servicio cae y no envía nada a nadie. Los `google.com/maps?q=lat,lng` que
aparecen en 2 vistas son enlaces, no incrustaciones: solo revelan la ubicación
si el usuario hace clic, y son aceptables.

---

## F3 · JavaScript dentro de las plantillas — Severidad alta

> 🟡 **Primer paso dado 2026-07-27** (de los 3 que propone esta sección).
> Creados los 3 módulos en `public/assets/js/app/`:
> - `http.js` — `fetch` envuelto con CSRF automático (`window.Http.get/post/put/del`).
> - `notify.js` — envoltorio sobre SweetAlert2 (`window.Notify.success/error/warning/info/confirm`).
> - `charts.js` — fábrica de Chart.js con las opciones comunes (`window.ChartFactory.create`).
>
> Sin build ni módulos ES — mismo estilo que el resto del proyecto (script
> global vía IIFE), consistente con que esta sección no depende de decidir
> Vite (A6).
>
> **`notify.js` ya adoptado, no solo creado**: las 9 vistas donde se
> reemplazaron los `alert()` nativos por SweetAlert2 en esta misma sesión
> (ver F6) se migraron directamente a `Notify.error(...)`/`Notify.warning(...)`
> en vez de `Swal.fire({icon, text})` repetido — los 19 sitios que antes
> tenían `alert()` ahora pasan por el envoltorio único.
>
> **`charts.js` también adoptado**: las 3 vistas de Productividad Sedes
> (`cobranza`, `caja-chica`, `comentarios`) que ya se habían tocado hoy
> para SweetAlert2 tenían cada una su propia función local `opcionesChart()`
> con las mismas opciones de Chart.js repetidas (stacked bars, tooltip
> custom). Sus 6 `new Chart(...)` pasaron a `ChartFactory.create(ctx, tipo,
> data, opcionesChart())` — la función local sigue existiendo (define las
> opciones específicas de cada gráfico), `ChartFactory` solo aporta el
> merge con las opciones comunes (leyenda, tooltip de fondo). Verificado en
> navegador: el gráfico "KPI Diario" renderiza igual que antes, con datos
> reales, sin errores de consola ni requests fallidos.
>
> `http.js` queda creado y listo pero **sin adoptar todavía**: ninguna
> vista se tocó esta sesión por una razón que ameritara migrar su
> `fetch`/`$.ajax` (las que sí se tocaron ya usaban `fetch` con manejo de
> error propio, migrarlas solo para usar `Http.get/post` sin otro motivo
> hubiera sido tocar código funcionando sin necesidad).
>
> Quedan las 52 vistas con JavaScript embebido (12 724 líneas) sin migrar
> — sigue siendo, por diseño de esta misma sección, trabajo sin final
> cerrado: se migra vista por vista cuando se toque cada una por otra
> razón, no de una vez. Intentar migrar las 52 en una sola sesión sin
> tests de JavaScript que detecten una regresión habría sido el tipo de
> cambio de alto riesgo que esta sección explícitamente desaconseja.

### Qué pasa

**12 724 líneas de JavaScript** viven dentro de etiquetas `<script>` en 52
vistas Blade — el 29 % de todo el código de `resources/views/`.

| Vista | Líneas de JS |
|---|---|
| `comercial/acuerdos.blade.php` | 1370 |
| `comercial/descuentos-especiales.blade.php` | 1213 |
| `productividad/cobranza-sedes/cobranza.blade.php` | 867 |
| `vouchers/index.blade.php` | 673 |
| `comercial/lead-time-semanal.blade.php` | 652 |
| `comercial/lead-time-objetivo-mas.blade.php` | 566 |
| `productividad/ordenes-x-usuario/index.blade.php` | 555 |
| `marketing/dashboard/index.blade.php` | 519 |

Un solo archivo de plantilla contiene 1370 líneas de lógica de cliente.

### La duplicación que eso provoca

Sin módulos compartidos, cada vista reimplementa lo mismo:

| Patrón | Ocurrencias |
|---|---|
| `Swal.fire(` | 63 |
| `fetch(` | 49 |
| `$.ajax(` | 37 |
| `new Chart(` | 33 |
| Lectura manual del token CSRF | 15 |

Conviven **dos estilos de cliente HTTP** —`fetch` moderno y `$.ajax` de
jQuery— sin criterio aparente. El token CSRF se lee a mano 15 veces en lugar de
configurarse una vez.

### Por qué importa

Ese código no se puede lintar (ESLint no entra en `.blade.php`), no se puede
minificar, no se puede cachear por separado —cambia con cada render de la
página—, no se puede probar, y no se puede reutilizar: cuando hay que cambiar
cómo se muestra un error de red, hay 63 sitios donde mirar.

Es también la causa directa de que `resources/views/` tenga el doble de código
que toda la aplicación PHP (ver [A6](ARQUITECTURA.md#a6--pipeline-de-frontend-inutilizado--severidad-media)),
y lo que bloquea aplicar una `Content-Security-Policy` estricta: una CSP que
prohíba `script` inline rompería las 52 vistas.

### Corrección propuesta

Trabajo incremental, sin big bang. El orden que da resultado antes:

1. **Extraer primero lo compartido**, que es lo que más se repite y menos riesgo
   tiene. Un `public/assets/js/app/` con tres módulos:
   - `http.js` — un `fetch` envuelto que ya incluye el CSRF y el manejo de error
     estándar. Elimina las 15 lecturas manuales de token.
   - `notify.js` — envoltorio sobre SweetAlert. Absorbe los 63 `Swal.fire`.
   - `charts.js` — fábrica con las opciones comunes. Absorbe los 33 `new Chart`.
2. **Migrar las vistas de mayor a menor**, y solo cuando ya haya que tocarlas
   por otra razón. Las cuatro primeras de la tabla son el 40 % del problema.
3. **Unificar en `fetch`** y retirar `$.ajax` a medida que se toque cada vista.

Cada vista migrada reduce la superficie y acerca la posibilidad de una CSP. No
requiere decidir antes lo de Vite (A6): funciona igual con el template estático.

---

## F4 · Tres versiones de Chart.js conviviendo — Severidad media

> ✅ **Resuelto 2026-07-27, junto con F1** (mismo cambio, misma causa —
> resueltos en una sola pasada). Detalle completo en
> [F1](#f1--terceros-desde-cdn-sin-integridad-ni-versión--severidad-alta):
> en realidad eran **4** versiones, no 3 (se encontró una `@3.9.1` adicional
> en `admin/dashboard.blade.php` que el inventario original no había
> detectado). Unificadas a Chart.js 4.4.0 servido localmente desde un único
> `<script>` en el layout; los 12 `<script>` de Chart.js sueltos en vistas
> individuales (duplicados, ya cubiertos por el layout) se borraron;
> `Chart.roundedBarCharts.js` (el plugin v2 incompatible) se retiró y se
> borró el archivo, confirmado que ningún gráfico dependía de él;
> `vendors/chart.js/` v2.9.4 (sin usar) se reemplazó por la 4.4.0.

### Qué pasa

| Origen | Versión | Estado |
|---|---|---|
| `public/assets/vendors/chart.js/` | 2.9.4 | Presente en disco, **nunca cargada** |
| CDN sin fijar (`/npm/chart.js`), en el layout | la última (hoy 4.x) | Cargada en las 58 vistas del layout |
| CDN `chart.js@4.4.0` | 4.4.0 | Cargada en 7 vistas |

Las 7 vistas que fijan 4.4.0 **también** reciben la del layout sin fijar: cargan
Chart.js dos veces, y la segunda pisa a la primera.

Y hay un detalle peor. El layout carga, después del CDN:

```blade
<script src="{{ asset('assets/js/Chart.roundedBarCharts.js') }}"></script>
```

Ese plugin está escrito para la **API de Chart.js v2** (`Chart.elements.Rectangle`,
`Chart.helpers.extend`), que **no existe en v4**. Se está ejecutando un plugin
contra una versión mayor incompatible en todas las páginas.

### Por qué importa

Es el escenario donde un gráfico "a veces no sale redondeado" y nadie sabe por
qué. Peor: como la versión del CDN no está fijada, el día que jsDelivr publique
una v5 con cambios de API, los 33 `new Chart()` de la aplicación pueden dejar de
funcionar **sin que nadie haya desplegado nada**.

### Corrección propuesta

1. Elegir **una** versión —4.4.0, la que ya está fijada en 7 vistas— y servirla
   localmente (resuelve también F1).
2. Cargarla **solo en el layout** y quitar las 7 referencias duplicadas.
3. Verificar si `Chart.roundedBarCharts.js` aporta algo hoy. Casi con seguridad
   no: retirarlo, o portarlo a la API de plugins de v4 si el redondeo se quiere
   conservar.
4. Borrar `vendors/chart.js/` v2.9.4, que no se usa.

---

## F5 · Librerías vendor sin usar — Severidad media

> ✅ **Resuelto 2026-07-27.** Verificación programática antes de borrar
> nada: cada una de las 50 carpetas de `vendors/` (sin contar `css/`/`js/`,
> que son utilidades, no librerías) se buscó contra `resources/views/`,
> `public/assets/css/` **y** `public/assets/js/` — este último paso
> encontró que varios "usos sueltos" que aparecían al buscar el nombre
> pelado (`clipboard`, `moment`, `sweetalert`) eran falsos positivos: la
> palabra "clipboard" aparecía en clases de íconos MDI
> (`mdi-clipboard-check-outline`), "moment" en el texto español "Un
> momento por favor", y "sweetalert" como substring de `sweetalert2`
> (la librería real, ya en uso). Otros "usos sueltos" (`dropify`,
> `dropzone`, `select2`, `quill`, `colcade`, `inputmask`, `lightgallery`,
> `pwstabs`, `simplemde`) resultaron ser scripts de inicialización del
> propio template de demo en `public/assets/js/` (`editorDemo.js`,
> `tabs.js`, etc.) que **tampoco** carga ninguna vista real — código muerto
> llamando a otro código muerto. También se revisó que ninguna de las 40
> quedara referenciada dentro de los bundles ya minificados
> (`vendors/js/vendor.bundle.base.js`, `vendors/css/vendor.bundle.base.css`).
>
> Confirmadas **40 carpetas sin ninguna referencia real** (una más que la
> estimación original de 41 menos las que este trabajo ya había convertido
> en usadas: `chart.js` — antes v2.9.4 muerta, ahora v4.4.0 real, ver F1/F4).
> Borradas las 40, incluida `jquery-file-upload` (la que traía jQuery v1.5
> de 2011 embebido). `vendors/` pasó de **33 MB a 4,5 MB**. Quedan 10
> carpetas, todas con uso real verificado:
> `bootstrap-datepicker`, `chart.js`, `feather`, `leaflet`, `mdi`,
> `progressbar.js`, `simple-line-icons`, `sweetalert2`, `ti-icons`, `typicons`.
>
> Verificado en navegador tras el borrado (Puppeteer + Chrome headless):
> cero errores de consola, cero requests fallidos en la página de inicio.
> Suite completa: 76 tests en verde salvo el fallo preexistente de
> `ExampleTest`.

`public/assets/vendors/` contiene **50 librerías**. Referenciadas desde las
vistas o el layout: **9**.

```
Usadas:    bootstrap-datepicker · feather · mdi · simple-line-icons ·
           ti-icons · typicons · progressbar.js · css · js
Sin usar:  ace-builds · codemirror · dropzone · dropify · lightgallery ·
           jquery-file-upload · jquery-tags-input · jquery-validation ·
           ion-rangeslider · justgage · colcade · clipboard ·
           jquery-asColorPicker · jquery.repeater · … (41 en total)
```

**34 MB** del repositorio, versionados en git, servidos por nginx y descargados
nunca — pero disponibles públicamente.

Detalle relevante: `jquery-file-upload/jquery.uploadfile.min.js` lleva embebido
**jQuery v1.5** (de 2011). No se carga en ninguna página, así que no es
explotable; pero está publicado en la raíz web y es exactamente el tipo de
archivo que encuentra un escáner automático.

### Corrección propuesta

Eliminar las 41 carpetas no referenciadas. Antes de borrar, confirmar que
tampoco se cargan desde CSS ni desde el bundle:

```bash
grep -rl "nombre-libreria" resources/views/ public/assets/css/ public/assets/js/
```

Ahorra 34 MB en cada `clone` y en cada contexto de build de Docker, y reduce lo
que un escáner puede encontrar.

Nota de versiones sobre lo que **sí** se usa: Bootstrap 5.0.0 (2021) y
jQuery 3.5.1 (2020). No les consta CVE aplicable —las vulnerabilidades conocidas
de jQuery, CVE-2020-11022/11023, se corrigieron en 3.5.0— pero llevan cinco años
sin actualizarse. Conviene planificar la subida, no urge.

---

## F6 · Accesibilidad y consistencia — Severidad media

> ✅ **2026-07-27 — punto 1 resuelto: los 10 `console.log` borrados.**
> Verificado caso por caso que ninguna variable quedaba huérfana al quitar
> el log — en `consulta-orden.blade.php::cargarRecientes()` `loadTime` se
> preservó porque también alimenta un `<span>` visible ("Cargado en Xs") y
> un toast de SweetAlert2, no solo el log que se borró.
>
> ✅ **Punto 3 resuelto también (`alt`/`aria-label`), con un alcance mayor
> al estimado ("14 y 6").** Un barrido exhaustivo de las 41 `<img>` de la
> app encontró **19** con `alt` ausente o vacío (9 sin el atributo, 10 con
> `alt=""`) y **39** controles interactivos que son solo un ícono sin
> `aria-label` ni `title` (36 con ícono `mdi`, 3 con SVG inline en el
> asistente de IA — un componente incluido en el layout global, así que
> aparecen en prácticamente todas las páginas).
>
> De las 10 imágenes con `alt=""`: las 8 caras de emoji del formulario de
> encuesta (`marketing/survey/form.blade.php`) se dejaron intencionalmente
> vacías — cada una tiene al lado una etiqueta visible ("Muy Feliz",
> "Feliz", ...) que ya transmite el mismo significado; ponerles `alt`
> habría duplicado el anuncio en un lector de pantalla. Solo se corrigió
> el logo de esa misma vista (`alt=""` → `alt="TRIMAX"`, sin texto
> adyacente que lo supla). Las 9 restantes con `alt` ausente sí se
> completaron con el nombre real del archivo/firma (variable ya disponible
> en cada caso: `a.name`, `f.name`, o una descripción fija para las firmas
> del PDF de requerimientos de RRHH).
>
> De los 39 controles: búsquedas, aprobar/rechazar, paginación, "Volver",
> mostrar/ocultar contraseña (con `aria-label` dinámico según estado), y
> los 2 toggles del navbar + los 3 del asistente de IA — estos últimos 5
> son los de mayor impacto real, por aparecer en el layout global. El
> toggle de mostrar/ocultar contraseña y el del asistente de IA actualizan
> el `aria-label` en el mismo evento que ya cambiaba el ícono, no solo al
> cargar la página.
>
> **Efecto colateral:** `resources/views/layouts/navigation.blade.php`
> —encontrado durante este barrido— es el mismo scaffolding muerto de
> Breeze que ya se venía limpiando (ver adendas de A7 en ARQUITECTURA.md):
> cero vistas lo incluyen. Borrado.
>
> Verificado en navegador (Puppeteer + Chrome headless): el `aria-label`
> de mostrar/ocultar contraseña y del asistente de IA cambian
> correctamente al hacer clic, cero errores de consola. Suite completa: 76
> tests en verde salvo el fallo preexistente de `ExampleTest`.
>
> ✅ **Punto 2 también resuelto: los 19 `alert()` nativos reemplazados por
> SweetAlert2.** 9 de las 10 vistas afectadas no cargaban SweetAlert2
> todavía (solo las de `comercial/` lo tenían) — se agregó el `<script>`
> local (el mismo de F1) donde hacía falta, incluyendo dos vistas que no
> extienden `layouts.app` (`marketing/survey/form.blade.php`, la encuesta
> pública de clientes, y `auth/login.blade.php`, previo a la sesión).
> Verificado en navegador: el enlace "¿Olvidaste tu contraseña?" del login
> ahora abre un modal de SweetAlert2 en vez de bloquear la pestaña con un
> `alert()` del navegador.
>
> Punto 4 (891 estilos inline) sigue pendiente — es trabajo de extraer
> clases de utilidad vista por vista, sin un punto de corte natural ni
> beneficio proporcional al esfuerzo de tocar prácticamente las 108 vistas
> de golpe; se deja para cuando se toque cada vista por otra razón, mismo
> criterio que F3.

| Comprobación | Resultado |
|---|---|
| `<img>` sin atributo `alt` | **14 de 29** |
| Botones de solo icono sin `aria-label` ni `title` | 6 |
| Uso de atributos `aria-*` | 74 (hay base, es irregular) |
| `<html lang>` en layouts | 2 de 3 |
| `<meta viewport>` | Presente en los layouts de página |
| `alert()` nativo del navegador | 19 |
| `console.log()` olvidados | 10 |
| Atributos `style=` inline | 891 |
| Vistas con bloque `<style>` | 57 |

Lo más visible para el usuario son los **19 `alert()` nativos** conviviendo con
63 diálogos de SweetAlert: la misma aplicación avisa de dos maneras distintas
según la pantalla.

Los **891 estilos inline** más 57 bloques `<style>` significan que el CSS está
tan disperso como el JS: cambiar un color de estado obliga a buscar por todo
`resources/views/`.

### Corrección propuesta

Por orden de coste/beneficio:

1. **Los 10 `console.log`** — borrado directo, cero riesgo.
2. **Los 19 `alert()`** → `notify.js` del módulo de F3. Unifica la experiencia.
3. **Los 14 `alt`** y los 6 `aria-label` — media hora, y es lo que permite usar
   el sistema con lector de pantalla.
4. **Los estilos inline** — solo al tocar cada vista; extraer a clases de
   utilidad en `public/assets/css/`.

---

## F7 · Fuentes SCSS y sourcemaps públicos — Severidad baja

> ✅ **Resuelto 2026-07-27.** Verificado antes que nada en `resources/views/`
> ni en `public/assets/css/` referenciaba `assets/scss/` (el CSS ya
> compilado es lo que se sirve realmente) — borrado `public/assets/scss/`
> completo (214 archivos, 1.1 MB) y los 11 `.map` sueltos bajo `public/`.
> Confirmado con `curl`: las rutas que antes daban 200
> (`/assets/scss/vertical-layout-light/_navbar.scss`,
> `/assets/vendors/js/bootstrap.min.js.map`) ahora dan 404; el resto de
> `public/assets/` (CSS/JS compilados, los vendors nuevos de F1) sigue en
> 200.

Verificado contra la instancia local:

```
GET /assets/scss/vertical-layout-light/_navbar.scss   → 200
GET /assets/vendors/js/bootstrap.min.js.map           → 200
```

Hay **214 archivos `.scss`** y **11 sourcemaps** dentro de `public/`, servidos
por nginx a cualquiera.

El impacto es bajo —son fuentes de una plantilla comercial, no código propio—,
pero los sourcemaps reconstruyen el JavaScript original y los SCSS son 1,1 MB
que nadie consume. No hay ningún motivo para publicarlos.

**Corrección:** eliminar `public/assets/scss/` y los `.map` del repositorio. Si
se quiere conservar el SCSS como fuente, moverlo a `resources/scss/`, fuera de
la raíz web.

---

## F8 · Sin vistas de error 404 ni 500 — Severidad baja

> ✅ **Resuelto 2026-07-27, con una decisión distinta a la sugerida.** La
> corrección propuesta original decía "extendiendo el mismo layout que las
> dos existentes" — pero de las dos vistas existentes, solo `403.blade.php`
> extiende `layouts.app` (seguro, porque un 403 solo ocurre dentro del
> grupo de rutas `auth`, con sesión ya garantizada); `503.blade.php` es
> HTML autocontenido, precisamente porque el modo mantenimiento puede
> mostrarse a cualquiera, con o sin sesión.
>
> Un 404 o un 500 pueden pasarle a **cualquiera** — un invitado con una URL
> mal escrita, un enlace roto, un error de servidor a media petición sin
> sesión todavía resuelta. `layouts.app` incluye el navbar, que llama
> `Auth::user()->name` sin verificar null. Extenderlo desde un 404/500
> habría cambiado un error controlado por un fatal error no controlado la
> primera vez que un invitado topara con una URL rota. Se optaron por
> páginas HTML autocontenidas (mismo criterio que `503.blade.php`), con la
> identidad visual de `403.blade.php` (logo, número grande, ícono, botones
> "Ir al Inicio"/"Volver").
>
> Verificado: `view('errors.404')->render()` y `view('errors.500')->render()`
> no lanzan error; una URL inexistente real (`curl`) devuelve **404** con
> el HTML nuevo (confirmado también con `APP_DEBUG=true`, que es como
> corre el entorno local — Laravel sí usa la vista de error en 404 aunque
> el modo debug esté activo, a diferencia de un 500 genérico). Captura de
> pantalla verificada en navegador. Suite completa: 76 tests en verde salvo
> el fallo preexistente de `ExampleTest`.

`resources/views/errors/` contiene solo `403.blade.php` y `503.blade.php`. Un
404 —el error más frecuente— y un 500 caen a la plantilla por defecto de
Laravel, ajena al diseño de la aplicación.

Con `APP_DEBUG=true` (ver [I8](INFRAESTRUCTURA.md#i8--configuración-de-entorno--severidad-media)),
además, un 500 muestra la traza completa. Corregir `APP_DEBUG` es lo prioritario;
las vistas son el acabado.

**Corrección:** añadir `404.blade.php` y `500.blade.php` extendiendo el mismo
layout que las dos existentes.

---

## Plan sugerido

| Orden | Acción | Hallazgo | Esfuerzo |
|---|---|---|---|
| 1 | Avatares generados localmente (quitar `ui-avatars.com`) | F2 | ~1 h |
| 2 | Descargar Chart.js, SweetAlert2 y Leaflet a `vendors/` | F1 | ~2 h |
| 3 | Unificar Chart.js en una versión y retirar el plugin v2 | F4 | ~1 h |
| 4 | Borrar `console.log`, SCSS y sourcemaps públicos | F6, F7 | ~30 min |
| 5 | Eliminar las 41 librerías vendor sin usar | F5 | ~1 h |
| 6 | `alt` e `aria-label` que faltan | F6 | ~30 min |
| 7 | Vistas de error 404 y 500 | F8 | ~30 min |
| 8 | Módulos `http.js` / `notify.js` / `charts.js` y migración por vista | F3 | Alto, incremental |

Los siete primeros suman menos de un día y cierran los dos hallazgos de
seguridad y privacidad. El octavo es el trabajo de fondo y no tiene final
cerrado: se avanza vista a vista.
