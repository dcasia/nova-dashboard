<?php

namespace DigitalCreative\NovaBi\Dashboards\Example;

use App\Nova\Filters\TestFilter;
use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Widgets\WidgetPreset;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;

class ExampleDashboard extends Dashboard
{

    public static string $title = 'Example Dashboard';

    public function filters(): array
    {
        return [
            (new TestFilter())->withMeta([ 'currentValue' => '2010-12-10' ]),
            (new TestFilter())->withMeta([ 'currentValue' => '2010-12-10' ]),
            (new TestFilter())->withMeta([ 'currentValue' => '2010-12-10' ]),
            (new TestFilter())->withMeta([ 'currentValue' => '2010-12-10' ]),
        ];
    }

    public function widgets(): array
    {
        return [
            SocialMediaWidget::make(1, 0, 1, 1),
        ];
    }

    public function preset(): array
    {
        return [

            WidgetPreset::make(SocialMediaWidget::class)
                        ->coordinates(4, 0, 4, 2)
                        ->options([
                            'type' => SocialMediaWidget::TYPE_FACEBOOK
                        ]),

            WidgetPreset::make(SocialMediaWidget::class)
                        ->coordinates(0, 0, 4, 2)
                        ->options([
                            'type' => SocialMediaWidget::TYPE_TWITTER
                        ]),

        ];
    }

    public function options(): array
    {
        return [
            'enableAddWidgetButton' => true,
            'enableWidgetEditing' => true,
            'expandFilterByDefault' => true,
            'gridOptions' => [
                'staticGrid' => false
            ]
        ];
    }

}
