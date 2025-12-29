<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordUpdate
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur est connecté et que update_password est à 1
        if (Auth::check() && Auth::user()->update_password == 1) {
            // Permettre l'accès uniquement à la route de changement de code et à la soumission du formulaire
            if (!$request->is('password/update-required') && !$request->is('password/update-store')) {
                return redirect()->route('password.update.required')
                    ->with('warning', 'Vous devez changer votre code de connexion pour continuer.');
            }
        }

        return $next($request);
    }
}