<?php

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Makeable;

/**
 * Class WidgetOptions
 *
 * @package DigitalCreative\NovaDashboard
 */
class WidgetOptionTab extends Fluent
{
    use Makeable;

    public const ATTRIBUTE_SEPARATOR = '__';

    private string $name;

    public function __construct(string $name, $attributes = [])
    {
        $this->name = $name;

        parent::__construct($attributes);
    }

    public function generateOptions(): Collection
    {
        return collect(Arr::dot($this->attributes))->mapWithKeys(static function ($value, string $attribute) {
            return [
                Str::of($attribute)->replace('.', static::ATTRIBUTE_SEPARATOR)->__toString() => $value
            ];
        });
    }

    public function resolveFields(): Collection
    {
        return once(function () {

            $fields = collect();

            $attributes = Arr::dot($this->attributes);

            $lastHeader = null;

            foreach ($attributes as $attribute => $value) {

                $attribute = Str::of($attribute);

                if ($header = $attribute->beforeLast('.')) {

                    $formatted = $header->kebab()->replace('.', ' ▸ ')->title()->__toString();

                    if ($lastHeader !== $formatted) {

                        $fields->push(
                            Heading::make(__($formatted))
                        );

                        $lastHeader = $formatted;

                    }

                }

                $attributeValue = $attribute->replace('.', static::ATTRIBUTE_SEPARATOR)->__toString();

                if (is_callable($value)) {

                    $fields->push($value($attributeValue));

                }

            }

            return $fields->filter(function (Field $action) {
                return $action->authorizedToSee(request());
            });

        });
    }

    public function getTab(): array
    {
        return [
            'title' => $this->name,
            'key' => Str::slug($this->name),
            'fields' => $this->resolveFields()
        ];
    }

}
