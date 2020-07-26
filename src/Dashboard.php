<?php

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Collection;
use JsonSerializable;
use RuntimeException;

abstract class Dashboard implements JsonSerializable
{

    use DashboardTrait;

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
    public function resolveValue(string $viewKey, string $widgetKey, Collection $options, Filters $filters): ValueResult
    {

        if ($view = $this->findViewByKey($viewKey)) {

            return $view->resolveWidgetValue($widgetKey, $options, $filters);

        }

        abort(404, __('View :view not found.', [ 'view' => $viewKey ]));

    }

    public function findViewByKey(string $key): ?View
    {
        return $this->resolveViews()->first(function (View $view) use ($key) {
            return $view->uriKey() === $key;
        });
    }

    public function resolveActiveView(): array
    {

        /**
         * @todo figure out a way to let user set which should be the default view
         * @var View $view
         */
        $view = $this->resolveViews()->first();

        if ($view) {

            return [
                'uriKey' => $view->uriKey(),
                'data' => $view->resolveData(),
            ];

        }

        throw new RuntimeException('No view defined. Create one by defining a views() method on your dashboard class.');

    }

    public function resolveViews(): Collection
    {
        return once(function () {
            return collect($this->views())
                ->filter(function (View $view) {
                    return $view->authorizedToSee(request());
                })
                ->each
                ->setDashboard($this->uriKey())
                ->values();
        });
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title(),
            'uriKey' => $this->uriKey(),
            'options' => $this->options(),
            'activeViewData' => $this->resolveActiveView(),
            'views' => $this->resolveViews(),
        ];
    }

}
