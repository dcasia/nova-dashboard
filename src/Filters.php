<?php

declare(strict_types = 1);

namespace DigitalCreative\NovaDashboard;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Filters\FilterDecoder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Query\ApplyFilter;

class Filters
{
    public function __construct(
        private readonly FilterDecoder $filterDecoder,
    )
    {
    }

    public static function fromRequest(Request $request, Collection $filters): static
    {
        $encodedFilters = $request->input(sprintf('%s_filter', $request->input('view')));

        return new static(
            new FilterDecoder($encodedFilters, $filters),
        );
    }

    public function getFilterValue(string $filterClass, ?string $name = null): mixed
    {
        return $this->filterDecoder
            ->filters()
            ->firstWhere(function (ApplyFilter $filter) use ($filterClass, $name) {

                $isInstanceOf = $filter->filter instanceof $filterClass;

                if ($name) {
                    return $isInstanceOf && $filter->filter->name() === $name;
                }

                return $isInstanceOf;

            })?->value;
    }

    public function applyToQueryBuilder(Builder $builder): Builder
    {
        $request = resolve(NovaRequest::class);

        foreach ($this->filterDecoder->filters() as $filter) {
            call_user_func($filter, $request, $builder);
        }

        return $builder;
    }
}
