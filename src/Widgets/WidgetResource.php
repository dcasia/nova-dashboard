<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
use DigitalCreative\NovaBi\Dashboards\Dashboard;

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
                'name' => 'nova-widgets',
                'params' => [
                    'resource' => $this->resource::uriKey(),
                ],
            ],
        ];
    }
}
