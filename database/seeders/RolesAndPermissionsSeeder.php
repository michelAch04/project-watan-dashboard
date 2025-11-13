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
            'final_approve_humanitarian',
            'mark_ready_humanitarian',
            'mark_collected_humanitarian',

            // Budget Management
            'view_budget',
            'create_budget',
            'edit_budget',
            'delete_budget',

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
            
            // Inbox
            'view_inbox',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin - Full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // HOR (Head of Region) - Full access except user management
        $hor = Role::firstOrCreate(['name' => 'hor']);
        $hor->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'delete_humanitarian',
            'approve_humanitarian',
            'final_approve_humanitarian',
            'mark_ready_humanitarian',
            'mark_collected_humanitarian',
            'view_budget',
            'create_budget',
            'edit_budget',
            'delete_budget',
            'view_financial',
            'create_financial',
            'edit_financial',
            'delete_financial',
            'approve_financial',
            'view_reports',
            'export_reports',
            'view_zones',
            'manage_zones',
            'view_inbox',
            'manage_settings',
        ]);

        // GS (General Secretary) - Can manage but not final approve
        $gs = Role::firstOrCreate(['name' => 'gs']);
        $gs->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'approve_humanitarian',
            'view_financial',
            'create_financial',
            'edit_financial',
            'view_reports',
            'view_inbox',
        ]);

        // S (Secretary) - Basic access
        $s = Role::firstOrCreate(['name' => 's']);
        $s->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'view_financial',
            'view_reports',
            'view_inbox',
        ]);

        // Manager - Can manage financial and humanitarian in their zone
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view_dashboard',
            'view_financial',
            'create_financial',
            'edit_financial',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'approve_humanitarian',
            'view_reports',
            'view_inbox',
        ]);

        // Viewer - Read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view_dashboard',
            'view_financial',
            'view_humanitarian',
            'view_reports',
            'view_inbox',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
    }
}