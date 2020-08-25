<?php

namespace DigitalCreative\NovaDashboard;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaDashboard extends Tool
{

    private array $dashboards;
    private bool $useNavigation = true;

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

    public function withoutNavigationMenu(): self
    {
        $this->useNavigation = false;

        return $this;
    }

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('nova-dashboard', __DIR__ . '/../dist/js/tool.js');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return View|null
     */
    public function renderNavigation(): ?View
    {

        $dashboards = $this->resolveDashboards();

        if ($dashboards->isNotEmpty() && $this->useNavigation) {

            return view('nova-dashboard::navigation', [ 'dashboards' => $dashboards ]);

        }

        return null;

    }

    public function getCurrentActiveDashboard(string $dashboardKey): ?Dashboard
    {

        /**
         * @var Dashboard $dashboard
         */
        foreach ($this->resolveDashboards() as $dashboard) {

            if ($dashboard->uriKey() === $dashboardKey) {

                if (is_string($dashboard) && class_exists($dashboard)) {

                    return new $dashboard();

                }

                return $dashboard;

            }

        }

        return null;

    }

    protected function resolveDashboards(): Collection
    {
        return once(function () {
            return collect($this->dashboards)
                ->map(static function ($dashboard) {
                    return $dashboard instanceof Dashboard ? $dashboard : resolve($dashboard);
                })
                ->filter(static function (Dashboard $dashboard) {
                    return $dashboard->authorizedToSee(request());
                });
        });
    }

}
