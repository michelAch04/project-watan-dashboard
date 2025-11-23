<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->can('view_dashboard')) {
            // Get user's accessible features based on permissions
            $features = $this->getAccessibleFeatures($user);

            return view('dashboard.index', [
                'user' => $user,
                'features' => $features,
                'zone' => $user->zone
            ]);
        }
        else {
            return redirect()->route('errors.403');
        }
    }

    private function getAccessibleFeatures($user)
    {
        $features = [];

        // Requests Hub (if user can view any request type)
        if ($user->can('view_humanitarian') || $user->can('view_public') || $user->can('view_diapers')) {
            $features[] = [
                'name' => 'Requests',
                'description' => 'Manage all request types',
                'icon' => 'requests',
                'color' => 'madder',
                'route' => 'requests.index',
                'permissions' => [
                    'view' => true,
                    'create' => $user->can('create_humanitarian') || $user->can('create_public') || $user->can('create_diapers'),
                    'edit' => $user->can('edit_humanitarian') || $user->can('edit_public') || $user->can('edit_diapers'),
                ]
            ];
        }

        // Budgets (HOR and Admin only)
        if (($user->hasRole('hor') || $user->hasRole('admin')) && $user->can('view_budget')) {
            $features[] = [
                'name' => 'Budgets',
                'description' => 'Manage zone budgets and track expenses',
                'icon' => 'wallet',
                'color' => 'madder',
                'route' => 'budgets.index',
                'permissions' => [
                    'view' => true,
                    'create' => $user->can('create_budget'),
                    'edit' => $user->can('edit_budget'),
                ]
            ];
        }

        // User Management (Admin only)
        if ($user->can('view_users')) {
            $features[] = [
                'name' => 'Users',
                'description' => 'Manage system users',
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

        // PW Members Management
        if ($user->can('view_pw_members')) {
            $features[] = [
                'name' => 'PW Members',
                'description' => 'Manage project watan members',
                'icon' => 'members',
                'color' => 'madder',
                'route' => 'pw-members.index',
                'permissions' => [
                    'view' => true,
                    'create' => $user->can('create_pw_members'),
                    'edit' => $user->can('edit_pw_members'),
                ]
            ];
        }

        // Voters List
        if ($user->can('view_voters_list')) {
            $features[] = [
                'name' => 'Voters List',
                'description' => 'View registered voters database',
                'icon' => 'voters',
                'color' => 'madder',
                'route' => 'voters-list.index',
                'permissions' => [
                    'view' => true,
                ]
            ];
        }

        return $features;
    }
}
