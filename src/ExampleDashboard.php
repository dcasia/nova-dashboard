<?php

namespace DigitalCreative\NovaDashboard;

use App\Nova\Filters\TestFilter;
use DigitalCreative\NovaDashboard\Views\AnotherView;
use DigitalCreative\NovaDashboard\Views\ProductsSalesView;

class ExampleDashboard extends Dashboard
{

    public static string $title = 'Example Dashboard';

    public function views(): array
    {
        return [
            new ProductsSalesView(),
            AnotherView::make()->editable()
        ];
    }

    public function options(): array
    {
        return [
            'expandFilterByDefault' => true,
            'grid' => [
                'compact' => true,
            ]
        ];
    }

}
