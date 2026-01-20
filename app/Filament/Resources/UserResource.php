<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->label(__('messages.fields.address'))
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('profile_photo_path')
                    ->image()
                    ->directory('profile-photos')
                    ->columnSpanFull(),
                Forms\Components\Select::make('role')
                    ->label(__('messages.fields.role'))
                    ->options([
                        'admin' => __('messages.roles.admin'),
                        'judge' => __('messages.roles.judge'),
                        'participant' => __('messages.roles.participant'),
                        'event_admin' => __('messages.roles.event_admin'),
                    ])
                    ->required()
                    ->default('participant'),
                Forms\Components\Toggle::make('otp_enabled')
                    ->label('Enable OTP Login')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('messages.fields.is_active'))
                    ->default(true)
                    ->required()
                    ->afterStateUpdated(function ($state, $record) {
                        if (!$state) {
                            \App\Models\SecurityLog::create([
                                'type' => 'user_block',
                                'event' => 'User Account Blocked',
                                'details' => "Admin " . auth()->user()->name . " blocked user: " . $record->name . " (" . $record->email . ")",
                                'ip_address' => request()->ip(),
                                'user_id' => $record->id,
                                'metadata' => ['blocked_by' => auth()->id()]
                            ]);
                        } else {
                            \App\Models\SecurityLog::create([
                                'type' => 'user_block',
                                'event' => 'User Account Unblocked',
                                'details' => "Admin " . auth()->user()->name . " unblocked user: " . $record->name . " (" . $record->email . ")",
                                'ip_address' => request()->ip(),
                                'user_id' => $record->id,
                                'metadata' => ['unblocked_by' => auth()->id()]
                            ]);
                        }
                    }),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => filled($state) ? \Illuminate\Support\Facades\Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_path')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('messages.fields.status'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('otp_enabled')
                    ->label('2FA (OTP)')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('role')
                    ->label(__('messages.fields.role'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin' => __('messages.roles.admin'),
                        'judge' => __('messages.roles.judge'),
                        'participant' => __('messages.roles.participant'),
                        'event_admin' => __('messages.roles.event_admin'),
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'judge' => 'warning',
                        'event_admin' => 'info',
                        'participant' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->isAdmin();
    }
}
