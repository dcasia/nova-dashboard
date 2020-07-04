<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Illuminate\Support\Str;
use Laravel\Nova\Makeable;
use Laravel\Nova\Metable;

abstract class Widget
{

    use Makeable;
    use Metable;

    private static int $counter = 0;

    public string $id;
    public int $x;
    public int $y;
    public int $width;
    public int $height;

    public function __construct(int $x, int $y, int $width, int $height)
    {
        $this->id = md5("$x, $y, $width, $height" . self::$counter++);
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }


    abstract public function resolveValue(Filters $filters);

    abstract public function component(): string;

    public function name(): string
    {
        return Str::title(Str::snake(class_basename(static::class), ' '));
    }

    public function value()
    {

    }

}
