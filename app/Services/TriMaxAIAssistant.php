<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\AiInteraction;
use App\Models\AiKnowledgeBase;
use App\Models\AcuerdoComercial;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;

class TriMaxAIAssistant
{
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $googleSheets;

    // Máximo de mensajes de historial a enviar
    protected int $maxHistoryMessages = 10;

    public function __construct(GoogleSheetsService $googleSheets)
    {
        // Usa Gemini si tiene API key configurada, sino cae a Groq
        if (config('services.gemini.api_key')) {
            $this->apiKey = config('services.gemini.api_key');
            $this->apiUrl = config('services.gemini.api_url');
            $this->model = config('services.gemini.model');
        } else {
            $this->apiKey = config('services.groq.api_key');
            $this->apiUrl = config('services.groq.api_url');
            $this->model = config('services.groq.model');
        }
        $this->googleSheets = $googleSheets;
    }

    // ================================================================
    // MÉTODO PRINCIPAL - CON HISTORIAL DE CONVERSACIÓN
    // ================================================================

    public function ask($question, $context = [])
    {
        $user = Auth::user();
        $systemContext = $this->getSystemContext($context);
        $sessionId = $context['sessionId'] ?? session()->getId();

        // Construir mensajes con historial
        $messages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt($user, $systemContext)],
        ];

        // Agregar historial de conversación de la sesión actual
        $history = $this->getConversationHistory($user->id, $sessionId);
        foreach ($history as $msg) {
            $messages[] = ['role' => 'user', 'content' => $msg->question];
            $messages[] = ['role' => 'assistant', 'content' => $msg->ai_response];
        }

        // Mensaje actual del usuario
        $messages[] = ['role' => 'user', 'content' => $question];

        // Primera llamada con tools
        $response = $this->callAIWithTools($messages, $user);

        if (!$response) {
            return $this->errorResponse($question, $context);
        }

        // Loop de function calling (máx 5 iteraciones)
        $toolsUsed = [];
        $maxIterations = 5;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $choice = $response['choices'][0] ?? null;
            if (!$choice) break;

            $message = $choice['message'] ?? [];
            $toolCalls = $message['tool_calls'] ?? [];

            if (!empty($toolCalls)) {
                $messages[] = $message;

                foreach ($toolCalls as $toolCall) {
                    $functionName = $toolCall['function']['name'] ?? '';
                    $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                    Log::info("🔧 Tool call: {$functionName}", $arguments);

                    $result = $this->executeFunction($functionName, $arguments, $user);
                    $toolsUsed[] = $functionName;

                    Log::info("📊 Resultado de {$functionName}: " . substr(json_encode($result), 0, 500));

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }

                $response = $this->callAIWithTools($messages, $user);
                if (!$response) break;

                $iteration++;
            } else {
                break;
            }
        }

        // Extraer respuesta final
        $answer = $response['choices'][0]['message']['content'] ?? 'No pude generar una respuesta.';

        // Determinar tipo de respuesta
        $responseType = !empty($toolsUsed) ? 'data_query' : 'direct_answer';

        // Guardar interacción
        $interaction = $this->saveInteraction($question, $answer, $context, $responseType, $toolsUsed);

        // Aprendizaje automático en background
        $this->learnFromInteraction($question, $answer, $responseType);

        return [
            'answer' => $answer,
            'confidence' => !empty($toolsUsed) ? 'high' : 'medium',
            'sources' => !empty($toolsUsed) ? 'real_data' : 'ai_knowledge',
            'tools_used' => $toolsUsed,
            'interaction_id' => $interaction->id,
        ];
    }

    // ================================================================
    // HISTORIAL DE CONVERSACIÓN
    // ================================================================

    protected function getConversationHistory(int $userId, string $sessionId): \Illuminate\Support\Collection
    {
        return AiInteraction::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subHours(2)) // Solo últimas 2 horas
            ->orderBy('created_at', 'desc')
            ->limit($this->maxHistoryMessages)
            ->get()
            ->reverse(); // Orden cronológico
    }

    // ================================================================
    // SYSTEM PROMPT MEJORADO
    // ================================================================

    protected function getSystemPrompt($user, $systemContext)
    {
        $userName = $user->name ?? 'Usuario';
        $userRole = $systemContext['user_role'] ?? 'guest';
        $userEmail = $user->email ?? '';
        $userSede = $user->sede ? strtoupper($user->sede) : null;
        $module = $systemContext['current_module'] ?? 'general';
        $fechaHoy = Carbon::now()->format('d/m/Y');
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        // Construir lista de permisos reales del usuario
        $permisos = [];
        if ($user->puedeVerVentasConsolidadas()) $permisos[] = 'Ver ventas consolidadas (todas las sedes)';
        if ($userSede && !$user->puedeVerVentasConsolidadas()) $permisos[] = "Ver ventas de su sede ({$userSede})";
        if ($user->puedeVerDescuentosEspeciales()) $permisos[] = 'Ver descuentos especiales';
        if ($user->puedeVerConsultarOrden()) $permisos[] = 'Consultar órdenes';
        if ($user->puedeVerAcuerdosComerciales()) $permisos[] = 'Ver acuerdos comerciales';
        if ($user->puedeVerLeadTime()) $permisos[] = 'Ver lead time';
        if ($user->puedeVerVentaClientes()) $permisos[] = 'Ver ventas por cliente';
        if ($user->puedeVerOrdenesXSede()) $permisos[] = 'Ver órdenes por sede';
        if ($user->puedeVerPendienteEntregaMontura()) $permisos[] = 'Ver pendiente entrega montura';
        if ($user->puedeCrearRequerimientos()) $permisos[] = 'Crear requerimientos de personal';
        if ($user->puedeVerTodosLosRequerimientos()) $permisos[] = 'Ver todos los requerimientos de personal';
        if ($user->puedeGestionarRequerimientos()) $permisos[] = 'Gestionar requerimientos de personal';
        if ($user->isSuperAdmin() || $user->isAdmin()) $permisos[] = 'Administración del sistema';

        $permisosStr = empty($permisos) ? 'Solo información general' : implode("\n  - ", $permisos);

        $sedeInfo = $userSede
            ? "- Sede asignada: {$userSede} (SOLO puedes ver datos de esta sede salvo que tengas permiso de consolidado)"
            : '- Sede: Sin restricción de sede';

        $actions = implode(', ', $systemContext['available_actions'] ?? []);

        return "Eres el Asistente Trimax, asistente inteligente del CRM de Trimax Perú (Laboratorio Óptico).
Trimax tiene más de 30 sedes a nivel nacional: Lima (Lince, SJM, SJL, Ate, Los Olivos, Puente Piedra, Comas, Cailloma, Napo, Surquillo, Villa El Salvador, Call Center), Arequipa, Trujillo, Chiclayo, Piura, Cusco, Huancayo, Ica, Iquitos, Cajamarca, Chimbote, Huánuco, Huaraz, Pucallpa, Tacna, Ayacucho, Juliaca, Tarapoto y más.

══════════════════════════════
USUARIO LOGUEADO:
══════════════════════════════
- Nombre: {$userName}
- Email: {$userEmail}
- Rol: {$userRole}
{$sedeInfo}
- Módulo actual: {$module}
- Acciones disponibles: {$actions}
- Fecha: {$fechaHoy} | Periodo: {$mesActual} {$anioActual}

══════════════════════════════
PERMISOS REALES DEL USUARIO:
══════════════════════════════
  - {$permisosStr}

IMPORTANTE: Solo puedes mostrar información que corresponda a los permisos listados arriba.
Si el usuario pide algo fuera de sus permisos, indícalo con cortesía.

══════════════════════════════
REGLAS CRÍTICAS:
══════════════════════════════
1. NUNCA inventes datos. SIEMPRE usa las funciones para consultar datos reales.
2. Si el usuario no tiene permiso para algo, dilo: 'No tienes acceso a esa información con tu rol actual.'
3. Si el usuario tiene sede asignada y NO tiene permiso de consolidado, las funciones ya filtran por su sede automáticamente.
4. Responde en español, natural y conciso. Usa emojis moderadamente.
5. Dirígete al usuario como {$userName}.
6. Respuestas cortas: máximo 3-4 párrafos, salvo que pidan un reporte detallado.
7. NUNCA digas que una sede no existe sin consultar primero.
8. Cuando muestres datos numéricos, formatea con separadores de miles y 2 decimales.
9. Si piden comparaciones entre sedes/periodos, llama la función múltiples veces.
10. Para ventas granulares (por marca, tipo cliente, zona) usa obtener_ventas_bd. Para cuotas y cumplimiento usa obtener_ventas_sede.
11. Tienes historial de conversación de esta sesión — úsalo para mantener el contexto.

MÓDULOS DEL SISTEMA:
- Descuentos Especiales: Solicitudes con flujo de aprobación (Pendiente → Aprobado/Rechazado)
- Acuerdos Comerciales: Convenios corporativos con validación y aprobación
- Consulta de Órdenes: Desde Google Sheets (estado, ubicación, importe)
- Dashboard de Ventas: Métricas por sede (cuota, venta, cumplimiento)
- Encuestas de Satisfacción: Calificaciones de clientes (1-5 estrellas)
- Requerimientos de Personal: Solicitudes de contratación con flujo RRHH";
    }

    // ================================================================
    // TOOLS - AHORA CON MÁS FUNCIONES
    // ================================================================

    protected function getTools($user = null): array
    {
        // Herramientas disponibles para todos los autenticados
        $tools = [
            $this->toolInfoUsuario(),
            $this->toolResumenGeneral(),
        ];

        if (!$user) return $tools;

        // Ventas: consolidado o solo su sede
        if ($user->puedeVerVentasConsolidadas() || $user->isSede()) {
            $tools[] = $this->toolVentasSede();
            $tools[] = $this->toolVentasBD();
            $tools[] = $this->toolTopSedes();
        }

        // Órdenes
        if ($user->puedeVerConsultarOrden() || $user->puedeVerOrdenesXSede()) {
            $tools[] = $this->toolBuscarOrden();
            $tools[] = $this->toolEstadisticasOrdenes();
        }

        // Acuerdos comerciales
        if ($user->puedeVerAcuerdosComerciales()) {
            $tools[] = $this->toolAcuerdosComerciales();
        }

        // Descuentos especiales
        if ($user->puedeVerDescuentosEspeciales()) {
            $tools[] = $this->toolDescuentosEspeciales();
        }

        // Encuestas: admins, marketing y superadmin
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isMarketing()) {
            $tools[] = $this->toolEncuestasSatisfaccion();
        }

        // Requerimientos de personal: RRHH, SuperAdmin, y quien tenga permiso
        if ($user->puedeCrearRequerimientos() || $user->puedeVerTodosLosRequerimientos()) {
            $tools[] = $this->toolRequerimientos();
        }

        return $tools;
    }

    protected function toolVentasSede(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_ventas_sede',
                'description' => 'Obtiene ventas mensuales de las 30+ sedes de Trimax desde Google Sheets (cuotas, cumplimiento, proyección). SIEMPRE usar cuando pregunten por ventas de cualquier sede.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Nombre de la sede (IQUITOS, LINCE, AREQUIPA, etc.) o "todas" para ver todas',
                        ],
                        'mes' => [
                            'type' => 'string',
                            'description' => 'Mes en español (Enero, Febrero, etc.). Por defecto: mes actual.',
                        ],
                        'anio' => [
                            'type' => 'integer',
                            'description' => 'Año a consultar. Por defecto: año actual.',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolVentasBD(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_ventas_bd',
                'description' => 'Consulta ventas detalladas desde la base de datos MySQL (tabla ventas). Útil para análisis por marca, tipo de cliente, producto, motorizado, zona. Más granular que Google Sheets.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Filtrar por sede',
                        ],
                        'mes' => [
                            'type' => 'integer',
                            'description' => 'Número del mes (1-12). Por defecto: mes actual.',
                        ],
                        'anio' => [
                            'type' => 'integer',
                            'description' => 'Año. Por defecto: año actual.',
                        ],
                        'marca' => [
                            'type' => 'string',
                            'description' => 'Filtrar por marca',
                        ],
                        'tipo_cliente' => [
                            'type' => 'string',
                            'description' => 'Filtrar por tipo de cliente',
                        ],
                        'agrupar_por' => [
                            'type' => 'string',
                            'description' => 'Agrupar resultados por: sede, marca, tipo_cliente, zona, motorizado, mes',
                            'enum' => ['sede', 'marca', 'tipo_cliente', 'zona', 'motorizado', 'mes'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolEstadisticasOrdenes(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_estadisticas_ordenes',
                'description' => 'Obtiene estadísticas de órdenes de trabajo: total, en tránsito, en sede, facturados, importes pendientes.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Filtrar por sede específica (opcional)',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolAcuerdosComerciales(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_acuerdos_comerciales',
                'description' => 'Obtiene acuerdos/convenios comerciales con sus estados (Solicitado, Vigente, Vencido, Rechazado).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'estado' => [
                            'type' => 'string',
                            'description' => 'Filtrar por estado: Solicitado, Vigente, Vencido, Rechazado, Deshabilitado',
                        ],
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Filtrar por sede',
                        ],
                        'buscar' => [
                            'type' => 'string',
                            'description' => 'Buscar por razón social, RUC o número de acuerdo',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolDescuentosEspeciales(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_descuentos_especiales',
                'description' => 'Obtiene solicitudes de descuentos especiales con sus estados de aprobación. Incluye tipos: ANULACION, CORTESIA, DESCUENTO ADICIONAL, DESCUENTO TOTAL, OTROS.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'estado_aplicado' => [
                            'type' => 'string',
                            'description' => 'Estado de aplicación: Pendiente, Aprobado, Rechazado',
                        ],
                        'estado_aprobado' => [
                            'type' => 'string',
                            'description' => 'Estado de aprobación: Pendiente, Aprobado, Rechazado',
                        ],
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Filtrar por sede',
                        ],
                        'tipo' => [
                            'type' => 'string',
                            'description' => 'Tipo de descuento: ANULACION, CORTESIA, DESCUENTO ADICIONAL, DESCUENTO TOTAL, OTROS',
                        ],
                        'buscar' => [
                            'type' => 'string',
                            'description' => 'Buscar por razón social, RUC, número de descuento u orden',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolBuscarOrden(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'buscar_orden',
                'description' => 'Busca una orden específica por número de orden, nombre de cliente o cualquier texto en Google Sheets.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'termino_busqueda' => [
                            'type' => 'string',
                            'description' => 'Texto a buscar: número de orden, nombre, etc.',
                        ],
                    ],
                    'required' => ['termino_busqueda'],
                ],
            ],
        ];
    }

    protected function toolInfoUsuario(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_info_usuario',
                'description' => 'Obtiene información detallada del usuario actual logueado.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ],
        ];
    }

    protected function toolResumenGeneral(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_resumen_general',
                'description' => 'Obtiene un resumen general del sistema: ventas del mes, órdenes pendientes, acuerdos activos, descuentos pendientes.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => new \stdClass(),
                ],
            ],
        ];
    }

    protected function toolEncuestasSatisfaccion(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_encuestas_satisfaccion',
                'description' => 'Obtiene resultados de encuestas de satisfacción de clientes con calificaciones y comentarios.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'dias' => [
                            'type' => 'integer',
                            'description' => 'Últimos N días a consultar. Por defecto: 30.',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolTopSedes(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_ranking_sedes',
                'description' => 'Obtiene el ranking de sedes por ventas, cumplimiento de cuota u órdenes. Útil para comparar rendimiento entre sedes.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'criterio' => [
                            'type' => 'string',
                            'description' => 'Criterio de ranking: ventas, cumplimiento, ordenes',
                            'enum' => ['ventas', 'cumplimiento', 'ordenes'],
                        ],
                        'top' => [
                            'type' => 'integer',
                            'description' => 'Cantidad de sedes a mostrar. Por defecto: 10.',
                        ],
                        'orden' => [
                            'type' => 'string',
                            'description' => 'Orden: mejores o peores',
                            'enum' => ['mejores', 'peores'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function toolRequerimientos(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'obtener_requerimientos_personal',
                'description' => 'Obtiene requerimientos de contratación de personal con sus estados y KPIs. Solo disponible para RRHH y administradores.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'estado' => [
                            'type' => 'string',
                            'description' => 'Filtrar por estado: Pendiente, En Proceso, Contratado, Cancelado',
                            'enum' => ['Pendiente', 'En Proceso', 'Contratado', 'Cancelado'],
                        ],
                        'sede' => [
                            'type' => 'string',
                            'description' => 'Filtrar por sede',
                        ],
                        'gerencia' => [
                            'type' => 'string',
                            'description' => 'Filtrar por gerencia o área',
                        ],
                        'sla_vencido' => [
                            'type' => 'boolean',
                            'description' => 'true para ver solo requerimientos con SLA vencido (>45 días)',
                        ],
                    ],
                ],
            ],
        ];
    }

    // ================================================================
    // EJECUTOR DE FUNCIONES
    // ================================================================

    protected function executeFunction(string $functionName, array $args, $user): array
    {
        try {
            return match ($functionName) {
                'obtener_ventas_sede' => $this->fnObtenerVentasSede($args, $user),
                'obtener_ventas_bd' => $this->fnObtenerVentasBD($args, $user),
                'obtener_estadisticas_ordenes' => $this->fnObtenerEstadisticasOrdenes($args, $user),
                'obtener_acuerdos_comerciales' => $this->fnObtenerAcuerdos($args, $user),
                'obtener_descuentos_especiales' => $this->fnObtenerDescuentos($args, $user),
                'buscar_orden' => $this->fnBuscarOrden($args, $user),
                'obtener_info_usuario' => $this->fnObtenerInfoUsuario($user),
                'obtener_resumen_general' => $this->fnObtenerResumenGeneral($user),
                'obtener_encuestas_satisfaccion' => $this->fnObtenerEncuestas($args, $user),
                'obtener_ranking_sedes' => $this->fnObtenerRankingSedes($args, $user),
                'obtener_requerimientos_personal' => $this->fnObtenerRequerimientos($args, $user),
                default => ['error' => "Función '{$functionName}' no disponible"],
            };
        } catch (\Exception $e) {
            Log::error("Error en función {$functionName}: " . $e->getMessage());
            return ['error' => "Error al consultar datos: " . $e->getMessage()];
        }
    }

    // ================================================================
    // FUNCIONES DE DATOS REALES
    // ================================================================

    /**
     * 📊 Ventas por sede desde Google Sheets
     */
    protected function fnObtenerVentasSede(array $args, $user): array
    {
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        $mes = $args['mes'] ?? $mesActual;
        $anio = $args['anio'] ?? $anioActual;
        $sedeFilter = isset($args['sede']) ? strtoupper(trim($args['sede'])) : null;

        // Si el usuario es de sede y NO tiene permiso de consolidado, forzar su sede
        if ($user->isSede() && !$user->puedeVerVentasConsolidadas() && $user->sede) {
            $sedeFilter = strtoupper($user->sede);
        }

        $mes = ucfirst(strtolower(trim($mes)));

        // Normalizar variantes de meses
        $mesNormalizados = [
            'Septiembre' => ['Septiembre', 'Setiembre'],
            'Setiembre' => ['Septiembre', 'Setiembre'],
        ];
        $mesesBuscar = $mesNormalizados[$mes] ?? [$mes];

        $spreadsheetId = config('services.google_sheets.ventas_spreadsheet_id', '1zQ8h0cX8YdQ4Jko69vGpDNMXMRrXD0ICCH6G4O6ubiY');

        try {
            $cacheKey = "ai_ventas_{$anio}_{$mes}_v3";

            $sedes = Cache::remember($cacheKey, 300, function () use ($spreadsheetId, $mesesBuscar, $anio) {
                $client = new Client();
                $client->setApplicationName('TRIMAX Ventas');
                $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
                $client->setAuthConfig(storage_path('app/google/service-account.json'));
                $client->setAccessType('offline');

                $service = new Sheets($client);
                $range = 'Historico!A:G';
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues();

                $sedes = [];
                if (empty($values)) return $sedes;

                foreach ($values as $index => $row) {
                    if ($index == 0) continue;
                    if (empty($row[0]) || !isset($row[1]) || !isset($row[2])) continue;

                    $sedeSheet = trim(strtoupper($row[0]));
                    $anioSheet = trim($row[1]);
                    $mesSheet = trim(ucfirst(strtolower($row[2])));

                    if ($anioSheet == $anio && in_array($mesSheet, $mesesBuscar)) {
                        $ventaGeneral = $this->limpiarNumero($row[3] ?? 0);
                        $ventaProyectada = $this->limpiarNumero($row[4] ?? 0);
                        $cuota = $this->limpiarNumero($row[5] ?? 0);
                        $cumplimiento = $this->limpiarPorcentaje($row[6] ?? '0%');

                        $sedes[] = [
                            'sede' => $sedeSheet,
                            'venta_general' => $ventaGeneral,
                            'venta_proyectada' => $ventaProyectada,
                            'cuota' => $cuota,
                            'cumplimiento_cuota' => $cumplimiento . '%',
                            'diferencia' => round($ventaGeneral - $cuota, 2),
                        ];
                    }
                }

                usort($sedes, fn($a, $b) =>
                    floatval(str_replace('%', '', $b['cumplimiento_cuota']))
                    <=> floatval(str_replace('%', '', $a['cumplimiento_cuota']))
                );

                return $sedes;
            });

            // Filtrar por sede
            if ($sedeFilter && $sedeFilter !== 'TODAS' && $sedeFilter !== 'ALL') {
                $sedes = array_values(array_filter($sedes, fn($s) =>
                    stripos($s['sede'], $sedeFilter) !== false
                ));
            }

            $totalVentas = array_sum(array_column($sedes, 'venta_general'));
            $totalCuota = array_sum(array_column($sedes, 'cuota'));

            return [
                'fuente' => 'Google Sheets',
                'periodo' => "{$mes} {$anio}",
                'cantidad_sedes' => count($sedes),
                'sedes' => $sedes,
                'resumen' => [
                    'total_ventas' => round($totalVentas, 2),
                    'total_cuota' => round($totalCuota, 2),
                    'cumplimiento_general' => $totalCuota > 0
                        ? round(($totalVentas / $totalCuota) * 100, 1) . '%'
                        : 'N/A',
                ],
                'moneda' => 'PEN (Soles)',
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo ventas Sheets: ' . $e->getMessage());
            return ['error' => 'No se pudieron obtener las ventas: ' . $e->getMessage()];
        }
    }

    /**
     * 💰 Ventas detalladas desde MySQL (tabla ventas)
     */
    protected function fnObtenerVentasBD(array $args, $user): array
    {
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        $mes = $args['mes'] ?? $mesActual;
        $anio = $args['anio'] ?? $anioActual;
        $sede = $args['sede'] ?? null;
        $marca = $args['marca'] ?? null;
        $tipoCliente = $args['tipo_cliente'] ?? null;
        $agruparPor = $args['agrupar_por'] ?? 'sede';

        // Si el usuario es de sede y NO tiene permiso de consolidado, forzar su sede
        if ($user->isSede() && !$user->puedeVerVentasConsolidadas() && $user->sede) {
            $sede = $user->sede;
        }

        try {
            $cacheKey = "ai_ventas_bd_{$anio}_{$mes}_{$agruparPor}_" . md5(json_encode($args));

            return Cache::remember($cacheKey, 300, function () use ($mes, $anio, $sede, $marca, $tipoCliente, $agruparPor) {
                $query = DB::table('ventas')
                    ->where('anio', $anio)
                    ->where('mes', $mes);

                if ($sede) {
                    $query->where('sede', 'LIKE', "%{$sede}%");
                }
                if ($marca) {
                    $query->where('marca', 'LIKE', "%{$marca}%");
                }
                if ($tipoCliente) {
                    $query->where('tipo_cliente', 'LIKE', "%{$tipoCliente}%");
                }

                // Agrupación
                $groupColumn = match ($agruparPor) {
                    'marca' => 'marca',
                    'tipo_cliente' => 'tipo_cliente',
                    'zona' => 'zona',
                    'motorizado' => 'motorizado',
                    default => 'sede',
                };

                $resultados = $query->select(
                    DB::raw("{$groupColumn} as grupo"),
                    DB::raw('COUNT(*) as cantidad_registros'),
                    DB::raw('COUNT(DISTINCT nro_documento) as cantidad_documentos'),
                    DB::raw('SUM(COALESCE(importe, 0)) as total_importe'),
                    DB::raw('SUM(COALESCE(importe_global, 0)) as total_importe_global'),
                    DB::raw('SUM(COALESCE(cantidad, 0)) as total_cantidad'),
                )
                    ->groupBy($groupColumn)
                    ->orderByDesc('total_importe_global')
                    ->limit(30)
                    ->get();

                $totalGeneral = $resultados->sum('total_importe_global');

                return [
                    'fuente' => 'Base de Datos MySQL',
                    'periodo' => "Mes {$mes}/{$anio}",
                    'agrupado_por' => $agruparPor,
                    'filtros' => array_filter([
                        'sede' => $sede,
                        'marca' => $marca,
                        'tipo_cliente' => $tipoCliente,
                    ]),
                    'resultados' => $resultados->map(fn($r) => [
                        'grupo' => $r->grupo ?? 'Sin asignar',
                        'cantidad_documentos' => $r->cantidad_documentos,
                        'total_importe' => round($r->total_importe, 2),
                        'total_importe_global' => round($r->total_importe_global, 2),
                        'total_unidades' => $r->total_cantidad,
                        'participacion' => $totalGeneral > 0
                            ? round(($r->total_importe_global / $totalGeneral) * 100, 1) . '%'
                            : '0%',
                    ])->toArray(),
                    'total_general' => round($totalGeneral, 2),
                    'moneda' => 'PEN (Soles)',
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error obteniendo ventas BD: ' . $e->getMessage());
            return ['error' => 'Error al consultar ventas: ' . $e->getMessage()];
        }
    }

    /**
     * 📦 Estadísticas de órdenes desde Google Sheets
     */
    protected function fnObtenerEstadisticasOrdenes(array $args, $user): array
    {
        try {
            $cacheKey = 'ai_stats_ordenes_v2';

            $stats = Cache::remember($cacheKey, 300, function () {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);
                return $this->calcularEstadisticasOrdenes($ordenes);
            });

            if (!empty($args['sede'])) {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);

                $ordenes = array_filter($ordenes, fn($orden) =>
                    stripos($orden['descripcion_sede'] ?? '', $args['sede']) !== false
                );

                $stats = $this->calcularEstadisticasOrdenes(array_values($ordenes));
                $stats['sede_filtrada'] = $args['sede'];
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Error estadísticas órdenes: ' . $e->getMessage());
            return ['error' => 'No se pudieron obtener las estadísticas: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Acuerdos comerciales desde MySQL
     */
    protected function fnObtenerAcuerdos(array $args, $user): array
    {
        $query = AcuerdoComercial::with(['creador:id,name,email']);

        // Si el usuario es de sede, filtrar por su sede automáticamente
        if ($user->isSede() && $user->sede) {
            $query->where('sede', 'LIKE', '%' . $user->sede . '%');
        }

        if (!empty($args['estado'])) {
            $query->where('estado', $args['estado']);
        }
        if (!empty($args['sede'])) {
            $query->where('sede', 'LIKE', '%' . $args['sede'] . '%');
        }
        if (!empty($args['buscar'])) {
            $buscar = $args['buscar'];
            $query->where(fn($q) =>
                $q->where('numero_acuerdo', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('ruc', 'like', "%{$buscar}%")
            );
        }

        $acuerdos = $query->orderBy('created_at', 'desc')->limit(20)->get();

        $resumenEstados = AcuerdoComercial::selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado')
            ->toArray();

        return [
            'acuerdos' => $acuerdos->map(fn($a) => [
                'numero' => $a->numero_acuerdo,
                'razon_social' => $a->razon_social,
                'ruc' => $a->ruc,
                'sede' => $a->sede,
                'estado' => $a->estado,
                'validado' => $a->validado,
                'aprobado' => $a->aprobado,
                'fecha_inicio' => $a->fecha_inicio?->format('d/m/Y'),
                'fecha_fin' => $a->fecha_fin?->format('d/m/Y'),
                'creado_por' => $a->creador->name ?? 'N/A',
                'tipo_promocion' => $a->tipo_promocion,
                'marca' => $a->marca,
            ])->toArray(),
            'total_encontrados' => $acuerdos->count(),
            'resumen_por_estado' => $resumenEstados,
            'filtros_aplicados' => array_filter($args),
        ];
    }

    /**
     * 🏷️ Descuentos especiales desde MySQL
     */
    protected function fnObtenerDescuentos(array $args, $user): array
    {
        $query = DB::table('descuentos_especiales')
            ->join('users', 'descuentos_especiales.user_id', '=', 'users.id')
            ->select(
                'descuentos_especiales.numero_descuento',
                'descuentos_especiales.numero_factura',
                'descuentos_especiales.numero_orden',
                'descuentos_especiales.sede',
                'descuentos_especiales.ruc',
                'descuentos_especiales.razon_social',
                'descuentos_especiales.consultor',
                'descuentos_especiales.tipo',
                'descuentos_especiales.marca',
                'descuentos_especiales.aplicado',
                'descuentos_especiales.aprobado',
                'descuentos_especiales.created_at',
                'users.name as creado_por'
            );

        // Si el usuario es de sede, filtrar por su sede automáticamente
        if ($user->isSede() && $user->sede) {
            $query->where('descuentos_especiales.sede', 'LIKE', '%' . $user->sede . '%');
        }

        if (!empty($args['estado_aplicado'])) {
            $query->where('descuentos_especiales.aplicado', $args['estado_aplicado']);
        }
        if (!empty($args['estado_aprobado'])) {
            $query->where('descuentos_especiales.aprobado', $args['estado_aprobado']);
        }
        if (!empty($args['sede'])) {
            $query->where('descuentos_especiales.sede', 'LIKE', '%' . $args['sede'] . '%');
        }
        if (!empty($args['tipo'])) {
            $query->where('descuentos_especiales.tipo', $args['tipo']);
        }
        if (!empty($args['buscar'])) {
            $buscar = $args['buscar'];
            $query->where(fn($q) =>
                $q->where('descuentos_especiales.numero_descuento', 'like', "%{$buscar}%")
                    ->orWhere('descuentos_especiales.razon_social', 'like', "%{$buscar}%")
                    ->orWhere('descuentos_especiales.ruc', 'like', "%{$buscar}%")
                    ->orWhere('descuentos_especiales.numero_orden', 'like', "%{$buscar}%")
            );
        }

        $descuentos = $query->orderBy('descuentos_especiales.created_at', 'desc')
            ->limit(20)
            ->get();

        // Resumen
        $resumen = DB::table('descuentos_especiales')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN aplicado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes_aplicar,
                SUM(CASE WHEN aprobado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes_aprobar,
                SUM(CASE WHEN aprobado = 'Aprobado' THEN 1 ELSE 0 END) as aprobados,
                SUM(CASE WHEN aprobado = 'Rechazado' THEN 1 ELSE 0 END) as rechazados
            ")
            ->first();

        $resumenTipo = DB::table('descuentos_especiales')
            ->selectRaw('tipo, COUNT(*) as cantidad')
            ->groupBy('tipo')
            ->pluck('cantidad', 'tipo')
            ->toArray();

        return [
            'descuentos' => $descuentos->map(fn($d) => [
                'numero' => $d->numero_descuento,
                'factura' => $d->numero_factura,
                'orden' => $d->numero_orden,
                'sede' => $d->sede,
                'ruc' => $d->ruc,
                'razon_social' => $d->razon_social,
                'consultor' => $d->consultor,
                'tipo' => $d->tipo,
                'marca' => $d->marca,
                'aplicado' => $d->aplicado,
                'aprobado' => $d->aprobado,
                'creado_por' => $d->creado_por,
                'fecha' => Carbon::parse($d->created_at)->format('d/m/Y'),
            ])->toArray(),
            'total_encontrados' => $descuentos->count(),
            'resumen_general' => [
                'total' => $resumen->total ?? 0,
                'pendientes_aplicar' => $resumen->pendientes_aplicar ?? 0,
                'pendientes_aprobar' => $resumen->pendientes_aprobar ?? 0,
                'aprobados' => $resumen->aprobados ?? 0,
                'rechazados' => $resumen->rechazados ?? 0,
            ],
            'resumen_por_tipo' => $resumenTipo,
            'filtros_aplicados' => array_filter($args),
        ];
    }

    /**
     * 🔍 Buscar orden en Google Sheets
     */
    protected function fnBuscarOrden(array $args, $user): array
    {
        $termino = $args['termino_busqueda'] ?? '';
        if (empty($termino)) {
            return ['error' => 'Debes proporcionar un término de búsqueda'];
        }

        try {
            $rawData = $this->googleSheets->getSheetData('Historico');
            $ordenes = $this->googleSheets->parseSheetData($rawData);
            $resultados = $this->googleSheets->searchInData($ordenes, $termino);
            $resultados = array_slice(array_values($resultados), 0, 10);

            return [
                'termino_buscado' => $termino,
                'resultados_encontrados' => count($resultados),
                'ordenes' => $resultados,
                'nota' => count($resultados) >= 10 ? 'Se muestran los primeros 10 resultados' : null,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Error al buscar orden: ' . $e->getMessage()];
        }
    }

    /**
     * 👤 Info del usuario actual
     */
    protected function fnObtenerInfoUsuario($user): array
    {
        return [
            'nombre' => $user->name,
            'email' => $user->email,
            'rol' => $user->getRoleName(),
            'roles_spatie' => $user->getRoleNames()->toArray(),
            'sede' => $user->getSedeName(),
            'permisos' => [
                'ver_ventas_consolidadas' => $user->puedeVerVentasConsolidadas(),
                'ver_descuentos_especiales' => $user->puedeVerDescuentosEspeciales(),
            ],
            'estado' => 'Autenticado y activo',
            'ultimo_acceso' => $user->last_login_at?->format('d/m/Y H:i') ?? 'N/A',
        ];
    }

    /**
     * 📊 Resumen general del sistema
     */
    protected function fnObtenerResumenGeneral($user): array
    {
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        $ventasData = $this->fnObtenerVentasSede([
            'mes' => $mesActual,
            'anio' => $anioActual,
        ], $user);

        $acuerdosVigentes = AcuerdoComercial::where('estado', 'Vigente')->count();
        $acuerdosPendientes = AcuerdoComercial::where('estado', 'Solicitado')->count();
        $totalAcuerdos = AcuerdoComercial::count();

        $descuentosPendientes = DB::table('descuentos_especiales')
            ->where('aprobado', 'Pendiente')
            ->count();

        // Encuestas recientes
        $encuestasRecientes = DB::table('surveys')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COUNT(*) as total, AVG(experience_rating) as avg_experiencia, AVG(service_quality_rating) as avg_servicio')
            ->first();

        return [
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
            'ventas_mes_actual' => [
                'periodo' => "{$mesActual} {$anioActual}",
                'total' => $ventasData['resumen']['total_ventas'] ?? 0,
                'cuota' => $ventasData['resumen']['total_cuota'] ?? 0,
                'cumplimiento' => $ventasData['resumen']['cumplimiento_general'] ?? 'N/A',
                'cantidad_sedes' => $ventasData['cantidad_sedes'] ?? 0,
            ],
            'acuerdos_comerciales' => [
                'vigentes' => $acuerdosVigentes,
                'pendientes_aprobacion' => $acuerdosPendientes,
                'total' => $totalAcuerdos,
            ],
            'descuentos_especiales' => [
                'pendientes_aprobacion' => $descuentosPendientes,
            ],
            'satisfaccion_clientes' => [
                'encuestas_ultimo_mes' => $encuestasRecientes->total ?? 0,
                'promedio_experiencia' => round($encuestasRecientes->avg_experiencia ?? 0, 1) . '/5',
                'promedio_servicio' => round($encuestasRecientes->avg_servicio ?? 0, 1) . '/5',
            ],
            'usuario_consultando' => $user->name,
        ];
    }

    /**
     * ⭐ Encuestas de satisfacción
     */
    protected function fnObtenerEncuestas(array $args, $user): array
    {
        $dias = $args['dias'] ?? 30;

        try {
            $encuestas = DB::table('surveys')
                ->join('users', 'surveys.user_id', '=', 'users.id')
                ->select(
                    'surveys.client_name',
                    'surveys.experience_rating',
                    'surveys.service_quality_rating',
                    'surveys.comments',
                    'surveys.created_at',
                    'users.name as registrado_por'
                )
                ->where('surveys.created_at', '>=', now()->subDays($dias))
                ->orderBy('surveys.created_at', 'desc')
                ->limit(20)
                ->get();

            $promedios = DB::table('surveys')
                ->where('created_at', '>=', now()->subDays($dias))
                ->selectRaw('
                    COUNT(*) as total,
                    AVG(experience_rating) as avg_experiencia,
                    AVG(service_quality_rating) as avg_servicio,
                    MIN(experience_rating) as min_experiencia,
                    MAX(experience_rating) as max_experiencia
                ')
                ->first();

            // Distribución de calificaciones
            $distribucion = DB::table('surveys')
                ->where('created_at', '>=', now()->subDays($dias))
                ->selectRaw('experience_rating, COUNT(*) as cantidad')
                ->groupBy('experience_rating')
                ->pluck('cantidad', 'experience_rating')
                ->toArray();

            return [
                'periodo' => "Últimos {$dias} días",
                'total_encuestas' => $promedios->total ?? 0,
                'promedios' => [
                    'experiencia' => round($promedios->avg_experiencia ?? 0, 1) . '/5',
                    'servicio' => round($promedios->avg_servicio ?? 0, 1) . '/5',
                ],
                'distribucion_calificaciones' => $distribucion,
                'encuestas_recientes' => $encuestas->map(fn($e) => [
                    'cliente' => $e->client_name ?? 'Anónimo',
                    'experiencia' => $e->experience_rating . '/5',
                    'servicio' => $e->service_quality_rating . '/5',
                    'comentario' => $e->comments,
                    'fecha' => Carbon::parse($e->created_at)->format('d/m/Y'),
                    'registrado_por' => $e->registrado_por,
                ])->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo encuestas: ' . $e->getMessage());
            return ['error' => 'Error al consultar encuestas: ' . $e->getMessage()];
        }
    }

    /**
     * 🏆 Ranking de sedes
     */
    protected function fnObtenerRankingSedes(array $args, $user): array
    {
        $criterio = $args['criterio'] ?? 'ventas';
        $top = min($args['top'] ?? 10, 30);
        $orden = $args['orden'] ?? 'mejores';

        // Obtener ventas del mes actual
        $ventasData = $this->fnObtenerVentasSede([
            'sede' => 'todas',
        ], $user);

        $sedes = $ventasData['sedes'] ?? [];

        if (empty($sedes)) {
            return ['error' => 'No hay datos de sedes disponibles'];
        }

        // Ordenar según criterio
        usort($sedes, function ($a, $b) use ($criterio, $orden) {
            $valorA = match ($criterio) {
                'cumplimiento' => floatval(str_replace('%', '', $a['cumplimiento_cuota'])),
                'ventas' => $a['venta_general'],
                default => $a['venta_general'],
            };
            $valorB = match ($criterio) {
                'cumplimiento' => floatval(str_replace('%', '', $b['cumplimiento_cuota'])),
                'ventas' => $b['venta_general'],
                default => $b['venta_general'],
            };

            return $orden === 'mejores'
                ? $valorB <=> $valorA
                : $valorA <=> $valorB;
        });

        $sedes = array_slice($sedes, 0, $top);

        // Agregar posición
        foreach ($sedes as $i => &$sede) {
            $sede['posicion'] = $i + 1;
        }

        return [
            'criterio' => $criterio,
            'orden' => $orden,
            'periodo' => $ventasData['periodo'] ?? 'Actual',
            'top' => $top,
            'ranking' => $sedes,
        ];
    }

    /**
     * 👥 Requerimientos de personal (solo RRHH y admins)
     */
    protected function fnObtenerRequerimientos(array $args, $user): array
    {
        if (!$user->puedeCrearRequerimientos() && !$user->puedeVerTodosLosRequerimientos()) {
            return ['error' => 'No tienes permiso para consultar requerimientos de personal.'];
        }

        try {
            $query = \App\Models\RequerimientoPersonal::with([
                'solicitante:id,name,sede',
                'responsableRh:id,name',
            ]);

            // Si no puede ver todos, solo ve los suyos
            if (!$user->puedeVerTodosLosRequerimientos()) {
                $query->where('solicitante_id', $user->id);
            }

            if (!empty($args['estado'])) {
                $query->where('estado', $args['estado']);
            }
            if (!empty($args['sede'])) {
                $query->where('sede', 'LIKE', '%' . $args['sede'] . '%');
            }
            if (!empty($args['gerencia'])) {
                $query->where('gerencia', 'LIKE', '%' . $args['gerencia'] . '%');
            }
            if (!empty($args['sla_vencido']) && $args['sla_vencido']) {
                $query->slaVencidos();
            }

            $requerimientos = $query->orderBy('created_at', 'desc')->limit(20)->get();

            $resumen = \App\Models\RequerimientoPersonal::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'En Proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'Contratado' THEN 1 ELSE 0 END) as contratados,
                SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) as cancelados
            ")->first();

            $slaVencidos = \App\Models\RequerimientoPersonal::slaVencidos()->count();

            return [
                'requerimientos' => $requerimientos->map(fn($r) => [
                    'codigo' => $r->codigo,
                    'puesto' => $r->puesto,
                    'gerencia' => $r->gerencia,
                    'sede' => $r->sede,
                    'tipo' => $r->tipo,
                    'estado' => $r->estado,
                    'semaforo' => $r->semaforo,
                    'dias_transcurridos' => $r->kpi,
                    'sla_limite' => $r->sla . ' días',
                    'solicitante' => $r->solicitante->name ?? 'N/A',
                    'responsable_rh' => $r->responsableRh->name ?? 'Sin asignar',
                    'fecha_solicitud' => $r->fecha_solicitud?->format('d/m/Y'),
                    'fecha_cierre' => $r->fecha_cierre?->format('d/m/Y'),
                ])->toArray(),
                'total_encontrados' => $requerimientos->count(),
                'resumen_estados' => [
                    'total' => $resumen->total ?? 0,
                    'pendientes' => $resumen->pendientes ?? 0,
                    'en_proceso' => $resumen->en_proceso ?? 0,
                    'contratados' => $resumen->contratados ?? 0,
                    'cancelados' => $resumen->cancelados ?? 0,
                    'sla_vencidos' => $slaVencidos,
                ],
                'filtros_aplicados' => array_filter($args),
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo requerimientos: ' . $e->getMessage());
            return ['error' => 'Error al consultar requerimientos: ' . $e->getMessage()];
        }
    }

    // ================================================================
    // LLAMADA AL PROVEEDOR DE IA CON TOOLS
    // ================================================================

    protected function callAIWithTools(array $messages, $user = null): ?array
    {
        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $this->getTools($user),
                'tool_choice' => 'auto',
                'temperature' => 0.1,
                'max_tokens' => 2000,
            ];

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI API Error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('AI Exception: ' . $e->getMessage());
            return null;
        }
    }

    // ================================================================
    // APRENDIZAJE
    // ================================================================

    protected function learnFromInteraction(string $question, string $answer, string $type): void
    {
        try {
            // Buscar patrones similares en knowledge base
            $words = array_filter(explode(' ', strtolower($question)), fn($w) => strlen($w) > 3);
            if (empty($words)) return;

            $existing = AiKnowledgeBase::where('is_active', true)
                ->where(function ($q) use ($words) {
                    foreach (array_slice($words, 0, 3) as $word) {
                        $q->orWhere('question_pattern', 'LIKE', "%{$word}%");
                    }
                })
                ->first();

            if ($existing) {
                $existing->increment('usage_count');
                $existing->update(['last_used_at' => now()]);
            }
        } catch (\Exception $e) {
            // No fallar silenciosamente el aprendizaje
            Log::debug('Learning error: ' . $e->getMessage());
        }
    }

    // ================================================================
    // HELPERS
    // ================================================================

    protected function calcularEstadisticasOrdenes(array $ordenes): array
    {
        $total = count($ordenes);
        $enTransito = $enSede = $facturados = 0;
        $importeTransito = $importeSede = 0;

        foreach ($ordenes as $orden) {
            $ubicacion = mb_strtoupper($orden['ubicacion_orden'] ?? '');
            $estado = mb_strtoupper($orden['estado_orden'] ?? '');
            $importe = $this->limpiarImporte($orden['importe'] ?? null);

            if (stripos($ubicacion, 'FACTURADO') !== false || stripos($ubicacion, 'ENTREGADO') !== false) {
                $facturados++;
            } elseif (stripos($ubicacion, 'SEDE') !== false) {
                $enSede++;
                if ($estado === 'SOLICITADO') $importeSede += $importe;
            } elseif (stripos($ubicacion, 'TRANSITO') !== false) {
                $enTransito++;
                if ($estado === 'SOLICITADO') $importeTransito += $importe;
            }
        }

        return [
            'total_ordenes' => $total,
            'en_transito' => $enTransito,
            'en_sede' => $enSede,
            'facturados' => $facturados,
            'disponibles_facturar' => $enSede + $enTransito,
            'importe_transito' => 'S/ ' . number_format($importeTransito, 2),
            'importe_sede' => 'S/ ' . number_format($importeSede, 2),
            'importe_total_pendiente' => 'S/ ' . number_format($importeTransito + $importeSede, 2),
        ];
    }

    protected function limpiarNumero($valor)
    {
        if (empty($valor)) return 0;
        $valor = trim((string)$valor);

        if (strpos($valor, '.') !== false && strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }

        return floatval($valor);
    }

    protected function limpiarPorcentaje($valor)
    {
        if (empty($valor)) return 0;
        $valor = str_replace('%', '', trim((string)$valor));
        $valor = str_replace(',', '.', $valor);
        return floatval($valor);
    }

    protected function limpiarImporte($importeStr)
    {
        if (!$importeStr || $importeStr === '-' || $importeStr === '') return 0;

        $limpio = trim((string)$importeStr);
        if ($limpio === '' || substr_count($limpio, '/') >= 2 || preg_match('/\/\d{4}/', $limpio)) return 0;

        $limpio = preg_replace('/[^0-9.,\-]/', '', $limpio);
        $limpio = str_replace(',', '.', $limpio);
        $numero = floatval($limpio);

        return $numero > 100000 ? 0 : $numero;
    }

    protected function getSystemContext($context)
    {
        $user = Auth::user();
        $role = $user ? $user->getRoleName() : 'guest';
        $module = $context['module'] ?? 'general';

        return [
            'user_role' => $role,
            'current_module' => $module,
            'available_actions' => $this->getAvailableActions($role, $module),
        ];
    }

    protected function getAvailableActions($role, $module)
    {
        $actions = [
            'Super Admin' => [
                'general'    => ['Acceso completo al sistema', 'Gestionar usuarios y permisos', 'Ver todos los reportes'],
                'descuentos' => ['Ver todos', 'Aprobar', 'Rechazar', 'Gestionar'],
                'convenios'  => ['Ver todos', 'Crear', 'Aprobar', 'Desactivar'],
                'ordenes'    => ['Ver todas las órdenes', 'Consultar cualquier sede'],
                'dashboard'  => ['Ver todas las métricas', 'Exportar reportes'],
                'encuestas'  => ['Ver todas las encuestas', 'Análisis completo'],
            ],
            'Admin' => [
                'general'    => ['Gestionar sistema', 'Ver todos los reportes'],
                'descuentos' => ['Ver todos', 'Aprobar', 'Rechazar'],
                'convenios'  => ['Ver todos', 'Crear', 'Aprobar'],
                'ordenes'    => ['Ver todas las órdenes'],
                'dashboard'  => ['Ver métricas globales', 'Exportar'],
                'encuestas'  => ['Ver encuestas', 'Análisis'],
            ],
            'RRHH' => [
                'general'          => ['Ver información general del sistema'],
                'requerimientos'   => ['Ver todos los requerimientos', 'Gestionar estados', 'Asignar responsable RH', 'Agregar notas'],
                'dashboard'        => ['Ver métricas de personal'],
            ],
            'Marketing' => [
                'general'   => ['Ver información general'],
                'encuestas' => ['Ver encuestas de satisfacción', 'Análisis de clientes'],
                'dashboard' => ['Ver métricas de ventas'],
            ],
            'Consultor' => [
                'general'    => ['Consultar información'],
                'descuentos' => ['Crear solicitud', 'Ver mis solicitudes'],
                'ordenes'    => ['Consultar órdenes'],
                'convenios'  => ['Ver convenios activos'],
                'dashboard'  => ['Ver métricas'],
            ],
        ];

        // Roles de sede (tienen el prefijo "Sede - ")
        if (str_starts_with($role, 'Sede')) {
            return match($module) {
                'descuentos' => ['Crear solicitud de descuento', 'Ver mis solicitudes', 'Ver estado de aprobación'],
                'ordenes'    => ['Consultar mis órdenes', 'Ver detalles de orden'],
                'convenios'  => ['Ver convenios de mi sede'],
                'dashboard'  => ['Ver ventas de mi sede'],
                default      => ['Consultar información de mi sede'],
            };
        }

        return $actions[$role][$module]
            ?? $actions[$role]['general']
            ?? ['Ver información general'];
    }

    protected function saveInteraction($question, $answer, $context, $responseType = 'direct_answer', $toolsUsed = [])
    {
        return AiInteraction::create([
            'user_id' => Auth::id(),
            'session_id' => $context['sessionId'] ?? session()->getId(),
            'user_role' => Auth::user()?->getRoleName() ?? 'guest',
            'module' => $context['module'] ?? 'general',
            'question' => $question,
            'context' => array_merge($context, ['tools_used' => $toolsUsed]),
            'ai_response' => $answer,
            'response_type' => $responseType,
        ]);
    }

    protected function errorResponse($question, $context)
    {
        $interaction = $this->saveInteraction(
            $question,
            'Lo siento, no pude procesar tu consulta en este momento.',
            $context,
            'error'
        );

        return [
            'answer' => 'Lo siento, no pude procesar tu consulta en este momento. Por favor intenta de nuevo.',
            'confidence' => 'low',
            'sources' => 'error',
            'tools_used' => [],
            'interaction_id' => $interaction->id,
        ];
    }
}