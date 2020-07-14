<?php

namespace DigitalCreative\NovaBi\Http\Controllers;

use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\NovaWidgets;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class WidgetController
{

    public function resource(string $resource, NovaRequest $request)
    {

        if ($dashboard = $this->findDashboard($request, $resource)) {

            return $dashboard->jsonSerialize();

        }

        return abort(404, "Dashboard $resource not found.");

    }

    public function fetch(string $resource, string $key, NovaRequest $request)
    {

        $filters = $request->input('filters');
        $options = $request->input('options');

        if ($dashboard = $this->findDashboard($request, $resource)) {

            $filters = new Filters($filters, $dashboard->filters());

            return $dashboard->resolveData($key, collect($options), $filters);

        }

        return abort(404, "Widget $resource not found.");

    }

    public function findDashboard(NovaRequest $request, string $resource): ?Dashboard
    {

        $tool = $this->findTool($request);

        if ($tool && $dashboard = $tool->getCurrentActiveDashboard($resource)) {

            return $dashboard;

        }

        return null;

    }

    public function findTool(NovaRequest $request): ?NovaWidgets
    {

        foreach (Nova::availableTools($request) as $tool) {

            if ($tool instanceof NovaWidgets) {

                return $tool;

            }

        }

        return null;

    }

}
