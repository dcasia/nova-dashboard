<?php

namespace DigitalCreative\NovaDashboard;

use Laravel\Nova\Card as BaseCard;

class Card extends BaseCard
{

    public Widget $instance;

    public function __construct(string $component, array $options = [])
    {
        $this->instance = resolve($component);

        parent::__construct($this->instance->component());

        $this->options($options);
    }

    public function options(array $options): self
    {
        return $this->withMeta([ 'options' => $options ]);
    }

}
