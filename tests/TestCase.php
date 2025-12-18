<?php

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use DarkCoder\Ofa\OfaServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [OfaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use in-memory SQLite for tests
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        // Provide a valid application key for encryption
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Load Laravel's default migrations (users table, etc.) and then package migrations
        $this->loadLaravelMigrations();

        // Load and run migrations from package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load and run any test-specific migrations (e.g., user flags)
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->artisan('migrate')->run();

        // Ensure package routes are loaded for tests when automatic discovery doesn't run
        $routesPath = dirname(__DIR__, 1) . '/routes/ofa.php';
        if (file_exists($routesPath)) {
            require $routesPath;
        }

        // Provide a minimal login route to satisfy auth middleware redirects during tests
        \Route::get('/login', function () {
            return 'login';
        })->name('login');

        // Ensure view namespace and default resources path are available in tests
        $this->app['view']->addNamespace('ofa', dirname(__DIR__, 1) . '/resources/views');
        $this->app['view']->addLocation(dirname(__DIR__, 1) . '/resources/views');
    }
}
