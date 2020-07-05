<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Laravel\Nova\Makeable;

class WidgetPreset
{
    use Makeable;

    public array $options = [];
    public int $x = 0;
    public int $y = 0;
    public int $width = 0;
    public int $height = 0;
    public string $widget;

    /**
     * PresetWidget constructor.
     *
     * @param string $widget
     */
    public function __construct(string $widget)
    {
        $this->widget = $widget;
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

    public function instantiate(array $availableFilters = []): array
    {
        /**
         * @var Widget $widget
         */
        $widget = app($this->widget);

        return [
            'widget' => $widget,
            'options' => $this->options,
            'coordinates' => [
                'x' => $this->x,
                'y' => $this->y,
                'width' => $this->width,
                'height' => $this->height
            ],
            'data' => $widget->resolveValue(collect($this->options), $this->resolveFilters($availableFilters))
        ];

    }

    private function resolveFilters(array $availableFilters): Filters
    {
        return Filters::fromUnencodedFilters($availableFilters);
    }

}
