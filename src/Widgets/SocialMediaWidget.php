<?php

namespace DigitalCreative\NovaBi\Widgets;

abstract class SocialMediaWidget extends Widget
{

    public function __construct(int $x, int $y, int $width, int $height)
    {
        parent::__construct($x, $y, $width, $height);

        $this->withMeta([ 'type' => $this->type() ]);
    }


    public function component(): string
    {
        return 'social-media-widget';
    }

    abstract public function type(): string;

}
