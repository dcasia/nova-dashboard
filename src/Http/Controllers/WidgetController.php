<?php

namespace DigitalCreative\NovaBi\Http\Controllers;

use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\NovaBi;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class WidgetController
{

    public function resource(string $resource, NovaRequest $request)
    {

        if ($dashboard = $this->findDashboard($request, $resource)) {

            return $dashboard->jsonSerialize();

        }

        return abort(404, 'Dashboard not found');

    }

    public function fetch(string $resource, string $key, NovaRequest $request)
    {

        $filters = $request->input('filters');
        $options = $request->input('options');

        $tool = $this->findTool($request);

        if ($tool && $dashboard = $tool->getCurrentActiveDashboard($resource)) {

            $filters = new Filters($filters, $dashboard->filters());

            return $dashboard->resolveData($key, collect($options), $filters);

        }

    }

    public function findDashboard(NovaRequest $request, string $resource): ?Dashboard
    {

        $tool = $this->findTool($request);

        if ($tool && $dashboard = $tool->getCurrentActiveDashboard($resource)) {

            return $dashboard;

        }

        return null;

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
