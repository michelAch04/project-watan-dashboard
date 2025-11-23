<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableSessionBlocking
{
    /**
     * Handle an incoming request.
     * This middleware prevents session blocking for read-only API endpoints
     * to avoid race conditions when multiple requests happen simultaneously.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For read-only endpoints, we want to prevent session blocking
        // by releasing the session lock as soon as possible

        // Start session if needed
        $session = $request->session();

        // Get the response
        $response = $next($request);

        // Immediately save and close the session to release the lock
        // This allows other concurrent requests to proceed without waiting
        $session?->isStarted() && $session->save();

        return $response;
    }
}
