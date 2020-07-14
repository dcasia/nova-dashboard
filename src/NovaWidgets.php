<?php

namespace DigitalCreative\NovaBi;

use DigitalCreative\NovaBi\Dashboards\Dashboard;
use DigitalCreative\NovaBi\Dashboards\Example\ExampleDashboard;
use Illuminate\View\View;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaWidgets extends Tool
{

    private array $dashboards;

    /**
     * Create a new element.
     *
     * @param array $dashboards
     */
    public function __construct(array $dashboards = [])
    {
        $this->dashboards = $dashboards;

        parent::__construct(null);
    }

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-widgets', __DIR__ . '/../dist/js/tool.js');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return View
     */
    public function renderNavigation(): View
    {
        return view('nova-widgets::navigation', [ 'dashboards' => $this->dashboards ]);
    }

    public function getCurrentActiveDashboard(string $resourceUri): ?Dashboard
    {

        /**
         * @var ExampleDashboard $dashboard
         */
        foreach ($this->dashboards as $dashboard) {

            if ($dashboard::uriKey() === $resourceUri) {

                return new $dashboard();

            }

        }

        return null;

    }

}
