<?php

namespace DigitalCreative\NovaBi\Dashboards;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Models\WidgetModel;
use DigitalCreative\NovaBi\Widgets\Action;
use DigitalCreative\NovaBi\Widgets\Value;
use DigitalCreative\NovaBi\Widgets\Widget;
use DigitalCreative\NovaBi\Widgets\WidgetPreset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Laravel\Nova\AuthorizedToSee;
use Laravel\Nova\ProxiesCanSeeToGate;

abstract class Dashboard implements JsonSerializable
{

    use AuthorizedToSee;
    use ProxiesCanSeeToGate;

    private array $presets = [];

    public static function humanize(string $value): string
    {
        return Str::title(Str::snake($value, ' '));
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return static::humanize(class_basename(static::class));
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function title(): string
    {
        return static::$title ?? static::humanize(class_basename(static::class));
    }

    public function subtitle(): ?string
    {
        return static::$subtitle ?? null;
    }

    public function filters(): array
    {
        return [];
    }

    public function widgets(): array
    {
        return [];
    }

    public function options(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function resolveDataFromPresets(array $availableFilters): array
    {

        $collection = [];

        /**
         * @var WidgetPreset $preset
         */
        foreach ($this->presets as $preset) {

            $collection[] = $preset->instantiate($availableFilters);

        }

        return $collection;

    }

    public function resolveDataFromDatabase(array $availableFilters): Collection
    {

        /**
         * @var Builder $query
         */
        $modelClass = config('nova-widgets.widget_model');
        $query = $modelClass::query();

        return $query->where('dashboard', self::uriKey())
                     ->get()
                     ->map(function (WidgetModel $model) use ($availableFilters) {

                         if ($widget = $this->findWidgetByKey($model->key)) {

                             return $widget::fromModel($model, $availableFilters);

                         }

                         return null;

                     })
                     ->filter();

    }

    public function resolveFilters(): array
    {
        return $this->filters();
    }

    /**
     * @param string $key
     * @param Collection $options
     * @param Filters $filters
     *
     * @return mixed
     */
    public function resolveValue(string $key, Collection $options, Filters $filters): Value
    {

        if ($widget = $this->findWidgetByKey($key)) {

            return $widget->resolveValue($options, $filters);

        }

        return new Value(0);

    }

    public function findActionByKey(string $key): ?Action
    {
        return collect($this->actions())->first(function (Action $action) use ($key) {
            return $action->uriKey() === $key;
        });
    }

    public function findWidgetByKey(string $key): ?Widget
    {
        return collect($this->getFlattenedWidgets())->firstWhere('key', $key);
    }

    public function getFlattenedWidgets(): Collection
    {
        return once(function () {

            return collect($this->widgets())->map(function ($widget) {

                return $widget instanceof WidgetPreset ? $widget->widget : $widget;

            });

        });
    }

    public function jsonSerialize(): array
    {

        $filters = $this->resolveFilters();
        $widgets = $this->resolveWidgets();
        $usePreset = filled($this->presets);

        return [
            'title' => $this->title(),
            'subtitle' => $this->subtitle(),
            'filters' => $filters,
            'data' => $usePreset ? $this->resolveDataFromPresets($filters) : $this->resolveDataFromDatabase($filters),
            'usePreset' => $usePreset,
            'widgets' => $widgets,
            'options' => $this->options(),
            'actions' => $this->actions(),
        ];

    }

    private function resolveWidgets(): array
    {

        $widgets = [];
        $uri = self::uriKey();

        /**
         * @var Widget|WidgetPreset $widget
         */
        foreach ($this->widgets() as $widget) {

            if ($widget instanceof WidgetPreset) {

                $this->presets[] = $widget;
                $widget = $widget->widget;

            }

            $widgetClassName = get_class($widget);

            /**
             * Ignore duplicated definitions
             */
            if (isset($widgets[ $widgetClassName ])) {

                continue;

            }

            $widgets[ $widgetClassName ] = [
                'key' => $widget->key,
                'uri' => $uri,
                'text' => $widget->name(),
                'component' => $widget->component(),
                'data' => $widget->meta(),
                'options' => $widget->options(),
            ];

        }

        return array_values($widgets);

    }

}
