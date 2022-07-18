<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\CarbonImmutable;
use DateTimeImmutable;

class BarChart
{
    /**
     * Renders a bar chart using the "components/barchart"  view.
     *
     * @param array<string> $rowLabels Map row key => row label
     * @param array<string, string> $colors Map row key => color
     * @param array<mixed, array> $data The chart data group key => [ row name => value ]
     */
    public static function render(
        array $rowLabels,
        array $colors,
        array $data
    ): string {
        $groupLabels = [];
        $max = 0;

        foreach ($data as $groupKey => $groupData) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $groupKey);
            $groupLabels[$groupKey] = $groupKey;

            if ($date) {
                $groupLabels[$groupKey] = $date->format(__('Y-m-d'));
            }

            foreach ($rowLabels as $rowKey => $rowName) {
                $max = max($max, $groupData[$rowKey]);
            }
        }

        $roundedMax = $max === 0
            ? 5
            : (int) ceil($max / 5) * 5;

        return view('components/barchart', [
            'groups' => self::calculateChartGroups(
                $rowLabels,
                $colors,
                $data,
                $roundedMax,
                $groupLabels
            ),
            'colors' => $colors,
            'rowLabels' => $rowLabels,
            'barChartClass' => self::calculateBarChartClass($rowLabels, $data),
            'yLabels' => self::calculateYLabels($roundedMax),
        ]);
    }

    private static function calculateChartGroups(
        array $rowLabels,
        array $colors,
        array $data,
        int $max,
        array $groupLabels
    ): array {
        $chartGroups = [];

        foreach ($data as $groupKey => $groupData) {
            $group = [
                'label' => $groupLabels[$groupKey],
                'bars' => [],
            ];

            foreach ($rowLabels as $rowKey => $rowName) {
                $value = $groupData[$rowKey];
                $group['bars'][] = [
                    'value' => $value,
                    'title' => $group['label'] . "\n" . $rowName . ': ' . $value,
                    'height' => $max === 0 ? '0%' : ($value / $max * 100) . '%',
                    'bg' => $colors[$rowKey],
                ];
            }

            $chartGroups[] = $group;
        }

        return $chartGroups;
    }

    /**
     * @param int $max Max Y value
     * @return array<array{label: string, bottom: string}>
     */
    private static function calculateYLabels(int $max): array
    {
        $step = $max / 5;
        $yLabels = [];

        for ($y = 0; $y <= $max; $y += $step) {
            $yLabels[] = [
                'label' => $y,
                'bottom' => $max === 0 ? '0%' : ($y / $max * 100) . '%',
            ];
        }

        return $yLabels;
    }

    private static function calculateBarChartClass(array $rowLabels, array $data): string
    {
        $bars = count($data) * count($rowLabels);

        if ($bars >= 50) {
            return 'barchart-50';
        }

        if ($bars >= 40) {
            return 'barchart-40';
        }

        if ($bars >= 20) {
            return 'barchart-20';
        }

        return '';
    }

    /**
     * Generates bar chart demo data.
     *
     * @param int $days Number of days to generate data for
     * @return array ready to be passed to BarChart::render
     */
    public static function generateChartDemoData(int $days): array
    {
        $step = $days === 0 ? 0 : floor(10000 / $days + 1);
        $now = CarbonImmutable::now();
        $twoWeeksAgo = $now->subDays($days);
        $current = $twoWeeksAgo;

        $demoData = [];
        $count = 1;

        while ($current->isBefore($now)) {
            $current_key = $current->format('Y-m-d');
            $demoData[$current_key] = [
                'day' => $current_key,
                'count' => $step,
                'sum' => $step * $count,
            ];
            $current = $current->addDay(1);
            $count++;
        }

        return [
            [
                'count' => __('arrived'),
                'sum'   => __('arrived sum'),
            ],
            [

                'count' => '#090',
                'sum'   => '#888'
            ],
            $demoData,
        ];
    }
}
