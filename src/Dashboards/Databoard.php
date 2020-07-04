<?php

namespace DigitalCreative\NovaBi\Dashboards;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Widgets\Widget;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Databoard implements JsonSerializable
{

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    public function filters(): array
    {
        return [];
    }

    public function widgets(): array
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
                'id' => $widget->id,
                'x' => $widget->x,
                'y' => $widget->y,
                'w' => $widget->width,
                'h' => $widget->height,
                'available' => false,
                'uri' => $uri,
                'text' => $widget->name(),
                'comp' => $widget->component(),
                'data' => $widget->meta(),
            ];

        }

        return $widgets;

    }

    public function resolveFilters(): array
    {
        return $this->filters();
    }

    public function resolveData(string $id, Filters $filters): array
    {

        if ($widget = $this->findWidgetById($id)) {

            return $widget->resolveValue($filters);

        }

        return [];

    }

    public function findWidgetById(string $id): ?Widget
    {
        return collect($this->widgets())->firstWhere('id', $id);
    }

    public function jsonSerialize(): array
    {
        return [
            'columns' => 5,
            'rows' => 3,
            'filters' => $this->resolveFilters(),
            'widgets' => $this->resolveWidgets(),
        ];
    }
}
