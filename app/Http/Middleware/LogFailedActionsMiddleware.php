<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

/**
 * Registra automáticamente los INTENTOS FALLIDOS en cualquier módulo:
 *  - Envío de formularios / reportes que no pasan validación.
 *  - Subida de documentos o imágenes rechazada (formato/tamaño inválido, error al guardar).
 *  - Accesos denegados por falta de permiso (403/401).
 *  - Token CSRF expirado (419) y errores de servidor (5xx) en operaciones de escritura.
 *
 * No registra el login fallido (ese se maneja en LoginController, con motivo y user_id),
 * porque aquí el usuario aún no está autenticado.
 */
class LogFailedActionsMiddleware
{
    /** Acciones de escritura que pueden generar un "intento fallido". */
    protected array $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->logIfFailed($request, $response);
        } catch (\Throwable $e) {
            // El registro nunca debe afectar la respuesta al usuario.
            Log::error('LogFailedActionsMiddleware falló', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    protected function logIfFailed(Request $request, Response $response): void
    {
        $user = $request->user();
        if (!$user) {
            return; // intentos sin sesión (ej. login) se registran en otro lado
        }

        $status   = $response->getStatusCode();
        $isWrite   = in_array($request->method(), $this->writeMethods, true);
        $hasErrors = $request->hasSession() && $request->session()->has('errors');

        // Redirección 302 "de vuelta" con errores = validación de formulario fallida.
        $isValidationRedirect = $isWrite && $status === 302 && $hasErrors;

        // ¿Debemos registrar?
        $shouldLog = match (true) {
            in_array($status, [401, 403], true) => true,   // acceso denegado (cualquier método)
            $isValidationRedirect               => true,   // formulario rechazado
            $isWrite && $status >= 400          => true,   // 419 / 422 / 4xx / 5xx en escritura
            default                             => false,
        };

        if (!$shouldLog) {
            return;
        }

        [$action, $detalle] = $this->classify($request, $response, $status);

        $ruta = optional($request->route())->getName() ?: $request->path();

        $descripcion = "Intento fallido [{$request->method()} {$ruta}]"
            . ($detalle ? " — {$detalle}" : '');

        ActivityLogService::log(
            $user->id,
            $action,
            'Request',
            null,
            Str::limit($descripcion, 480),
            $status
        );
    }

    /**
     * Clasifica el fallo y arma un detalle legible (sin exponer datos sensibles).
     *
     * @return array{0:string,1:?string}  [accion, detalle]
     */
    protected function classify(Request $request, Response $response, int $status): array
    {
        if (in_array($status, [401, 403], true)) {
            return ['access_denied', 'Acceso no autorizado'];
        }

        if ($status === 419) {
            return ['csrf_expired', 'Sesión/token expirado'];
        }

        if ($status === 422 || ($status === 302 && $request->hasSession() && $request->session()->has('errors'))) {
            return ['validation_failed', $this->extractValidationMessages($request, $response)];
        }

        if ($status >= 500) {
            return ['server_error', 'Error interno al procesar la solicitud'];
        }

        return ['action_failed', "Respuesta {$status}"];
    }

    /**
     * Extrae los mensajes de validación (no los valores enviados) para dar contexto.
     */
    protected function extractValidationMessages(Request $request, Response $response): ?string
    {
        $mensajes = [];

        // Respuesta JSON (apiFetch / AJAX): { message, errors: { campo: [..] } }
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            foreach (($data['errors'] ?? []) as $errs) {
                foreach ((array) $errs as $e) {
                    $mensajes[] = $e;
                }
            }
        }
        // Formulario web: errores flasheados a la sesión.
        elseif ($request->hasSession() && $request->session()->has('errors')) {
            $bag = $request->session()->get('errors');
            if (method_exists($bag, 'all')) {
                $mensajes = $bag->all();
            }
        }

        if (empty($mensajes)) {
            return 'Validación fallida';
        }

        $mensajes = array_slice($mensajes, 0, 3);

        return 'Validación: ' . implode(' | ', $mensajes);
    }
}
