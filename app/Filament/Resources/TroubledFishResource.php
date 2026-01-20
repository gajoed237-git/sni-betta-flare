<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TroubledFishResource\Pages;
use App\Models\Fish;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TroubledFishResource extends Resource
{
    protected static ?string $model = Fish::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.dq_moved');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('registration_no')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'disqualified' => 'Disqualified',
                        'moved' => 'Moved',
                    ])->required(),
                Forms\Components\Textarea::make('admin_note')
                    ->label(__('messages.fields.reason_note'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_no')
                    ->label(__('messages.fields.reg_no'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label(__('messages.fields.participant'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'disqualified' => 'danger',
                        'moved' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('bettaClass.name')
                    ->label(__('messages.fields.current_class')),
                Tables\Columns\TextColumn::make('originalClass.name')
                    ->label(__('messages.fields.original_class'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('admin_note')
                    ->label(__('messages.fields.reason_note'))
                    ->limit(50)
                    ->tooltip(fn($record) => $record->admin_note),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'disqualified' => 'Disqualified',
                        'moved' => 'Moved',
                    ]),
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
            'index' => Pages\ManageTroubledFish::route('/'),
        ];
    }
}
