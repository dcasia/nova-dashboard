<?php

namespace DigitalCreative\NovaBi;

use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Query\ApplyFilter;

class Filters extends FilterDecoder
{

    private Collection $filters;

    private function resolvedFilters(): Collection
    {
        return $this->filters ?? ($this->filters = $this->filters());
    }

    public function getFilterValue(string $filterClass, string $name = null)
    {

        /**
         * @var ApplyFilter|null $match
         */
        $match = $this->resolvedFilters()
                      ->first(static function (ApplyFilter $value) use ($filterClass, $name) {

                          $isInstanceOf = $value->filter instanceof $filterClass;

                          /**
                           * If name is defined try to match the name as well
                           */
                          if ($name) {

                              return $isInstanceOf && $value->filter->name() === $name;

                          }

                          return $isInstanceOf;

                      });

        if ($match) {

            return $match->value;

        }

        return null;

    }

}
