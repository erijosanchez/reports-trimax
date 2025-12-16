<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_name',
        'experience_rating',
        'service_quality_rating',
        'comments',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'experience_rating' => 'integer',
        'service_quality_rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRatingTextAttribute()
    {
        return match($this->experience_rating) {
            4 => 'Muy Feliz',
            3 => 'Feliz',
            2 => 'Insatisfecho',
            1 => 'Muy Insatisfecho',
            default => 'N/A',
        };
    }

    public function getRatingEmojiAttribute()
    {
        return match($this->experience_rating) {
            4 => '😊',
            3 => '🙂',
            2 => '😐',
            1 => '😞',
            default => '❓',
        };
    }

    public function getRatingColorAttribute()
    {
        return match($this->experience_rating) {
            4 => '#4CAF50',
            3 => '##2196F3',
            2 => '#FF9800',
            1 => '#F44336',
            default => '#999',
        };
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('experience_rating', $rating);
    }
}
