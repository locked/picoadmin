<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use App\Models\Alarm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AlarmsRelationManager extends RelationManager
{
    protected static string $relationship = 'alarms';

    protected static ?string $title = 'Alarms';

    public function isReadOnly(): bool
    {
        return !auth()->user()->isAdmin() && !$this->getOwnerRecord()->user_id;
    }

    public function canCreate(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_set')
                    ->label('Enabled')
                    ->default(true),
                Forms\Components\TextInput::make('weekdays')
                    ->label('Weekdays Bitmask')
                    ->required()
                    ->numeric()
                    ->helperText('Mon=64, Tue=32, Wed=16, Thu=8, Fri=4, Sat=2, Sun=1'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('hour')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        Forms\Components\TextInput::make('minute')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59),
                    ]),
                Forms\Components\Select::make('week')
                    ->options([
                        'all' => 'All Weeks',
                        'odd' => 'Odd Weeks',
                        'even' => 'Even Weeks',
                    ])
                    ->default('all')
                    ->required(),
                Forms\Components\TextInput::make('chime')
                    ->placeholder('e.g. Tellement.wav'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('chime')
            ->columns([
                Tables\Columns\IconColumn::make('is_set')
                    ->boolean()
                    ->label('On'),
                Tables\Columns\TextColumn::make('weekdays')
                    ->label('Days')
                    ->getStateUsing(function ($record) {
                        $days = [];
                        $map = [
                            64 => 'Mon', 32 => 'Tue', 16 => 'Wed',
                            8 => 'Thu', 4 => 'Fri', 2 => 'Sat', 1 => 'Sun',
                        ];
                        foreach ($map as $bit => $day) {
                            if ($record->weekdays & $bit) {
                                $days[] = $day;
                            }
                        }
                        return implode(', ', $days) ?: 'None';
                    }),
                Tables\Columns\TextColumn::make('hour')
                    ->label('Time')
                    ->getStateUsing(fn ($record) => sprintf('%02d:%02d', $record->hour, $record->minute)),
                Tables\Columns\TextColumn::make('week'),
                Tables\Columns\TextColumn::make('chime'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
