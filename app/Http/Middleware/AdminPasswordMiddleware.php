<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPasswordMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First, ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login.show');
        }

        // Check if user is admin using Spatie's hasRole method
        if (!Auth::user()->hasRole('admin')) {
            // Not an admin - show 403 error
            return response()->view('errors.403', [], 403);
        }

        // Skip password check for index page (viewing only)
        if ($request->is('users') && $request->isMethod('get')) {
            return $next($request);
        }

        // Skip password check for verify-password routes
        if ($request->is('users/verify-password') || $request->is('users/verify-password/*')) {
            return $next($request);
        }

        // Check if password verification is in session (valid for 5 minutes)
        if (session()->has('admin_password_verified') && 
            session('admin_password_verified') > now()->subMinutes(5)) {
            return $next($request);
        }

        // If not verified, show password prompt
        session(['intended_url' => $request->fullUrl()]);
        return redirect()->route('users.verify-password');
    }
}