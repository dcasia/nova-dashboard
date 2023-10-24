<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\NovaDashboard\Traits\GuessCaller;
use Laravel\Nova\Element;
use Laravel\Nova\Http\Requests\NovaRequest;

abstract class Widget extends Element
{
    use GuessCaller;

    public function key(): string
    {
        return md5(static::class);
    }

    abstract public function value(Filters $filters);

    public function configure(NovaRequest $request): void
    {
    }

    public function resolveValue(NovaRequest $request, ?View $view = null): mixed
    {
        $view ??= View::findView($request, fn () => $this->caller);

        return $this->value(
            Filters::fromRequest($request, collect($view?->filters())),
        );
    }

    public function layout(int $width, int $height, int $x, int $y): self
    {
        return $this->width($width)->height($height)->position($x, $y);
    }

    public function position(int $x, int $y): self
    {
        return $this->withMeta([ 'x' => $x, 'y' => $y ]);
    }

    public function width(int $width): self
    {
        return $this->withMeta([ 'width' => $width ]);
    }

    public function height(int $height): self
    {
        return $this->withMeta([ 'height' => $height ]);
    }

    public function minWidth(int $width): self
    {
        return $this->withMeta([ 'minWidth' => $width ]);
    }

    public function minHeight(int $height): self
    {
        return $this->withMeta([ 'minHeight' => $height ]);
    }

    public function jsonSerialize(): array
    {
        $request = resolve(NovaRequest::class);

        $this->configure($request);

        return array_merge([
            'key' => $this->key(),
            'value' => $this->resolveValue($request),
            'width' => 2,
            'height' => 1,
            'x' => 0,
            'y' => 0,
        ], parent::jsonSerialize());
    }
}
