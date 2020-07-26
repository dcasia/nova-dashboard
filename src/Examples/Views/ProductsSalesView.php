<?php

namespace DigitalCreative\NovaDashboard\Examples\Views;

use DigitalCreative\NovaDashboard\Examples\Actions\DemoActionOne;
use DigitalCreative\NovaDashboard\Examples\Actions\DemoActionTwo;
use DigitalCreative\NovaDashboard\Examples\Filters\Category;
use DigitalCreative\NovaDashboard\Examples\Filters\Date;
use DigitalCreative\NovaDashboard\Examples\Filters\Quantity;
use DigitalCreative\NovaDashboard\Examples\Widgets\ConversionRate;
use DigitalCreative\NovaDashboard\Examples\Widgets\ProductsInStock;
use DigitalCreative\NovaDashboard\Examples\Widgets\TotalSales;
use DigitalCreative\NovaDashboard\View;
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
