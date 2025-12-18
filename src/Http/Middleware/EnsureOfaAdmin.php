<?php

namespace DarkCoder\Ofa\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOfaAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Common Pterodactyl admin property or attribute
        if ((property_exists($user, 'root_admin') || isset($user->root_admin)) && $user->root_admin) {
            return $next($request);
        }

        // Common method check
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        // Roles array or relation
        if (isset($user->roles) && is_array($user->roles) && in_array('admin', $user->roles)) {
            return $next($request);
        }

        abort(403);
    }
}
