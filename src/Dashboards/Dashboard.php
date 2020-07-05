<?php

namespace DigitalCreative\NovaBi\Dashboards;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Widgets\Widget;
use DigitalCreative\NovaBi\Widgets\WidgetPreset;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Dashboard implements JsonSerializable
{

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return Str::plural(Str::title(Str::snake(class_basename(static::class), ' ')));
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return Str::plural(Str::kebab(class_basename(static::class)));
    }

    public function filters(): array
    {
        return [];
    }

    public function widgets(): array
    {
        return [];
    }

    public function preset(): array
    {
        return [];
    }

    private function resolveWidgets(): array
    {

        $widgets = [];
        $uri = self::uriKey();

        /**
         * @var Widget $widget
         */
        foreach ($this->widgets() as $widget) {

            $widgets[] = [
                'key' => $widget->key,
                'uri' => $uri,
                'text' => $widget->name(),
                'component' => $widget->component(),
                'data' => $widget->meta(),
                'options' => $widget->options()
            ];

        }

        return $widgets;

    }

    public function resolvePresets(array $availableFilters): array
    {

        $presets = [];

        /**
         * @var WidgetPreset $preset
         */
        foreach ($this->preset() as $preset) {

            $presets[] = $preset->instantiate($availableFilters);

        }

        return $presets;

    }

    public function resolveFilters(): array
    {
        return $this->filters();
    }

    public function resolveData(string $key, Collection $options, Filters $filters): array
    {

        if ($widget = $this->findWidgetByKey($key)) {

            return $widget->resolveValue($options, $filters);

        }

        return [];

    }

    public function findWidgetByKey(string $key): ?Widget
    {
        return collect($this->widgets())->firstWhere('key', $key);
    }

    public function jsonSerialize(): array
    {

        $filters = $this->resolveFilters();

        return [
            'filters' => $filters,
            'presets' => $this->resolvePresets($filters),
            'widgets' => $this->resolveWidgets(),
        ];

    }

}
