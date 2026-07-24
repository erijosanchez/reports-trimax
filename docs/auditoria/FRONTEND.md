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
