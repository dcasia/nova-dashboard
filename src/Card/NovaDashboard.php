<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Card;

use Closure;
use Laravel\Nova\Card;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaDashboard extends Card
{
    public $width = Card::FULL_WIDTH;

    public function __construct(array $views = [])
    {
        foreach ($views as $view) {
            $this->addView(null, $view);
        }
    }

    public function component(): string
    {
        return 'nova-dashboard';
    }

    public function addView(?string $name, Closure|View $view): static
    {
        $views = data_get($this->meta, 'views', []);
        $views[] = value($view, View::make($name));

        return $this->withMeta([
            'views' => collect($views)
                ->filter(fn (View $view) => $view->authorizedToSee(resolve(NovaRequest::class)))
                ->values(),
        ]);
    }
}
