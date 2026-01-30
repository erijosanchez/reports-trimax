<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TriMaxAIAssistant;
use App\Models\AiInteraction;

class AIAssistantController extends Controller
{
    protected $assistant;
    
    public function __construct(TriMaxAIAssistant $assistant)
    {
        $this->assistant = $assistant;
    }
    
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);
        
        $result = $this->assistant->ask(
            $request->question,
            $request->context ?? []
        );
        
        return response()->json($result);
    }
    
    public function feedback(Request $request)
    {
        $request->validate([
            'interaction_id' => 'required|exists:ai_interactions,id',
            'was_helpful' => 'required|boolean',
            'comment' => 'nullable|string|max:500',
        ]);
        
        $interaction = AiInteraction::findOrFail($request->interaction_id);
        
        $interaction->update([
            'was_helpful' => $request->was_helpful,
            'feedback_comment' => $request->comment,
        ]);
        
        return response()->json(['message' => 'Feedback registrado con éxito']);
    }
}
