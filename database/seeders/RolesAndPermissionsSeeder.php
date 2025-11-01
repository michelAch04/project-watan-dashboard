<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            
            // Financial Management
            'view_financial',
            'create_financial',
            'edit_financial',
            'delete_financial',
            'approve_financial',
            
            // Humanitarian Management
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'delete_humanitarian',
            'approve_humanitarian',
            
            // Zone Management
            'view_zones',
            'manage_zones',
            
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Reports
            'view_reports',
            'export_reports',
            
            // Settings
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin - Full access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Manager - Can manage financial and humanitarian in their zone
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view_dashboard',
            'view_financial',
            'create_financial',
            'edit_financial',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'view_reports',
        ]);

        // Viewer - Read-only access
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'view_dashboard',
            'view_financial',
            'view_humanitarian',
            'view_reports',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
    }
}