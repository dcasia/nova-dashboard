<?php

namespace DigitalCreative\NovaBi\Dashboards\Example;

use App\Nova\Filters\TestFilter;
use DigitalCreative\NovaBi\Dashboards\Databoard;
use DigitalCreative\NovaBi\Widgets\SocialMediaWidget;

class ExampleDashboard extends Databoard
{

    public function filters(): array
    {
        return [
            new TestFilter(),
        ];
    }

    public function widgets(): array
    {
        return [
            FacebookExampleWidget::make(1, 0, 1, 1),
            TwitterExampleWidget::make(2, 0, 2, 2),
        ];
    }

}
