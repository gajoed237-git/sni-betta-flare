<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FishScoreResource\Pages;
use App\Models\FishScore;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class FishScoreResource extends Resource
{
    protected static ?string $model = FishScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // If user is event admin, only show scores from their managed events
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereHas('fish', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            });
        }

        return $query->with(['fish', 'judge']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.scores');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('messages.fields.judge_info'))
                    ->schema([
                        Forms\Components\Select::make('fish_id')
                            ->label(__('messages.resources.fishes'))
                            ->relationship('fish', 'registration_no', function ($query) {
                                $user = auth()->user();
                                if ($user && $user->isEventAdmin()) {
                                    $eventIds = $user->managed_events()->pluck('events.id');
                                    $query->whereIn('event_id', $eventIds);
                                }
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(request()->query('fish_id')),
                        Forms\Components\Select::make('judge_id')
                            ->label(__('messages.fields.judge'))
                            ->relationship('judge', 'name', function ($query) {
                                $query->whereIn('role', ['judge', 'admin', 'event_admin']);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                    ])->columns(2),

                Forms\Components\Section::make(__('messages.fields.scoring'))
                    ->description(__('messages.fields.scoring_description'))
                    ->schema([
                        // IBC Standards (Numbers)
                        Forms\Components\TextInput::make('minus_kepala')->label(__('messages.fields.aspect_head'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_badan')->label(__('messages.fields.aspect_body'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_dorsal')->label(__('messages.fields.aspect_dorsal'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_anal')->label(__('messages.fields.aspect_anal'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_ekor')->label(__('messages.fields.aspect_caudal'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_dasi')->label(__('messages.fields.aspect_ventral'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_kerapihan')->label(__('messages.fields.aspect_neatness'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_warna')->label(__('messages.fields.aspect_color'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('minus_lain_lain')->label(__('messages.fields.aspect_others'))->numeric()->default(0)->live()->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotals($set, $get))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),

                        // SNI Standards (Notes / X Marks)
                        Forms\Components\TextInput::make('kepala_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_head'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('kedokan_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_gill'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('badan_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_body'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('dorsal_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_dorsal'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('anal_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_anal'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('ekor_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_caudal'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('dasi_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_ventral'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('warna_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_color'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('mental_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_mental'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('kerapihan_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_neatness'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('proporsi_notes')->label(__('messages.fields.deficiency_id') . ': ' . __('messages.fields.aspect_proportion'))
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                        Forms\Components\TextInput::make('final_rank')->label(__('messages.fields.rank') . ' (SNI)')
                            ->numeric()
                            ->visible(fn(Get $get) => self::getStandard($get) === 'sni'),
                    ])->columns(3),

                Forms\Components\Section::make(__('messages.fields.summary'))
                    ->schema([
                        Forms\Components\TextInput::make('total_minus')->label('Total Minus')->numeric()->readOnly()->default(0)
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\TextInput::make('total_score')->label(__('messages.fields.total_score'))->numeric()->readOnly()->default(100)
                            ->visible(fn(Get $get) => self::getStandard($get) === 'ibc'),
                        Forms\Components\Textarea::make('admin_note')->label('Admin Note')->columnSpanFull(),
                        Forms\Components\Toggle::make('is_corrected')->label('Admin Correction')->default(false),
                    ])->columns(2),
            ]);
    }

    protected static function getStandard(Get $get): string
    {
        $fishId = $get('fish_id');
        if (!$fishId) return 'sni'; // Default or hide

        $fish = \App\Models\Fish::with('event')->find($fishId);
        return $fish?->event?->judging_standard ?? 'sni';
    }

    public static function updateTotals(Set $set, Get $get): void
    {
        $total_minus = (int) $get('minus_kepala') +
            (int) $get('minus_badan') +
            (int) $get('minus_dorsal') +
            (int) $get('minus_anal') +
            (int) $get('minus_ekor') +
            (int) $get('minus_dasi') +
            (int) $get('minus_kerapihan') +
            (int) $get('minus_warna') +
            (int) $get('minus_lain_lain');

        $set('total_minus', $total_minus);
        $set('total_score', 100 - $total_minus);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fish.registration_no')->label(__('messages.resources.fishes'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('judge.name')->label(__('messages.fields.judge'))->sortable(),
                Tables\Columns\TextColumn::make('penilaian')
                    ->label(__('Penilaian'))
                    ->getStateUsing(function (FishScore $record) {
                        $standard = $record->fish?->event?->judging_standard ?? 'sni';

                        if ($standard === 'sni') {
                            $aspects = [
                                'kepala',
                                'kedokan',
                                'badan',
                                'dorsal',
                                'anal',
                                'ekor',
                                'dasi',
                                'warna',
                                'mental',
                                'kerapihan',
                                'proporsi'
                            ];
                            $filled = [];
                            foreach ($aspects as $a) {
                                if (!empty($record->{$a . '_notes'})) {
                                    $filled[] = strtoupper($a[0]); // Initial letter
                                }
                            }
                            return count($filled) > 0 ? implode(', ', $filled) : 'Bersih';
                        } else {
                            // IBC
                            return "-{$record->total_minus} pts";
                        }
                    })
                    ->badge()
                    ->color(fn($state) => $state === 'Bersih' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('total_score')->label(__('messages.fields.total_score'))->sortable(),
                Tables\Columns\TextColumn::make('final_rank')
                    ->label('Rank')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->visible(fn(?FishScore $record) => $record ? ($record->fish?->event?->judging_standard === 'sni') : true),
                Tables\Columns\IconColumn::make('is_corrected')->label('Corrected')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('messages.resources.events'))
                    ->relationship('fish.event', 'name', function ($query) {
                        $user = auth()->user();
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    }),
                Tables\Filters\SelectFilter::make('judge')->label(__('messages.fields.judge'))->relationship('judge', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn(FishScore $record): bool => $record->fish->event->is_locked && !auth()->user()->isAdmin()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFishScores::route('/'),
            'create' => Pages\CreateFishScore::route('/create'),
            'edit' => Pages\EditFishScore::route('/{record}/edit'),
        ];
    }
}
