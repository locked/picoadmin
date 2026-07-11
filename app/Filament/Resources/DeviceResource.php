<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Filament\Resources\DeviceResource\RelationManagers\AlarmsRelationManager;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Device Management';

    protected static ?string $modelLabel = 'Device';

    protected static ?string $pluralModelLabel = 'Devices';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\Select::make('device_model_id')
                            ->relationship('deviceModel', 'type')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('serialnumber')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Unassigned'),
                    ])->columns(3),
                Forms\Components\Section::make('Firmware')
                    ->schema([
                        Forms\Components\TextInput::make('target_firmware')
                            ->maxLength(255)
                            ->placeholder('e.g. 1.2.0'),
                        Forms\Components\TextInput::make('current_firmware')
                            ->maxLength(255)
                            ->readOnly()
                            ->placeholder('Set by device'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deviceModel.type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'water' => 'info',
                        'clock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('serialnumber')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_firmware')
                    ->label('Target')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_firmware')
                    ->label('Current')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Assigned To')
                    ->searchable()
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('modified')
                    ->label('Modified')
                    ->dateTime()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->modified ? \Carbon\Carbon::createFromTimestamp($record->modified) : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('deviceModel')
                    ->relationship('deviceModel', 'type')
                    ->label('Device Type'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'email')
                    ->label('Assigned User'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
