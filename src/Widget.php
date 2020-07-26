<?php

namespace DigitalCreative\NovaDashboard;

use DigitalCreative\NovaDashboard\Models\Widget as WidgetModel;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use RuntimeException;

abstract class Widget implements JsonSerializable
{

    use DashboardTrait;

    public ?Preset $preset = null;

    public function __construct()
    {

        /**
         * If 4 or 5 arguments was given assume this is to be set in preset mode
         */
        $arguments = func_get_args();
        $argumentsCount = count($arguments);

        if ($argumentsCount !== 0 && $argumentsCount < 4) {

            throw new RuntimeException('Invalid number of arguments, expected: { int $x, int $y, int $width, int $height, ?array $options } or none.');

        }

        if (count($arguments) >= 4) {

            $this->preset = Preset::make($this)
                                  ->coordinates($arguments[ 0 ], $arguments[ 1 ], $arguments[ 2 ], $arguments[ 3 ])
                                  ->options($arguments[ 4 ] ?? []);

        }

    }

    abstract public function resolveValue(Collection $options, Filters $filters): ValueResult;

    abstract public function component(): string;

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
        foreach ($this->resolveFields() as $option) {

            $options[ $option->attribute ] = $option->value ?? $option->jsonSerialize()[ 'value' ] ?? null;

        }

        return $options;

    }

    public function resolveFields(): Collection
    {
        return once(function () {
            return collect($this->fields())->filter(function (Field $action) {
                return $action->authorizedToSee(request());
            });
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

    public function getSchema(): array
    {
        return [
            'component' => $this->component(),
            'title' => $this->title(),
            'fields' => $this->resolveFields(),
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'uriKey' => $this->uriKey(),
            'editable' => blank($this->preset),
            'data' => $this->meta(),
        ];
    }

}
