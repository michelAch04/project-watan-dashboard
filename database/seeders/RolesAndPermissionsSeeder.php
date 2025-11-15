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
            
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Inbox
            'view_inbox',

            // PW Members Management
            'view_pw_members',
            'create_pw_members',
            'edit_pw_members',
            'delete_pw_members',

            // Voters List
            'view_voters_list',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin - Full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // HOR (Head of Region) - Full access to their zone
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
            'view_inbox',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_pw_members',
            'create_pw_members',
            'edit_pw_members',
            'delete_pw_members',
            'view_voters_list',
        ]);

        // GS (General Secretary) - Can manage but not final approve
        $gs = Role::firstOrCreate(['name' => 'gs']);
        $gs->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'approve_humanitarian',
            'view_inbox',
            'view_pw_members',
            'view_voters_list',
        ]);

        // S (Secretary) - Basic access
        $s = Role::firstOrCreate(['name' => 's']);
        $s->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'view_inbox',
            'view_pw_members',
            'view_voters_list',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
    }
}