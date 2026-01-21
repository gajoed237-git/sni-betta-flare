<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantSfJuResource\Pages;
use App\Filament\Resources\ParticipantSfJuResource\RelationManagers;
use App\Models\Participant;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParticipantSfJuResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Monitoring SF/JU';
    protected static ?string $navigationLabel = 'Monitoring Peserta SF/JU';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereIn('category', ['single_fighter', 'team']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Peserta')
                    ->disabled(),
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'team' => 'JUARA UMUM (TEAM)',
                        'single_fighter' => 'SINGLE FIGHTER',
                        'other' => 'Lainnya (Bukan SF/JU)'
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, $set) => $state === 'single_fighter' ? $set('team_name', null) : null)
                    ->rules([
                        fn(Forms\Get $get, ?Participant $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (!$record) return;

                            $event = $record->event;
                            if (!$event) return;

                            $fishCount = $record->fishes()->count();

                            if ($value === 'single_fighter' && $event->sf_max_fish && $fishCount > $event->sf_max_fish) {
                                $fail("Gagal pindah ke SINGLE FIGHTER. Jumlah ikan peserta ({$fishCount}) melebihi kuota maksimal SF ({$event->sf_max_fish}).");
                            }

                            if ($value === 'team' && $event->ju_max_fish && $fishCount > $event->ju_max_fish) {
                                $fail("Gagal pindah ke JUARA UMUM (TEAM). Jumlah ikan peserta ({$fishCount}) melebihi kuota maksimal JU ({$event->ju_max_fish}).");
                            }
                        },
                    ]),
                Forms\Components\TextInput::make('team_name')
                    ->label('Nama Team')
                    ->maxLength(255)
                    ->required(fn($get) => $get('category') === 'team')
                    ->visible(fn($get) => $get('category') === 'team'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->label('Nama Team')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori Juara')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'team' => 'success',
                        'single_fighter' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'team' => 'JUARA UMUM (TEAM)',
                        'single_fighter' => 'SINGLE FIGHTER',
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('fishes_count')
                    ->label('Jumlah Ikan')
                    ->counts('fishes')
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        $limitField = $record->category === 'team' ? 'ju_max_fish' : 'sf_max_fish';
                        $limit = $record->event->$limitField ?? 0;
                        return ($record->category !== 'other' && $record->fishes_count > $limit) ? 'danger' : 'warning';
                    }),
                Tables\Columns\TextColumn::make('limit_info')
                    ->label('Maksimal Ikan')
                    ->getStateUsing(function ($record) {
                        if ($record->category === 'other') return '-';
                        $limitField = $record->category === 'team' ? 'ju_max_fish' : 'sf_max_fish';
                        return $record->event->$limitField ?? '-';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(Event::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'team' => 'JUARA UMUM (TEAM)',
                        'single_fighter' => 'SINGLE FIGHTER',
                        'other' => 'Lainnya',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageParticipantSfJus::route('/'),
        ];
    }
}
