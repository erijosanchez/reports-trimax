# Productivy Sedes — Nueva columna "Órdenes"

**Fecha:** 2026-07-17
**Autor:** Erick
**Módulo:** Productividad Sedes → Productivy (`/productividad/productivy`)

## Objetivo

Agregar una nueva columna **"Órdenes"** a la tabla de Productivy Sedes. El
porcentaje de esta columna, por cada sede, representa qué porcentaje del total
de órdenes de la sede fueron registradas **antes de las 5:00 pm** durante la
semana que se está visualizando. Esta nueva columna pasa a formar parte del
promedio final (**Productivy**) con el mismo peso que las demás.

## Fuente de datos

- Tabla `ordenes_historico` (la misma que usa el módulo **Órdenes x Usuario**).
- Columnas relevantes: `descripcion_sede`, `nombre_usuario`, `fecha_orden`
  (date), `hora_orden` (string tipo `HH:MM`).
- "Antes de las 5pm" = hora < 17, con el **mismo criterio SQL** que ya usa
  `OrdenesXUsuarioController` (`CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED) < 17`).
  Se mantiene idéntico a propósito para que los números coincidan con lo que el
  usuario ya ve en Órdenes x Usuario.

## Decisiones (confirmadas con el usuario)

1. **Período:** la misma semana que muestra el navegador de Productivy
   (`weekStart` … `weekEnd`, lunes a sábado).
2. **Corte de hora:** antes de las 5pm (hora < 17).
3. **Promedio final:** peso igual → `Productivy = (Dep + CC + Com + Órdenes) / 4`.
4. **Sedes sin órdenes en la semana:** muestran **0%** y ese 0 **sí** cuenta en
   el promedio.

## Agrupación por sede

Cada fila de `ordenes_historico` ya viene etiquetada con `descripcion_sede`, que
es la sede de la orden. Por lo tanto, **agrupar las órdenes por
`descripcion_sede` es equivalente** a "sumar las órdenes de los usuarios de esa
sede" (que fue como lo describió el usuario) y es mucho más robusto que emparejar
por nombre de usuario.

**Normalización de nombres de sede:** la lista de sedes de la tabla Productivy
proviene de `users.sede` (ej. `HUÁNUCO` con tilde), mientras que en
`ordenes_historico.descripcion_sede` aparece sin tilde (`HUANUCO`). Para que
empaten, se normaliza ambos lados: `strtoupper` + eliminación de tildes
(`Á É Í Ó Ú Ü Ñ`). Con eso `HUÁNUCO` = `HUANUCO`.

**Casos borde conocidos:**
- `MONTURAS` (sede con usuarios pero sin órdenes) → 0% (según decisión 4).
- `NAPO EXTRA` (aparece en órdenes pero no es sede de ningún usuario) → sus
  órdenes no se muestran en ninguna fila; Productivy solo lista sedes que tienen
  usuarios. Se deja así intencionalmente.

## Cálculo

Por cada sede, en la semana seleccionada:

```
antes_5pm = SUM(CASE WHEN hora < 17 THEN 1 ELSE 0)   -- mismo SQL que Órdenes x Usuario
total     = COUNT(*)                                  -- todas las órdenes de la sede esa semana
ord_kpi   = total > 0 ? round(antes_5pm / total * 100, 1) : 0
```

Y el promedio final:

```
productivy = round((dep_kpi + cc_kpi + com_kpi + ord_kpi) / 4, 1)
```

## Cambios en el código

### 1. `app/Http/Controllers/ProductivyController.php`

- Nueva consulta única a `ordenes_historico` filtrada por
  `fecha_orden BETWEEN weekStart..weekEnd`, agrupada por `descripcion_sede`,
  devolviendo `antes_5pm` y `total`. Respeta el mismo `sedeFilter` que ya se usa
  para el rol sede (comparando normalizado).
- Construir un mapa `[sedeNormalizada => ['antes' => x, 'total' => y]]`.
- Helper de normalización de sede (uppercase + sin tildes).
- Dentro del `foreach ($sedes as $sede)`: calcular `$ordKpi` con la sede
  normalizada; agregar `'ord_kpi'` y `'ord_total'` a cada `$row`.
- Cambiar el cálculo de `$productivy` a dividir entre 4.
- Nuevo agregado global `$avgOrdenes` para el card de KPI.
- Pasar `avgOrdenes` a la vista.

### 2. `resources/views/productividad/productivy/index.blade.php`

- **Tabla:** nueva columna `<th>Órdenes</th>` entre "Comentarios" y
  "Productivy". Celda con el mismo estilo de badge redondeado que las demás
  columnas KPI (`$kpiBg/$kpiCls/$kpiBorder`), mostrando `ord_kpi %` y como
  subtexto `antes/total` (ej. "12/20 órd").
- **KPI cards del header:** agregar un 5º card "KPI Órdenes" (icono
  `mdi-cart-outline` o similar). Ajustar la grilla para que entren 5 cards de
  forma responsiva (ej. `col-xl` / `col-lg-4` en lugar de `col-lg-3` fijo), sin
  romper el diseño existente.
- Actualizar los textos de ayuda que dicen "(Dep + CC + Com) / 3" → "/ 4" e
  incluir Órdenes.
- Actualizar el `colspan` del estado vacío (de 6 a 7).

### Sin cambios de esquema

No se crean tablas ni columnas nuevas: se reutiliza `ordenes_historico`. No hay
SQL que ejecutar.

## Verificación

- Cargar `/productividad/productivy` en la semana actual y en una semana pasada;
  confirmar que la columna Órdenes aparece con % coherente y que el Productivy
  refleja el promedio de 4 columnas.
- Verificar que `HUÁNUCO` muestra órdenes (prueba de la normalización de tilde).
- Verificar una sede sin órdenes (0% y baja su Productivy).
- `php artisan view:cache` para validar sintaxis Blade, luego `view:clear`.
