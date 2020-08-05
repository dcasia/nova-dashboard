<?php

namespace DigitalCreative\NovaDashboard\Examples\Widgets;

use DigitalCreative\ChartJsWidget\BarChartWidget;
use DigitalCreative\ChartJsWidget\BarChatStyle;
use DigitalCreative\ChartJsWidget\DataSet;
use DigitalCreative\ChartJsWidget\Options;
use DigitalCreative\ChartJsWidget\ValueResult;
use DigitalCreative\NovaDashboard\Filters;
use Illuminate\Support\Collection;

class BarChartExampleWidget extends BarChartWidget
{

    public function getRandomData($min = 1, $max = 100): array
    {
        return [
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
            random_int($min, $max),
        ];
    }

    public function resolveValue(Collection $options, Filters $filters): ValueResult
    {

        $configuration = BarChatStyle::make()
                                     ->hoverBackgroundColor('green');

        $dataSet1 = DataSet::make('Sample A', $this->getRandomData(), $configuration);
        $dataSet2 = DataSet::make('Sample B', $this->getRandomData(), $configuration);
        $dataSet3 = DataSet::make('Sample C', $this->getRandomData(), $configuration);
        $dataSet4 = DataSet::make('Sample D', $this->getRandomData(), $configuration);

        return $this->value()
                    ->labels($this->getRandomData())
                    ->addDataset($dataSet1, $dataSet2, $dataSet3, $dataSet4);

    }

    public function defaults(): array
    {
        return [
            'layout' => [
                'padding' => [
                    'left' => 50,
                    'right' => 50,
                    'top' => 50,
                    'bottom' => 50,
                ]
            ],
            'legend' => [
                'display' => false
            ]
        ];
    }

}
