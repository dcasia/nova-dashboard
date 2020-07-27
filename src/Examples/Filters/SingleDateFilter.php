<?php

namespace DigitalCreative\NovaDashboard\Examples\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\DateFilter;

class SingleDateFilter extends DateFilter
{

    public function apply(Request $request, $query, $value)
    {
    }

}
