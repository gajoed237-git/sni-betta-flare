<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BettaClassResource\Pages;
use App\Models\BettaClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BettaClassResource extends Resource
{
    protected static ?string $model = BettaClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.classes');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event', 'division']);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            return true;
        }

        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->exists();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label(__('messages.resources.events'))
                    ->relationship('event', 'name', function ($query) use ($user) {
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
                Forms\Components\Select::make('division_id')
                    ->label(__('messages.resources.divisions'))
                    ->relationship('division', 'name', function ($query, $get) {
                        $eventId = $get('event_id');
                        if ($eventId) {
                            $query->where('event_id', $eventId);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label(__('messages.fields.code'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label(__('messages.resources.events'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->label(__('messages.resources.divisions'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('messages.fields.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.fields.start_date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBettaClasses::route('/'),
            'create' => Pages\CreateBettaClass::route('/create'),
            'edit' => Pages\EditBettaClass::route('/{record}/edit'),
        ];
    }
}
