<?php

namespace DigitalCreative\NovaDashboard\Examples\Widgets;

use DigitalCreative\ChartJsWidget\DataSet;
use DigitalCreative\ChartJsWidget\Gradient;
use DigitalCreative\ChartJsWidget\LineChartWidget;
use DigitalCreative\ChartJsWidget\Style;
use DigitalCreative\ChartJsWidget\ValueResult;
use DigitalCreative\NovaDashboard\Filters;
use Illuminate\Support\Collection;

class LineChartExample2Widget extends LineChartWidget
{

    public function getRandomData($min = 0, $max = 100): array
    {
        return array_rand(range($min, $max), 12);
    }

    public function getMonthsInTheYear(): array
    {

        return array_map(static function ($month) {
            return now()->startOfMonth()->setMonth($month)->format('M');
        }, range(1, 12));

    }

    public function resolveValue(Collection $options, Filters $filters): ValueResult
    {

        /**
         * Some basic stylish settings
         */
        $dataset1Gradient = new Gradient([ '#bc00dd', '#a100f2', '#540d6e' ]);
        $dataset2Gradient = new Gradient([ '#8d99ae', '#33415c' ]);

        $style = Style::make();

        $dataSet1 = DataSet::make(
            'Downloads',
            $this->getRandomData(0, 30),
            $style->color($dataset1Gradient)
                  ->background($dataset1Gradient->opacity(.1))
        );

        $dataSet2 = DataSet::make(
            'Purchases',
            $this->getRandomData(),
            $style->color($dataset2Gradient)
                  ->background($dataset2Gradient->opacity(.1))
        );

        return ValueResult::make()
                          ->labels($this->getMonthsInTheYear())
                          ->addDataset($dataSet1)
                          ->addDataset($dataSet2);

    }

}
