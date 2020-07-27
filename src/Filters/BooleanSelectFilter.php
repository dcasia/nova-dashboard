<?php

namespace DigitalCreative\NovaDashboard\Filters;

use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class BooleanSelectFilter extends BooleanFilter
{

    public function apply(Request $request, $query, $value)
    {
    }

    public function options(Request $request)
    {
        return [
            'Option 1' => 0,
            'Option 2' => 1,
            'Option 3' => 2,
        ];
    }
}
