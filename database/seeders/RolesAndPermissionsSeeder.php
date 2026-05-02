<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos de sucursales ──────────────────────────────────────
        $all = [
            'branches.index',
            'branches.show',
            'branches.create',
            'branches.edit',
            'branches.delete',
        ];

        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // ── Rol admin: acceso total ─────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($all);

        // ── Rol user: solo lectura ──────────────────────────────────────
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'branches.index',
            'branches.show',
        ]);
    }
}
