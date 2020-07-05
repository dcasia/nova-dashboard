<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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

    abstract public function resolveValue(Collection $options, Filters $filters);

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

}
