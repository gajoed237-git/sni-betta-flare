<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HandlerResource\Pages;
use App\Models\Handler;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HandlerResource extends Resource
{
    protected static ?string $model = Handler::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.handlers');
    }

    public static function getModelLabel(): string
    {
        return __('messages.resources.handlers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label(__('messages.fields.description'))
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label(__('messages.fields.participant_count'))
                    ->counts('participants')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.fields.start_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListHandlers::route('/'),
            'create' => Pages\CreateHandler::route('/create'),
            'edit' => Pages\EditHandler::route('/{record}/edit'),
        ];
    }
}
