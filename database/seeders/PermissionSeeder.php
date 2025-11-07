<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
            'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks',
            'view_sales', 'create_sales'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $client = Role::firstOrCreate(['name' => 'Client']);

        // Assign all to Super Admin
        $superAdmin->givePermissionTo(Permission::all());

        // Manager permissions
        $manager->givePermissionTo(['view_clients', 'view_tasks', 'create_tasks']);

        // Create Super Admin User
        $user = User::updateOrCreate(
            ['email' => 'admin@yuvirioncrm.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('Admin@123')
            ]
        );

        $user->assignRole('Super Admin');
    }
}