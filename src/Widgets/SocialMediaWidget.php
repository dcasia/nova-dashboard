<?php

namespace DigitalCreative\NovaBi\Widgets;

use DigitalCreative\NovaBi\Filters;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Select;

class SocialMediaWidget extends Widget
{

    public const TYPE_FACEBOOK = 'facebook';
    public const TYPE_TWITTER = 'twitter';

    public function component(): string
    {
        return 'social-media-widget';
    }

    public function fields(): array
    {
        return [
            Select::make('Type')
                  ->options([
                      self::TYPE_TWITTER => 'Twitter',
                      self::TYPE_FACEBOOK => 'Facebook',
                  ])
        ];
    }

    /**
     * @param Filters $filters
     * @param Collection $options
     *
     * @return string[][]
     */
    public function resolveValue(Collection $options, Filters $filters): array
    {

        if ($options->get('type') === self::TYPE_TWITTER) {

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
