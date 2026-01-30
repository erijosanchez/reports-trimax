<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\AiInteraction;
use App\Models\AiKnowledgeBase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TriMaxAIAssistant
{
    protected $apiKey;
    protected $apiUrl;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->apiUrl = config('services.groq.api_url');
        $this->model = config('services.groq.model');
    }

    public function ask($question, $context = [])
    {
        // 1. Buscar en conocimiento existente
        $existingKnowledge = $this->searchKnowledgeBase($question);

        // 2. Obtener contexto del sistema
        $systemContext = $this->getSystemContext($context);

        // 3. Decidir si usar conocimiento o IA
        $useAI = true; // Por defecto usar IA para ser más dinámico
        $knowledgeContext = null;

        if ($existingKnowledge) {
            // Solo usar conocimiento directo si es MUY similar (95%+)
            $similarity = $this->calculateSimilarity($question, $existingKnowledge->question_pattern);

            if ($similarity > 0.95) {
                $answer = $existingKnowledge->answer_template;
                $source = 'learned_knowledge';
                $useAI = false;

                // Actualizar stats
                $existingKnowledge->increment('usage_count');
                $existingKnowledge->update(['last_used_at' => now()]);
            } else {
                // Usar como contexto para la IA
                $knowledgeContext = $existingKnowledge->answer_template;
            }
        }

        if ($useAI) {
            // Construir prompt con contexto
            $prompt = $this->buildPrompt($question, $knowledgeContext, $systemContext);

            // Llamar a Groq
            $answer = $this->callGroq($prompt);
            $source = $knowledgeContext ? 'ai_enhanced' : 'ai_generated';
        }

        // Guardar la interacción
        $interaction = $this->saveInteraction($question, $answer, $context);

        return [
            'answer' => $answer,
            'confidence' => $existingKnowledge ? 'high' : 'medium',
            'sources' => $source,
            'interaction_id' => $interaction->id
        ];
    }

    protected function calculateSimilarity($question1, $question2)
    {
        // Normalizar textos
        $q1 = strtolower(trim($question1));
        $q2 = strtolower(trim($question2));

        // Si son exactamente iguales
        if ($q1 === $q2) {
            return 1.0;
        }

        // Calcular similitud por palabras clave
        $words1 = $this->extractKeywords($q1);
        $words2 = $this->extractKeywords($q2);

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    protected function callGroq($prompt)
    {
        try {
            Log::info('Llamando a Groq API...');

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getSystemPrompt()
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['choices'][0]['message']['content'] ?? 'No pude generar una respuesta.';

                Log::info('Respuesta de Groq recibida exitosamente');
                return $answer;
            }

            Log::error('Groq API Error: ' . $response->status() . ' - ' . $response->body());
            return "Lo siento, no pude procesar tu pregunta en este momento. Por favor intenta de nuevo.";
        } catch (\Exception $e) {
            Log::error('Groq Exception: ' . $e->getMessage());
            return "Ocurrió un error al procesar tu consulta. Por favor intenta de nuevo.";
        }
    }

    protected function getSystemPrompt()
    {
        return "Eres un asistente experto del sistema Trimax Peru, un laboratorio óptico con sedes en Lima, Arequipa y Trujillo.

MÓDULOS DEL SISTEMA:
1. **Descuentos Especiales**: Sistema de solicitudes de descuentos con flujo de aprobación por planeamiento comercial
2. **Convenios Comerciales**: Gestión de acuerdos comerciales con clientes corporativos
3. **Consulta de Órdenes**: Integración con Google Sheets para consultar órdenes de trabajo
4. **Dashboard**: Visualización de métricas de ventas con Power BI embebido

ROLES DE USUARIOS:
- **Vendedor**: Crea solicitudes, consulta órdenes, ve sus métricas
- **Planeamiento Comercial**: Aprueba/rechaza descuentos, gestiona convenios, ve reportes
- **Auditoría**: Audita operaciones, ve históricos completos

INSTRUCCIONES IMPORTANTES:
- Responde SIEMPRE en español de forma natural y conversacional
- Sé claro, conciso y útil
- Si te preguntan sobre cómo hacer algo, da pasos específicos y numerados
- Si no tienes información suficiente, pide aclaración
- Si te saludan o preguntan qué puedes hacer, preséntate brevemente
- Adapta tu respuesta al contexto: si es pregunta técnica sé preciso, si es conversacional sé amigable
- NO repitas la misma respuesta una y otra vez
- Mantén respuestas cortas (3-4 párrafos máximo)
- Menciona solo acciones relevantes según el rol del usuario";
    }

    protected function buildPrompt($question, $knowledgeContext, $systemContext)
    {
        $role = $systemContext['user_role'];
        $module = $systemContext['current_module'];
        $actions = implode(', ', $systemContext['available_actions']);

        $prompt = "CONTEXTO DEL USUARIO:
- Rol: {$role}
- Módulo actual: {$module}
- Acciones disponibles: {$actions}";

        if ($knowledgeContext) {
            $prompt .= "\n\nINFORMACIÓN DE CONTEXTO (úsala solo si es relevante):\n{$knowledgeContext}";
        }

        $prompt .= "\n\nPREGUNTA DEL USUARIO:\n{$question}";

        // Detectar tipo de pregunta
        $lowerQuestion = strtolower($question);

        if (str_contains($lowerQuestion, 'hola') || str_contains($lowerQuestion, 'que puedes') || str_contains($lowerQuestion, 'ayudar')) {
            $prompt .= "\n\nNOTA: Esta es una pregunta de introducción/saludo. Responde de forma breve y amigable.";
        } elseif (str_contains($lowerQuestion, 'como') || str_contains($lowerQuestion, 'cómo')) {
            $prompt .= "\n\nNOTA: Esta pregunta pide instrucciones. Da pasos específicos y claros.";
        } elseif (str_contains($lowerQuestion, 'que es') || str_contains($lowerQuestion, 'qué es')) {
            $prompt .= "\n\nNOTA: Esta pregunta pide definición. Explica el concepto de forma clara.";
        }

        return $prompt;
    }

    protected function searchKnowledgeBase($question)
    {
        $keywords = $this->extractKeywords($question);

        if (empty($keywords)) {
            return null;
        }

        // Buscar solo si hay al menos 2 palabras clave que coincidan
        $query = AiKnowledgeBase::where('is_active', true);

        $matchCount = 0;
        foreach ($keywords as $keyword) {
            $hasKeyword = clone $query;
            if ($hasKeyword->where('question_pattern', 'LIKE', "%{$keyword}%")->exists()) {
                $matchCount++;
            }
        }

        // Si no hay suficientes coincidencias, no buscar
        if ($matchCount < 2) {
            return null;
        }

        foreach ($keywords as $keyword) {
            $query->orWhere('question_pattern', 'LIKE', "%{$keyword}%");
        }

        return $query->orderBy('success_rate', 'desc')
            ->orderBy('confidence_score', 'desc')
            ->orderBy('usage_count', 'desc')
            ->first();
    }

    protected function extractKeywords($text)
    {
        // Palabras comunes en español que no aportan valor
        $stopWords = [
            'como',
            'que',
            'para',
            'por',
            'con',
            'el',
            'la',
            'los',
            'las',
            'un',
            'una',
            'de',
            'en',
            'del',
            'al',
            'y',
            'o',
            'pero',
            'si',
            'no',
            'me',
            'te',
            'se',
            'es',
            'son',
            'esta',
            'este',
            'esto',
            'puedo',
            'puede',
            'hacer',
            'hago',
            'haces',
            'cual',
            'cuales',
            'cuando',
            'donde',
            'quien',
            'quienes',
            'hola',
            'sabes'
        ];

        $text = strtolower($text);
        $text = preg_replace('/[¿?¡!,.\-:;]/', ' ', $text); // Remover puntuación
        $words = preg_split('/\s+/', $text);

        $words = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });

        return array_values(array_unique($words));
    }

    protected function getSystemContext($context)
    {
        $user = Auth::user();
        $role = $user->role ?? 'guest';
        $module = $context['module'] ?? 'general';

        return [
            'user_role' => $role,
            'current_module' => $module,
            'module_description' => $this->getModuleDescription($module),
            'available_actions' => $this->getAvailableActions($role, $module)
        ];
    }

    protected function getModuleDescription($module)
    {
        $descriptions = [
            'descuentos' => 'Sistema de descuentos especiales con flujo de aprobación',
            'convenios' => 'Gestión de convenios comerciales con clientes',
            'ordenes' => 'Consulta de órdenes mediante Google Sheets',
            'dashboard' => 'Visualización de métricas de ventas',
            'general' => 'Sistema general de Trimax Peru'
        ];

        return $descriptions[$module] ?? $descriptions['general'];
    }

    protected function getAvailableActions($role, $module)
    {
        $actions = [
            'vendedor' => [
                'descuentos' => ['Crear solicitud', 'Ver estado', 'Consultar histórico'],
                'ordenes' => ['Consultar orden', 'Ver detalles'],
                'convenios' => ['Ver convenios activos'],
                'dashboard' => ['Ver mis ventas'],
                'general' => ['Navegar el sistema', 'Consultar información'],
            ],
            'planeamiento' => [
                'descuentos' => ['Aprobar', 'Rechazar', 'Ver reportes'],
                'convenios' => ['Crear', 'Editar', 'Aprobar', 'Desactivar'],
                'dashboard' => ['Ver todas las métricas', 'Exportar reportes'],
                'general' => ['Gestionar sistema'],
            ],
            'auditor' => [
                'descuentos' => ['Auditar', 'Ver histórico completo'],
                'convenios' => ['Auditar', 'Ver cambios'],
                'dashboard' => ['Análisis completo'],
                'general' => ['Auditar operaciones'],
            ],
        ];

        return $actions[$role][$module] ?? ['Ver información general'];
    }

    protected function saveInteraction($question, $answer, $context)
    {
        return AiInteraction::create([
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'user_role' => Auth::user()->role ?? 'guest',
            'module' => $context['module'] ?? 'general',
            'question' => $question,
            'context' => $context,
            'ai_response' => $answer,
            'response_type' => 'direct_answer',
        ]);
    }
}
