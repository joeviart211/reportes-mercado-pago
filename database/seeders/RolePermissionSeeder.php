<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        
        Permission::firstOrCreate(['name' => 'users.view']);
        Permission::firstOrCreate(['name' => 'users.create']);
        Permission::firstOrCreate(['name' => 'users.update']);
        Permission::firstOrCreate(['name' => 'users.delete']);
        Permission::firstOrCreate(['name' => 'users.manage']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo(['users.view', 'users.create', 'users.update']); 

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo();

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('changeme123'),
            ]
        );
        $adminUser->assignRole($admin);
    }
}

