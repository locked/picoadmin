<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('firstname')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('lastname')
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options([
                                'user' => 'Normal User',
                                'admin' => 'Administrator',
                            ])
                            ->default('user')
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Device Assignment')
                    ->schema([
                        Forms\Components\Placeholder::make('devices_info')
                            ->content(function (?User $record) {
                                if (!$record) return 'Save the user first to assign devices.';
                                $count = $record->devices()->count();
                                return "{$count} device(s) assigned.";
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('devices_count')
                    ->counts('devices')
                    ->label('Devices'),
                Tables\Columns\TextColumn::make('created')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->created ? \Carbon\Carbon::createFromTimestamp($record->created) : null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'user' => 'Normal User',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
