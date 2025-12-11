<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Gère la requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Vérifie si l'utilisateur est connecté
        if (!Auth::check()) {
            // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
            return redirect('/login');
        }

        // 2. Vérifie le rôle de l'utilisateur connecté
        // Assurez-vous que la colonne 'role' existe dans votre table 'users'
        if (Auth::User()->role === 'admin') {
            // Si le rôle est 'admin', la requête continue vers la route demandée
            return $next($request);
        }

        // 3. Redirection si l'accès est refusé
        // Si l'utilisateur est connecté mais n'est pas 'admin', redirige vers le tableau de bord
        // ou affiche une erreur 403 (Accès interdit)
        return redirect('/dashboard')->with('error', 'Accès non autorisé. Seuls les administrateurs peuvent accéder à cette page.');
        
        // Ou pour une réponse 403:
        // abort(403, 'Accès interdit. Vous n\'êtes pas administrateur.');
    }
}