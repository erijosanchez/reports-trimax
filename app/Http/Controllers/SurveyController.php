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
            ]
        ];

        if ($user->role === 'trimax') {
            $data['sedes'] = UsersMarketing::where('role', 'sede')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'location']);
        }

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
            'experience_rating'      => 'required|integer|between:1,4',
            'service_quality_rating' => 'required|integer|between:1,4',
            'client_name'            => 'nullable|string|max:255',
            'comments'               => 'nullable|string|max:1000',
            'sede_id'                => 'nullable|integer|exists:users_marketing,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors()
            ], 422);
        }

        // El sede_id solo aplica a la encuesta de Trimax General, y debe ser una sede real
        $sedeId = null;
        if ($user->role === 'trimax' && $request->filled('sede_id')) {
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
        }

        try {
            $survey = Survey::create([
                'user_id'                => $user->id,
                'sede_id'                => $sedeId,
                'client_name'            => $request->client_name,
                'experience_rating'      => $request->experience_rating,
                'service_quality_rating' => $request->service_quality_rating,
                'comments'               => $request->comments,
                'ip_address'             => $request->ip(),
                'user_agent'             => $request->userAgent(),
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
