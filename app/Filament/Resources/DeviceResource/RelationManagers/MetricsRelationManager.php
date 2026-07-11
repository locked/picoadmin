<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use App\Models\Metric;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MetricsRelationManager extends RelationManager
{
    protected static string $relationship = 'metrics';

    protected static ?string $title = 'Metrics';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('metric_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('metric_type')
                    ->label('Type')
                    ->getStateUsing(fn ($record) => Metric::typeLabel($record->metric_type))
                    ->badge()
                    ->color(fn ($record): string => match ($record->metric_type) {
                        Metric::TYPE_TEMPERATURE => 'warning',
                        Metric::TYPE_CO2 => 'danger',
                        Metric::TYPE_WATER_LEVEL => 'info',
                        Metric::TYPE_BATTERY, Metric::TYPE_BATTERY2 => 'success',
                        Metric::TYPE_HUMIDITY => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('metric_value')
                    ->label('Value')
                    ->sortable(),
            ])
            ->defaultSort('metric_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
