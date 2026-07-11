<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\Metric;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MetricsChart extends ChartWidget
{
    protected static ?string $heading = 'Device Metrics (Last 7 Days)';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 10;

    protected function getData(): array
    {
        $user = auth()->user();
        $devices = $user->isAdmin()
            ? Device::with('deviceModel')->get()
            : $user->devices()->with('deviceModel')->get();

        if ($devices->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $since = Carbon::now()->subDays(7);

        $datasets = [];
        $allLabels = collect();

        foreach ($devices as $device) {
            $metrics = Metric::where('device_id', $device->id)
                ->where('metric_date', '>=', $since)
                ->orderBy('metric_date')
                ->get();

            $grouped = $metrics->groupBy('metric_type');

            foreach ($grouped as $type => $entries) {
                $label = $device->serialnumber . ' - ' . Metric::typeLabel($type);
                $allLabels->push(...$entries->pluck('metric_date')->values()->all());

                $datasets[] = [
                    'label' => $label,
                    'data' => $entries->map(fn ($e) => [
                        'x' => Carbon::parse($e->metric_date)->format('Y-m-d H:i'),
                        'y' => $e->metric_value,
                    ])->toArray(),
                ];
            }
        }

        $labels = $allLabels
            ->map(fn ($d) => $d instanceof Carbon ? $d->format('M d H:i') : $d)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [
            'datasets' => array_map(function ($ds) use ($labels) {
                $dataMap = collect($ds['data'])->pluck('y', 'x')->toArray();
                return [
                    'label' => $ds['label'],
                    'data' => array_map(fn ($l) => $dataMap[$l] ?? null, $labels),
                    'borderColor' => $this->getColorForLabel($ds['label']),
                    'tension' => 0.3,
                    'fill' => false,
                ];
            }, $datasets),
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'type' => 'category',
                ],
            ],
        ];
    }

    private function getColorForLabel(string $label): string
    {
        $colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];
        $hash = crc32($label);
        return $colors[abs($hash) % count($colors)];
    }
}
