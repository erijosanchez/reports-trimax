<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Frontera de datos por sede (ARQUITECTURA.md, A1). Un usuario con rol
 * 'sede' solo debe ver las filas de su propia sede — hoy eso se comprueba a
 * mano en cada controlador (212 sitios). Este scope lo hace el
 * comportamiento por defecto para los modelos donde se aplica: el fallo se
 * vuelve visible (sin resultados / 404) en vez de silencioso.
 *
 * Para saltárselo a propósito (ej. reportes de finanzas que sí ven todas las
 * sedes): Modelo::withoutGlobalScope(SedeScope::class).
 */
class SedeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user && $user->isSede() && $user->sede) {
            $builder->where($model->getTable().'.sede', $user->sede);
        }
    }
}
