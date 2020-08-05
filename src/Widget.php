<?php

namespace DigitalCreative\NovaDashboard;

use DigitalCreative\NovaDashboard\Models\Widget as WidgetModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use RuntimeException;

abstract class Widget implements JsonSerializable
{

    use DashboardTrait;

    public ?Preset $preset = null;

    /**
     * @var array|callable
     */
    public $defaultCallback = [];

    public function __construct()
    {

        /**
         * If 4 arguments was given assume this is to be set in preset mode
         */
        $arguments = func_get_args();
        $argumentsCount = count($arguments);

        if ($argumentsCount !== 0 && $argumentsCount !== 4) {

            throw new RuntimeException('Invalid number of arguments, expected: { int $x, int $y, int $width, int $height } or none.');

        }

        if (count($arguments) === 4) {

            $this->preset = Preset::make($this)
                                  ->coordinates($arguments[ 0 ], $arguments[ 1 ], $arguments[ 2 ], $arguments[ 3 ]);

        }

    }

    abstract public function resolveValue(Collection $options, Filters $filters): ValueResult;

    abstract public function component(): string;

    /**
     * Receives an un dotted array and merge with the defaults and then return an dotted array
     *
     * @param Collection $options
     *
     * @return array
     */
    public function jsonSerializeOptions(Collection $options): array
    {
        $defaults = $this->resolveDottedOptions()->toArray();
        $options = $this->dotArray($options->toArray())->toArray();

        return array_merge($defaults, $options);
    }

    /**
     * @return WidgetOptionTab|array
     */
    public function resolveWidgetOptions()
    {
        return [];
    }

    public function fields(): array
    {
        return [];
    }

    /**
     * Return the default data for static widgets
     *
     * @param array|callable $defaults
     *
     * @return Widget
     */
    public function default($defaults): self
    {
        $this->defaultCallback = $defaults;

        return $this;
    }

    private function resolveDefaults(): array
    {
        return is_callable($this->defaultCallback) ? $this->defaultCallback() : $this->defaultCallback;
    }

    public function resolveDottedOptions(): Collection
    {

        $options = [];

        /**
         * @var Field $option
         */
        foreach ($this->resolveFields() as $option) {

            if ($attribute = $option->attribute) {

                $options[ $attribute ] = $option->value ?? $option->jsonSerialize()[ 'value' ] ?? null;

            }

        }

        $options = array_undot($options);
        $defaults = $this->resolveDefaults();

        return $this->dotArray(array_merge_recursive_distinct($options, $defaults));

    }

    public function resolveDefaultOptions(): Collection
    {

        $options = [];

        /**
         * @var Field $option
         */
        foreach ($this->resolveFields() as $option) {

            if ($option->attribute) {

                $attribute = Str::of($option->attribute)->replace(WidgetOptionTab::ATTRIBUTE_SEPARATOR, '.')->__toString();

                $options[ $attribute ] = $option->value ?? $option->jsonSerialize()[ 'value' ] ?? null;

            }

        }

        $options = array_undot($options);
        $defaults = $this->resolveDefaults();

        return collect(
            array_merge_recursive_distinct($options, $defaults)
        );

    }

    public function fieldsTabTitle(): string
    {
        return __('Settings');
    }

    public function resolveTabs(): Collection
    {
        return once(function () {

            $collection = collect();
            $widgetSettings = $this->resolveWidgetOptions();
            $request = app(NovaRequest::class);

            if ($widgetSettings instanceof WidgetOptionTab) {

                $collection->push($widgetSettings->getTab($request));

            }

            if (is_array($widgetSettings)) {

                /**
                 * @var WidgetOptionTab $widgetSetting
                 */
                foreach ($widgetSettings as $widgetSetting) {

                    $collection->push($widgetSetting->getTab($request));

                }

            }

            $clientFields = collect($this->fields())->filter(function (Field $action) {
                return $action->authorizedToSee(request());
            });

            if ($clientFields->isNotEmpty()) {

                $collection->prepend([
                    'title' => $this->fieldsTabTitle(),
                    'key' => 'userDefinedSettings',
                    'fields' => $clientFields->toArray(),
                ]);

            }

            return $collection->push($this->getDefaultWidgetSettings());

        });
    }

    public function getDefaultWidgetSettings(): array
    {
        return [
            'title' => __('Widget Settings'),
            'key' => 'widgetSettings',
            'fields' => [
                Text::make(__('Title'), 'widget_title')->default($this->title()),
                Textarea::make(__('Help'), 'widget_help'),
            ],
        ];
    }

    public function resolveFields(): Collection
    {
        return once(function () {
            return $this->resolveTabs()->pluck('fields')->flatten();
        });
    }

    public function creationRules(NovaRequest $request): Collection
    {
        return $this->resolveFields()->mapWithKeys(function ($field) use ($request) {
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
            'options' => $this->jsonSerializeOptions($model->getAttribute('options')),
            'coordinates' => $model->getAttribute('coordinates'),
        ]);
    }

    public function getSchema(): array
    {
        return [
            'component' => $this->component(),
            'title' => $this->title(),
            'tabs' => $this->resolveTabs(),
        ];
    }

    private function dotArray(array $options): Collection
    {
        return collect(Arr::dot($options))
            ->mapWithKeys(static function ($value, string $attribute) {
                return [
                    Str::of($attribute)->replace('.', WidgetOptionTab::ATTRIBUTE_SEPARATOR)->__toString() => $value,
                ];
            });
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
