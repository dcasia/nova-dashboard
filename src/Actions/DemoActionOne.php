<?php

namespace DigitalCreative\NovaDashboard\Actions;

use DigitalCreative\NovaDashboard\Action;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class DemoActionOne extends Action
{

    public function execute(ActionFields $fields, Filters $filters): ?array
    {
        return self::message('You are awesome!');
    }

    public function fields(): array
    {
        return [

            Text::make('Validation')
                ->rules('required')
                ->help('Validation works everywhere \o/'),

            Select::make('Type')->options([
                'Example 1',
                'Example 2',
                'Example 3',
            ])

        ];
    }

}
