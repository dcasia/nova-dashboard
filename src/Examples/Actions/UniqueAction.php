<?php

namespace DigitalCreative\NovaDashboard\Examples\Actions;

use DigitalCreative\NovaDashboard\Action;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\SocialMediaWidget\Widgets\SocialMediaWidget;
use Laravel\Nova\Fields\ActionFields;

class UniqueAction extends Action
{

    public function execute(ActionFields $fields, Filters $filters): ?array
    {
        return self::message('This action is only available on this view.');
    }

}
