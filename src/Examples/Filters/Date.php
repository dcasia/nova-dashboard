<?php

namespace DigitalCreative\NovaDashboard\Examples\Filters;

use DigitalCreative\RangeInputFilter\RangeDateFilter;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Illuminate\Http\Request;

class Date extends RangeDateFilter
{

    public function apply(Request $request, $query, $value)
    {
    }

    public function options(Request $request): array
    {
        return [
            'fromPlaceholder' => today()->startOfMonth(50)->format('Y/m/d'),
            'toPlaceholder' => today()->endOfMonth(25)->format('Y/m/d'),
            'fullWidth' => true,
        ];
    }

}
