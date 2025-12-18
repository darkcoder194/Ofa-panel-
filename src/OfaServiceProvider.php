<?php

namespace DarkCoder\Ofa;

use Illuminate\Support\ServiceProvider;

class OfaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $base = dirname(__DIR__, 2);

            $this->publishes([
                $base . '/config/ofa.php' => config_path('ofa.php'),
            ], 'config');

            $this->publishes([
                $base . '/resources/views' => resource_path('views/vendor/ofa'),
                $base . '/resources/css' => public_path('css'),
                $base . '/resources/js' => resource_path('js/vendor/ofa'),
            ], 'ofa-assets');

            $this->loadMigrationsFrom($base . '/database/migrations');

            $this->commands([
                \DarkCoder\Ofa\Console\InstallOfaCommand::class,
            ]);
        }

        // Load routes and views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'ofa');
        $routesPath = __DIR__ . '/../../routes/ofa.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // Register middleware alias for admin-only routes
        $router = $this->app['router'];
        $router->aliasMiddleware('ofa.admin', \DarkCoder\Ofa\Http\Middleware\EnsureOfaAdmin::class);

        // You can add translation loading here in the future
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $path = dirname(__DIR__, 2) . '/config/ofa.php';
        if (file_exists($path)) {
            $this->mergeConfigFrom($path, 'ofa');
        }
    }
}
