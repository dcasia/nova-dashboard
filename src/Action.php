<?php

namespace DigitalCreative\NovaDashboard;

use Laravel\Nova\Actions\Action as BaseAction;
use Laravel\Nova\Fields\ActionFields;

abstract class Action extends BaseAction
{
    abstract public function execute(ActionFields $fields, Filters $filters): ?array;
}
