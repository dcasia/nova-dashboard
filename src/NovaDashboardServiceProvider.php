<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class NovaDashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event): void {

            Nova::script('nova-dashboard', __DIR__ . '/../dist/js/card.js');
            Nova::style('nova-dashboard', __DIR__ . '/../dist/css/card.css');

        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware([ 'nova' ])
            ->prefix('nova-vendor/nova-dashboard')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
