<?php

namespace DigitalCreative\NovaBi\Widgets;

use Illuminate\Support\Str;

trait TitleableTrait
{

    public static function humanize(string $value): string
    {
        return Str::title(Str::snake($value, ' '));
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return static::humanize(class_basename(static::class));
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public function title(): string
    {
        return static::$title ?? static::humanize(class_basename(static::class));
    }

}
