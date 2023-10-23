<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Traits;

use Closure;
use DigitalCreative\NovaDashboard\Card\NovaDashboard;
use DigitalCreative\NovaDashboard\Card\View;
use DigitalCreative\NovaDashboard\Card\Widget;
use Laravel\Nova\Http\Controllers\CardController;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

trait ResolveView
{
    public static function findView(NovaRequest $request, ?Closure $resolver = null): ?View
    {
        $viewKey = $request->input('view');

        if ($request->route()->getController() instanceof CardController && $resolver) {

            $cards = $resolver()->availableCards($request);

        } else if ($request->route('resource')) {

            $cards = $request->newResource()->availableCards($request);

        } else {

            $cards = Nova::allAvailableDashboardCards($request);

        }

        return $cards
            ->whereInstanceOf(NovaDashboard::class)
            ->flatMap(fn (NovaDashboard $dashboard) => $dashboard->meta[ 'views' ])
            ->firstWhere(fn (View $view) => !$viewKey || $view->key() === $viewKey);
    }

    public function findWidgetByKey(string $key): ?Widget
    {
        return $this
            ->widgets()
            ->firstWhere(fn (Widget $widget) => $widget->key() === $key);
    }

    public function resolveWidgetValue(NovaRequest $request, string $key): mixed
    {
        return $this->findWidgetByKey($key)?->resolveValue($request, $this);
    }
}
