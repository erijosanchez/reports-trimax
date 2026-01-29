<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescuentoEspecial;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DescuentoEspecialCreado;
use App\Notifications\DescuentoEspecialAprobado;
use App\Notifications\DescuentoEspecialDeshabilitado;
use App\Notifications\DescuentoEspecialRehabilitado;

class DescuentosEspecialesController extends Controller
{
    /**
     * Vista principal
     */
    public function index()
    {
        return view('comercial.descuentos-especiales');
    }

    /**
     * Obtener descuentos
     */
    public function obtenerDescuentos(Request $request)
    {
        try {
            $query = DescuentoEspecial::with(['creador', 'aplicador', 'aprobador']);

            // Filtros
            if ($request->filled('usuario')) {
                $query->where('user_id', $request->usuario);
            }

            if ($request->filled('sede')) {
                $query->where('sede', $request->sede);
            }

            if ($request->filled('aplicado')) {
                $query->where('aplicado', $request->aplicado);
            }

            if ($request->filled('aprobado')) {
                $query->where('aprobado', $request->aprobado);
            }

            // Búsqueda general
            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_descuento', 'like', "%{$buscar}%")
                        ->orWhere('razon_social', 'like', "%{$buscar}%")
                        ->orWhere('ruc', 'like', "%{$buscar}%")
                        ->orWhere('numero_factura', 'like', "%{$buscar}%")
                        ->orWhere('numero_orden', 'like', "%{$buscar}%");
                });
            }

            $descuentos = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $descuentos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerDescuentos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener descuentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear descuento
     */
    public function crearDescuento(Request $request)
    {
        try {
            $validated = $request->validate([
                'numero_factura' => 'nullable|string',
                'numero_orden' => 'nullable|string',
                'sede' => 'required|string',
                'ruc' => 'required|string',
                'razon_social' => 'required|string',
                'consultor' => 'required|string',
                'ciudad' => 'required|string',
                'descuento_especial' => 'required|string',
                'tipo' => 'required|in:ANULACION,CORTESIA,DESCUENTO ADICIONAL,DESCUENTO TOTAL,OTROS',
                'marca' => 'required|string',
                'ar' => 'nullable|string',
                'disenos' => 'nullable|string',
                'material' => 'nullable|string',
                'comentarios' => 'nullable|string',
                'archivos.*' => 'nullable|file|max:10240'
            ]);

            // Generar número de descuento
            $numeroDescuento = DescuentoEspecial::generarNumeroDescuento();

            // Subir archivos si existen
            $archivosAdjuntos = [];
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    $path = $archivo->store('descuentos/' . $numeroDescuento, 'public');
                    $archivosAdjuntos[] = [
                        'nombre' => $archivo->getClientOriginalName(),
                        'path' => $path,
                        'size' => $archivo->getSize()
                    ];
                }
            }

            // Crear descuento
            $descuento = DescuentoEspecial::create([
                'numero_descuento' => $numeroDescuento,
                'user_id' => Auth::id(),
                'numero_factura' => $validated['numero_factura'] ?? null,
                'numero_orden' => $validated['numero_orden'] ?? null,
                'sede' => $validated['sede'],
                'ruc' => $validated['ruc'],
                'razon_social' => $validated['razon_social'],
                'consultor' => $validated['consultor'],
                'ciudad' => $validated['ciudad'],
                'descuento_especial' => $validated['descuento_especial'],
                'tipo' => $validated['tipo'],
                'marca' => $validated['marca'],
                'ar' => $validated['ar'] ?? null,
                'disenos' => $validated['disenos'] ?? null,
                'material' => $validated['material'] ?? null,
                'comentarios' => $validated['comentarios'] ?? null,
                'archivos_adjuntos' => $archivosAdjuntos,
                'aplicado' => 'Pendiente',
                'aprobado' => 'Pendiente'
            ]);

            // Enviar notificaciones
            $this->enviarNotificacionCreacion($descuento);

            return response()->json([
                'success' => true,
                'message' => 'Descuento especial creado exitosamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear descuento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 NUEVO: Aplicar descuento (solo auditor.junior@trimaxperu.com)
     */
    public function aplicarDescuento(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            // 🔥 CAMBIO: Solo auditor junior puede aplicar
            if (Auth::user()->email !== 'auditor.junior@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para aplicar descuentos'
                ], 403);
            }

            $validated = $request->validate([
                'accion' => 'required|in:Aprobado,Rechazado'
            ]);

            $descuento->update([
                'aplicado' => $validated['accion'],
                'aplicado_por' => Auth::id(),
                'aplicado_at' => now()
            ]);

            // Si está completamente aprobado (aplicado Y aprobado), enviar notificaciones
            if ($descuento->aplicado === 'Aprobado' && $descuento->aprobado === 'Aprobado') {
                $this->enviarNotificacionAprobacion($descuento);
            }

            return response()->json([
                'success' => true,
                'message' => 'Descuento aplicado correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al aplicar descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 CAMBIO: Aprobar descuento (solo Sergio y planeamiento)
     */
    public function aprobarDescuento(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            // 🔥 CAMBIO: Solo Sergio y planeamiento pueden aprobar
            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para aprobar descuentos'
                ], 403);
            }

            $validated = $request->validate([
                'accion' => 'required|in:Aprobado,Rechazado'
            ]);

            $descuento->update([
                'aprobado' => $validated['accion'],
                'aprobado_por' => Auth::id(),
                'aprobado_at' => now()
            ]);

            // Si está completamente aprobado (aplicado Y aprobado), enviar notificaciones
            if ($descuento->aplicado === 'Aprobado' && $descuento->aprobado === 'Aprobado') {
                $this->enviarNotificacionAprobacion($descuento);
            }

            return response()->json([
                'success' => true,
                'message' => 'Descuento aprobado correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al aprobar descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deshabilitar descuento
     */
    public function deshabilitarDescuento(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para deshabilitar descuentos'
                ], 403);
            }

            $validated = $request->validate([
                'motivo' => 'required|string|min:10'
            ]);

            $descuento->update([
                'habilitado' => false,
                'motivo_deshabilitacion' => $validated['motivo'],
                'deshabilitado_at' => now(),
                'deshabilitado_por' => Auth::id()
            ]);

            $this->enviarNotificacionDeshabilitacion($descuento, $validated['motivo']);

            return response()->json([
                'success' => true,
                'message' => 'Descuento deshabilitado correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador', 'deshabilitador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al deshabilitar descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rehabilitar descuento
     */
    public function rehabilitarDescuento(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para rehabilitar descuentos'
                ], 403);
            }

            if ($descuento->habilitado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este descuento ya está habilitado'
                ], 400);
            }

            $validated = $request->validate([
                'motivo' => 'required|string|min:10'
            ]);

            $descuento->update([
                'habilitado' => true,
                'motivo_rehabilitacion' => $validated['motivo'],
                'rehabilitado_at' => now(),
                'rehabilitado_por' => Auth::id()
            ]);

            $this->enviarNotificacionRehabilitacion($descuento, $validated['motivo']);

            return response()->json([
                'success' => true,
                'message' => 'Descuento rehabilitado correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador', 'rehabilitador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al rehabilitar descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar descuento
     */
    public function editarDescuento(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (Auth::id() !== $descuento->user_id && !in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar este descuento'
                ], 403);
            }

            $validated = $request->validate([
                'numero_factura' => 'nullable|string',
                'numero_orden' => 'nullable|string',
                'sede' => 'required|string',
                'ruc' => 'required|string',
                'razon_social' => 'required|string',
                'consultor' => 'required|string',
                'ciudad' => 'required|string',
                'descuento_especial' => 'required|string',
                'tipo' => 'required|in:ANULACION,CORTESIA,DESCUENTO ADICIONAL,DESCUENTO TOTAL,OTROS',
                'marca' => 'required|string',
                'ar' => 'nullable|string',
                'disenos' => 'nullable|string',
                'material' => 'nullable|string',
                'comentarios' => 'nullable|string',
                'archivos.*' => 'nullable|file|max:10240'
            ]);

            $archivosActuales = $descuento->archivos_adjuntos ?? [];
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    $path = $archivo->store('descuentos/' . $descuento->numero_descuento, 'public');
                    $archivosActuales[] = [
                        'nombre' => $archivo->getClientOriginalName(),
                        'path' => $path,
                        'size' => $archivo->getSize()
                    ];
                }
            }

            $descuento->update([
                'numero_factura' => $validated['numero_factura'] ?? null,
                'numero_orden' => $validated['numero_orden'] ?? null,
                'sede' => $validated['sede'],
                'ruc' => $validated['ruc'],
                'razon_social' => $validated['razon_social'],
                'consultor' => $validated['consultor'],
                'ciudad' => $validated['ciudad'],
                'descuento_especial' => $validated['descuento_especial'],
                'tipo' => $validated['tipo'],
                'marca' => $validated['marca'],
                'ar' => $validated['ar'] ?? null,
                'disenos' => $validated['disenos'] ?? null,
                'material' => $validated['material'] ?? null,
                'comentarios' => $validated['comentarios'] ?? null,
                'archivos_adjuntos' => $archivosActuales,
                'aplicado' => 'Pendiente',
                'aprobado' => 'Pendiente',
                'aplicado_por' => null,
                'aprobado_por' => null,
                'aplicado_at' => null,
                'aprobado_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Descuento actualizado exitosamente. Las validaciones se han reseteado.',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al editar descuento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al editar descuento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 NUEVO: Cambiar aplicación (solo auditor junior)
     */
    public function cambiarAplicacion(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            if (Auth::user()->email !== 'auditor.junior@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar la aplicación'
                ], 403);
            }

            $validated = $request->validate([
                'nuevo_estado' => 'required|in:Aprobado,Rechazado,Pendiente'
            ]);

            $descuento->update([
                'aplicado' => $validated['nuevo_estado'],
                'aplicado_por' => Auth::id(),
                'aplicado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aplicación actualizada correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar aplicación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 CAMBIO: Cambiar aprobación (solo Sergio y planeamiento)
     */
    public function cambiarAprobacion(Request $request, $id)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);

            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar la aprobación'
                ], 403);
            }

            $validated = $request->validate([
                'nuevo_estado' => 'required|in:Aprobado,Rechazado,Pendiente'
            ]);

            $descuento->update([
                'aprobado' => $validated['nuevo_estado'],
                'aprobado_por' => Auth::id(),
                'aprobado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aprobación actualizada correctamente',
                'descuento' => $descuento->load(['creador', 'aplicador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar aprobación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar archivo
     */
    public function descargarArchivo($id, $index)
    {
        try {
            $descuento = DescuentoEspecial::findOrFail($id);
            $archivos = $descuento->archivos_adjuntos;

            if (!isset($archivos[$index])) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $archivo = $archivos[$index];
            $path = storage_path('app/public/' . $archivo['path']);

            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no existe'], 404);
            }

            return response()->download($path, $archivo['nombre']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al descargar archivo'], 500);
        }
    }

    /**
     * Obtener usuarios creadores
     */
    public function obtenerUsuariosCreadores()
    {
        try {
            $usuarios = DescuentoEspecial::with('creador')
                ->get()
                ->pluck('creador')
                ->unique('id')
                ->filter()
                ->sortBy('name')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $usuarios->map(function ($usuario) {
                    return [
                        'id' => $usuario->id,
                        'name' => $usuario->name,
                        'email' => $usuario->email
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar notificación de creación
     * Notificar a: Auditor Junior, Sergio, Planeamiento y Creador
     */
    private function enviarNotificacionCreacion($descuento)
    {
        try {
            // 🔥 CAMBIO: Notificar a auditor junior en lugar de planeamiento
            $auditorJunior = User::where('email', 'auditor.junior@trimaxperu.com')->first();
            $sergio = User::where('email', 'smonopoli@trimaxperu.com')->first();
            $planeamiento = User::where('email', 'planeamiento.comercial@trimaxperu.com')->first();

            $usuarios = collect([$auditorJunior, $sergio, $planeamiento, $descuento->creador])->filter();

            Notification::send($usuarios, new DescuentoEspecialCreado($descuento));

            \Log::info('✅ Notificaciones de creación enviadas a: ' . $usuarios->pluck('email')->implode(', '));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de creación: ' . $e->getMessage());
        }
    }

    /**
     * 📧 Enviar notificación de aprobación completa
     * Enviar a: Auditor Junior, usuario solicitante, Sergio y planeamiento
     */
    private function enviarNotificacionAprobacion($descuento)
    {
        try {
            // 🔥 Auditor Junior (el que aplica)
            $auditorJunior = User::where('email', 'auditor.junior@trimaxperu.com')->first();

            // Sergio
            $sergio = User::where('email', 'smonopoli@trimaxperu.com')->first();

            // Planeamiento
            $planeamiento = User::where('email', 'planeamiento.comercial@trimaxperu.com')->first();

            // Usuario solicitante (creador)
            $creador = $descuento->creador;

            $destinatarios = collect([$auditorJunior, $sergio, $planeamiento, $creador])->filter()->unique('id');

            Notification::send($destinatarios, new DescuentoEspecialAprobado($descuento));

            \Log::info('✅ Notificaciones de aprobación enviadas a: ' . $destinatarios->pluck('email')->implode(', '));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de aprobación: ' . $e->getMessage());
        }
    }

    /**
     * 📧 Enviar notificación de deshabilitación
     */
    private function enviarNotificacionDeshabilitacion($descuento, $motivo)
    {
        try {
            $destinatarios = User::whereIn('email', [
                'smonopoli@trimaxperu.com',
                'planeamiento.comercial@trimaxperu.com',
                'auditor.junior@trimaxperu.com'  
            ])->get();

            if ($descuento->creador) {
                $destinatarios = $destinatarios->push($descuento->creador);
            }

            $destinatarios = $destinatarios->unique('id');

            Notification::send($destinatarios, new DescuentoEspecialDeshabilitado($descuento, $motivo));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de deshabilitación: ' . $e->getMessage());
        }
    }

    /**
     * 📧 Enviar notificación de rehabilitación
     */
    private function enviarNotificacionRehabilitacion($descuento, $motivo)
    {
        try {
            $destinatarios = User::whereIn('email', [
                'smonopoli@trimaxperu.com',
                'planeamiento.comercial@trimaxperu.com',
                'auditor.junior@trimaxperu.com'  
            ])->get();

            if ($descuento->creador) {
                $destinatarios = $destinatarios->push($descuento->creador);
            }

            $destinatarios = $destinatarios->unique('id');

            Notification::send($destinatarios, new DescuentoEspecialRehabilitado($descuento, $motivo));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de rehabilitación: ' . $e->getMessage());
        }
    }
}
