<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use Closure;
use Laravel\Nova\Card;

class NovaDashboard extends Card
{
    public $width = Card::FULL_WIDTH;

    public function component(): string
    {
        return 'nova-dashboard';
    }

    public function addView(string $name, Closure|View $view): static
    {
        $views = data_get($this->meta, 'views', []);
        $views[] = value($view, View::make($name));

        return $this->withMeta([ 'views' => $views ]);
    }
}
