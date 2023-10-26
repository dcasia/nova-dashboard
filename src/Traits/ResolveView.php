<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Traits;

use Closure;
use DigitalCreative\NovaDashboard\Card\NovaDashboard;
use DigitalCreative\NovaDashboard\Card\View;
use DigitalCreative\NovaDashboard\Card\Widget;
use DigitalCreative\NovaDashboard\Http\Controllers\WidgetController;
use Laravel\Nova\Http\Controllers\CardController;
use Laravel\Nova\Http\Controllers\DashboardController;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use RuntimeException;

trait ResolveView
{
    public static function findView(NovaRequest $request, ?Closure $resolver = null): ?View
    {
        $viewKey = $request->input('view');
        $controller = $request->route()->getController();

        $cards = match (true) {
            /**
             * When there is a "resource" param on the url, we are able to infer the Resource from it
             */
            !is_null($request->route('resource')) => $request->newResource()->availableCards($request),

            /**
             * If the dashboard is placed on a Nova Resource we need to find which resource was it
             * And retrieve its available cards
             */
            $controller instanceof CardController && $resolver => $resolver()->availableCards($request),

            /**
             * When it is nova dashboard, we retrieve the cards from global nova helper function
             */
            $controller instanceof WidgetController,
            $controller instanceof DashboardController => Nova::allAvailableDashboardCards($request),

            /**
             * ¯\_(ツ)_/¯
             */
            default => throw new RuntimeException('Unable to find dashboard card.'),
        };

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
        $widget = $this->findWidgetByKey($key);
        $widget?->configure($request);

        return $widget?->resolveValue($request, $this);
    }
}
