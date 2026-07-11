<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceModelResource\Pages;
use App\Models\DeviceModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceModelResource extends Resource
{
    protected static ?string $model = DeviceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Device Management';

    protected static ?string $modelLabel = 'Device Model';

    protected static ?string $pluralModelLabel = 'Device Models';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'water' => 'Water (Automatic Watering System)',
                        'clock' => 'Clock (Connected Alarm Clock)',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'water' => 'info',
                        'clock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('devices_count')
                    ->counts('devices')
                    ->label('Devices'),
                Tables\Columns\TextColumn::make('firmwares_count')
                    ->counts('firmwares')
                    ->label('Firmware Versions'),
                Tables\Columns\TextColumn::make('modified')
                    ->label('Modified')
                    ->dateTime()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->modified ? \Carbon\Carbon::createFromTimestamp($record->modified) : null;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceModels::route('/'),
            'create' => Pages\CreateDeviceModel::route('/create'),
            'edit' => Pages\EditDeviceModel::route('/{record}/edit'),
        ];
    }
}
