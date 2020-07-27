<?php

namespace DigitalCreative\NovaDashboard\Examples\Filters;

use DigitalCreative\PillFilter\PillFilter;
use Illuminate\Http\Request;

class Category extends PillFilter
{

    public function apply(Request $request, $query, $value)
    {
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Arts & Entertainment' => 0,
            'Baby & Toddler' => 1,
            'Business & Industrial' => 2,
            'Cameras & Optics' => 3,
            'Clothing & Accessories' => 4,
            'Electronics' => 5,
            'Furniture' => 6,
            'Hardware' => 7
        ];
    }

}
