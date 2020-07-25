<?php

namespace DigitalCreative\NovaBi\Dashboards;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Widgets\Action;
use DigitalCreative\NovaBi\Widgets\TitleableTrait;
use DigitalCreative\NovaBi\Widgets\Value;
use DigitalCreative\NovaBi\Widgets\View;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\AuthorizedToSee;
use Laravel\Nova\ProxiesCanSeeToGate;

abstract class Dashboard implements JsonSerializable
{

    use AuthorizedToSee;
    use ProxiesCanSeeToGate;
    use TitleableTrait;

    public function options(): array
    {
        return [];
    }

    public function views(): array
    {
        return [];
    }

    /**
     * @param string $viewKey
     * @param string $widgetKey
     * @param Collection $options
     * @param Filters $filters
     *
     * @return mixed
     */
    public function resolveValue(string $viewKey, string $widgetKey, Collection $options, Filters $filters): Value
    {

        if ($view = $this->findViewByKey($viewKey)) {

            return $view->resolveWidgetValue($widgetKey, $options, $filters);

        }

        abort(404, __('View :view not found.', [ 'view' => $viewKey ]));

    }

    public function findViewByKey(string $key): ?View
    {
        return $this->resolveViews()->first(function (View $view) use ($key) {
            return $view::uriKey() === $key;
        });
    }

    public function findActionByKey(string $key): ?Action
    {
        return collect($this->actions())->first(function (Action $action) use ($key) {
            return $action->uriKey() === $key;
        });
    }

    public function resolveActiveView(): array
    {

        /**
         * @todo figure out a way to let user set which should be the default view
         * @var View $view
         */
        $view = $this->resolveViews()->first();

        if ($view->isEditable()) {

            return [
                'uriKey' => $view::uriKey(),
                'data' => $view->resolveDataFromDatabase(),
            ];

        }

        return [
            'uriKey' => $view::uriKey(),
            'data' => $view->resolveData(),
        ];

    }

    private function resolveViews(): Collection
    {
        return once(function () {
            return collect($this->views())
                ->filter(function (View $view) {
                    return $view->authorizedToSee(request());
                })
                ->each
                ->setDashboard(self::uriKey())
                ->values();
        });
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title(),
            'uriKey' => static::uriKey(),
            'options' => $this->options(),
            'activeViewData' => $this->resolveActiveView(),
            'views' => $this->resolveViews(),
        ];
    }

}
