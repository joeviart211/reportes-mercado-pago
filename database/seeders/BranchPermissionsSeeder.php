<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BranchPermissionsSeeder extends Seeder
{
    public function run()
    {

        $permissions = [
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin   = Role::where('name', 'admin')->first();
        $manager = Role::where('name', 'manager')->first();
        $user    = Role::where('name', 'user')->first();

        if ($user) {
            $user->revokePermissionTo($permissions);
        }

        if ($manager) {
            $manager->syncPermissions(array_merge(
                $manager->permissions->pluck('name')->toArray(), 
                $permissions                                      
            ));
        }

        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }
    }
}
