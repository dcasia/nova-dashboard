<?php

namespace DigitalCreative\NovaDashboard;

use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;

class WidgetResource extends AbstractResource
{

    /**
     * @var Dashboard
     */
    private $resource;

    /**
     * WidgetResource constructor.
     *
     * @param string $resource
     */
    public function __construct(string $resource)
    {
        $this->resource = $resource;

        parent::__construct([]);
    }

    /**
     * Specify data which should be serialized to JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'raw_resource',
            'badge' => $this->getBadge(),
            'icon' => $this->getIcon(),
            'label' => $this->getLabel(),
            'router' => [
                'name' => 'nova-dashboard',
                'params' => [
                    'dashboardKey' => resolve($this->resource)->uriKey(),
                ],
            ],
        ];
    }
}
