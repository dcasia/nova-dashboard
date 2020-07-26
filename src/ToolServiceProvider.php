<?php

namespace DigitalCreative\NovaDashboard;

use DigitalCreative\NovaDashboard\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nova-dashboard');

        /**
         * Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([ __DIR__ . '/../database/migrations' => database_path('migrations'), ], 'migrations');

        /**
         * Config
         */
        $this->publishes([ __DIR__ . '/../config/config.php' => config_path('nova-dashboard.php') ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware([ 'nova', Authorize::class ])
             ->prefix('nova-vendor/nova-dashboard')
             ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/config.php', 'nova-dashboard');
    }
}
