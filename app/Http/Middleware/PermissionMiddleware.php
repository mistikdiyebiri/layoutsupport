<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission, $guard = null)
    {
        if (! $request->user()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! $request->user()->hasPermissionTo($permission, $guard)) {
            throw UnauthorizedException::forPermissions([$permission]);
        }

        return $next($request);
    }
} 