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

        $since = Carbon::now()->subDays(7)->startOfHour();

        $deviceIds = $devices->pluck('id')->all();
        $deviceById = $devices->keyBy('id');

        $rows = Metric::whereIn('device_id', $deviceIds)
            ->where('metric_date', '>=', $since)
            ->where('metric_type', '!=', Metric::TYPE_PUMP)
            ->where('metric_type', '!=', Metric::TYPE_MEM_FREE)
            ->where(function ($query) {
                $query->where('metric_type', '!=', Metric::TYPE_STCC4_CO2)
                    ->orWhere('metric_value', '<=', 5000);
            })
            ->selectRaw(
                'device_id, metric_type, DATE_FORMAT(metric_date, "%Y-%m-%d %H:00") as hour_bucket, AVG(metric_value) as avg_value'
            )
            ->groupBy('device_id', 'metric_type', 'hour_bucket')
            ->orderBy('hour_bucket')
            ->get();

        $labels = [];
        $cursor = $since->copy();
        $now = Carbon::now();
        while ($cursor->lessThanOrEqualTo($now)) {
            $labels[] = $cursor->format('Y-m-d H:00');
            $cursor->addHour();
        }

        $points = [];
        foreach ($rows as $row) {
            $points[$row->device_id . '.' . $row->metric_type . '.' . $row->hour_bucket] = round((float) $row->avg_value, 1);
        }

        $datasets = [];
        foreach ($rows->groupBy(fn ($r) => $r->device_id . '|' . $r->metric_type) as $key => $group) {
            [$deviceId, $type] = explode('|', $key);
            $device = $deviceById[$deviceId] ?? null;
            if (!$device) {
                continue;
            }

            $label = ($device->name ?? $device->serialnumber) . ' - ' . Metric::typeLabel((int) $type);

            $data = [];
            foreach ($labels as $l) {
                $data[] = $points[$deviceId . '.' . $type . '.' . $l] ?? null;
            }

            $datasets[] = [
                'label' => $label,
                'data' => $data,
                'borderColor' => $this->getColorForLabel($label),
                'tension' => 0.3,
                'fill' => false,
                'pointRadius' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_map(fn ($l) => Carbon::parse($l)->format('M d H:i'), $labels),
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
            'maintainAspectRatio' => true,
            'aspectRatio' => 3,
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
