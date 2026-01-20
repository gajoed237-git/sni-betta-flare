<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityLogResource\Pages;
use App\Models\SecurityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SecurityLogResource extends Resource
{
    protected static ?string $model = SecurityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 100;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.fields.security_logs');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type'),
                Forms\Components\TextInput::make('event'),
                Forms\Components\Textarea::make('details')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address'),
                Forms\Components\TextInput::make('user_agent'),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'security' => 'danger',
                        'malfunction' => 'warning',
                        'user_block' => 'gray',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('event')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Guest'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'security' => 'Security',
                        'malfunction' => 'Malfunction',
                        'user_block' => 'User Block',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSecurityLogs::route('/'),
        ];
    }
}
