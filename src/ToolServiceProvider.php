<?php

namespace DigitalCreative\NovaBi;

use DigitalCreative\NovaBi\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nova-widgets');

        /**
         * Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([ __DIR__ . '/../database/migrations' => database_path('migrations'), ], 'migrations');

        /**
         * Config
         */
        $this->publishes([ __DIR__ . '/../config/config.php' => config_path('nova-widgets.php') ], 'config');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {

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
             ->prefix('nova-vendor/nova-widgets')
             ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/config.php', 'nova-widgets');
    }
}
