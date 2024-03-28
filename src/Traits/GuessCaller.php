<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard\Traits;

use Laravel\Nova\Resource;
use Laravel\Nova\Dashboard;

trait GuessCaller
{
    protected Dashboard|Resource $caller;

    public function __construct(Dashboard|Resource $caller = null)
    {
        if ($caller) {

            $this->caller = $caller;

        } else {

            $caller = collect(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 5))
                ->firstWhere(function (array $data) {

                    return is_subclass_of(data_get($data, 'class'), Dashboard::class)
                        || is_subclass_of(data_get($data, 'class'), Resource::class);

                });

            $this->caller = $caller[ 'object' ];

        }
    }
}
