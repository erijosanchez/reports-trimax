<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'is_online',
        'last_activity',
        'login_at',
        'logout_at',
        'session_duration',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_activity' => 'datetime',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'session_duration' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class, 'session_id');
    }

    public function closeSession()
    {
        $duration = $this->logout_at ? 
            $this->logout_at->diffInSeconds($this->login_at) : 
            now()->diffInSeconds($this->login_at);

        $this->update([
            'is_online' => false,
            'logout_at' => now(),
            'session_duration' => $duration,
        ]);
    }

    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_online', true)
            ->where('last_activity', '>=', now()->subMinutes(5));
    }
}
