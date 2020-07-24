<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Laravel\Nova\Actions\Action as BaseAction;
use Laravel\Nova\Fields\ActionFields;

abstract class Action extends BaseAction
{
    public abstract function execute(ActionFields $fields, Filters $filters): ?array;
}
