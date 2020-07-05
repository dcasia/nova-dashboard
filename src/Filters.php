<?php

namespace DigitalCreative\NovaBi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
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

    public function applyToQueryBuilder(Builder $builder): Builder
    {
        return tap($builder, function (Builder $builder) {
            $this->resolvedFilters()
                 ->each(static function (ApplyFilter $applyFilter) use ($builder) {
                     $applyFilter->filter->apply(resolve(NovaRequest::class), $builder, $applyFilter->value);
                 });
        });
    }

    public static function fromUnencodedFilters(array $availableFilters): self
    {

        $result = collect($availableFilters)
            ->map(fn(Filter $filter, string $filterClass) => [ 'class' => get_class($filter), 'value' => $filter->meta[ 'currentValue' ] ])
            ->values();

        return new self(base64_encode(json_encode($result, JSON_THROW_ON_ERROR)), $availableFilters);

    }

//    public function encodeFilter()
//    {
//
//        $this->filters
//
//    }

}
