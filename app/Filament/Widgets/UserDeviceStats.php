<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserDeviceStats extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $userId = auth()->id();
        $devices = Device::where('user_id', $userId)->with('deviceModel')->get();

        $waterCount = $devices->where('deviceModel.type', 'water')->count();
        $clockCount = $devices->where('deviceModel.type', 'clock')->count();

        return [
            Stat::make('My Devices', $devices->count())
                ->description('Assigned to you')
                ->descriptionIcon('heroicon-m-cpu-chip'),
            Stat::make('Water Devices', $waterCount)
                ->description('Watering systems')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),
            Stat::make('Clock Devices', $clockCount)
                ->description('Alarm clocks')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
        ];
    }
}
