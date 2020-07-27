<?php

namespace DigitalCreative\NovaDashboard\Examples\Actions;

use DigitalCreative\NovaDashboard\Action;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;

class DemoActionTwo extends Action
{

    public function execute(ActionFields $fields, Filters $filters): ?array
    {
        return self::message('Action was executed successfully.');
    }

    public function fields(): array
    {
        return [
            Boolean::make('Background?')->help('Should this task run in background?')
        ];
    }

}
