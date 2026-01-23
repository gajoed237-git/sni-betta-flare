<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FishResource\Pages;
use App\Models\Fish;
use App\Models\Participant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FishScoreResource;
use Filament\Forms\Get;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class FishResource extends Resource
{
    protected static ?string $model = Fish::class;
    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event', 'bettaClass', 'snapshot']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.fishes');
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function canDeleteAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        /** @var \App\Models\User $user */
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
                Forms\Components\Select::make('class_id')
                    ->label(__('messages.resources.classes'))
                    ->relationship('bettaClass', 'name', function ($query, $get) {
                        $eventId = $get('event_id');
                        if ($eventId) {
                            $query->where('event_id', $eventId);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('registration_no')
                    ->label(__('messages.fields.registration_no'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('participant_id')
                    ->label(__('messages.fields.participant'))
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

                            // Count current fish of the target participant
                            // If we are currently editing this fish and it already belongs to this participant, don't count it as "new"
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
                    ->label(__('messages.fields.participant') . ' (Manual)')
                    ->helperText('Otomatis terisi saat memilih peserta di atas')
                    ->maxLength(255),
                Forms\Components\TextInput::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('messages.fields.status'))
                    ->options([
                        'registered' => __('messages.statuses.registered'),
                        'checking' => __('messages.statuses.checking'),
                        'judging' => __('messages.statuses.judging'),
                        'completed' => __('messages.statuses.completed'),
                        'disqualified' => __('messages.statuses.disqualified'),
                        'moved' => __('messages.statuses.moved'),
                    ])
                    ->required()
                    ->default('registered'),
                Forms\Components\Toggle::make('is_nominated')
                    ->label(__('Nominasi'))
                    ->default(false),
                Forms\Components\Select::make('original_class_id')
                    ->relationship('originalClass', 'name')
                    ->label(__('messages.fields.original_class'))
                    ->placeholder('-')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Textarea::make('admin_note')
                    ->label(__('messages.fields.admin_note_dq'))
                    ->columnSpanFull(),

                Forms\Components\Section::make(__('messages.fields.official_results'))
                    ->description(__('messages.fields.official_results_desc'))
                    ->schema([
                        Forms\Components\Select::make('final_rank')
                            ->label(__('messages.fields.official_rank'))
                            ->options([
                                1 => __('messages.fields.rank_1'),
                                2 => __('messages.fields.rank_2'),
                                3 => __('messages.fields.rank_3'),
                            ])
                            ->placeholder('-')
                            ->selectablePlaceholder(),
                        Forms\Components\CheckboxList::make('winner_type')
                            ->label(__('messages.fields.winner_type'))
                            ->options(function (Get $get, ?Fish $record) {
                                $eventId = $get('event_id') ?? $record?->event_id;
                                if (!$eventId) return [];

                                $event = \App\Models\Event::find($eventId);
                                $standard = $event?->judging_standard ?? 'sni';

                                if ($standard === 'ibc') {
                                    return [
                                        'bod' => 'BOD - BEST OF DIVISION',
                                        'boo' => 'BOO - BEST OF OPTIONAL',
                                        'bov' => 'BOV - BEST OF VARIETY',
                                        'bos' => 'BOS - BEST OF SHOW',
                                    ];
                                }

                                return [
                                    'class' => __('messages.fields.winner_class'),
                                    'gc' => __('messages.fields.winner_gc'),
                                    'bob' => 'BOB - BEST OF BEST',
                                ];
                            })
                            ->descriptions([
                                'class' => 'Juara Kelas (Rank 1/2/3)',
                            ])
                            ->columns(2),
                        Forms\Components\Placeholder::make('score_reference')
                            ->label(__('messages.fields.score_reference'))
                            ->content(fn(?Fish $record): string => $record?->snapshot?->average_score ? number_format($record->snapshot->average_score, 2) . ' pts' : 'No scores yet')
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_no')
                    ->label(__('messages.fields.registration_no'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bettaClass.code')
                    ->label(__('messages.resources.classes'))
                    ->description(fn(Fish $record): ?string => $record->status === 'moved' ? "Prev: " . ($record->originalClass?->code ?? '-') : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label(__('messages.fields.participant'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('messages.fields.status'))
                    ->badge()
                    ->formatStateUsing(function (string $state, Fish $record): string {
                        $label = match ($state) {
                            'registered' => __('messages.statuses.registered'),
                            'checking' => __('messages.statuses.checking'),
                            'judging' => __('messages.statuses.judging'),
                            'completed' => __('messages.statuses.completed'),
                            'disqualified' => __('messages.statuses.disqualified'),
                            'moved' => __('messages.statuses.moved'),
                            default => $state,
                        };
                        return $record->is_nominated ? "{$label} (Nom)" : $label;
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'registered' => 'gray',
                        'checking' => 'warning',
                        'completed' => 'success',
                        'disqualified' => 'danger',
                        'judging' => 'info',
                        'moved' => 'primary',
                        default => 'gray',
                    })
                    ->icon(fn(?Fish $record): ?string => $record?->is_nominated ? 'heroicon-m-star' : null),
                Tables\Columns\ToggleColumn::make('is_nominated')
                    ->label(__('messages.fields.nominasi')),
                Tables\Columns\TextColumn::make('snapshot.average_score')
                    ->label(__('messages.fields.avg_score'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('snapshot.rank_in_class')
                    ->label(__('messages.fields.score_rank_est'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('final_rank')
                    ->label(__('messages.fields.official_rank'))
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('winner_type')
                    ->label(__('messages.fields.winner_type'))
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'gc' => 'warning',
                        'class' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => $state ? strtoupper($state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name', function ($query) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    }),
                Tables\Filters\SelectFilter::make('bettaClass')
                    ->label(__('messages.resources.classes'))
                    ->relationship('bettaClass', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'registered' => __('messages.statuses.registered'),
                        'checking' => __('messages.statuses.checking'),
                        'judging' => __('messages.statuses.judging'),
                        'completed' => __('messages.statuses.completed'),
                        'disqualified' => __('messages.statuses.disqualified'),
                        'moved' => __('messages.statuses.moved'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_nominated')
                    ->label(__('messages.fields.nominasi')),
                Tables\Filters\SelectFilter::make('final_rank')
                    ->label(__('messages.fields.rank'))
                    ->options([
                        1 => __('messages.fields.rank_1'),
                        2 => __('messages.fields.rank_2'),
                        3 => __('messages.fields.rank_3'),
                    ]),
                Tables\Filters\SelectFilter::make('winner_type')
                    ->label(__('messages.fields.winner_type'))
                    ->options([
                        'class' => __('messages.fields.winner_class'),
                        'gc' => __('messages.fields.winner_gc'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('print_label')
                    ->label(__('messages.actions.print_label'))
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Fish $record): string => route('print.labels', ['ids' => [$record->id]]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('give_score')
                    ->label(__('messages.actions.give_score'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->url(fn(Fish $record): string => FishScoreResource::getUrl('create', ['fish_id' => $record->id]))
                    ->hidden(function (Fish $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $record->event->is_locked && !$user->isAdmin();
                    }),
                Tables\Actions\EditAction::make()
                    ->hidden(function (Fish $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $record->event->is_locked && !$user->isAdmin();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->hidden(function (Fish $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        return $record->event->is_locked && !$user->isAdmin();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('print_selected_labels')
                        ->label(__('messages.actions.print_label'))
                        ->icon('heroicon-o-printer')
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray();
                            
                            // Generate URL
                            $printUrl = route('print.labels', ['ids' => $ids]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil')
                                ->body('Membuka label ikan di tab baru...')
                                ->success()
                                ->send();
                            
                            // Return view dengan JavaScript untuk membuka tab baru
                            return \Illuminate\Support\Facades\Response::view('print-redirect', ['url' => $printUrl]);
                        }),
                    Tables\Actions\BulkAction::make('move_to_sf_ju')
                        ->label('Pindah ke SF/JU')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('category')
                                ->label('Pindah ke Kategori')
                                ->options([
                                    'single_fighter' => 'SINGLE FIGHTER (SF)',
                                    'team' => 'JUARA UMUM (TEAM)',
                                ])
                                ->required()
                                ->reactive(),
                            Forms\Components\TextInput::make('team_name')
                                ->label('Nama Team')
                                ->visible(fn($get) => $get('category') === 'team')
                                ->required(fn($get) => $get('category') === 'team'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $category = $data['category'];
                            $teamName = $data['team_name'] ?? null;
                            $count = $records->count();

                            // Group records by event to handle cross-event bulk selection safely (though usually filtered)
                            $recordsByEvent = $records->groupBy('event_id');

                            foreach ($recordsByEvent as $eventId => $fishes) {
                                $event = \App\Models\Event::find($eventId);
                                if (!$event) continue;

                                foreach ($fishes as $fish) {
                                    $originalParticipant = $fish->participant;

                                    // 1. Find or Create SF/JU Participant for the same owner
                                    // Criteria: same event, same user_id (if exists), same category, and same team_name (if JU)
                                    $newParticipant = Participant::where('event_id', $eventId)
                                        ->where('user_id', $originalParticipant->user_id)
                                        ->where('category', $category);

                                    if ($category === 'team') {
                                        $newParticipant->where('team_name', $teamName);
                                    }

                                    $newParticipantProfile = $newParticipant->first();

                                    if (!$newParticipantProfile) {
                                        // Create new mirror participant profile
                                        $suffix = $category === 'team' ? " (JU)" : " (SF)";
                                        $newParticipantProfile = Participant::create([
                                            'event_id' => $eventId,
                                            'user_id' => $originalParticipant->user_id,
                                            'name' => $originalParticipant->name . $suffix,
                                            'email' => $originalParticipant->email,
                                            'phone' => $originalParticipant->phone,
                                            'category' => $category,
                                            'team_name' => $teamName,
                                            'handler_id' => $originalParticipant->handler_id,
                                        ]);
                                    }

                                    // 2. Check Limit
                                    $currentFishInNew = $newParticipantProfile->fishes()->count();
                                    $limitField = $category === 'team' ? 'ju_max_fish' : 'sf_max_fish';
                                    $limit = $event->$limitField;

                                    if ($limit && ($currentFishInNew + 1) > $limit) {
                                        $catLabel = $category === 'team' ? 'JUARA UMUM' : 'SINGLE FIGHTER';
                                        Notification::make()
                                            ->title("Gagal memindahkan ikan {$fish->registration_no}")
                                            ->body("Peserta {$newParticipantProfile->name} sudah mencapai batas maksimal {$catLabel} ({$limit} ekor).")
                                            ->danger()
                                            ->send();
                                        continue;
                                    }

                                    // 3. Move Fish
                                    $fish->update([
                                        'participant_id' => $newParticipantProfile->id,
                                        'participant_name' => $newParticipantProfile->name,
                                        'team_name' => $newParticipantProfile->team_name,
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Berhasil memindahkan ikan')
                                ->body("{$count} ikan telah dipindahkan ke kategori " . ($category === 'team' ? 'JUARA UMUM' : 'SINGLE FIGHTER'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFish::route('/'),
            'create' => Pages\CreateFish::route('/create'),
            'edit' => Pages\EditFish::route('/{record}/edit'),
        ];
    }
}
