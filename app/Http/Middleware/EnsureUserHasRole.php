<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.show');
        }

        $user = Auth::user();
        
        // Support multiple roles separated by pipe (|)
        $requiredRoles = explode('|', $role);
        
        // Check if user has any of the required roles
        $hasRole = false;
        foreach ($user->roles as $userRole) {
            if (in_array($userRole->name, $requiredRoles)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            abort(403, 'Unauthorized - This action requires one of the following roles: ' . implode(', ', $requiredRoles));
        }

        return $next($request);
    }
}
