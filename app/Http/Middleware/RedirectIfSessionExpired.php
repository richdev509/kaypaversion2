<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSessionExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur doit être authentifié
        if ($request->expectsJson()) {
            // Pour les requêtes AJAX, retourner 401
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Session expirée. Veuillez vous reconnecter.',
                    'redirect' => route('login')
                ], 401);
            }
        } else {
            // Pour les requêtes normales, rediriger vers login
            if (!Auth::check() && !$request->routeIs('login') && !$request->routeIs('register')) {
                return redirect()->route('login')->with('info', 'Votre session a expiré. Veuillez vous reconnecter.');
            }
        }

        return $next($request);
    }
}
