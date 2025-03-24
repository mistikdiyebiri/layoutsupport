<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role, $permission = null)
    {
        if (! $request->user()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! $request->user()->hasRole($role)) {
            throw UnauthorizedException::forRoles([$role]);
        }

        if ($permission && ! $request->user()->can($permission)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
} 