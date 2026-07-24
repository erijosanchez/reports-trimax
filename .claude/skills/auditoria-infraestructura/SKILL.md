---
name: auditoria-infraestructura
description: Audita y corrige la infraestructura de reports-trimax - Docker Compose, Dockerfile, nginx, PHP-FPM, MySQL, Redis, Horizon, scheduler y configuración .env. Úsala cuando se pida revisar infraestructura, levantar o diagnosticar el stack, exponer o cerrar puertos, revisar contenedores caídos, preparar despliegue, o cuando la app falle por causas de entorno y no de código. Palabras clave que la disparan - "infraestructura", "docker", "contenedor", "despliegue", "deploy", "levantar el stack", "no arranca", "500", "redis", "mysql", "horizon", "nginx", "puertos", "env".
---

# Auditoría de infraestructura — reports-trimax

Aplica a `docker-compose.yml`, `Dockerfile`, `docker/`, `.env` y el estado de
los contenedores. Para código PHP, usa `auditoria-arquitectura`.

## Contexto del host — léelo antes de tocar puertos

Esta máquina corre **dos stacks a la vez**. El proyecto Apollo (`globalmega`)
ya ocupa 3306 (MySQL) y 8080 (Tomcat). Por eso `reports-trimax` está remapeado:

| Servicio | Host | Contenedor | Nota |
|---|---|---|---|
| App (nginx) | 8000 | 80 | |
| MySQL | **3307** | 3306 | 3306 lo tiene globalmega-mysql |
| phpMyAdmin | **8090** | 80 | 8080 lo tiene globalmega-tomcat |
| Redis | 6379 | 6379 | |
| Redis Commander | 8081 | 8081 | |

**Nunca devuelvas MySQL a 3306 ni phpMyAdmin a 8080.** Rompe el otro stack.
La app se conecta internamente por la red Docker a `mysql:3306` — el remapeo
solo afecta el acceso desde el host.

## Reglas de operación

- **No corras builds, `up`, `down`, `restart` ni deploys sin permiso explícito.**
  El usuario gestiona la ejecución. Diagnosticar (leer logs, `ps`, consultas
  SELECT) sí es libre.
- **Nunca `docker compose down -v`.** El volumen `mysql_data` contiene el backup
  restaurado por el usuario y no está en el repo.
- **Nunca `php artisan migrate` a ciegas** — ver "Deriva de migraciones" abajo.
- No hay PHP ni Composer en el host. Todo comando artisan va por contenedor:
  `docker compose run --rm --no-deps app php artisan <cmd>`

## Diagnóstico rápido

```bash
docker compose ps
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/up   # health sin BD
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/login # health con BD
```

`/up` responde 200 aunque MySQL esté caído; `/login` no. Si `/up` va y `/login`
da 500, el problema es base de datos, no la app.

Errores recientes:

```bash
grep -oE 'local\.(ERROR|CRITICAL): .{0,110}' storage/logs/laravel.log | sort | uniq -c | sort -rn | head
docker compose logs --since 15m horizon nginx
```

`storage/logs/laravel.log` es acumulativo y grande — filtra por hora antes de
concluir que un error es actual. Un error de hace 3 horas ya corregido sigue
apareciendo en el `tail`.

Procesos dentro de un contenedor (no hay `ps` en las imágenes):

```bash
docker exec trimax_horizon sh -c 'for p in $(ls /proc | grep -E "^[0-9]+$"); do tr "\0" " " < /proc/$p/cmdline 2>/dev/null; echo; done'
```

Horizon sano = tres procesos: `horizon`, `horizon:supervisor`, `horizon:work`.
`artisan horizon:status` lee estado de Redis y puede mentir si el proceso murió.

## Puntos de fallo conocidos

### Deriva de migraciones (crítico)

El esquema viene de un backup SQL que **no está en el repo**. La tabla
`migrations` del backup solo registra 4 filas, pero el esquema de las 8
migraciones restantes ya está aplicado. `php artisan migrate` intentaría
re-crearlo. Dos migraciones no tienen guardas y fallarían:

- `2025_11_06_152819_create_permission_tables`
- `2025_11_18_052915_add_gps_fields_to_user_locations_table`

Antes de sugerir `migrate`, verifica columna por columna si el cambio ya está:

```bash
docker exec trimax_mysql mysql -uroot -proot reports_trimax -e "SHOW COLUMNS FROM <tabla>;"
```

La corrección es sincronizar el registro con `INSERT` en `migrations`, no correr
las migraciones.

### Claves .env obsoletas de Laravel 10

Laravel 12 lee **`CACHE_STORE`**, no `CACHE_DRIVER`. Verifica siempre el valor
efectivo en runtime, no lo que dice el `.env`:

```bash
docker compose run --rm --no-deps app php artisan tinker --execute="echo config('cache.default').' | '.config('session.driver').' | '.config('queue.default');"
```

### Symlink de storage

`public/storage -> /var/www/storage/app/public` apunta a la ruta **interna del
contenedor**. Desde el host se ve roto; no lo está. No lo "arregles".

### Directorios de storage ausentes

`storage/framework/views` no se versiona. Si falta, la app cae con "Please
provide a valid cache path". Si el error aparece, créalo — no reinstales nada.

### Credencial de Google ausente

`storage/app/google/service-account.json` no está en el repo. Los comandos
`trimax:sync-*` fallan sin ella. En local es esperado; no es una regresión.

## Checklist de auditoría

Al revisar `docker-compose.yml` y `Dockerfile`, verifica:

- [ ] Credenciales fuera del compose (`env_file`), no literales
- [ ] Servicios de datos sin publicar al host, o con contraseña
- [ ] Herramientas de administración (phpMyAdmin, Redis Commander) sin auto-login
      de root y no expuestas fuera de local
- [ ] `healthcheck` en app, nginx y horizon — no solo en mysql/redis
- [ ] `depends_on` con `condition: service_healthy`
- [ ] `horizon` y `scheduler` reutilizando `image: trimax-app`, no un `build:`
      propio (cada `build:` duplicado cuesta ~1.3 GB de disco)
- [ ] `Dockerfile` sin paquetes que no se usan
- [ ] `composer install` dentro de la imagen si se busca una imagen autónoma
- [ ] `APP_DEBUG=false` y `APP_ENV=production` en cualquier entorno no local

## Formato del reporte

Separa **"roto ahora"** de **"riesgo al desplegar"**. La mayor parte de lo que
se encuentra aquí es lo segundo: el stack local funciona, pero el mismo compose
llevado a un servidor expondría datos. Decirlo como si todo estuviera en llamas
hace que el reporte se ignore.

Da el comando exacto de corrección y espera aprobación antes de aplicarlo.

El diagnóstico vigente está en `docs/auditoria/INFRAESTRUCTURA.md` — léelo antes
de auditar y actualízalo cuando algo se corrija.
