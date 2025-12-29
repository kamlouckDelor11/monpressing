<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status === 'inactive') {
            // On autorise l'accès uniquement au dashboard (pour voir le message) et au logout
            if ($request->routeIs('dashboard') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}