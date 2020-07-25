<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Models\WidgetModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Laravel\Nova\AuthorizedToSee;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Makeable;
use Laravel\Nova\Metable;
use Laravel\Nova\ProxiesCanSeeToGate;
use RuntimeException;

abstract class Widget implements JsonSerializable
{

    use Makeable;
    use Metable;
    use ProxiesCanSeeToGate;
    use AuthorizedToSee;
    use TitleableTrait;

    private static int $counter = 0;

    public string $key;
    public ?Preset $preset = null;

    public function __construct()
    {

        $this->key = static::key();

        /**
         * If 5 arguments was given assume this is to be set in preset mode
         */
        $arguments = func_get_args();
        $argumentsCount = count($arguments);

        if ($argumentsCount !== 0 && $argumentsCount !== 5) {

            throw new RuntimeException('Invalid number of arguments, expected: { int $x, int $y, int $width, int $height, array $options } or none.');

        }

        if (count($arguments) === 5) {

            $this->preset = Preset::make($this)
                                  ->coordinates($arguments[ 0 ], $arguments[ 1 ], $arguments[ 2 ], $arguments[ 3 ])
                                  ->options($arguments[ 4 ]);

        }

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

    public function fields(): array
    {
        return [];
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
        foreach ($this->fields() as $option) {

            $options[ $option->attribute ] = $option->value ?? $option->jsonSerialize()[ 'value' ] ?? null;

        }

        return $options;

    }

    public static function preset(int $x, int $y, int $width, int $height): Preset
    {
        return Preset::make(new static)->coordinates($x, $y, $width, $height);
    }

    public function resolveFields(): Collection
    {
        return collect($this->fields())->filter(function (Field $action) {
            return $action->authorizedToSee(request());
        });
    }

    public function creationRules(NovaRequest $request): Collection
    {
        return $this->resolveFields()->mapWithKeys(function (Field $field) use ($request) {
            return $field->getCreationRules($request);
        });
    }

    public function rules(NovaRequest $request): Collection
    {
        return $this->resolveFields()->mapWithKeys(function (Field $field) use ($request) {
            return $field->getRules($request);
        });
    }

    public function updateRules(NovaRequest $request): Collection
    {
        return $this->resolveFields()->mapWithKeys(function (Field $field) use ($request) {
            return $field->getUpdateRules($request);
        });
    }

    public function hydrateFromPreset(Preset $preset): self
    {
        return $this->withMeta([
            'id' => $preset->id,
            'options' => $preset->resolveOptions(),
            'coordinates' => $preset->resolveCoordinates(),
        ]);
    }

    public function hydrate(WidgetModel $model): self
    {
        return $this->withMeta([
            'id' => $model->getKey(),
            'options' => $model->getAttribute('options'),
            'coordinates' => $model->getAttribute('coordinates'),
        ]);
    }

    public function jsonSerialize(): array
    {
        return [
            'uriKey' => static::uriKey(),
            'editable' => blank($this->preset),
            //            'title' => $this->title(),
            //            'component' => $this->component(),
            'data' => $this->meta(),
            //            'preset' => $this->preset,
            //            'fields' => $this->resolveFields(),
        ];
    }

}
