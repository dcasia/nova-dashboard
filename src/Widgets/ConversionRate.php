<?php

namespace DigitalCreative\NovaDashboard\Widgets;

use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\Widgets\ValueResult;
use DigitalCreative\ValueWidget\Widgets\ValueWidget;
use Illuminate\Support\Collection;

class ConversionRate extends ValueWidget
{
    public function resolveValue(Collection $options, Filters $filters): ValueResult
    {
        return ValueResult::make()
                          ->currentValue(random_int(0, 100))
                          ->previousValue(random_int(0, 100));
    }
}
