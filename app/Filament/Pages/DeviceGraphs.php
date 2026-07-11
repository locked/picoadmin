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

            $metrics = Metric::where('device_id', $device->id)
                ->whereIn('metric_type', $allowedTypes)
                ->where('metric_date', '>=', now()->subDays(7))
                ->orderBy('metric_date')
                ->get()
                ->groupBy('metric_type');

            $deviceMetrics = [];

            foreach ($metrics as $type => $entries) {
                $deviceMetrics[] = [
                    'type' => $type,
                    'label' => Metric::typeLabel($type),
                    'entries' => $entries->map(fn ($e) => [
                        'date' => $e->metric_date->format('Y-m-d H:i'),
                        'value' => $e->metric_value,
                    ])->toArray(),
                ];
            }

            if (!empty($deviceMetrics)) {
                $result[] = [
                    'device' => $device,
                    'metrics' => $deviceMetrics,
                ];
            }
        }

        return $result;
    }
}
