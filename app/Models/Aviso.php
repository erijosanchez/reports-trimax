<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends Model
{
    protected $fillable = [
        'titulo',
        'mensaje',
        'roles',
        'user_id',
        'total_destinatarios',
    ];

    protected $casts = [
        'roles' => 'array',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Etiqueta legible de a quién llegó: "Todos" o la lista de roles. */
    public function destinatariosLabel(): string
    {
        if (empty($this->roles)) {
            return 'Todos los usuarios';
        }

        return collect($this->roles)->map(fn($r) => ucfirst($r))->implode(', ');
    }
}
