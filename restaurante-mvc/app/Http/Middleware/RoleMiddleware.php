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
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;
        $safeRole = is_string($userRole) ? strtolower(trim($userRole)) : '';

        $roles = [
            'admin' => 3,
            'manager' => 2,
            'employee' => 1,
        ];

        $requiredLevel = $roles[$role] ?? 0;
        $userLevel = $roles[$safeRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
