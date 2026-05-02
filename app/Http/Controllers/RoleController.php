<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$role->name}\" creado correctamente.");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->name)[0]);

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255', "unique:roles,name,{$role->id}"],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.edit', $role)
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        abort_if(
            in_array($role->name, ['admin', 'user']),
            403,
            'Los roles base del sistema no pueden eliminarse.'
        );

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol \"{$role->name}\" eliminado.");
    }
}
