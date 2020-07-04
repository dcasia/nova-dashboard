<?php

namespace DigitalCreative\NovaBi;

use DigitalCreative\NovaBi\Dashboards\Example\ExampleDashboard;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaBi extends Tool
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

        /**
         * @var NovaRequest $request
         */
        $request = resolve(NovaRequest::class);

        $novaPath = Nova::path();
        $resourceSlug = Str::of($request->getPathInfo())->after("$novaPath/nova-bi/");

        if ($instance = $this->getCurrentActiveDashboard($resourceSlug)) {

            Nova::provideToScript([
                'nova-bi' => $instance->jsonSerialize()
            ]);

        }

        Nova::script('nova-bi', __DIR__ . '/../dist/js/tool.js');

    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return View
     */
    public function renderNavigation(): View
    {
        return view('nova-bi::navigation', [ 'dashboards' => $this->dashboards ]);
    }

    public function getCurrentActiveDashboard(string $resourceUri): ?ExampleDashboard
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
