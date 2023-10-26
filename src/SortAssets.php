<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard;

use Closure;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;
use Laravel\Nova\Script;
use Laravel\Nova\Style;

class SortAssets
{
    public function handle(Request $request, Closure $next): mixed
    {
        usort(Nova::$scripts, fn (Script $left, Script $right) => $right->name() === 'nova-dashboard');
        usort(Nova::$styles, fn (Style $left, Style $right) => $right->name() === 'nova-dashboard');

        return $next($request);
    }
}
