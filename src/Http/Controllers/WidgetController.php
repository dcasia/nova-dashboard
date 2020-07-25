<?php

namespace DigitalCreative\NovaBi\Http\Controllers;

use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Models\WidgetModel;
use DigitalCreative\NovaBi\NovaWidgets;
use DigitalCreative\NovaBi\Widgets\Action;
use DigitalCreative\NovaBi\Widgets\View;
use DigitalCreative\NovaBi\Widgets\Widget;
use DigitalCreative\NovaBi\Widgets\WidgetCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Fluent;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class WidgetController
{

    public function executeAction(ActionRequest $request): JsonResponse
    {

        $dashboardKey = $request->query('dashboard');
        $viewKey = $request->query('view');
        $actionKey = $request->query('action');
        $filters = $request->query('filters');

        $viewKey = $this->findViewByKey($request, $dashboardKey, $viewKey);
        $action = $viewKey->findActionByKey($actionKey);


        $filters = new Filters($filters, $viewKey->resolveFilters());
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

        return response()->json($action->execute($actionFields, $filters));

    }

    public function getViewData(NovaRequest $request): JsonResponse
    {

        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');

        $view = $this->findViewByKey($request, $dashboardKey, $viewKey);

        return response()->json($view->resolveDataFromDatabase());

    }

    public function updateCoordinates(NovaRequest $request): JsonResponse
    {
        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');
        $widgetKey = $request->input('widget');
        $id = $request->input('id');
        $coordinates = $request->input('coordinates');

        /**
         * Attempt to find the widget so authorization kicks in if user is unauthorized to interact with this resource
         */
        $this->findWidget($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * @var WidgetModel $widgetInstance
         */
        $widgetInstance = $this->widgetModel()->newQuery()->whereKey($id)->firstOrFail();
        $widgetInstance->setAttribute('coordinates', $coordinates);
        $widgetInstance->save();

        return response()->json($widgetInstance->id);
    }

    public function createWidget(NovaRequest $request): int
    {

        $dashboardKey = $request->query('dashboard');
        $viewKey = $request->query('view');
        $widgetKey = $request->query('widget');
        $options = $request->post();

        $widget = $this->findWidget($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * $validate the request
         */
        $request->validate($widget->creationRules($request)->toArray());

        return $this->storeWidget($dashboardKey, $viewKey, $widgetKey, $options)->id;

    }

    private function findViewByKey(NovaRequest $request, string $dashboardKey, string $viewKey): View
    {

        if ($dashboardInstance = $this->findDashboardByKey($request, $dashboardKey)) {

            if ($view = $dashboardInstance->findViewByKey($viewKey)) {

                return $view;

            }

            return abort(404, __('View :view not found.', [ 'view' => $viewKey ]));

        }

        return abort(404, __('Dashboard :dashboard not found.', [ 'dashboard' => $dashboardKey ]));

    }

    private function findWidget(NovaRequest $request, string $dashboardKey, string $viewKey, string $widgetKey): Widget
    {

        $view = $this->findViewByKey($request, $dashboardKey, $viewKey);

        if ($widget = $view->findWidgetByKey($widgetKey)) {

            return $widget;

        }

        return abort(404, __('Widget :widget not found.', [ 'widget' => $widgetKey ]));

    }

    private function storeWidget(string $dashboardKey, string $viewKey, string $widgetKey, array $options): WidgetModel
    {

        /**
         * @var Builder $query
         * @var WidgetModel $widgetInstance
         */
        $modelClass = config('nova-widgets.widget_model');
        $widgetInstance = resolve($modelClass);

        $widgetInstance->setAttribute('key', $widgetKey);
        $widgetInstance->setAttribute('dashboard', $dashboardKey);
        $widgetInstance->setAttribute('view', $viewKey);
        $widgetInstance->setAttribute('options', $options);
        $widgetInstance->save();

        return $widgetInstance;

    }

    public function deleteWidget(NovaRequest $request): JsonResponse
    {

        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');
        $widgetKey = $request->input('widget');
        $id = $request->input('id');

        /**
         * Attempt to find the widget so authorization kicks in if user is unauthorized to interact with this resource
         */
        $this->findWidget($request, $dashboardKey, $viewKey, $widgetKey);

        return response()->json(
            (bool) $this->widgetModel()->newQuery()->whereKey($id)->delete()
        );

    }

    public function updateWidget(NovaRequest $request): JsonResponse
    {

        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');
        $widgetKey = $request->input('widget');
        $id = $request->input('id');

        /**
         * Attempt to find the widget so authorization kicks in if user is unauthorized to interact with this resource
         */
        $this->findWidget($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * @var WidgetModel $widgetInstance
         */
        $widgetInstance = $this->widgetModel()->newQuery()->whereKey($id)->firstOrFail();
        $widgetInstance->setAttribute('options', $request->post());
        $widgetInstance->save();

        return response()->json($widgetInstance->id);

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

        if ($dashboard = $this->findDashboardByKey($request, $resource)) {

            return $dashboard->jsonSerialize();

        }

        return abort(404, "Dashboard $resource not found.");

    }

    /**
     * Fetch the actual value of each widget
     *
     * @param NovaRequest $request
     *
     * @return JsonResponse
     */
    public function fetch(NovaRequest $request): JsonResponse
    {

        $filters = $request->input('filters');
        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');
        $widgetKey = $request->input('widget');
        $options = $request->input('options');

        $view = $this->findViewByKey($request, $dashboardKey, $viewKey);
        $filters = new Filters($filters, $view->filters());

        return response()->json(
            $view->resolveWidgetValue($widgetKey, collect($options), $filters)
        );

    }

    public function findDashboardByKey(NovaRequest $request, string $resource): ?Dashboard
    {

        $tool = $this->findTool($request);

        if ($tool && $dashboard = $tool->getCurrentActiveDashboard($resource)) {

            if ($dashboard->authorizedToSee($request)) {

                return $dashboard;

            }

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

    private function widgetModel(): WidgetModel
    {
        return resolve(config('nova-widgets.widget_model'));
    }

}
