<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviceStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users'),
            Stat::make('Total Devices', Device::count())
                ->description('Registered devices')
                ->descriptionIcon('heroicon-m-cpu-chip'),
            Stat::make('Water Devices', Device::whereHas('deviceModel', fn ($q) => $q->where('type', 'water'))->count())
                ->description('Watering systems')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),
            Stat::make('Clock Devices', Device::whereHas('deviceModel', fn ($q) => $q->where('type', 'clock'))->count())
                ->description('Alarm clocks')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
            Stat::make('Device Models', DeviceModel::count())
                ->description('Registered models')
                ->descriptionIcon('heroicon-m-square-3-stack-3d'),
        ];
    }
}
