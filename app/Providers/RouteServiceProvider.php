<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
        
        // Debug için - Hata ayıklama için kritik rotaları logla
        if (app()->environment('local')) {
            $this->app->booted(function () {
                $this->logDebugRouteInfo();
            });
        }
    }
    
    /**
     * Hata ayıklama için route bilgilerini logla
     */
    protected function logDebugRouteInfo()
    {
        try {
            // Kritik rotaları logla
            $routes = collect(Route::getRoutes())->filter(function ($route) {
                return str_contains($route->getName() ?? '', 'tickets.assigned');
            })->map(function ($route) {
                return [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'methods' => $route->methods(),
                    'action' => $route->getActionName(),
                    'middleware' => $route->middleware(),
                ];
            })->toArray();

            \Log::info('Debug ticket assigned routes', ['routes' => $routes]);
        } catch (\Exception $e) {
            \Log::error('Error logging route info', [
                'error' => $e->getMessage()
            ]);
        }
    }
} 