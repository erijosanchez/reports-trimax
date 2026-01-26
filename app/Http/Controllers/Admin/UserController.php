<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Lista de sedes disponibles
    private function getSedes()
    {
        return [
            'AREQUIPA' => 'Arequipa',
            'ATE' => 'Ate',
            'AYACUCHO' => 'Ayacucho',
            'CAILLOMA' => 'Cailloma',
            'CAJAMARCA' => 'Cajamarca',
            'CHICLAYO' => 'Chiclayo',
            'CHIMBOTE' => 'Chimbote',
            'COMAS' => 'Comas',
            'CUSCO' => 'Cusco',
            'HUANCAYO' => 'Huancayo',
            'HUARAZ' => 'Huaraz',
            'HUÁNUCO' => 'Huánuco',
            'ICA' => 'Ica',
            'IQUITOS' => 'Iquitos',
            'LINCE' => 'Lince',
            'LOS OLIVOS' => 'Los Olivos',
            'NAPO' => 'Napo',
            'PIURA' => 'Piura',
            'PUCALLPA' => 'Pucallpa',
            'PUENTE PIEDRA' => 'Puente Piedra',
            'SJL' => 'San Juan de Lurigancho',
            'SJM' => 'San Juan de Miraflores',
            'SURQUILLO' => 'Surquillo',
            'TACNA' => 'Tacna',
            'TARAPOTO' => 'Tarapoto',
            'TRUJILLO' => 'Trujillo',
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ];

        // Si el rol es 'sede', la sede es obligatoria
        if ($request->role === 'sede') {
            $rules['sede'] = 'required|string|max:50';
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sede' => $request->role === 'sede' ? strtoupper($request->sede) : null,
            'puede_ver_ventas_consolidadas' => $request->boolean('puede_ver_ventas_consolidadas'),
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        $sedes = $this->getSedes();

        return view('admin.users-edit', compact('user', 'roles', 'sedes'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validación
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|min:8|confirmed',
            'is_active' => 'boolean',
        ];

        // Si el rol es 'sede', la sede es obligatoria
        if ($request->role === 'sede') {
            $rules['sede'] = 'required|string|max:50';
        }

        $request->validate($rules);

        // Actualizar datos básicos
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'sede' => $request->role === 'sede' ? strtoupper($request->sede) : null,
            'puede_ver_ventas_consolidadas' => $request->boolean('puede_ver_ventas_consolidadas'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Actualizar contraseña SOLO si se proporcionó una nueva
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Actualizar rol
        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevenir auto-eliminación
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminarte a ti mismo']);
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado exitosamente');
    }
}
