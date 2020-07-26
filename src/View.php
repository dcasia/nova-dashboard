<?php

namespace DigitalCreative\NovaDashboard;

use DigitalCreative\NovaDashboard\Models\Widget as WidgetModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\Contracts\Filter as FilterContract;
use Laravel\Nova\Http\Requests\NovaRequest;

class View implements JsonSerializable
{

    use DashboardTrait;

    private string $dashboardKey;
    private bool $private = false;

    public function setDashboard(string $dashboardKey): self
    {
        $this->dashboardKey = $dashboardKey;

        return $this;
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function widgets(): array
    {
        return [];
    }

    public function resolveWidgetValue(string $widgetKey, Collection $options, Filters $filters): ValueResult
    {

        if ($widget = $this->findWidgetByKey($widgetKey)) {

            return $widget->resolveValue($options, $filters);

        }

        abort(404, __('Widget :widget not found.', [ 'widget' => $widgetKey ]));

    }

    public function findWidgetByKey(string $key): ?Widget
    {
        return $this->resolveWidgets()->first(function (Widget $widget) use ($key) {
            return $widget->uriKey() === $key;
        });
    }

    public function findActionByKey(string $key): ?Action
    {
        return $this->resolveActions()->first(function (Action $action) use ($key) {
            return $action->uriKey() === $key;
        });
    }

    /**
     * When set to true only who created the widgets are able to see it
     *
     * @param bool $isPrivate
     *
     * @return $this
     */
    public function private(bool $isPrivate = true): self
    {
        $this->private = $isPrivate;

        return $this;
    }

    /**
     * @param bool|callable $editable
     *
     * @return $this
     */
    public function editable($editable = true): self
    {
        return $this->withMeta([ 'editable' => $editable ]);
    }

    public function isEditable(): bool
    {
        return $this->meta('editable');
    }

    public function resolveFilters(): Collection
    {
        return once(function () {
            return collect($this->filters())->filter(function (FilterContract $filter) {
                return $filter->authorizedToSee(request());
            });
        });
    }

    private function resolveActions(): Collection
    {
        return once(function () {
            return collect($this->actions())->filter(function (Action $action) {
                return $action->authorizedToSee(request());
            });
        });
    }

    private function resolveWidgets(): Collection
    {
        return once(function () {
            return collect($this->widgets())->filter(function (Widget $widget) {
                return $widget->authorizedToSee(request());
            });
        });
    }

    public function resolveDataFromPreset(): Collection
    {
        return $this->resolveWidgets()
                    ->map(function (Widget $widget) {

                        if ($preset = $widget->preset) {

                            return $widget->hydrateFromPreset($preset);

                        }

                    });
    }

    public function resolveData(): Collection
    {

        $widgets = $this->resolveWidgets();

        /**
         * @var Collection $withPresets
         * @var Collection $withoutPresets
         */
        [ $withPresets, $withoutPresets ] = $widgets->partition(function (Widget $widget) {
            return $widget->preset;
        });

        $fromPreset = $withPresets->map(function (Widget $widget) {
            return $widget->hydrateFromPreset($widget->preset);
        });

        $keys = $withoutPresets
            ->map(function (Widget $widget) {
                return $widget->uriKey();
            })
            ->unique();

        /**
         * @var Builder $query
         */
        $modelClass = config('nova-dashboard.widget_model');
        $query = $modelClass::query();

        $fromDatabase = $query->where('dashboard', $this->dashboardKey)
                              ->where('view', $this->uriKey())
                              ->whereIn('key', $keys)
                              ->when($this->private, function (Builder $builder) {
                                  $builder->where('user_id', resolve(NovaRequest::class)->user()->id);
                              })
                              ->get()
                              ->map(function (WidgetModel $model) {

                                  if ($widget = $this->findWidgetByKey($model->key)) {

                                      return (clone $widget)->hydrate($model);

                                  }

                                  return null;

                              })
                              ->filter();

        return $fromDatabase->concat($fromPreset);

    }

    public function resolveDataFromDatabase(): Collection
    {

        /**
         * @var Builder $query
         */
        $modelClass = config('nova-dashboard.widget_model');
        $query = $modelClass::query();

        return $query->where('dashboard', $this->dashboardKey)
                     ->where('view', $this->uriKey())
                     ->get()
                     ->map(function (WidgetModel $model) {

                         if ($widget = $this->findWidgetByKey($model->key)) {

                             return (clone $widget)->hydrate($model);

                         }

                         return null;

                     })
                     ->filter();

    }

    private function resolveSchemas(): Collection
    {
        return $this->resolveWidgets()
                    ->mapWithKeys(function (Widget $widget) {
                        return [
                            $widget->uriKey() => $widget->getSchema(),
                        ];
                    });
    }

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title(),
            'uriKey' => $this->uriKey(),
            'filters' => $this->resolveFilters(),
            'actions' => $this->resolveActions(),
            'schemas' => $this->resolveSchemas(),
            'meta' => $this->meta(),
        ];
    }
}
