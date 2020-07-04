<?php

namespace DigitalCreative\NovaBi\Dashboards\Example;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Widgets\SocialMediaWidget;

class FacebookExampleWidget extends SocialMediaWidget
{
    public function type(): string
    {
        return 'facebook';
    }

    public function resolveValue(Filters $filters): array
    {
        return [
            [
                '800k',
                'followers'
            ],
            [
                '1800k',
                'likes'
            ]
        ];
    }
}
