<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Models\WidgetModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Makeable;
use Laravel\Nova\Metable;

abstract class Widget
{

    use Makeable;
    use Metable;

    private static int $counter = 0;

    public string $key;

    public function __construct()
    {
        $this->key = static::key();
    }

    abstract public function resolveValue(Collection $options, Filters $filters): Value;

    abstract public function component(): string;

    public function name(): string
    {
        return Str::title(Str::snake(class_basename(static::class), ' '));
    }

    public static function key(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function options(): array
    {
        return [];
    }

    public function disableEditingSettings(): self
    {
        return $this->withMeta([ 'disableEditingSettings' => true ]);
    }

    public function help(string $text): self
    {
        return $this->withMeta([ 'help' => $text ]);
    }

    public function resolveOptions(): array
    {

        $options = [];

        /**
         * @var Field $option
         */
        foreach ($this->options() as $option) {

            $options[ $option->attribute ] = $option->value ?? $option->jsonSerialize()[ 'value' ] ?? null;

        }

        return $options;

    }

    public static function preset(int $x, int $y, int $width, int $height): WidgetPreset
    {
        return WidgetPreset::make(static::class)->coordinates($x, $y, $width, $height);
    }

    /**
     * @param WidgetModel $model
     * @param Collection $filters
     *
     * @throws JsonException
     * @return WidgetData
     */
    public static function fromModel(WidgetModel $model, Collection $filters): WidgetData
    {

        $instance = new static();

        return new WidgetData([
            'widget' => $instance,
            'id' => $model->id,
            'options' => $model->options,
            'coordinates' => $model->coordinates,
            'data' => $instance->resolveValue($model->options, Filters::fromUnencodedFilters($filters)),
        ]);

    }

}
