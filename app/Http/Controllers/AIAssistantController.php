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

    /**
     * Procesar pregunta del usuario
     */
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

    /**
     * Registrar feedback del usuario
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'interaction_id' => 'required|exists:ai_interactions,id',
            'was_helpful' => 'required|boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        $interaction = AiInteraction::where('id', $request->interaction_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $interaction->update([
            'was_helpful' => $request->was_helpful,
            'feedback_comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Feedback registrado']);
    }

    /**
     * Obtener historial de conversación de la sesión actual
     */
    public function history(Request $request)
    {
        $sessionId = $request->input('session_id', session()->getId());

        $history = AiInteraction::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subHours(2))
            ->orderBy('created_at', 'asc')
            ->select('id', 'question', 'ai_response', 'response_type', 'was_helpful', 'created_at')
            ->get();

        return response()->json([
            'history' => $history,
            'count' => $history->count(),
        ]);
    }

    /**
     * Limpiar historial de la sesión actual
     */
    public function clearHistory(Request $request)
    {
        $sessionId = $request->input('session_id', session()->getId());

        AiInteraction::where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->delete();

        return response()->json(['message' => 'Historial limpiado']);
    }
}
