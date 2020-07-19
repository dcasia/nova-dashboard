<?php

namespace DigitalCreative\NovaBi\Widgets;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Laravel\Nova\Makeable;

/**
 * Class Value
 *
 * @package DigitalCreative\NovaBi\Widgets
 */
class Value extends Fluent
{

    use Makeable;

    /**
     * @var Collection
     */
    protected Collection $options;

    public function __construct($options)
    {
        $this->options = collect($options); // temp @todo
        parent::__construct([]);
    }

}
