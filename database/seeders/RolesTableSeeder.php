<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-dashboard',
            'import-data',
            'export-data',
            'manage-kpis',
            'view-reports',
            'generate-reports',
            'manage-companies',
            'manage-users',
            'manage-teams',
            'view-analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin role - full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Manager role - can manage data and view reports
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'view-dashboard',
            'import-data',
            'export-data',
            'manage-kpis',
            'view-reports',
            'generate-reports',
            'manage-companies',
            'view-analytics',
        ]);

        // Viewer role - read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'view-dashboard',
            'view-reports',
            'view-analytics',
        ]);
    }
}
