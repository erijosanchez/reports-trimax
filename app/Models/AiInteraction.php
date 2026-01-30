<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'user_role',
        'module',
        'question',
        'context',
        'ai_response',
        'response_type',
        'was_helpful',
        'feedback_comment',
        'action_taken'
    ];

    protected $casts = [
        'context' => 'array',
        'was_helpful' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
