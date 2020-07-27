<?php

namespace DigitalCreative\NovaDashboard\Views;

use DigitalCreative\NovaDashboard\Actions\DemoActionOne;
use DigitalCreative\NovaDashboard\Actions\DemoActionTwo;
use DigitalCreative\NovaDashboard\Filters\Category;
use DigitalCreative\NovaDashboard\Filters\Date;
use DigitalCreative\NovaDashboard\Filters\Quantity;
use DigitalCreative\NovaDashboard\View;
use DigitalCreative\NovaDashboard\Widgets\ConversionRate;
use DigitalCreative\NovaDashboard\Widgets\ProductsInStock;
use DigitalCreative\NovaDashboard\Widgets\TotalSales;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;

class ProductsSalesView extends View
{

    public function filters(): array
    {
        return [
            new Date(),
            new Quantity(),
            new Category(),
        ];
    }

    public function actions(): array
    {
        return [
            new DemoActionOne(),
            new DemoActionTwo()
        ];
    }

    public function widgets(): array
    {
        return [
            new TotalSales(0, 0, 2, 1, [
                TotalSales::OPTION_PREFIX => '$',
                TotalSales::OPTION_HELP => 'This is awesome inst it?',
            ]),
            new ProductsInStock(2, 0, 2, 1),
            new ConversionRate(4, 0, 2, 1, [
                ConversionRate::OPTION_SUFFIX => '%',
            ]),
        ];
    }

}
