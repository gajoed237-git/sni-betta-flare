<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JudgeResource\Pages;
use App\Filament\Resources\JudgeResource\RelationManagers;
use App\Models\User;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JudgeResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.management_judge');
    }

    public static function getModelLabel(): string
    {
        return __('messages.roles.judge');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('role', 'judge');
        $user = auth()->user();

        // If user is event admin, only show judges assigned to their managed events
        if ($user && $user->isEventAdmin()) {
            $managedEventIds = $user->managed_events()->pluck('events.id');

            $query->whereHas('assigned_judging_events', function ($q) use ($managedEventIds) {
                $q->whereIn('events.id', $managedEventIds);
            });
        }

        return $query;
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->hiddenOn('edit')
                    ->dehydrateStateUsing(fn($state) => \Illuminate\Support\Facades\Hash::make($state)),
                Forms\Components\Hidden::make('role')->default('judge'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('events_judged_count')
                    ->label(__('messages.fields.total_score')) // Using existing key for 'Total Score' or similar
                    ->counts('events_judged')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_event')
                    ->label('Event Berlangsung')
                    ->query(fn(Builder $query) => $query->whereHas('scores', function ($query) {
                        $query->whereHas('fish', function ($query) {
                            $query->whereHas('event', function ($query) {
                                $query->where('is_active', true);
                            });
                        });
                    })),
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
            'index' => Pages\ManageJudges::route('/'),
        ];
    }
}
