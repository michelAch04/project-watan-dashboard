<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's accessible features based on permissions
        $features = $this->getAccessibleFeatures($user);
        
        return view('dashboard.index', [
            'user' => $user,
            'features' => $features,
            'zone' => $user->zone
        ]);
    }

    private function getAccessibleFeatures($user)
    {
        $features = [];

        // Financial Management
        if ($user->can('view_financial')) {
            $features[] = [
                'name' => 'Financial',
                'description' => 'Manage donations and expenses',
                'icon' => 'money',
                'color' => 'madder',
                'route' => 'financial.index',
                'permissions' => [
                    'view' => $user->can('view_financial'),
                    'create' => $user->can('create_financial'),
                    'edit' => $user->can('edit_financial'),
                ]
            ];
        }

        // Humanitarian Management
        if ($user->can('view_humanitarian')) {
            $features[] = [
                'name' => 'Humanitarian',
                'description' => 'Track aid and assistance',
                'icon' => 'heart',
                'color' => 'madder',
                'route' => 'humanitarian.index',
                'permissions' => [
                    'view' => $user->can('view_humanitarian'),
                    'create' => $user->can('create_humanitarian'),
                    'edit' => $user->can('edit_humanitarian'),
                ]
            ];
        }

        // Reports
        if ($user->can('view_reports')) {
            $features[] = [
                'name' => 'Reports',
                'description' => 'View analytics and summaries',
                'icon' => 'chart',
                'color' => 'madder',
                'route' => 'reports.index',
                'permissions' => [
                    'view' => true,
                    'export' => $user->can('export_reports'),
                ]
            ];
        }

        // User Management (Admin/Manager only)
        if ($user->can('view_users')) {
            $features[] = [
                'name' => 'Users',
                'description' => 'Manage team members',
                'icon' => 'users',
                'color' => 'madder',
                'route' => 'users.index',
                'permissions' => [
                    'view' => true,
                    'create' => $user->can('create_users'),
                    'edit' => $user->can('edit_users'),
                ]
            ];
        }

        // Zones (Admin only)
        if ($user->can('view_zones')) {
            $features[] = [
                'name' => 'Zones',
                'description' => 'Manage geographic areas',
                'icon' => 'location',
                'color' => 'madder',
                'route' => 'zones.index',
                'permissions' => [
                    'view' => true,
                    'manage' => $user->can('manage_zones'),
                ]
            ];
        }

        // Settings
        if ($user->can('manage_settings')) {
            $features[] = [
                'name' => 'Settings',
                'description' => 'System configuration',
                'icon' => 'settings',
                'color' => 'madder',
                'route' => 'settings.index',
                'permissions' => [
                    'manage' => true,
                ]
            ];
        }

        return $features;
    }
}