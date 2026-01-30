<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiKnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'ai_knowledge_base';

    protected $fillable = [
        'category',
        'question_pattern',
        'answer_template',
        'confidence_score',
        'usage_count',
        'success_rate',
        'last_used_at',
        'created_from_interactions',
        'is_active'
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];
}
