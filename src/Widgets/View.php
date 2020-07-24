<?php

namespace DigitalCreative\NovaBi\Widgets;

use Laravel\Nova\Makeable;
use Laravel\Nova\Metable;

class View
{
    use Makeable;
    use Metable;

    public string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function editable(bool $allowEdit = true): self
    {
        return $this->withMeta([ 'editable' => $allowEdit ]);
    }

}
