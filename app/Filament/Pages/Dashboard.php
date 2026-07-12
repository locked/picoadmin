<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Models\Metric;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets;

class Dashboard extends BaseDashboard
{
    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        $widgets = [];

        if (auth()->check()) {
            if (auth()->user()->isAdmin()) {
                $widgets[] = \App\Filament\Widgets\DeviceStatsOverview::class;
            } else {
                $widgets[] = \App\Filament\Widgets\UserDeviceStats::class;
            }

            $widgets[] = \App\Filament\Widgets\MetricsChart::class;
        }

        return $widgets;
    }
}
