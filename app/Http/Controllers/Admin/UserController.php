<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private function getSedes(): array
    {
        return [
            'AREQUIPA'     => 'Arequipa',
            'ATE'          => 'Ate',
            'AYACUCHO'     => 'Ayacucho',
            'CAILLOMA'     => 'Cailloma',
            'CAJAMARCA'    => 'Cajamarca',
            'CHICLAYO'     => 'Chiclayo',
            'CHIMBOTE'     => 'Chimbote',
            'COMAS'        => 'Comas',
            'CUSCO'        => 'Cusco',
            'HUANCAYO'     => 'Huancayo',
            'HUARAZ'       => 'Huaraz',
            'HUÁNUCO'      => 'Huánuco',
            'ICA'          => 'Ica',
            'IQUITOS'      => 'Iquitos',
            'LINCE'        => 'Lince',
            'LOS OLIVOS'   => 'Los Olivos',
            'NAPO'         => 'Napo',
            'PIURA'        => 'Piura',
            'PUCALLPA'     => 'Pucallpa',
            'PUENTE PIEDRA' => 'Puente Piedra',
            'SJL'          => 'San Juan de Lurigancho',
            'SJM'          => 'San Juan de Miraflores',
            'SURQUILLO'    => 'Surquillo',
            'TACNA'        => 'Tacna',
            'TARAPOTO'     => 'Tarapoto',
            'TRUJILLO'     => 'Trujillo',
            'CALL CENTER'  => 'Call Center',
            'MONTURAS'     => 'Monturas',
        ];
    }

    /**
     * Campos de permisos especiales (comercial + rrhh).
     * DRY: se usa en store() y update() para no repetir la lista.
     */
    private function permisosEspeciales(): array
    {
        return [
            // Comercial
            'puede_ver_ventas_consolidadas',
            'puede_ver_descuentos_especiales',
            'puede_ver_consultar_orden',
            'puede_ver_acuerdos_comerciales',
            'puede_ver_lead_time',
            'puede_ver_pendiente_entrega_montura',
            'puede_ver_venta_clientes',
            'puede_ver_ordenes_x_sede',
            'puede_ver_asignacion_bases',
            // RRHH
            'puede_crear_requerimientos',
            'puede_gestionar_requerimientos',
            'puede_ver_todos_requerimientos',
            // Productividad Sedes
            'puede_ver_productividad_sedes',
            'puede_ver_productivy_total',
            // Tracking Motorizados
            'puede_ver_motorizados',
            // Retiros de Órdenes
            'puede_ver_retiros_ordenes',
            // Vouchers
            'puede_ver_vouchers',
            // Desbloqueo de clientes
            'puede_ver_desbloqueo',
        ];
    }

    public function create()
    {
        $roles = Role::all();
        $sedes = $this->getSedes();
        return view('admin.users-create', compact('roles', 'sedes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()->uncompromised()],
            'role'     => 'required|exists:roles,name',
        ];

        if ($request->role === 'sede') {
            $rules['sede'] = 'required|string|max:50';
        }

        $request->validate($rules);

        // Construir array de permisos desde la request
        $permisos = collect($this->permisosEspeciales())
            ->mapWithKeys(fn($campo) => [$campo => $request->boolean($campo)])
            ->toArray();

        $user = User::create(array_merge([
            'name'               => $request->name,
            'cargo'              => $request->cargo ?: null,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'sede'               => $request->role === 'sede' ? strtoupper($request->sede) : null,
            'is_active'          => true,
            'es_gerente_general' => $request->boolean('es_gerente_general'),
        ], $permisos));

        $user->assignRole($request->role);

        ActivityLogService::log(Auth::id(), 'create_user', 'User', $user->id, "Creó usuario: {$user->name} ({$user->email}) con rol {$request->role}");

        return redirect()->route('admin.users')
            ->with('success', "Usuario {$user->name} creado exitosamente.");
    }

    public function edit($id)
    {
        $user  = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        $sedes = $this->getSedes();
        return view('admin.users-edit', compact('user', 'roles', 'sedes'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'role'      => 'required|exists:roles,name',
            'password'  => ['nullable', 'confirmed', Password::min(10)->letters()->numbers()->uncompromised()],
            'is_active' => 'boolean',
        ];

        if ($request->role === 'sede') {
            $rules['sede'] = 'required|string|max:50';
        }

        $request->validate($rules);

        // Construir array de permisos desde la request
        $permisos = collect($this->permisosEspeciales())
            ->mapWithKeys(fn($campo) => [$campo => $request->boolean($campo)])
            ->toArray();

        $user->update(array_merge([
            'name'             => $request->name,
            'cargo'            => $request->cargo ?: null,
            'email'            => $request->email,
            'sede'             => $request->role === 'sede' ? strtoupper($request->sede) : null,
            'is_active'        => $request->boolean('is_active'),
            'es_gerente_general' => $request->boolean('es_gerente_general'),
        ], $permisos));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        ActivityLogService::log(Auth::id(), 'update_user', 'User', $user->id, "Actualizó usuario: {$user->name} ({$user->email}), rol: {$request->role}");

        return redirect()->route('admin.users')
            ->with('success', "Usuario {$user->name} actualizado exitosamente.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userId = $user->id;
        $user->delete();

        ActivityLogService::log(Auth::id(), 'delete_user', 'User', $userId, "Eliminó usuario: {$userName} ({$userEmail})");

        return back()->with('success', "Usuario {$userName} eliminado exitosamente.");
    }

    /**
     * Reinicia el 2FA de un usuario bloqueado (perdió el teléfono y se
     * quedó sin códigos de recuperación). Sin esto no había ninguna forma
     * de recuperar el acceso salvo un UPDATE directo en la base.
     */
    public function resetTwoFactor($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);

        ActivityLogService::log(Auth::id(), 'admin_reset_2fa', 'User', $user->id, "Reinició el 2FA de {$user->name} ({$user->email})");

        return back()->with('success', "2FA de {$user->name} reiniciado. Deberá configurarlo de nuevo en su próximo ingreso.");
    }
}
