<?php

namespace DigitalCreative\NovaDashboard\Examples\Widgets;

use DigitalCreative\NovaDashboard\Examples\Filters\Category;
use DigitalCreative\NovaDashboard\Examples\Filters\Date;
use DigitalCreative\NovaDashboard\Examples\Filters\Quantity;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\Widgets\ValueResult;
use DigitalCreative\ValueWidget\Widgets\ValueWidget;
use Illuminate\Support\Collection;

class TotalSales extends ValueWidget
{
    public function resolveValue(Collection $options, Filters $filters): ValueResult
    {

        /**
         * Get the current value of any given filter...
         * Call the database, find your data..
         * Return a instance of ValueResult
         *
         * For demo only no DB call is made.. and only random values are returned
         */

        $dateValue = $filters->getFilterValue(Date::class);
        $quantityValue = $filters->getFilterValue(Quantity::class);
        $vategoryValue = $filters->getFilterValue(Category::class);

        /**
         * You can also leverage the default functionality of all filters in nova by calling applyToQueryBuilder
         * This will call the apply() method of the original filter file and return the builder once all filter has been applied to it
         *
         * $filters->applyToQueryBuilder(Model::query())->get()
         */

        return ValueResult::make()
                          ->currentValue(random_int(-500, 500))
                          ->previousValue(random_int(-500, 500));

    }
}
