<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use DigitalCreative\NovaDashboard\Traits\ResolveView;
use Illuminate\Support\Collection;
use JsonSerializable;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Makeable;
use Laravel\Nova\Metable;

class View implements JsonSerializable
{
    use Makeable;
    use Metable;
    use ResolveView;

    public function __construct(
        private readonly string $name,
    )
    {
    }

    public function addWidget(Widget ...$widgets): self
    {
        $metaWidgets = data_get($this->meta, 'widgets', []);

        foreach ($widgets as $widget) {
            $metaWidgets[] = $widget;
        }

        return $this->withMeta([ 'widgets' => $metaWidgets ]);
    }

    public function addWidgets(array $widgets): self
    {
        return $this->addWidget(...$widgets);
    }

    public function addFilter(Filter ...$filters): self
    {
        $metaFilters = data_get($this->meta, 'filters', []);

        foreach ($filters as $filter) {
            $metaFilters[] = $filter;
        }

        return $this->withMeta([ 'filters' => $metaFilters ]);
    }

    public function addFilters(array $filters): self
    {
        return $this->addFilter(...$filters);
    }

    public function icon(string $icon): self
    {
        return $this->withMeta([ 'icon' => $icon ]);
    }

    public function key(): string
    {
        return md5($this->name);
    }

    public function filters(): Collection
    {
        return collect(data_get($this->meta, 'filters', []));
    }

    public function widgets(): Collection
    {
        return collect(data_get($this->meta, 'widgets', []));
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'name' => $this->name,
            'key' => $this->key(),
        ], $this->meta());
    }
}
