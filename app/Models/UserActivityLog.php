<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'response_status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'resource_id' => 'integer',
        'response_status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(UserSession::class, 'session_id');
    }

    /**
     * Atajo de registro. Delega en ActivityLogService para mantener una sola
     * fuente de verdad (incluye session_id y manejo de errores con try/catch).
     */
    public static function log($userId, $action, $resourceType = null, $resourceId = null, $description = null, $responseStatus = null)
    {
        return \App\Services\ActivityLogService::log($userId, $action, $resourceType, $resourceId, $description, $responseStatus);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}

