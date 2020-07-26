<?php

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Laravel\Nova\Makeable;

/**
 * Class ValueResult
 *
 * @package DigitalCreative\NovaDashboard\Widgets
 */
class ValueResult extends Fluent
{

    use Makeable;

    /**
     * @var Collection
     */
    protected Collection $options;

    public function __construct($options = null)
    {
        $this->options = collect($options); // temp @todo

        parent::__construct([]);

    }

}
