# Checklist de despliegue — reports-trimax

Cubre I8 y S5 de la auditoría técnica (`docs/auditoria/INFRAESTRUCTURA.md`,
`docs/auditoria/SEGURIDAD.md`). Nace de correr esta app **solo** en local
hasta ahora — nada de esto está probado en un despliegue real todavía.

## `.env` de producción — variables que deben cambiar respecto al `.env` local

| Variable | Local (hoy) | Producción |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` — con `true` cada error 500 expone rutas del servidor, versiones y variables de entorno |
| `APP_URL` | `http://localhost` | URL real, con `https://` |
| `LOG_LEVEL` | `debug` | `warning` |
| `SESSION_SECURE_COOKIE` | sin definir (equivale a `false`) | `true` — **solo si el entorno ya sirve por HTTPS**. Con HTTP, `true` rompe la sesión: el navegador no envía cookies `Secure` por HTTP. Ver [S5](auditoria/SEGURIDAD.md#s5--cookie-de-sesión-sin-flag-secure--severidad-media). |
| `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `REDIS_PASSWORD` | valores de esta máquina (`trimax123`, `root`, uno generado 2026-07-27) | generar credenciales propias del entorno — no reusar las de desarrollo. Ver [I2](auditoria/INFRAESTRUCTURA.md#i2--credenciales-literales-en-el-compose--severidad-alta), [I3](auditoria/INFRAESTRUCTURA.md#i3--servicios-de-datos-expuestos--severidad-alta). |

`CACHE_STORE=redis` ya es correcto (I1, resuelto 2026-07-25) — no revertir a
`database`.

## Credencial de Google Sheets

`GOOGLE_SERVICE_ACCOUNT_FILE=storage/app/google/service-account.json` no
existe en este repo a propósito (correctamente fuera de git). Sin ese
archivo, los comandos `trimax:sync-*` fallan al leer Google Sheets.

Se necesita un Service Account de Google Cloud con acceso a Google Sheets
API sobre las hojas configuradas en `GOOGLE_SPREADSHEET_ID`,
`GOOGLE_LEAD_TIME_SPREADSHEET_ID` y `GOOGLE_VENTA_CLIENTES_SPREADSHEET_ID`
(ver `.env`). Solicitar el JSON a quien administra el proyecto de Google
Cloud de Trimax y colocarlo en esa ruta — no generar uno nuevo sin
coordinarlo, porque cambia qué cuenta de servicio tiene acceso a las hojas
ya compartidas.

## Antes de servir tráfico

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Repetir estos tres cada vez que cambien `.env`, rutas o vistas — Laravel no
recarga los caches solo. `php artisan config:clear route:clear view:clear`
para revertir si algo queda inconsistente tras un deploy.

## Migraciones

`php artisan migrate --force` ya es seguro de correr (I4, cerrado del todo
2026-07-27): las 28 tablas que faltaban tienen migración `Schema::create`
guardada (no-op donde ya existen), y se validó contra una copia de la base
real antes de aplicarlo. La advertencia anterior de "no correr migrate" ya
no aplica.

## Docker / puertos

- No republicar `mysql` en `3306` ni `phpmyadmin` en `8080` — los ocupa el
  stack Apollo (`globalmega`) en la misma máquina de desarrollo. Ver
  `docs/auditoria/README.md`. En un servidor de despliegue dedicado esto no
  aplica, pero mantener los mismos puertos (3307/8090) evita divergencias
  entre entornos.
- `phpmyadmin` y `redis-commander` están tras `profiles: ["tools"]` (I3) —
  no arrancan con `docker compose up -d`. Si se necesitan en el servidor de
  despliegue: `docker compose --profile tools up -d`, y cambiar sus
  credenciales/exposición según la política de la red donde corran.
- `redis` ya no publica puerto al host (I3) — solo accesible dentro de
  `trimax-network`.

## Qué falta para que esto sea un despliegue real (fuera de alcance de esta
sesión, ver `docs/auditoria/`)

- I6: el `Dockerfile` no corre `composer install`, depende del bind mount de
  desarrollo — la imagen no es autónoma todavía.
- I7: sin `healthcheck` en `app`/`nginx`/`horizon`/`scheduler`.
- I9/I10: peso muerto en la imagen, sin límites de recursos por contenedor.
