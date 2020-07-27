<?php

namespace DigitalCreative\NovaDashboard\Widgets;

use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\Widgets\ValueResult;
use DigitalCreative\ValueWidget\Widgets\ValueWidget;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Select;

class ExampleWidgetOne extends ValueWidget
{
    public function resolveValue(Collection $options, Filters $filters): ValueResult
    {
        return ValueResult::make()
                          ->currentValue(random_int(0, 100))
                          ->previousValue(random_int(0, 100));
    }

    public function fields(): array
    {
        return array_merge([
            Select::make('Data Source')
                  ->options([
                      'Customer interests',
                      'Customer purchases',
                      'Customer cart',
                  ])
                  ->rules('required'),
        ], parent::fields());
    }

}
