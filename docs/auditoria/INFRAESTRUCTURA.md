# Auditoría de infraestructura — reports-trimax

**Fecha:** 2026-07-24
**Alcance:** `docker-compose.yml`, `Dockerfile`, `docker/`, `.env`, estado de contenedores
**Stack:** Docker Compose · PHP 8.2-FPM · nginx alpine · MySQL 8 · Redis 7 · Horizon

> Documento vivo. Actualízalo cuando se corrija un hallazgo.
> Para código PHP, ver [ARQUITECTURA.md](ARQUITECTURA.md).

---

## Resumen ejecutivo

**El stack local funciona correctamente.** Los 8 contenedores están arriba,
MySQL y Redis reportan `healthy`, Horizon procesa cola con sus tres procesos
vivos, la app responde 200 en `/`, `/login` y `/up`, y no hay errores en el log
desde la importación del backup.

Casi todo lo que sigue es **riesgo al desplegar**, no avería actual. La
excepción es I1, que es un bug real degradando rendimiento ahora mismo.

| # | Hallazgo | Severidad | Estado |
|---|---|---|---|
| I1 | `CACHE_DRIVER` obsoleto: la caché va a MySQL, no a Redis | **Alta** | ✅ Resuelto 2026-07-25 |
| I2 | Credenciales literales en `docker-compose.yml` | **Alta** | Riesgo de despliegue |
| I3 | Redis, phpMyAdmin y Redis Commander expuestos sin protección | **Alta** | Riesgo de despliegue |
| I4 | Deriva entre el registro de migraciones y el esquema real | **Alta** | ✅ Resuelto 2026-07-25 |
| I5 | `horizon` y `scheduler` reconstruyen la imagen: 2.5 GB duplicados | Media | ✅ Resuelto 2026-07-25 |
| I6 | `Dockerfile` sin `composer install`; imagen no autónoma | Media | Activo |
| I7 | Sin `healthcheck` en app, nginx, horizon ni scheduler | Media | Activo |
| I8 | `APP_DEBUG=true` / `APP_ENV=local` | Media | Riesgo de despliegue |
| I9 | `Dockerfile` instala nginx y supervisor que nunca se usan | Baja | Activo |
| I10 | Sin límites de recursos por contenedor | Baja | Riesgo de despliegue |

---

## Mapa del entorno

Este host corre **dos stacks simultáneos**. Apollo (`globalmega`) ya ocupa 3306
y 8080, por lo que reports-trimax está remapeado:

| Servicio | Contenedor | Host | Interno | Nota |
|---|---|---|---|---|
| App (nginx) | `trimax_nginx` | 8000 | 80 | |
| PHP-FPM | `trimax_app` | — | 9000 | Solo red interna |
| MySQL | `trimax_mysql` | **3307** | 3306 | 3306 lo tiene globalmega-mysql |
| Redis | `trimax_redis` | 6379 | 6379 | |
| phpMyAdmin | `trimax_phpmyadmin` | **8090** | 80 | 8080 lo tiene globalmega-tomcat |
| Redis Commander | `trimax_redis_commander` | 8081 | 8081 | |
| Horizon | `trimax_horizon` | — | — | Worker de cola |
| Scheduler | `trimax_scheduler` | — | — | Bucle `schedule:run` cada 60 s |

**Los remapeos 3307 y 8090 no se deben revertir.** La app se conecta
internamente a `mysql:3306` por la red Docker; el puerto del host solo afecta al
acceso desde fuera.

---

## I1 · La caché va a MySQL, no a Redis — Severidad alta · **Activo**

> ✅ **Resuelto 2026-07-25.** `.env`: eliminada la línea `CACHE_DRIVER=redis`
> (obsoleta) y `CACHE_STORE=database` → `CACHE_STORE=redis`. Verificado con
> `php artisan tinker --execute="echo config('cache.default');"` → `redis`.
> No hizo falta `config:clear` para que tomara efecto (no había config cacheada
> en `bootstrap/cache/`), pero se corrió igual por higiene.

### Qué pasa

`.env` declara:

```ini
CACHE_DRIVER=redis     # ← clave de Laravel 10, IGNORADA en Laravel 12
CACHE_STORE=database   # ← la que realmente se lee
```

`config/cache.php:18` es `env('CACHE_STORE', 'database')`. Verificado en runtime:

```
cache.default   = database   ← debería ser redis
session.driver  = redis      ✓
queue.default   = redis      ✓
```

Redis está levantado, sano y correctamente usado para sesiones y colas — pero
**la caché lo ignora y escribe en MySQL**. La tabla `cache` tiene ya 4338 filas.

### Por qué importa

Hay 20 `Cache::remember` en el código, varios sobre consultas de reporting
pesadas. Cada acierto de caché, que debería ser una lectura en memoria, es hoy
un `SELECT` contra la misma base que se intentaba aliviar. Se paga el costo de
operar Redis sin recibir el beneficio principal.

Esto además explica la cascada de errores `Table 'reports_trimax.cache' doesn't
exist` que llenó el log antes de importar el backup: con `CACHE_STORE=redis` la
app no habría dependido de esa tabla para arrancar.

### Corrección

```diff
-CACHE_DRIVER=redis
-CACHE_STORE=database
+CACHE_STORE=redis
```

Después, dentro del contenedor: `php artisan config:clear`.

Revisar también si quedan otras claves de Laravel 10 en el `.env`. La caché de
base de datos puede dejarse configurada como *fallback*, pero no como `default`.

---

## I2 · Credenciales literales en el compose — Severidad alta

### Qué pasa

`docker-compose.yml:50-54` y `:131-134` llevan las contraseñas escritas en un
archivo versionado en git:

```yaml
MYSQL_ROOT_PASSWORD: root
MYSQL_PASSWORD: trimax123
...
PMA_USER: root
PMA_PASSWORD: root
```

El `.env` sí está correctamente en `.gitignore` y no hay secretos en el
historial — el compose es la única fuga.

### Corrección

Mover a `env_file` y dejar el compose sin valores:

```yaml
  mysql:
    env_file: [.env.docker]
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
```

Agregar `.env.docker` a `.gitignore` y versionar un `.env.docker.example`.
En cualquier entorno que no sea esta máquina, `root/root` y `trimax123` deben
cambiarse.

---

## I3 · Servicios de datos expuestos — Severidad alta

### Qué pasa

Tres servicios publican puerto al host **sin ninguna autenticación efectiva**:

| Servicio | Puerto | Problema |
|---|---|---|
| Redis | `6379:6379` | Sin `requirepass`. Contiene sesiones activas y la cola. |
| Redis Commander | `8081:8081` | Sin auth. UI completa de lectura/escritura sobre Redis. |
| phpMyAdmin | `8090:80` | `PMA_USER=root` + `PMA_PASSWORD=root`: **auto-login como root**, sin pedir credenciales. |

En `localhost` con firewall es tolerable. Este mismo compose en un servidor con
IP pública entrega la base de datos completa a cualquiera que abra `:8090` —
sin siquiera un formulario de login de por medio.

Redis sin contraseña, además, permite tomar sesiones activas de usuarios
autenticados (`SESSION_DRIVER=redis`).

### Corrección

**1. No publicar servicios de datos.** Los contenedores se comunican por la red
`trimax-network`; el puerto del host solo hace falta para herramientas externas:

```diff
   redis:
-    ports:
-      - "6379:6379"
+    # Sin publicar. Acceso interno vía "redis:6379".
+    # Para depurar desde el host: docker exec -it trimax_redis redis-cli
```

**2. Contraseña en Redis** aunque no se publique:

```yaml
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
```

y `REDIS_PASSWORD` en el `.env` de la app.

**3. phpMyAdmin y Redis Commander en un perfil opcional**, para que no arranquen
por defecto:

```yaml
  phpmyadmin:
    profiles: [tools]
    environment:
      PMA_HOST: mysql
      # PMA_USER / PMA_PASSWORD eliminados: que pida credenciales
```

Se levantan solo cuando se necesitan: `docker compose --profile tools up -d`.

---

## I4 · Deriva de migraciones — Severidad alta · **Activo**

> ✅ **Resuelto 2026-07-25.** Verificado el estado real antes de insertar (la
> tabla tenía 4 filas, batch máximo 2 — no 3 como hubiera asumido un batch
> fijo). Insertadas las 8 filas faltantes con `batch = 3`. `php artisan
> migrate:status` confirma las 8 como `Ran`, ninguna `Pending`. No se corrió
> `php artisan migrate` en ningún momento. Queda como curiosidad, no como
> pendiente: la fila original `id=4` sigue apuntando a
> `2026_04_27_172251_create_personal_access_tokens_table` (el timestamp del
> backup, que no corresponde a ningún archivo del repo) — es un residuo
> inofensivo, Laravel ya no la necesita porque la fila `id=8` con el nombre de
> archivo real (`2026_04_26_221449_...`) es la que cuenta.

> ✅ **Ampliado y cerrado del todo — 2026-07-27.** Lo de arriba (2026-07-25)
> solo sincronizaba el registro de las 8 migraciones que ya existían en el
> repo. Al escribir tests de feature se descubrió que **28 tablas más no
> tenían ninguna migración de creación** (`ai_interactions`, `descuentos_especiales`,
> `requerimientos_personal`, `ventas`, `users_marketing`, etc. — la lista
> completa en los 8 archivos `2026_07_2*_create_*.php`), y que
> `add_gps_fields_to_user_locations_table` alteraba una tabla que ninguna
> migración creaba. Se generaron migraciones `Schema::create` guardadas
> (`hasTable`) a partir de un volcado real (`mysqldump --no-data`) para las 28,
> más la creación completa de `user_locations`.
>
> Antes de tocar la base real se validó con una **copia descartable**: se
> clonaron el esquema completo y las filas de `migrations` a una base temporal
> en el mismo MySQL, se corrió `php artisan migrate --force` de verdad ahí, y
> un diff de tablas/columnas antes-vs-después dio **0 diferencias**. Con esa
> prueba en mano, se corrió `migrate --force` sobre `reports_trimax`: 22/22
> migraciones en `Ran`, sin ningún `CREATE`/`ALTER` real (todas las nuevas son
> no-op ahí), y los conteos de filas quedaron intactos (65 usuarios, 874
> vouchers, 1830 facturas). El registro de `migrations` ahora refleja el
> esquema real por completo — la advertencia de "no corras migrate" del
> `README.md` ya no aplica.
>
> Nota aparte, no bloqueante: `--pretend` no sirve para verificar migraciones
> guardadas en este proyecto — también simula las `SELECT` de
> `Schema::hasTable()`/`hasColumn()`, así que los guards siempre parecen
> fallar (muestra `CREATE TABLE` aunque la tabla ya exista). Para confirmar de
> verdad hace falta una copia descartable como la de arriba.

### Qué pasa

El esquema proviene de un backup SQL que **no está en el repositorio**. La tabla
`migrations` importada solo registra 4 filas, pero el esquema de 8 migraciones
más ya está presente en la base. Laravel las considera pendientes.

Verificado columna por columna: **todo el esquema existe** — tablas de permisos,
campos GPS en `user_locations`, columnas `revision_*` en `reportes_*` y
`vouchers`, `ruc` en `voucher_facturas`, y `solicitudes_desbloqueo`.

De las 8 pendientes, 6 tienen guardas `Schema::hasTable` / `hasColumn` y serían
no-op. **Dos fallarían**:

- `2025_11_06_152819_create_permission_tables` → `CREATE TABLE` sin guarda
- `2025_11_18_052915_add_gps_fields_to_user_locations_table` → `ADD COLUMN` sin guarda

Detalle adicional: el backup registra
`2026_04_27_172251_create_personal_access_tokens_table`, pero el archivo del
repo es `2026_04_26_221449_...` — mismo destino, distinto timestamp.

### Por qué importa

No afecta a la app funcionando. Es una bomba de relojería para el día en que
alguien corra `php artisan migrate`, o para un pipeline de despliegue que lo
haga automáticamente: fallaría a mitad, dejando el despliegue en estado
indeterminado.

### Corrección

Sincronizar el registro, **no correr las migraciones**:

```sql
INSERT INTO migrations (migration, batch) VALUES
  ('2025_11_06_152819_create_permission_tables', 3),
  ('2025_11_18_052915_add_gps_fields_to_user_locations_table', 3),
  ('2025_12_01_200111_create_acuerdos_comerciales_table', 3),
  ('2026_04_26_221449_create_personal_access_tokens_table', 3),
  ('2026_06_22_120000_add_revision_to_reportes_tables', 3),
  ('2026_07_04_120000_add_revision_and_ruc_to_vouchers', 3),
  ('2026_07_05_120000_create_solicitudes_desbloqueo_table', 3),
  ('2026_07_09_120000_add_kpi_penalidad_archivos_to_reportes_tables', 3);
```

No toca ni un dato de negocio y es reversible con un `DELETE` de esas 8 filas.

A futuro conviene versionar un `database/schema/mysql-schema.sql`
(`php artisan schema:dump`) para que el esquema base deje de depender de un
backup manual.

---

## I5 · Imágenes duplicadas — Severidad media · **Activo**

> ✅ **Resuelto 2026-07-25.** `horizon` y `scheduler` en `docker-compose.yml`
> cambiados de `build: {context: ., dockerfile: Dockerfile}` a
> `image: trimax-app`. Aplicado con `docker compose up -d --no-deps horizon
> scheduler` — ambos recreados y verificados corriendo sobre la imagen
> `trimax-app` (`docker ps` confirma), Horizon arrancó limpio
> ("Horizon started successfully" en logs). Las imágenes viejas
> `reports-trimax-horizon` y `reports-trimax-scheduler` (2.5 GB) quedaron en
> disco sin usarse — se pueden borrar con `docker image prune` cuando
> convenga, no se hizo automáticamente para no tocar nada fuera de lo pedido.
>
> **Hallazgo adicional detectado al tocar este archivo (no estaba en la
> auditoría original):** `docker-compose.yml` declaraba `mysql: 3306:3306` y
> `phpmyadmin: 8080:80`, pero los contenedores realmente en ejecución ya
> corrían en 3307 y 8090 (ver "Mapa del entorno" arriba) — el archivo
> versionado nunca reflejó esos remapeos, probablemente porque se arrancaron
> con otra configuración que después no se volvió a versionar. Un
> `docker compose down && up` desde el archivo tal como estaba habría
> intentado tomar 3306/8080, chocando con `globalmega-mysql` y
> `globalmega-tomcat`. Corregido en el mismo cambio: `docker-compose.yml` ahora
> declara `3307:3306` y `8090:80`. Como el archivo quedó coincidiendo
> exactamente con lo que ya corría, `docker compose up -d --no-deps mysql
> phpmyadmin` no necesitó recrear ningún contenedor — cero downtime,
> verificado con `docker ps` (ambos "Up 25 hours", sin reinicio).

### Qué pasa

`horizon` y `scheduler` declaran su propio bloque `build:` en lugar de reutilizar
la imagen ya construida:

```
trimax-app:latest                921 MB
reports-trimax-horizon:latest   1.28 GB   ← misma imagen
reports-trimax-scheduler:latest 1.28 GB   ← misma imagen
```

**~2.5 GB de disco desperdiciados** y, peor, tres imágenes que pueden divergir:
un `docker compose build app` actualiza el código de la app pero deja a Horizon
corriendo la versión anterior.

### Corrección

```diff
   horizon:
-    build:
-      context: .
-      dockerfile: Dockerfile
+    image: trimax-app
+    depends_on:
+      app:
+        condition: service_started
```

Idéntico para `scheduler`. Una sola imagen, una sola versión del código.

---

## I6 · La imagen no es autónoma — Severidad media

### Qué pasa

El `Dockerfile` copia el código pero **nunca ejecuta `composer install`** (0
ocurrencias) ni ningún build de frontend. `vendor/` llega únicamente por el bind
mount `./:/var/www` y por el `composer install` que se corre a mano.

Además `COPY . /var/www` va antes de cualquier instalación de dependencias, así
que no hay cacheo de capas: cualquier cambio en un `.blade.php` invalida todo lo
que venga después.

### Por qué importa

La imagen solo funciona con el código montado desde el host. No se puede
desplegar en otro servidor, ni publicar en un registry, ni hacer rollback a una
versión anterior. Es una imagen de desarrollo usada como si fuera de producción.

### Corrección

Separar dependencias de código para aprovechar la caché de capas:

```dockerfile
# Dependencias primero: solo se reinstalan si cambia composer.json/lock
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Luego el código
COPY --chown=$user:$user . /var/www
RUN composer dump-autoload --optimize
```

Para desarrollo se sigue montando el volumen y sobreescribiendo; para
despliegue, la imagen ya es autosuficiente. Si se adopta la Opción B de
frontend (ver ARQUITECTURA.md, A6), aquí es donde entra
`npm ci && npm run build` en una etapa `node` separada.

---

## I7 · Healthchecks incompletos — Severidad media

`mysql` y `redis` tienen `healthcheck`; `app`, `nginx`, `horizon` y `scheduler`
no. Y los `depends_on` son de arranque, no de disponibilidad:

```yaml
    depends_on:
      - mysql      # espera a que arranque, no a que esté listo
      - redis
```

Por eso en el arranque en frío Horizon intentó consultar la base antes de que
MySQL aceptara conexiones y murió con `QueryException`.

### Corrección

```yaml
  app:
    healthcheck:
      test: ["CMD-SHELL", "php-fpm-healthcheck || exit 1"]
      interval: 30s
      timeout: 5s
      retries: 3
    depends_on:
      mysql: { condition: service_healthy }
      redis: { condition: service_healthy }
```

Para `nginx`, un `curl -f http://localhost/up`. Con `condition: service_healthy`
el arranque en frío deja de ser una carrera.

---

## I8 · Configuración de entorno — Severidad media

```ini
APP_ENV=local
APP_DEBUG=true      # ← expone stack traces con credenciales en pantalla
APP_URL=http://localhost
LOG_LEVEL=debug
```

Correcto para esta máquina. **En cualquier despliegue real**, `APP_DEBUG=true`
publica rutas del servidor, versiones y variables de entorno en cada error 500.

Relacionado: `storage/app/google/service-account.json` no existe, por lo que los
comandos `trimax:sync-*` fallan al leer Google Sheets. En local es esperable —
es una credencial que correctamente no está en el repo. Documentar de dónde se
obtiene.

### Corrección

Checklist mínimo de despliegue: `APP_ENV=production`, `APP_DEBUG=false`,
`LOG_LEVEL=warning`, `APP_URL` real, y `php artisan config:cache route:cache
view:cache`.

---

## I9 · Peso muerto en la imagen — Severidad baja

El `Dockerfile` instala `nginx` y `supervisor` dentro de la imagen de PHP-FPM,
pero **ninguno se usa**: nginx corre en su propio contenedor
(`nginx:alpine`) y no hay configuración de supervisor. El `CMD` es `php-fpm`
a secas.

Son ~80 MB y superficie de ataque a cambio de nada. Eliminarlos de la línea
`apt-get install`. El directorio `/var/log/php-fpm` que se crea tampoco recibe
nada, ya que PHP-FPM escribe a stdout.

---

## I10 · Sin límites de recursos — Severidad baja

Ningún servicio declara `deploy.resources.limits`. Con `memory_limit = 1024M`
en PHP (ver ARQUITECTURA.md, A2) y varios workers de Horizon, una consulta sin
paginar puede consumir la RAM del host — que además comparte con el stack
Apollo.

```yaml
    deploy:
      resources:
        limits: { memory: 1G, cpus: '1.0' }
```

Contención, no solución: la causa real es A2.

---

## Plan sugerido

| Orden | Acción | Hallazgo | Riesgo | Esfuerzo |
|---|---|---|---|---|
| 1 | `CACHE_STORE=redis` + `config:clear` | I1 | Bajo | Minutos |
| 2 | Sincronizar registro de `migrations` | I4 | Bajo | Minutos |
| 3 | `horizon`/`scheduler` → `image: trimax-app` | I5 | Bajo | Minutos |
| 4 | Quitar nginx y supervisor del `Dockerfile` | I9 | Bajo | Minutos |
| 5 | Despublicar Redis; perfil `tools` para las UIs | I3 | Medio | ~1 h |
| 6 | Credenciales a `env_file` | I2 | Medio | ~1 h |
| 7 | Healthchecks + `condition: service_healthy` | I7 | Bajo | ~1 h |
| 8 | `composer install` y capas en el `Dockerfile` | I6 | Medio | ~2 h |
| 9 | Checklist de despliegue (`APP_DEBUG`, caches) | I8 | — | ~1 h |

Los cuatro primeros son de minutos y sin riesgo apreciable. Del 5 al 8 cambian
cómo arranca el stack: conviene aplicarlos juntos y en una ventana en la que se
pueda validar el arranque completo.
