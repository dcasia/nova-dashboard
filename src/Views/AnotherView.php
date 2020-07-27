<?php

namespace DigitalCreative\NovaDashboard\Views;

use DigitalCreative\NovaDashboard\Actions\UniqueAction;
use DigitalCreative\NovaDashboard\Filters\BooleanSelectFilter;
use DigitalCreative\NovaDashboard\Filters\SingleDateFilter;
use DigitalCreative\NovaDashboard\View;
use DigitalCreative\NovaDashboard\Widgets\ExampleWidgetOne;
use DigitalCreative\NovaDashboard\Widgets\ExampleWidgetTwo;
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
        ];
    }

}
