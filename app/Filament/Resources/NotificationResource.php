<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.notifications');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->nullable()
                    ->placeholder(__('messages.fields.all_users'))
                    ->searchable(),
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->nullable()
                    ->placeholder(__('messages.fields.all_events'))
                    ->searchable()
                    ->helperText(__('messages.fields.broadcast_global_helper')),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'fish_status' => __('messages.fields.notification_fish_status'),
                        'payment' => __('messages.fields.notification_payment'),
                        'system' => __('messages.fields.notification_system'),
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('read_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('messages.fields.target_user'))
                    ->placeholder(__('messages.fields.all_users'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label(__('messages.fields.target_event'))
                    ->placeholder(__('messages.fields.all_events'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fish_status' => 'info',
                        'payment' => 'success',
                        'system' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('read_at')
                    ->label(__('messages.fields.is_read'))
                    ->boolean(fn($state) => $state !== null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fish_status' => __('messages.fields.notification_fish_status'),
                        'payment' => __('messages.fields.notification_payment'),
                        'system' => __('messages.fields.notification_system'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageNotifications::route('/'),
        ];
    }
}
