<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verifica que el usuario autenticado tenga el rol requerido.
     *
     * Uso en rutas: ->middleware('role:director,utp')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // El usuario debe estar autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar si tiene alguno de los roles permitidos
        if (!in_array($user->rol, $roles)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}