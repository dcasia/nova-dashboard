<?php

namespace DigitalCreative\NovaBi\Http\Controllers;

use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Models\WidgetModel;
use DigitalCreative\NovaBi\NovaWidgets;
use DigitalCreative\NovaBi\Widgets\Action;
use DigitalCreative\NovaBi\Widgets\WidgetCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Fluent;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class WidgetController
{

    public function executeAction(string $dashboard, string $action, ActionRequest $request)
    {

        $dashboard = $this->findDashboard($request, $dashboard);

        if (!$dashboard->authorizedToSee($request)) {

            return Action::danger(__('Sorry! You are not authorized to perform this action.'));

        }

        $action = $dashboard->findActionByKey($action);
        $filters = new Filters($request->input('filters'), $dashboard->filters());
        $fakeModel = new Fluent;
        $fields = $action->fields();

        $rules = collect($fields)->mapWithKeys(function (Field $field) {
            return [ $field->attribute => $field->rules ];
        });

        /**
         * Run validation
         */
        $request->validate($rules->toArray());

        if (!$action->authorizedToRun($request, $fakeModel) ||
            !$action->authorizedToSee($request)) {

            return Action::danger(__('Sorry! You are not authorized to perform this action.'));

        }

        $results = collect($action->fields())->mapWithKeys(function (Field $field) use ($request, $fakeModel) {
            return [ $field->attribute => $field->fillForAction($request, $fakeModel) ];
        });

        $actionFields = new ActionFields(collect($fakeModel->getAttributes()), $results->filter(function ($field) {
            return is_callable($field);
        }));

        return $action->execute($actionFields, $filters);

    }

    public function deleteWidget(string $dashboard, NovaRequest $request): bool
    {

        $widgetInstance = $this->resolveWidget($request->input('id'), compact('dashboard'));

        return (bool) $widgetInstance->delete();

    }

    public function updateWidget(string $dashboard, NovaRequest $request): int
    {

        $widgetInstance = $this->resolveWidget($request->input('id'));
        $widgetInstance->setAttribute('dashboard', $dashboard);
        $widgetInstance->setAttribute('key', $request->input('key'));
        $widgetInstance->setAttribute('options', $request->input('options'));
        $widgetInstance->setAttribute('coordinates', $request->input('coordinates'));
        $widgetInstance->save();

        return $widgetInstance->id;

    }

    public function resolveCardResource(NovaRequest $request)
    {
        /**
         * @var WidgetCard $first
         */
        $first = $request->newResource()->cards($request)[ 0 ];

        return $first->instance->resolveValue(collect(), new Filters(''));

    }

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

            return $dashboard->resolveValue($key, collect($options), $filters);

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

    private function resolveWidget(string $id, array $where = null): WidgetModel
    {

        /**
         * @var Builder $query
         */
        $modelClass = config('nova-widgets.widget_model');
        $query = $modelClass::query();

        /**
         * @var WidgetModel $widgetInstance
         */
        $widgetInstance = $query
            ->when($where, function (Builder $builder) use ($where) {
                $builder->where($where);
            })
            ->find($id);

        if ($widgetInstance === null) {

            return resolve($modelClass);

        }

        return $widgetInstance;

    }

}
