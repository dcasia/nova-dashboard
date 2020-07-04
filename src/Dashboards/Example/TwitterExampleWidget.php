<?php

namespace DigitalCreative\NovaBi\Dashboards\Example;

use DigitalCreative\NovaBi\Filters;
use DigitalCreative\NovaBi\Widgets\SocialMediaWidget;

class TwitterExampleWidget extends SocialMediaWidget
{
    public function type(): string
    {
        return 'twitter';
    }

    public function resolveValue(Filters $filters): array
    {
        return [
            [
                '10k',
                'tweets'
            ],
            [
                '20k',
                'mentions'
            ],
            [
                '13k',
                'shares'
            ]
        ];
    }
}
