<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\NovaDashboard\Traits\GuessCaller;
use Illuminate\Support\Str;
use Laravel\Nova\Card;
use Laravel\Nova\Http\Requests\NovaRequest;

abstract class Widget extends Card
{
    use GuessCaller;

    public function key(): string
    {
        return static::class;
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

    public function grid(int $x, int $y, int $width, int $height): self
    {
        return $this->withMeta([ 'grid' => [ 'x' => $x, 'y' => $y, 'w' => $width, 'h' => $height ] ]);
    }

    public function jsonSerialize(): array
    {
        $request = resolve(NovaRequest::class);

        $this->configure($request);

        return array_merge([
            'key' => $this->key(),
            'uriKey' => Str::random(),
            'value' => $this->resolveValue($request),
        ], parent::jsonSerialize());
    }
}
