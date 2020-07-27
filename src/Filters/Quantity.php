<?php

namespace DigitalCreative\NovaDashboard\Filters;

use DigitalCreative\RangeInputFilter\RangeInputFilter;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Illuminate\Http\Request;

class Quantity extends RangeInputFilter
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
    public function options(Request $request): array
    {
        return [
            'fromPlaceholder' => 50,
            'toPlaceholder' => 100,
            'inputType' => 'number',
            'dividerLabel' => 'to',
            'fullWidth' => true
        ];
    }

}
