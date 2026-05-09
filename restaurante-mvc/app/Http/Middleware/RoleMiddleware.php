<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado'], 401);
            }
            return redirect()->route('login');
        }

        $userRole = is_string($user->role) ? strtolower(trim($user->role)) : '';

        $roles = [
            'admin' => 3,
            'manager' => 2,
            'employee' => 1,
        ];

        $requiredLevel = $roles[$role] ?? 0;
        $userLevel = $roles[$userRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permiso para acceder a este recurso. Se requiere nivel: ' . $role,
                ], 403);
            }
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
