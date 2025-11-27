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

            // Public Requests Management
            'view_public',
            'create_public',
            'edit_public',
            'delete_public',
            'approve_public',
            'final_approve_public',
            'mark_ready_public',
            'mark_collected_public',

            // Diapers Requests Management
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'delete_diapers',
            'approve_diapers',
            'final_approve_diapers',
            'mark_ready_diapers',
            'mark_collected_diapers',

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

        // FC (Financial Controller) - Can manipulate budgets of all zones and see requests of all zones, plus HOR base permissions
        $fc = Role::firstOrCreate(['name' => 'fc']);
        $fc->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'delete_humanitarian',
            'approve_humanitarian',
            'final_approve_humanitarian',
            'mark_ready_humanitarian',
            'mark_collected_humanitarian',
            'view_public',
            'create_public',
            'edit_public',
            'delete_public',
            'approve_public',
            'final_approve_public',
            'mark_ready_public',
            'mark_collected_public',
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'delete_diapers',
            'approve_diapers',
            'final_approve_diapers',
            'mark_ready_diapers',
            'mark_collected_diapers',
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
            'view_public',
            'create_public',
            'edit_public',
            'delete_public',
            'approve_public',
            'final_approve_public',
            'mark_ready_public',
            'mark_collected_public',
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'delete_diapers',
            'approve_diapers',
            'final_approve_diapers',
            'mark_ready_diapers',
            'mark_collected_diapers',
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

        // HOZ (Head of Zone) - Same permissions as GS, between HOR and GS in hierarchy
        $hoz = Role::firstOrCreate(['name' => 'hoz']);
        $hoz->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'approve_humanitarian',
            'view_public',
            'create_public',
            'edit_public',
            'approve_public',
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'approve_diapers',
            'view_inbox',
            'view_pw_members',
            'create_pw_members',
            'edit_pw_members',
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
            'view_public',
            'create_public',
            'edit_public',
            'approve_public',
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'approve_diapers',
            'view_inbox',
            'view_pw_members',
            'create_pw_members',
            'edit_pw_members',
            'view_voters_list',
        ]);

        // S (Secretary) - Basic access
        $s = Role::firstOrCreate(['name' => 's']);
        $s->syncPermissions([
            'view_dashboard',
            'view_humanitarian',
            'create_humanitarian',
            'edit_humanitarian',
            'view_public',
            'create_public',
            'edit_public',
            'view_diapers',
            'create_diapers',
            'edit_diapers',
            'view_inbox',
            'view_pw_members',
            'view_voters_list',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
    }
}
