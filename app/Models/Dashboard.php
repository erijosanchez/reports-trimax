<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    protected $fillable = [
        'title',
        'description',
        'embed_url',
        'order_position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_position' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_dashboard_access')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_position', 'asc');
    }
}
