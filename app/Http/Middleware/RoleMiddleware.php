<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::guard('sanctum')->user();

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden: You do not have access to this resource'
            ], 403);
        }

        return $next($request);
    }
}
