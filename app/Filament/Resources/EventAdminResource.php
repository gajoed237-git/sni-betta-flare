<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventAdminResource\Pages;
use App\Models\User;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventAdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.roles.event_admin');
    }

    public static function getModelLabel(): string
    {
        return __('messages.roles.event_admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'event_admin');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin(); // Only superadmin can manage event admins
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn('edit')
                    ->dehydrateStateUsing(fn($state) => \Illuminate\Support\Facades\Hash::make($state)),
                Forms\Components\Select::make('managed_events')
                    ->label(__('messages.resources.events'))
                    ->multiple()
                    ->relationship('managed_events', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\Hidden::make('role')->default('event_admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('managed_events.name')
                    ->label(__('messages.resources.events'))
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Filter by Event')
                    ->relationship('managed_events', 'name'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEventAdmins::route('/'),
        ];
    }
}
