<?php

namespace DigitalCreative\NovaDashboard\Http\Controllers;

use DigitalCreative\NovaDashboard\Action;
use DigitalCreative\NovaDashboard\Card;
use DigitalCreative\NovaDashboard\WidgetOptionTab;
use DigitalCreative\NovaDashboard\Dashboard;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\NovaDashboard\Models\Widget as WidgetModel;
use DigitalCreative\NovaDashboard\NovaDashboard;
use DigitalCreative\NovaDashboard\ValueResult;
use DigitalCreative\NovaDashboard\View;
use DigitalCreative\NovaDashboard\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
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

        return response()->json($view->resolveData());

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
        $this->findWidgetKey($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * @var WidgetModel $widgetInstance
         */
        $widgetInstance = $this->widgetModel()->newQuery()->whereKey($id)->firstOrFail();
        $widgetInstance->setAttribute('coordinates', $coordinates);
        $widgetInstance->save();

        return response()->json($widgetInstance->id);


    }

    public function updateAllCoordinates(NovaRequest $request)
    {
        $widgetsData = request()->input('widgetsData');
        foreach($widgetsData as $widget){
            $dashboardKey = $widget['dashboard'];
            $viewKey = $widget['view'];
            $widgetKey = $widget['widget'];
            $id = $widget['id'];
            $coordinates = $widget['coordinates'];

            /**
             * Attempt to find the widget so authorization kicks in if user is unauthorized to interact with this resource
             */
            $this->findWidgetKey($request, $dashboardKey, $viewKey, $widgetKey);

            /**
             * @var WidgetModel $widgetInstance
             */
            $widgetInstance = $this->widgetModel()->newQuery()->whereKey($id)->firstOrFail();
            $widgetInstance->setAttribute('coordinates', $coordinates);
            $widgetInstance->save();
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function createWidget(NovaRequest $request): JsonResponse
    {

        $dashboardKey = $request->query('dashboard');
        $viewKey = $request->query('view');
        $widgetKey = $request->query('widget');

        $widget = $this->findWidgetKey($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * $validate the request
         */
        $request->validate($widget->creationRules($request)->toArray());

        $widgetModel = $this->storeWidget($request, $dashboardKey, $viewKey, $widgetKey, $widget->resolveFields());

        return response()->json([
            'id' => $widgetModel->id,
            'options' => $widget->jsonSerializeOptions($widgetModel->options)
        ]);

    }

    private function findViewByKey(NovaRequest $request, string $dashboardKey, string $viewKey, bool $checkEditableStatus = false): View
    {

        if ($dashboard = $this->findDashboardByKey($request, $dashboardKey)) {

            if ($view = $dashboard->findViewByKey($viewKey)) {

                if ($checkEditableStatus === false || $view->isEditable()) {

                    return $view;

                }

                abort(403);

            }

            return abort(404, __('View :view not found.', [ 'view' => $viewKey ]));

        }

        return abort(404, __('Dashboard :dashboard not found.', [ 'dashboard' => $dashboardKey ]));

    }

    private function findWidgetKey(NovaRequest $request, string $dashboardKey, string $viewKey, string $widgetKey): Widget
    {

        $view = $this->findViewByKey($request, $dashboardKey, $viewKey, true);

        if ($widget = $view->findWidgetByKey($widgetKey)) {

            return $widget;

        }

        return abort(404, __('Widget :widget not found.', [ 'widget' => $widgetKey ]));

    }

    private function storeWidget(NovaRequest $request, string $dashboardKey, string $viewKey, string $widgetKey, Collection $fields): WidgetModel
    {

        /**
         * @var Builder $query
         * @var WidgetModel $widgetInstance
         */
        $modelClass = config('nova-dashboard.widget_model');
        $widgetInstance = resolve($modelClass);

        $widgetInstance->setAttribute('key', $widgetKey);
        $widgetInstance->setAttribute('dashboard', $dashboardKey);
        $widgetInstance->setAttribute('view', $viewKey);
        $widgetInstance->setAttribute('options', $this->parseOptions($request, $fields));
        $widgetInstance->setAttribute('user_id', $request->user()->id);
        $widgetInstance->save();

        return $widgetInstance;

    }

    private function parseOptions(NovaRequest $request, Collection $fields): array
    {

        $data = new Fluent();

        /**
         * @var Field $field
         */
        foreach ($fields as $field) {

            $field->fillForAction($request, $data);

        }

        $options = collect($data)->mapWithKeys(static function ($value, $key) {
            return [
                str_replace(WidgetOptionTab::ATTRIBUTE_SEPARATOR, '.', $key) => $value
            ];
        });

        return array_undot($options->toArray());

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
        $this->findWidgetKey($request, $dashboardKey, $viewKey, $widgetKey);

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
        $widget = $this->findWidgetKey($request, $dashboardKey, $viewKey, $widgetKey);

        /**
         * Validate the request
         */
        $request->validate($widget->creationRules($request)->toArray());

        /**
         * @var WidgetModel $widgetInstance
         */
        $widgetInstance = $this->widgetModel()->newQuery()->whereKey($id)->firstOrFail();
        $options = $this->parseOptions($request, $widget->resolveFields());
        $widgetInstance->setAttribute('options', $options);
        $widgetInstance->save();

        return response()->json($widgetInstance->id);

    }

    public function resolveCardResource(NovaRequest $request): ValueResult
    {
        /**
         * @var Card $first
         */
        $first = $request->newResource()->cards($request)[ 0 ];

        return $first->instance->resolveValue(collect(), new Filters(''));

    }

    public function getDashboard(string $dashboardKey, NovaRequest $request)
    {

        if ($dashboard = $this->findDashboardByKey($request, $dashboardKey)) {

            return response()->json($dashboard);

        }

        return abort(404, __('Dashboard :dashboard not found.', [ 'dashboard' => $dashboardKey ]));

    }

    /**
     * Fetch the actual value of each widget
     *
     * @param NovaRequest $request
     *
     * @return JsonResponse
     */
    public function fetchWidgetData(NovaRequest $request): JsonResponse
    {

        $filters = $request->input('filters');
        $dashboardKey = $request->input('dashboard');
        $viewKey = $request->input('view');
        $widgetKey = $request->input('widget');
        $options = $request->input('options', []);

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

    public function findTool(NovaRequest $request): ?NovaDashboard
    {

        foreach (Nova::availableTools($request) as $tool) {

            if ($tool instanceof NovaDashboard) {

                return $tool;

            }

        }

        return null;

    }

    private function widgetModel(): WidgetModel
    {
        return resolve(config('nova-dashboard.widget_model'));
    }

}
