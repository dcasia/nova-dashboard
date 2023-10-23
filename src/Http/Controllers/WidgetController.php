<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Http\Controllers;

use DigitalCreative\NovaDashboard\Card\View;
use Illuminate\Http\JsonResponse;
use Laravel\Nova\Http\Requests\NovaRequest;

class WidgetController
{
    public function __invoke(NovaRequest $request): JsonResponse
    {
        $widgetKey = $request->input('widget');

        return response()->json([
            'value' => View::findView($request)->resolveWidgetValue($request, $widgetKey),
        ]);
    }
}
