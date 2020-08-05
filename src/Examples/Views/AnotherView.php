<?php

namespace DigitalCreative\NovaDashboard\Examples\Views;

use DigitalCreative\NovaDashboard\Examples\Actions\UniqueAction;
use DigitalCreative\NovaDashboard\Examples\Filters\BooleanSelectFilter;
use DigitalCreative\NovaDashboard\Examples\Filters\SingleDateFilter;
use DigitalCreative\NovaDashboard\Examples\Widgets\BarChartExampleWidget;
use DigitalCreative\NovaDashboard\Examples\Widgets\ExampleWidgetOne;
use DigitalCreative\NovaDashboard\Examples\Widgets\ExampleWidgetTwo;
use DigitalCreative\NovaDashboard\View;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;

class AnotherView extends View
{

    public function filters(): array
    {
        return [
            new SingleDateFilter(),
            new BooleanSelectFilter()
        ];
    }

    public function actions(): array
    {
        return [
            new UniqueAction(),
        ];
    }

    public function widgets(): array
    {
        return [
            new ExampleWidgetOne(),
            new ExampleWidgetTwo(),
            new BarChartExampleWidget()
        ];
    }

}
