<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FirmwareResource\Pages;
use App\Models\Firmware;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FirmwareResource extends Resource
{
    protected static ?string $model = Firmware::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Device Management';

    protected static ?string $modelLabel = 'Firmware';

    protected static ?string $pluralModelLabel = 'Firmware';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Firmware Information')
                    ->schema([
                        Forms\Components\Select::make('device_model_id')
                            ->relationship('deviceModel', 'type')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('version')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. 1.0.0'),
                    ])->columns(2),
                Forms\Components\Section::make('Firmware Binary')
                    ->schema([
                        Forms\Components\FileUpload::make('data')
                            ->label('Firmware File')
                            ->required()
                            ->binary()
                            ->downloadable()
                            ->previewable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deviceModel.type')
                    ->label('Device Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'water' => 'info',
                        'clock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('firmware.download', $record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFirmware::route('/'),
            'create' => Pages\CreateFirmware::route('/create'),
            'edit' => Pages\EditFirmware::route('/{record}/edit'),
        ];
    }
}
