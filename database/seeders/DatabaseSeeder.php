<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Module;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "Starting Yuvirion CRM Seeder...\n";

        // Step 1: Create Modules
        $modules = [
            ['name' => 'Users', 'slug' => 'users'],
            ['name' => 'Clients', 'slug' => 'clients'],
            ['name' => 'Sales', 'slug' => 'sales'],
            ['name' => 'Tasks', 'slug' => 'tasks'],
            ['name' => 'Tickets', 'slug' => 'tickets'],
            ['name' => 'Reports', 'slug' => 'reports'],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(['slug' => $m['slug']], $m);
        }

        echo "Modules Created.\n";

        // Step 2: Create Permissions
        $actions = ['view', 'create', 'edit', 'delete'];
        foreach (Module::all() as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$module->slug}",
                    'guard_name' => 'web',
                    'module_id' => $module->id
                ]);
            }
        }

        echo "Permissions Created.\n";

        // Step 3: Create Super Admin Role
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->syncPermissions(Permission::all());

        echo "Super Admin Role Created.\n";

        // Step 4: Create Super Admin User
        $user = User::updateOrCreate(
            ['email' => 'admin@yuvirioncrm.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Rahul Kumar (Super Admin)',
                'password' => Hash::make('Admin@123'),
                'status' => 'active',
            ]
        );
        $user->assignRole('Super Admin');

        echo "Super Admin Created: admin@yuvirioncrm.com / Admin@123\n";

        // Step 5: Activity Log
        \DB::table('activity_logs')->insert([
            'log_name' => 'system',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'description' => 'CRM Setup Completed',
            'properties' => json_encode(['seed' => true]),
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Yuvirion CRM Ready!\n";
    }
}