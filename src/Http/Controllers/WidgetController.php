<?php

namespace DigitalCreative\NovaBi\Http\Controllers;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\NovaBi;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class WidgetController
{

    public function fetch(string $resource, string $id, NovaRequest $request)
    {

        $filters = $request->input('filters');


        $tool = $this->findTool($request);

        if ($tool && $dashboard = $tool->getCurrentActiveDashboard($resource)) {

            $filters = new Filters($filters, $dashboard->filters());

            return $dashboard->resolveData($id, $filters);

        }

    }

    public function findTool(NovaRequest $request): ?NovaBi
    {

        foreach (Nova::availableTools($request) as $tool) {

            if ($tool instanceof NovaBi) {

                return $tool;

            }

        }

        return null;

    }

}
