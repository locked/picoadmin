<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Models\Metric;
use Filament\Pages\Page;

class DeviceGraphs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'My Devices';

    protected static ?string $title = 'Device Graphs';

    protected static string $view = 'filament.pages.device-graphs';

    const CHART_GROUPS = [
        [
            'key' => 'co2_tvoc',
            'label' => 'CO2 / TVOC',
            'types' => [Metric::TYPE_CO2, Metric::TYPE_ECO2, Metric::TYPE_SCD43_CO2, Metric::TYPE_STCC4_CO2, Metric::TYPE_TVOC],
        ],
        [
            'key' => 'battery',
            'label' => 'Battery',
            'types' => [Metric::TYPE_BATTERY, Metric::TYPE_BATTERY2],
        ],
    ];

    const CHART_COLORS = [
        '#3b82f6',
        '#ef4444',
        '#22c55e',
        '#f59e0b',
        '#8b5cf6',
        '#ec4899',
        '#06b6d4',
    ];

    private function buildChartGroups(array $allowedTypes, $metricsByType): array
    {
        $groups = [];
        $assignedTypes = [];

        foreach (self::CHART_GROUPS as $groupDef) {
            $intersect = array_intersect($groupDef['types'], $allowedTypes);
            if (empty($intersect)) {
                continue;
            }

            $datasets = [];
            $colorIdx = 0;
            foreach ($intersect as $type) {
                if (!isset($metricsByType[$type])) {
                    continue;
                }
                $entries = $metricsByType[$type];
                $datasets[] = [
                    'label' => Metric::typeLabel($type),
                    'data' => $entries->map(fn ($e) => [
                        'date' => $e->metric_date->format('Y-m-d H:i'),
                        'value' => $e->metric_value,
                    ])->values()->toArray(),
                    'color' => self::CHART_COLORS[$colorIdx % count(self::CHART_COLORS)],
                ];
                $colorIdx++;
                $assignedTypes[] = $type;
            }

            if (!empty($datasets)) {
                $groups[] = [
                    'key' => $groupDef['key'],
                    'label' => $groupDef['label'],
                    'datasets' => $datasets,
                ];
            }
        }

        foreach ($allowedTypes as $type) {
            if (in_array($type, $assignedTypes)) {
                continue;
            }
            if (!isset($metricsByType[$type])) {
                continue;
            }
            $entries = $metricsByType[$type];
            $groups[] = [
                'key' => 'metric-' . $type,
                'label' => Metric::typeLabel($type),
                'datasets' => [
                    [
                        'label' => Metric::typeLabel($type),
                        'data' => $entries->map(fn ($e) => [
                            'date' => $e->metric_date->format('Y-m-d H:i'),
                            'value' => $e->metric_value,
                        ])->values()->toArray(),
                        'color' => self::CHART_COLORS[0],
                    ],
                ],
            ];
        }

        return $groups;
    }

    public function getDeviceMetrics(): array
    {
        $user = auth()->user();
        $devices = $user->isAdmin()
            ? Device::with('deviceModel')->get()
            : $user->devices()->with('deviceModel')->get();

        $result = [];

        foreach ($devices as $device) {
            $type = $device->deviceModel?->type;

            $allowedTypes = Metric::allowedTypesForDevice($type);

            $metricsByType = Metric::where('device_id', $device->id)
                ->whereIn('metric_type', $allowedTypes)
                ->where('metric_date', '>=', now()->subDays(7))
                ->orderBy('metric_date')
                ->get()
                ->groupBy('metric_type');

            $chartGroups = $this->buildChartGroups($allowedTypes, $metricsByType);

            if (!empty($chartGroups)) {
                $result[] = [
                    'device' => $device,
                    'chartGroups' => $chartGroups,
                ];
            }
        }

        return $result;
    }
}
