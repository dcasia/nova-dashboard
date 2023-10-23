<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use DigitalCreative\NovaDashboard\Traits\ResolveView;
use Illuminate\Support\Collection;
use JsonSerializable;
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

    public function addWidget(Widget $widget): self
    {
        $widgets = data_get($this->meta, 'widgets', []);
        $widgets[] = $widget;

        return $this->withMeta([ 'widgets' => $widgets ]);
    }

    public function addFilters(array $filters): self
    {
        return $this->withMeta([ 'filters' => $filters ]);
    }

    public function icon(string $icon): self
    {
        return $this->withMeta([ 'icon' => $icon ]);
    }

    public function key(): string
    {
        return md5($this->name);
    }

    public function getFilters(): Collection
    {
        return collect($this->meta[ 'filters' ] ?? []);
    }

    public function getWidgets(): Collection
    {
        return collect($this->meta[ 'widgets' ] ?? []);
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'name' => $this->name,
            'key' => $this->key(),
        ], $this->meta());
    }
}
