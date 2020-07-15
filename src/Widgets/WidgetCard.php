<?php

namespace DigitalCreative\NovaBi\Widgets;

use Laravel\Nova\Card;

class WidgetCard extends Card
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
