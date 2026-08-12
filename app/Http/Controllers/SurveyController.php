<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\UsersMarketing;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SurveyController extends Controller
{
    public function show($token)
    {
        $user = UsersMarketing::where('unique_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return view('marketing.survey.not-found');
        }

        return view('marketing.survey.form', compact('user', 'token'));
    }

    public function getData($token)
    {
        $user = UsersMarketing::where('unique_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Encuesta no encontrada o inactiva'
            ], 404);
        }

        $data = [
            'user' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'role'     => $user->role,
                'location' => $user->location,
            ],
            'sedes' => UsersMarketing::where('role', 'sede')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'location']),
            'sede_preseleccionada_id' => $user->sedePreseleccionadaId(),
            'consultores' => UsersMarketing::where('role', 'consultor')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($c) => [
                    'id'       => $c->id,
                    'name'     => $c->name,
                    'sede_ids' => $c->sedes()->pluck('users_marketing.id'),
                ]),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request, $token)
    {
        $user = UsersMarketing::where('unique_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Encuesta no encontrada o inactiva'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'client_name'             => 'required|string|max:255',
            'ruc'                     => 'required|digits:11',
            'sede_id'                 => 'required|integer|exists:users_marketing,id',
            'experience_rating'       => 'required|integer|between:1,4',
            'sede_rating'             => 'required|integer|between:1,4',
            'tiene_consultor'         => 'required|boolean',
            'consultor_id'            => 'nullable|integer|exists:users_marketing,id',
            'consultor_rating'        => 'nullable|integer|between:1,4',
            'tiempos_entrega_rating'  => 'required|integer|between:1,4',
            'promociones_rating'      => 'nullable|integer|between:1,4',
            'comments'                => 'nullable|string|max:1000',
        ], [
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors()
            ], 422);
        }

        // La sede ya no es opcional (rediseño 2026-08-12): "General" no es una
        // sede real, solo se conserva a nivel de token/QR para no romper los
        // links de Trimax General ya distribuidos. El cliente siempre elige
        // una sede real y activa, sin importar el rol del link.
        $sede = UsersMarketing::where('id', $request->sede_id)
            ->where('role', 'sede')
            ->where('is_active', true)
            ->first();

        if (!$sede) {
            return response()->json([
                'success' => false,
                'message' => 'La sede seleccionada no es válida',
            ], 422);
        }

        $sedeId = $sede->id;

        // La rama del consultor solo se procesa si tiene_consultor = true;
        // cualquier dato de consultor enviado fuera de esa rama se ignora
        // en vez de rechazar la encuesta completa. Ya no existe la opción
        // "no sabe / no tiene consultor" (rediseño 2026-08-12): si tiene
        // consultor, tiene que elegir uno específico y calificarlo.
        $tieneConsultor  = $request->boolean('tiene_consultor');
        $consultorId     = null;
        $consultorRating = null;

        if ($tieneConsultor) {
            if (!$request->filled('consultor_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selecciona el consultor que te atiende',
                ], 422);
            }

            $consultor = UsersMarketing::where('id', $request->consultor_id)
                ->where('role', 'consultor')
                ->where('is_active', true)
                ->first();

            if (!$consultor) {
                return response()->json([
                    'success' => false,
                    'message' => 'El consultor seleccionado no es válido',
                ], 422);
            }

            $consultorId = $consultor->id;

            if (!$request->filled('consultor_rating')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falta calificar la atención del consultor',
                ], 422);
            }

            $consultorRating = (int) $request->consultor_rating;
        }

        try {
            $survey = Survey::create([
                'user_id'                 => $user->id,
                'sede_id'                 => $sedeId,
                'client_name'             => $request->client_name,
                'ruc'                     => $request->ruc,
                'experience_rating'       => $request->experience_rating,
                'sede_rating'             => $request->sede_rating,
                'tiene_consultor'         => $tieneConsultor,
                'consultor_id'            => $consultorId,
                'consultor_rating'        => $consultorRating,
                'tiempos_entrega_rating'  => $request->tiempos_entrega_rating,
                'promociones_rating'      => $request->promociones_rating,
                'comments'                => $request->comments,
                'ip_address'              => $request->ip(),
                'user_agent'              => $request->userAgent(),
            ]);

            // Email de alerta en try/catch propio — si falla el email, la encuesta igual se guarda
            try {
                MarketingController::dispatchAlertIfNeeded($survey, $survey->display_entity);
            } catch (\Exception $emailEx) {
                Log::error('[SurveyAlert] Fallo al enviar notificación: ' . $emailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => '¡Gracias por tu opinión! Tu encuesta ha sido enviada correctamente.',
                'data'    => [
                    'survey_id' => $survey->id,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('[Survey store] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la encuesta',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
