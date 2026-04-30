<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios con sus roles.
     */
    public function index()
    {
        $users = User::with('roles')
            ->withCount('permissions')
            ->latest()
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $roles       = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Guarda un nuevo usuario.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'roles'       => ['nullable', 'array'],
            'roles.*'     => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return redirect()->route('users.index')
            ->with('success', "Usuario {$user->name} creado correctamente.");
    }

    /**
     * Formulario de edición.
     */
    public function edit(User $user)
    {
        $roles       = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0]);

        $userRoles       = $user->roles->pluck('name')->toArray();
        $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        return view('users.edit', compact('user', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    /**
     * Actualiza datos del usuario.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return redirect()->route('users.edit', $user)
            ->with('success', 'Datos del usuario actualizados.');
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'No puedes eliminarte a ti mismo.');

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Usuario {$user->name} eliminado.");
    }

    /**
     * Asigna / revoca roles al usuario.
     */
    public function syncRoles(Request $request, User $user)
    {
        $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->syncRoles($request->input('roles', []));

        return redirect()->route('users.edit', $user)
            ->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Asigna / revoca permisos directos al usuario.
     * Los permisos heredados por roles NO se tocan aquí.
     */
    public function syncPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('users.edit', $user)
            ->with('success', 'Permisos directos actualizados.');
    }
}
