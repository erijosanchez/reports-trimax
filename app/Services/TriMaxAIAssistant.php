<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

    public function __construct(GoogleSheetsService $googleSheets)
    {
        $this->apiKey = config('services.groq.api_key');
        $this->apiUrl = config('services.groq.api_url');
        $this->model = config('services.groq.model');
        $this->googleSheets = $googleSheets;
    }

    // ================================================================
    // MÉTODO PRINCIPAL - AHORA CON FUNCTION CALLING
    // ================================================================

    public function ask($question, $context = [])
    {
        $user = Auth::user();
        $systemContext = $this->getSystemContext($context);

        // Construir mensajes
        $messages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt($user, $systemContext)],
            ['role' => 'user', 'content' => $question],
        ];

        // Primera llamada con tools
        $response = $this->callGroqWithTools($messages);

        if (!$response) {
            return $this->errorResponse($question, $context);
        }

        // Loop de function calling (máx 5 iteraciones)
        $maxIterations = 5;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $choice = $response['choices'][0] ?? null;
            if (!$choice) break;

            $message = $choice['message'] ?? [];
            $finishReason = $choice['finish_reason'] ?? '';
            $toolCalls = $message['tool_calls'] ?? [];

            // Si el modelo quiere llamar funciones
            if (!empty($toolCalls)) {
                // Agregar el mensaje del asistente (con tool_calls)
                $messages[] = $message;

                foreach ($toolCalls as $toolCall) {
                    $functionName = $toolCall['function']['name'] ?? '';
                    $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?? [];

                    Log::info("🔧 Function call: {$functionName}", $arguments);

                    // Ejecutar la función real
                    $result = $this->executeFunction($functionName, $arguments, $user);

                    Log::info("📊 Resultado de {$functionName}:", $result);

                    // Agregar resultado como mensaje tool
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }

                // Volver a llamar al modelo con los resultados
                $response = $this->callGroqWithTools($messages);
                if (!$response) break;

                $iteration++;
            } else {
                // El modelo ya tiene la respuesta final
                break;
            }
        }

        // Extraer respuesta final
        $answer = $response['choices'][0]['message']['content'] ?? 'No pude generar una respuesta.';

        // Guardar interacción
        $interaction = $this->saveInteraction($question, $answer, $context);

        return [
            'answer' => $answer,
            'confidence' => 'high',
            'sources' => 'ai_with_real_data',
            'interaction_id' => $interaction->id,
        ];
    }

    // ================================================================
    // SYSTEM PROMPT - AHORA CON CONTEXTO DEL USUARIO REAL
    // ================================================================

    protected function getSystemPrompt($user, $systemContext)
    {
        $userName = $user->name ?? 'Usuario';
        $userRole = $systemContext['user_role'] ?? 'guest';
        $userEmail = $user->email ?? '';
        $module = $systemContext['current_module'] ?? 'general';
        $actions = implode(', ', $systemContext['available_actions'] ?? []);
        $fechaHoy = Carbon::now()->format('d/m/Y');
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        return "Eres el Asistente Trimax, el asistente inteligente del CRM de Trimax Perú (Laboratorio Óptico).
Trimax tiene más de 30 sedes a nivel nacional incluyendo Lima (Lince, SJM, SJL, Ate, Los Olivos, Puente Piedra, Comas, Cailloma, Napo, Surquillo, Villa El Salvador, Call Center), Arequipa, Trujillo, Chiclayo, Piura, Cusco, Huancayo, Ica, Iquitos, Cajamarca, Chimbote, Huánuco, Huaraz, Pucallpa, Tacna, Ayacucho, Juliaca, Tarapoto y más.

═══════════════════════════════════
USUARIO ACTUAL (ESTÁ LOGUEADO):
═══════════════════════════════════
- Nombre: {$userName}
- Email: {$userEmail}
- Rol: {$userRole}
- Módulo actual: {$module}
- Acciones disponibles: {$actions}

═══════════════════════════════════
FECHA ACTUAL: {$fechaHoy}
MES ACTUAL: {$mesActual} {$anioActual}
═══════════════════════════════════

REGLAS CRÍTICAS:
1. NUNCA inventes datos, cifras, montos o números. SIEMPRE usa las funciones disponibles para consultar datos reales.
2. Si el usuario pregunta por ventas, órdenes, acuerdos o cualquier dato → LLAMA a la función correspondiente. NO asumas que una sede no existe.
3. Si te preguntan por ventas de CUALQUIER sede, SIEMPRE llama a obtener_ventas_sede. La función tiene datos de todas las sedes reales.
4. Si NO tienes una función para responder algo específico, dilo honestamente: 'No tengo acceso a esa información en este momento.'
5. Responde SIEMPRE en español de forma natural, clara y concisa.
6. Dirígete al usuario por su nombre ({$userName}).
7. Adapta la información según su rol ({$userRole}).
8. Mantén respuestas cortas (3-4 párrafos máximo).
9. NO repitas la misma respuesta.
10. NUNCA digas que una sede no existe sin antes consultar la función.

MÓDULOS DEL SISTEMA:
- Descuentos Especiales: Flujo de aprobación por planeamiento comercial
- Convenios Comerciales: Acuerdos con clientes corporativos (validación + aprobación)
- Consulta de Órdenes: Integración con Google Sheets (historial de órdenes)
- Dashboard de Ventas: Métricas por sede desde Google Sheets

FUNCIONES DISPONIBLES:
- obtener_ventas_sede: Ventas mensuales de CUALQUIER sede (todas las 30+ sedes). Pasar sede='todas' para ver todas.
- obtener_estadisticas_ordenes: Estadísticas de órdenes (tránsito, en sede, facturados)
- obtener_acuerdos_comerciales: Lista de acuerdos con filtros
- buscar_orden: Buscar una orden específica
- obtener_info_usuario: Información del usuario actual
- obtener_resumen_general: Resumen general del sistema";
    }

    // ================================================================
    // DEFINICIÓN DE TOOLS (FUNCIONES)
    // ================================================================

    protected function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'obtener_ventas_sede',
                    'description' => 'Obtiene las ventas mensuales de cualquiera de las 30+ sedes de Trimax a nivel nacional (Lima, Arequipa, Trujillo, Iquitos, Cusco, Chiclayo, Piura, etc). SIEMPRE usar esta función cuando pregunten por ventas de cualquier sede.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sede' => [
                                'type' => 'string',
                                'description' => 'Nombre de la sede exacto como aparece en el sistema (ejemplo: IQUITOS, LINCE, AREQUIPA, CUSCO, etc.) o "todas" para ver todas las sedes',
                            ],
                            'mes' => [
                                'type' => 'string',
                                'description' => 'Nombre del mes en español (Enero, Febrero, etc.). Si no se indica, usa el mes actual.',
                            ],
                            'anio' => [
                                'type' => 'integer',
                                'description' => 'Año a consultar. Si no se indica, usa el año actual.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'obtener_estadisticas_ordenes',
                    'description' => 'Obtiene estadísticas de las órdenes de trabajo: total, en tránsito, en sede, facturados, importes pendientes.',
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
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'obtener_acuerdos_comerciales',
                    'description' => 'Obtiene la lista de acuerdos/convenios comerciales con sus estados (Solicitado, Vigente, Vencido, etc.)',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'estado' => [
                                'type' => 'string',
                                'description' => 'Filtrar por estado: Solicitado, Vigente, Vencido, Rechazado',
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
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'buscar_orden',
                    'description' => 'Busca una orden específica por número de orden, nombre de cliente o cualquier texto.',
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
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'obtener_info_usuario',
                    'description' => 'Obtiene información detallada del usuario actual logueado.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'obtener_resumen_general',
                    'description' => 'Obtiene un resumen general del sistema: ventas del mes, órdenes pendientes, acuerdos activos.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
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
                'obtener_estadisticas_ordenes' => $this->fnObtenerEstadisticasOrdenes($args, $user),
                'obtener_acuerdos_comerciales' => $this->fnObtenerAcuerdos($args, $user),
                'buscar_orden' => $this->fnBuscarOrden($args, $user),
                'obtener_info_usuario' => $this->fnObtenerInfoUsuario($user),
                'obtener_resumen_general' => $this->fnObtenerResumenGeneral($user),
                default => ['error' => "Función '{$functionName}' no disponible"],
            };
        } catch (\Exception $e) {
            Log::error("Error ejecutando función {$functionName}: " . $e->getMessage());
            return ['error' => 'Error al consultar datos: ' . $e->getMessage()];
        }
    }

    // ================================================================
    // FUNCIONES REALES QUE CONSULTAN DATOS
    // ================================================================

    /**
     * 📊 Obtener ventas por sede desde Google Sheets (spreadsheet de ventas)
     */
    protected function fnObtenerVentasSede(array $args, $user): array
    {
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        $mes = $args['mes'] ?? $mesActual;
        $anio = $args['anio'] ?? $anioActual;
        $sedeFilter = isset($args['sede']) ? strtoupper(trim($args['sede'])) : null;

        // Normalizar mes (primera letra mayúscula)
        $mes = ucfirst(strtolower(trim($mes)));

        // Normalizar "Setiembre" / "Septiembre"
        $mesNormalizados = [
            'Septiembre' => ['Septiembre', 'Setiembre'],
            'Setiembre' => ['Septiembre', 'Setiembre'],
        ];
        $mesesBuscar = $mesNormalizados[$mes] ?? [$mes];

        $spreadsheetId = '1zQ8h0cX8YdQ4Jko69vGpDNMXMRrXD0ICCH6G4O6ubiY';

        try {
            $cacheKey = "ai_ventas_{$anio}_{$mes}_v2";

            $sedes = Cache::store('file')->remember($cacheKey, 300, function () use ($spreadsheetId, $mesesBuscar, $anio) {
                $client = new Client();
                $client->setApplicationName('TRIMAX Ventas');
                $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
                $client->setAuthConfig(storage_path('app/google/service-account.json'));
                $client->setAccessType('offline');

                $service = new Sheets($client);

                // Columnas: A=Sedes, B=Año, C=Mes, D=Venta General, E=Venta Proyectada, F=Cuota, G=Cum Cuota
                $range = 'Historico!A:G';
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues();

                $sedes = [];

                if (empty($values)) return $sedes;

                foreach ($values as $index => $row) {
                    // Saltar header
                    if ($index == 0) continue;

                    // Verificar que la fila tenga al menos sede, año y mes
                    if (empty($row[0]) || !isset($row[1]) || !isset($row[2])) continue;

                    $sedeSheet = trim(strtoupper($row[0]));
                    $anioSheet = trim($row[1]);
                    $mesSheet = trim(ucfirst(strtolower($row[2])));

                    // Comparar año y mes (soportar variantes de mes)
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

                // Ordenar por cumplimiento descendente
                usort($sedes, function ($a, $b) {
                    return floatval(str_replace('%', '', $b['cumplimiento_cuota']))
                        <=> floatval(str_replace('%', '', $a['cumplimiento_cuota']));
                });

                return $sedes;
            });

            // Filtrar por sede si se especificó
            if ($sedeFilter && $sedeFilter !== 'TODAS' && $sedeFilter !== 'ALL') {
                $sedes = array_values(array_filter($sedes, function ($s) use ($sedeFilter) {
                    return stripos($s['sede'], $sedeFilter) !== false;
                }));
            }

            // Calcular totales
            $totalVentas = array_sum(array_column($sedes, 'venta_general'));
            $totalCuota = array_sum(array_column($sedes, 'cuota'));

            return [
                'periodo' => "{$mes} {$anio}",
                'cantidad_sedes' => count($sedes),
                'sedes' => $sedes,
                'resumen' => [
                    'total_ventas_todas_sedes' => round($totalVentas, 2),
                    'total_cuota_todas_sedes' => round($totalCuota, 2),
                    'cumplimiento_general' => $totalCuota > 0
                        ? round(($totalVentas / $totalCuota) * 100, 1) . '%'
                        : 'N/A',
                ],
                'moneda' => 'PEN (Soles)',
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo ventas: ' . $e->getMessage());
            return ['error' => 'No se pudieron obtener las ventas: ' . $e->getMessage()];
        }
    }

    /**
     * 📦 Obtener estadísticas de órdenes desde Google Sheets
     */
    protected function fnObtenerEstadisticasOrdenes(array $args, $user): array
    {
        try {
            $cacheKey = 'ai_stats_ordenes';

            $stats = Cache::store('file')->remember($cacheKey, 300, function () {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);
                return $this->calcularEstadisticasOrdenes($ordenes);
            });

            // Si filtran por sede, recalcular
            if (!empty($args['sede'])) {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);

                $ordenes = array_filter($ordenes, function ($orden) use ($args) {
                    return stripos($orden['descripcion_sede'] ?? '', $args['sede']) !== false;
                });

                $stats = $this->calcularEstadisticasOrdenes(array_values($ordenes));
                $stats['sede_filtrada'] = $args['sede'];
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de órdenes: ' . $e->getMessage());
            return ['error' => 'No se pudieron obtener las estadísticas: ' . $e->getMessage()];
        }
    }

    /**
     * 📋 Obtener acuerdos comerciales desde MySQL
     */
    protected function fnObtenerAcuerdos(array $args, $user): array
    {
        $query = AcuerdoComercial::with(['creador:id,name,email']);

        if (!empty($args['estado'])) {
            $query->where('estado', $args['estado']);
        }

        if (!empty($args['sede'])) {
            $query->where('sede', 'LIKE', '%' . $args['sede'] . '%');
        }

        if (!empty($args['buscar'])) {
            $buscar = $args['buscar'];
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_acuerdo', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('ruc', 'like', "%{$buscar}%");
            });
        }

        $acuerdos = $query->orderBy('created_at', 'desc')->limit(20)->get();

        // Resumen por estado
        $resumenEstados = AcuerdoComercial::selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado')
            ->toArray();

        return [
            'acuerdos' => $acuerdos->map(function ($a) {
                return [
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
                ];
            })->toArray(),
            'total_encontrados' => $acuerdos->count(),
            'resumen_por_estado' => $resumenEstados,
            'filtros_aplicados' => array_filter($args),
        ];
    }

    /**
     * 🔍 Buscar orden específica en Google Sheets
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

            // Limitar resultados para no sobrecargar
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
     * 👤 Obtener información del usuario actual
     */
    protected function fnObtenerInfoUsuario($user): array
    {
        return [
            'nombre' => $user->name,
            'email' => $user->email,
            'rol' => $user->getRoleName(),
            'roles_spatie' => $user->getRoleNames()->toArray(),
            'sede' => $user->getSedeName(),
            'permisos_especiales' => [
                'ver_ventas_consolidadas' => $user->puedeVerVentasConsolidadas(),
                'ver_descuentos_especiales' => $user->puedeVerDescuentosEspeciales(),
            ],
            'estado' => 'Autenticado y activo',
            'ultimo_acceso' => $user->last_login_at?->format('d/m/Y H:i') ?? 'N/A',
        ];
    }

    /**
     * 📊 Obtener resumen general del sistema
     */
    protected function fnObtenerResumenGeneral($user): array
    {
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        // Ventas del mes
        $ventasData = $this->fnObtenerVentasSede([
            'mes' => $mesActual,
            'anio' => $anioActual,
        ], $user);

        // Acuerdos activos
        $acuerdosVigentes = AcuerdoComercial::where('estado', 'Vigente')->count();
        $acuerdosPendientes = AcuerdoComercial::where('estado', 'Solicitado')->count();
        $totalAcuerdos = AcuerdoComercial::count();

        return [
            'fecha' => Carbon::now()->format('d/m/Y H:i'),
            'ventas_mes_actual' => [
                'periodo' => "{$mesActual} {$anioActual}",
                'total' => $ventasData['resumen']['total_ventas_todas_sedes'] ?? 0,
                'cuota' => $ventasData['resumen']['total_cuota_todas_sedes'] ?? 0,
                'cumplimiento' => $ventasData['resumen']['cumplimiento_general'] ?? 'N/A',
                'por_sede' => $ventasData['sedes'] ?? [],
            ],
            'acuerdos_comerciales' => [
                'vigentes' => $acuerdosVigentes,
                'pendientes_aprobacion' => $acuerdosPendientes,
                'total' => $totalAcuerdos,
            ],
            'usuario_consultando' => $user->name,
        ];
    }

    // ================================================================
    // LLAMADA A GROQ CON TOOLS
    // ================================================================

    protected function callGroqWithTools(array $messages): ?array
    {
        try {
            Log::info('🤖 Llamando a Groq API con function calling...');

            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $this->getTools(),
                'tool_choice' => 'auto',
                'temperature' => 0.1, // Bajo para respuestas más precisas con datos
                'max_tokens' => 1500,
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('✅ Respuesta de Groq recibida');
                return $response->json();
            }

            Log::error('❌ Groq API Error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('❌ Groq Exception: ' . $e->getMessage());
            return null;
        }
    }

    // ================================================================
    // HELPERS
    // ================================================================

    protected function calcularEstadisticasOrdenes(array $ordenes): array
    {
        $total = count($ordenes);
        $enTransito = 0;
        $enSede = 0;
        $facturados = 0;
        $importeTransito = 0;
        $importeSede = 0;

        foreach ($ordenes as $orden) {
            $ubicacion = mb_strtoupper($orden['ubicacion_orden'] ?? '');
            $estado = mb_strtoupper($orden['estado_orden'] ?? '');
            $importe = $this->limpiarImporte($orden['importe'] ?? null);

            if (stripos($ubicacion, 'FACTURADO') !== false || stripos($ubicacion, 'ENTREGADO') !== false) {
                $facturados++;
            } elseif (stripos($ubicacion, 'SEDE') !== false) {
                $enSede++;
                if ($estado === 'SOLICITADO') {
                    $importeSede += $importe;
                }
            } elseif (stripos($ubicacion, 'TRANSITO') !== false) {
                $enTransito++;
                if ($estado === 'SOLICITADO') {
                    $importeTransito += $importe;
                }
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
        $valor = (string)$valor;
        $valor = trim($valor);

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
        $valor = str_replace('%', '', $valor);
        $valor = trim($valor);
        $valor = str_replace(',', '.', $valor);
        return floatval($valor);
    }

    protected function limpiarImporte($importeStr)
    {
        if (!$importeStr || $importeStr === '-' || $importeStr === '') return 0;

        $limpio = trim((string)$importeStr);
        if ($limpio === '') return 0;
        if (substr_count($limpio, '/') >= 2) return 0;
        if (preg_match('/\/\d{4}/', $limpio)) return 0;

        $limpio = preg_replace('/[^0-9.,\-]/', '', $limpio);
        $limpio = str_replace(',', '.', $limpio);
        $numero = floatval($limpio);

        if ($numero > 100000) return 0;

        return $numero;
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
            'user_role' => Auth::user() ? Auth::user()->getRoleName() : 'guest',
            'module' => $context['module'] ?? 'general',
            'question' => $question,
            'context' => $context,
            'ai_response' => $answer,
            'response_type' => 'direct_answer',
        ]);
    }

    protected function errorResponse($question, $context)
    {
        $interaction = $this->saveInteraction(
            $question,
            'Lo siento, no pude procesar tu consulta en este momento.',
            $context
        );

        return [
            'answer' => 'Lo siento, no pude procesar tu consulta en este momento. Por favor intenta de nuevo.',
            'confidence' => 'low',
            'sources' => 'error',
            'interaction_id' => $interaction->id,
        ];
    }
}
