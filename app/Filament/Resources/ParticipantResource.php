<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
use App\Models\Participant;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Competition';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.participant_data');
    }

    public static function getModelLabel(): string
    {
        return __('messages.resources.participant');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Event admins can only see participants from their managed events
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event', 'handler', 'fishes.bettaClass']);
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Superadmin can always access
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Event admin can access if they manage at least one event
        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->exists();
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
                        // Event admins can only select their managed events
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.participant'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->label(__('messages.fields.category'))
                    ->options([
                        'team' => 'Team',
                        'single_fighter' => 'Single Fighter',
                    ])
                    ->required()
                    ->default('single_fighter')
                    ->reactive()
                    ->afterStateUpdated(fn($state, $set) => $state === 'single_fighter' ? $set('team_name', null) : null)
                    ->rules([
                        fn(Forms\Get $get, ?Participant $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            if (!$record) return; // Only for editing

                            $eventId = $get('event_id');
                            $event = Event::find($eventId);
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
                    ->label(__('messages.fields.team'))
                    ->maxLength(255)
                    ->required(fn($get) => $get('category') === 'team')
                    ->visible(fn($get) => $get('category') === 'team'),
                Forms\Components\Select::make('handler_id')
                    ->label(__('messages.resources.handlers'))
                    ->relationship('handler', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label(__('messages.fields.name'))
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('messages.fields.phone'))
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label(__('messages.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label(__('messages.resources.events'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('messages.fields.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'unpaid' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('messages.fields.category'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'team' => 'success',
                        'single_fighter' => 'info',
                    }),
                Tables\Columns\TextColumn::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label(__('messages.resources.handlers'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fishes_count')
                    ->label(__('messages.fields.fish_count'))
                    ->counts('fishes')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('fish_classes')
                    ->label(__('messages.fields.fish_classes'))
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(function ($record) {
                        return $record->fishes
                            ->pluck('bettaClass.code')
                            ->unique()
                            ->filter()
                            ->values()
                            ->toArray();
                    })
                    ->color('success')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('messages.resources.events'))
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('messages.fields.category'))
                    ->options([
                        'team' => 'Team',
                        'single_fighter' => 'Single Fighter',
                    ]),
                Tables\Filters\SelectFilter::make('handler')
                    ->label(__('messages.resources.handlers'))
                    ->relationship('handler', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify_payment')
                    ->label('Verifikasi Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->visible(fn(Participant $record) => $record->payment_proof !== null)
                    ->form([
                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->disk('public')
                            ->directory('payments')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'paid' => 'Lunas (Approved)',
                                'rejected' => 'Ditolak (Rejected)',
                                'pending' => 'Pending',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->placeholder('Alasan penolakan atau catatan lainnya...')
                    ])
                    ->action(function (Participant $record, array $data): void {
                        $record->update([
                            'payment_status' => $data['payment_status'],
                            'notes' => ($record->notes ? $record->notes . "\n" : "") . ($data['admin_notes'] ?? '')
                        ]);

                        // Send Notification to User
                        if ($record->user_id) {
                            $title = $data['payment_status'] === 'paid' ? 'Pembayaran Lunas! ✅' : 'Pembayaran Ditolak ⚠️';
                            $message = $data['payment_status'] === 'paid'
                                ? "Pembayaran Anda untuk event {$record->event->name} telah diverifikasi."
                                : "Bukti pembayaran ditolak: " . ($data['admin_notes'] ?? 'Mohon periksa kembali.');

                            Notification::create([
                                'user_id' => $record->user_id,
                                'event_id' => $record->event_id,
                                'title' => $title,
                                'message' => $message,
                                'type' => 'payment',
                                'data' => ['participant_id' => $record->id]
                            ]);
                        }

                        if ($data['payment_status'] === 'paid') {
                            \Filament\Notifications\Notification::make()
                                ->title('Pembayaran Diterima')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('printRegistration')
                        ->label('Cetak Registrasi')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Cetak Registrasi Peserta')
                        ->modalDescription('Apakah Anda yakin ingin mencetak formulir registrasi untuk peserta ini?')
                        ->modalSubmitActionLabel('Cetak')
                        ->modalCancelActionLabel('Batal')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            if ($records->count() !== 1) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Peringatan')
                                    ->body('Silakan pilih 1 peserta untuk dicetak.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $participant = $records->first();
                            $printUrl = route('print.registration-form', [
                                'eventId' => $participant->event_id,
                                'participant_name' => $participant->name
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil')
                                ->body('Membuka formulir cetak di tab baru...')
                                ->success()
                                ->send();
                            
                            // Use HTML Response with JavaScript
                            return \Illuminate\Support\Facades\Response::make(
                                "<script>window.open('" . addslashes($printUrl) . "', '_blank'); window.history.back();</script>"
                            );
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FishesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
