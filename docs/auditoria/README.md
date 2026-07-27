# Auditoría técnica — reports-trimax

**Fecha de la auditoría:** 2026-07-24
**Estado del sistema:** operativo, en uso con datos reales

**Empieza por [CORRECCIONES.md](CORRECCIONES.md)** — las 34 correcciones de las
cuatro auditorías priorizadas, indicando de cuál proviene cada una (el frontend
va en tabla aparte). Los documentos por capa tienen el detalle técnico y el
porqué:

| Documento | Alcance | Hallazgos |
|---|---|---|
| [CORRECCIONES.md](CORRECCIONES.md) | **Listado maestro priorizado** | 34 |
| [SEGURIDAD.md](SEGURIDAD.md) | OWASP, dependencias, sesión, subidas, cabeceras | 8 |
| [ARQUITECTURA.md](ARQUITECTURA.md) | Código PHP: `app/`, `routes/` | 8 |
| [INFRAESTRUCTURA.md](INFRAESTRUCTURA.md) | Docker, nginx, MySQL, Redis, Horizon, `.env` | 10 |
| [FRONTEND.md](FRONTEND.md) | Blade, `public/assets/`, terceros, accesibilidad | 8 |

Cada uno tiene su propia skill de Claude Code, que se activa sola cuando se
pide trabajar en esa capa:

| Skill | Ubicación |
|---|---|
| `auditoria-arquitectura` | `.claude/skills/auditoria-arquitectura/SKILL.md` |
| `auditoria-infraestructura` | `.claude/skills/auditoria-infraestructura/SKILL.md` |

También se pueden invocar a mano: `/auditoria-arquitectura`,
`/auditoria-infraestructura`.

---

## Punto de partida

El sistema **funciona y está razonablemente bien construido**. Hay capa de
servicios, Policies, Form Requests, jobs, comandos, middleware propio, Redis
para sesiones y colas, y Horizon operativo. No es un proyecto improvisado y
esta auditoría no debe leerse como si lo fuera.

La deuda encontrada es de un solo tipo, repetido en las dos capas: **decisiones
que deberían estar en un sitio y están copiadas en muchos**. 212 comprobaciones
de permisos a mano en vez de Gates; tres imágenes Docker idénticas en vez de
una; reglas de validación duplicadas entre `store()` y `update()`.

---

## Lo que hay que arreglar primero

Los tres de arriba de la lista maestra. El orden completo y razonado está en
[CORRECCIONES.md](CORRECCIONES.md):

| # | Acción | Capa | Detalle |
|---|---|---|---|
| 1 | Adjuntos de vouchers al disco `local` — hoy nginx los sirve sin autenticación | Seguridad | [S1](SEGURIDAD.md#s1--adjuntos-financieros-servidos-sin-autenticación--severidad-crítica) |
| 2 | `composer update`: 49 vulnerabilidades en 17 paquetes | Seguridad | [S2](SEGURIDAD.md#s2--dependencias-vulnerables--severidad-alta) |
| 3 | Despublicar Redis; phpMyAdmin entra como root sin credenciales | Infra | [I3](INFRAESTRUCTURA.md#i3--servicios-de-datos-expuestos--severidad-alta) |

Y seis ganancias rápidas que suman menos de un día: cabeceras de seguridad,
`CACHE_STORE=redis`, registro de `migrations`, escapar el correo de RRHH,
unificar la imagen de horizon/scheduler y borrar el código muerto.

---

## Una advertencia que aplica a cualquier trabajo en este repo

**No devuelvas MySQL a 3306 ni phpMyAdmin a 8080.** Esos puertos los ocupa el
stack Apollo (`globalmega`) en esta misma máquina. Los remapeos a 3307 y 8090
son deliberados.

> **Actualizado 2026-07-27 — I4 cerrado del todo.** La advertencia de no correr
> `php artisan migrate` ya no aplica: se generaron migraciones `Schema::create`
> guardadas (`hasTable`/`hasColumn`) para las 28 tablas que llegaron con el
> backup sin migración propia, más el fix de las 2 que sí existían pero sin
> guarda. Se validó con una copia descartable de la base (mismo esquema +
> mismas filas de `migrations`) que `migrate` no genera ningún `CREATE`/`ALTER`
> real, y luego se corrió `php artisan migrate --force` sobre la base real:
> 22/22 migraciones en `Ran`, filas intactas (65 usuarios, 874 vouchers, 1830
> facturas — mismos conteos que reportaba esta auditoría). Detalle completo en
> [I4](INFRAESTRUCTURA.md#i4--deriva-de-migraciones--severidad-alta--activo).

---

## Qué queda fuera

- **Pentest activo**: la auditoría de seguridad es revisión estática más
  `composer audit` y verificación de cabeceras. No se explotó nada ni se probó
  la red corporativa.
- **Rendimiento medido**: los hallazgos de A2 e I1 son estructurales, deducidos
  del código y la configuración. No hay perfilado real ni métricas de consultas
  lentas.
- **El módulo de IA** (`TriMaxAIAssistant`, 1663 LOC): quedó fuera del alcance.
