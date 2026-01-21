<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FishSfJuResource\Pages;
use App\Models\Fish;
use App\Models\Participant;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FishSfJuResource extends Resource
{
    protected static ?string $model = Fish::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';
    protected static ?string $navigationGroup = 'Monitoring SF/JU';
    protected static ?string $navigationLabel = 'Monitoring Ikan SF/JU';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->disabled()
                    ->reactive(),
                Forms\Components\TextInput::make('registration_no')
                    ->label('No. Reg')
                    ->disabled(),
                Forms\Components\Select::make('participant_id')
                    ->label('Pindah ke Peserta')
                    ->relationship('participant', 'name', function ($query, $get) {
                        if ($get('event_id')) {
                            $query->where('event_id', $get('event_id'));
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->rules([
                        fn(Forms\Get $get, ?Fish $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (!$value) return;

                            $participant = Participant::find($value);
                            if (!$participant || $participant->category === 'other') return;

                            $event = $participant->event;
                            if (!$event) return;

                            $isMovingToNewParticipant = $record ? ($record->participant_id != $value) : true;

                            if ($isMovingToNewParticipant) {
                                $currentCount = $participant->fishes()->count();
                                $limitField = $participant->category === 'team' ? 'ju_max_fish' : 'sf_max_fish';
                                $limit = $event->$limitField;

                                if ($limit && $currentCount >= $limit) {
                                    $catLabel = $participant->category === 'team' ? 'JUARA UMUM (TEAM)' : 'SINGLE FIGHTER';
                                    $fail("Gagal memindahkan ikan. Peserta tujuan ({$participant->name}) sudah mencapai batas maksimal {$catLabel} yaitu {$limit} ekor.");
                                }
                            }
                        },
                    ]),
                Forms\Components\TextInput::make('participant_name')
                    ->label('Nama Peserta (Manual)')
                    ->disabled(),
                Forms\Components\TextInput::make('team_name')
                    ->label('Nama Team')
                    ->disabled(),
                Forms\Components\Select::make('class_id')
                    ->label('Kelas')
                    ->relationship('bettaClass', 'name')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereHas('participant', function ($query) {
                $query->whereIn('category', ['single_fighter', 'team']);
            });

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_no')
                    ->label('No. Reg')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Nama Peserta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->label('Nama Team')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('participant.category')
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
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('bettaClass.code')
                    ->label('Kelas')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(Event::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('participant', 'category')
                    ->options([
                        'team' => 'JUARA UMUM (TEAM)',
                        'single_fighter' => 'SINGLE FIGHTER',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFishSfJus::route('/'),
        ];
    }
}
