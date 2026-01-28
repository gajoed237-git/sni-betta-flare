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
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Select::make('betta_class_id')
                    ->label('Kelas Tujuan')
                    ->relationship('bettaClass', 'name', function ($query, $record) {
                        if ($record) {
                            $query->where('event_id', $record->event_id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn(Forms\Get $get) => $get('status') === 'moved')
                    ->required(fn(Forms\Get $get) => $get('status') === 'moved'),
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
            ->headerActions([
                Tables\Actions\Action::make('print_moved_dq')
                    ->label('Cetak Laporan Pindah/DQ')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->url(function ($livewire): string {
                        $eventId = $livewire->tableFilters['event']['value'] ?? null;
                        return route('print.moved-dq', ['event_id' => $eventId]);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn() => \Illuminate\Support\Facades\Auth::user()->isAdmin() || \Illuminate\Support\Facades\Auth::user()->isEventAdmin()),

                Tables\Actions\Action::make('print_blank')
                    ->label('Cetak Blangko Kosong')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn() => route('print.moved-dq', ['blank' => 1]))
                    ->openUrlInNewTab()
                    ->visible(fn() => \Illuminate\Support\Facades\Auth::user()->isAdmin() || \Illuminate\Support\Facades\Auth::user()->isEventAdmin()),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name', function ($query) {
                        /** @var \App\Models\User $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'disqualified' => 'Disqualified',
                        'moved' => 'Moved',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, Fish $record): array {
                        if ($data['status'] === 'moved' && !$record->original_class_id) {
                            $data['original_class_id'] = $record->class_id;
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('print_selected')
                        ->label('Cetak Terpilih')
                        ->icon('heroicon-o-printer')
                        ->action(fn() => null) // handled by url
                        ->url(fn($records) => $records ? route('print.moved-dq', ['fish_ids' => $records->pluck('id')->implode(',')]) : '#')
                        ->openUrlInNewTab(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['disqualified', 'moved']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTroubledFish::route('/'),
        ];
    }
}
