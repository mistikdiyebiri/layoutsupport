<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Middleware'leri tanımla
        $this->app['router']->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', \App\Http\Middleware\PermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('role_or_permission', \App\Http\Middleware\RoleOrPermissionMiddleware::class);
        
        // Bootstrap sayfalayıcıyı kullan
        Paginator::useBootstrap();
    }
}
