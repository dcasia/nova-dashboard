<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Makeable;

class WidgetPreset
{
    use Makeable;

    public array $options = [];
    public int $x = 0;
    public int $y = 0;
    public int $width = 0;
    public int $height = 0;
    public Widget $widget;

    /**
     * PresetWidget constructor.
     *
     * @param string|Widget $widget
     */
    public function __construct($widget)
    {
        $this->widget = $widget instanceof Widget ? $widget : resolve($widget);
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function coordinates(int $x, int $y, int $width, int $height): self
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function instantiate(Collection $availableFilters): WidgetData
    {

        return new WidgetData([
            'id' => Str::random(),
            'widget' => $this->widget,
            'options' => array_merge($this->widget->resolveOptions(), $this->options),
            'coordinates' => [
                'x' => $this->x,
                'y' => $this->y,
                'width' => $this->width,
                'height' => $this->height,
            ],
            'data' => $this->widget->resolveValue(collect($this->options), $this->resolveFilters($availableFilters)),
        ]);

    }

    private function resolveFilters(Collection $availableFilters): Filters
    {
        return Filters::fromUnencodedFilters($availableFilters);
    }

}
