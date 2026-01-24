<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.master_data');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.events');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Event admins can only see their assigned events
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('id', $eventIds);
        }

        return $query;
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

    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only admin and event_admin can create events
        return $user && in_array($user->role, ['admin', 'event_admin']);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin can edit all events
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Event admin can edit their own events
        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->where('events.id', $record->id)->exists();
        }

        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin can delete all events
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Event admin can delete their own events
        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->where('events.id', $record->id)->exists();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('event_date')
                    ->label(__('messages.fields.start_date'))
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->label(__('messages.fields.location'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('messages.fields.additional_info'))
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('messages.fields.status'))
                    ->required()
                    ->default(true),
                Forms\Components\Select::make('judging_standard')
                    ->label(__('messages.fields.judging_standard'))
                    ->options([
                        'sni' => __('messages.fields.standard_sni'),
                        'ibc' => __('messages.fields.standard_ibc'),
                    ])
                    ->required()
                    ->default('sni')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state === 'ibc') {
                            $set('point_rank1', 10);
                            $set('point_rank2', 6);
                            $set('point_rank3', 4);
                            $set('point_gc', 20);
                            $set('point_bob', 40);
                            $set('point_bof', 0);
                            $set('point_bod', 30);
                            $set('point_boo', 45);
                            $set('point_bov', 40);
                            $set('point_bos', 60);

                            $set('label_gc', 'GRAND CHAMPION');
                            $set('label_bob', 'BEST OF BEST');
                            $set('label_bof', 'BEST OF FORM');
                            $set('label_bod', 'BEST OF DIVISION');
                            $set('label_boo', 'BEST OF OTHER');
                            $set('label_bov', 'BEST OF VARIETY');
                            $set('label_bos', 'BEST OF SHOW');

                            $set('ibc_minus_ringan', 3);
                            $set('ibc_minus_kecil', 5);
                            $set('ibc_minus_besar', 9);
                            $set('ibc_minus_berat', 17);
                        } else {
                            $set('point_rank1', 15);
                            $set('point_rank2', 7);
                            $set('point_rank3', 3);
                            $set('point_gc', 30);
                            $set('point_bob', 50);
                            $set('point_bof', 40);
                            $set('point_bos', 75);
                            $set('point_bod', 0);
                            $set('point_boo', 0);
                            $set('point_bov', 0);

                            $set('label_gc', 'GRAND CHAMPION');
                            $set('label_bob', 'BEST OF BEST');
                            $set('label_bof', 'BEST OF FORM');
                            $set('label_bos', 'BEST OF SHOW');

                            $set('ibc_minus_ringan', 0);
                            $set('ibc_minus_kecil', 0);
                            $set('ibc_minus_besar', 0);
                            $set('ibc_minus_berat', 0);
                        }
                    }),
                Forms\Components\Toggle::make('is_locked')
                    ->label(__('messages.fields.judging_lock'))
                    ->helperText(__('messages.fields.judging_lock_help'))
                    ->required()
                    ->default(false),
                Forms\Components\Toggle::make('is_finished')
                    ->label('Event Selesai')
                    ->helperText('Tandai event sebagai selesai/berakhir')
                    ->required()
                    ->default(false),

                Forms\Components\Section::make('Konfigurasi Poin & Nama Juara')
                    ->description('Atur poin perolehan dan ubah nama gelar juara (misal: GC bisa diubah jadi "BAIKAL CHAMPION") sesuai kebutuhan event.')
                    ->schema([
                        Forms\Components\Select::make('point_accumulation_mode')
                            ->label('Mode Perhitungan Poin')
                            ->options([
                                'highest' => 'Ambil Poin Tertinggi',
                                'accumulation' => 'Akumulasi (Jumlahkan Semua)',
                            ])
                            ->default('highest')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Contoh: Jika ikan menang BOD dan BOS. Mode Akumulasi = BOD+BOS. Mode Tertinggi = Hanya BOS.'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('point_rank1')
                                    ->label('Poin Rank 1')
                                    ->numeric()
                                    ->default(15),
                                Forms\Components\TextInput::make('point_rank2')
                                    ->label('Poin Rank 2')
                                    ->numeric()
                                    ->default(7),
                                Forms\Components\TextInput::make('point_rank3')
                                    ->label('Poin Rank 3')
                                    ->numeric()
                                    ->default(3),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('label_gc')
                                    ->label('Nama Gelar GC')
                                    ->default('GRAND CHAMPION'),
                                Forms\Components\TextInput::make('point_gc')
                                    ->label('Poin GC')
                                    ->numeric()
                                    ->default(30),

                                Forms\Components\TextInput::make('label_bob')
                                    ->label('Nama Gelar BOB')
                                    ->default('BEST OF BEST'),
                                Forms\Components\TextInput::make('point_bob')
                                    ->label('Poin BOB')
                                    ->numeric()
                                    ->default(50),

                                Forms\Components\TextInput::make('label_bof')
                                    ->label('Nama Gelar BOF')
                                    ->default('BEST OF FORM'),
                                Forms\Components\TextInput::make('point_bof')
                                    ->label('Poin BOF')
                                    ->numeric()
                                    ->default(40),
                            ])
                            ->visible(fn(Get $get) => $get('judging_standard') === 'sni'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('label_bod')
                                    ->label('Nama Gelar BOD')
                                    ->default('BEST OF DIVISION'),
                                Forms\Components\TextInput::make('point_bod')
                                    ->label('Poin BOD')
                                    ->numeric()
                                    ->default(30),

                                Forms\Components\TextInput::make('label_boo')
                                    ->label('Nama Gelar BOO/BOS')
                                    ->default('BEST OF OTHER'),
                                Forms\Components\TextInput::make('point_boo')
                                    ->label('Poin BOO')
                                    ->numeric()
                                    ->default(45),

                                Forms\Components\TextInput::make('label_bov')
                                    ->label('Nama Gelar BOV')
                                    ->default('BEST OF VARIETY'),
                                Forms\Components\TextInput::make('point_bov')
                                    ->label('Poin BOV')
                                    ->numeric()
                                    ->default(40),
                            ])
                            ->visible(fn(Get $get) => $get('judging_standard') === 'ibc'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('label_bos')
                                    ->label('Nama Gelar BOS')
                                    ->default('BEST OF SHOW'),
                                Forms\Components\TextInput::make('point_bos')
                                    ->label('Poin BOS')
                                    ->numeric()
                                    ->default(60),
                            ])
                            ->visible(fn(Get $get) => in_array($get('judging_standard'), ['ibc', 'sni'])),

                        Forms\Components\Section::make('IBC Penalty/Faults Points')
                            ->description('Atur nilai pengurangan poin untuk kesalahan ikan (Hanya Standard IBC)')
                            ->visible(fn(Get $get) => $get('judging_standard') === 'ibc')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('ibc_minus_ringan')
                                            ->label('Ringan (-)')
                                            ->numeric()
                                            ->required()
                                            ->default(3),
                                        Forms\Components\TextInput::make('ibc_minus_kecil')
                                            ->label('Kecil (-)')
                                            ->numeric()
                                            ->required()
                                            ->default(5),
                                        Forms\Components\TextInput::make('ibc_minus_besar')
                                            ->label('Besar (-)')
                                            ->numeric()
                                            ->required()
                                            ->default(9),
                                        Forms\Components\TextInput::make('ibc_minus_berat')
                                            ->label('Berat (-)')
                                            ->numeric()
                                            ->required()
                                            ->default(17),
                                    ]),
                            ]),
                    ])->collapsible(),

                Forms\Components\Section::make(__('messages.fields.event_info'))
                    ->description(__('messages.fields.event_info_desc'))
                    ->schema([
                        Forms\Components\TextInput::make('committee_name')
                            ->label(__('messages.fields.committee_name'))
                            ->placeholder('Contoh: Betta Flare Indonesia')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ticket_price')
                            ->label(__('messages.fields.ticket_price'))
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('sf_max_fish')
                            ->label('Max Ikan Single Fighter')
                            ->helperText('Batas maksimum ikan per pendaftar SF')
                            ->numeric()
                            ->default(50),
                        Forms\Components\TextInput::make('ju_max_fish')
                            ->label('Max Ikan Juara Umum')
                            ->helperText('Batas maksimum ikan per pendaftar Juara Umum (Team)')
                            ->numeric()
                            ->default(60),
                        Forms\Components\TextInput::make('share_url')
                            ->label('Custom Share URL')
                            ->placeholder('https://example.com/event-id')
                            ->helperText('Jika dikosongkan, akan menggunakan URL default sistem.')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('brochure_image')
                            ->label(__('messages.fields.brochure_upload'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('events/brochures')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('messages.fields.payment_method'))
                    ->description(__('messages.fields.payment_method_desc'))
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label(__('messages.fields.bank_name_placeholder'))
                            ->placeholder('BCA, Mandiri, dll'),
                        Forms\Components\TextInput::make('bank_account_name')
                            ->label(__('messages.fields.account_holder_placeholder'))
                            ->placeholder('Contoh: Panitia Betta Flare'),
                        Forms\Components\TextInput::make('bank_account_number')
                            ->label(__('messages.fields.account_number_placeholder'))
                            ->placeholder('0001234567'),
                        Forms\Components\TextInput::make('registration_fee')
                            ->label(__('messages.fields.registration_fee'))
                            ->numeric()
                            ->prefix('Rp')
                            ->default(100000)
                            ->required(),
                        Forms\Components\FileUpload::make('qris_image')
                            ->label(__('messages.fields.upload_qris'))
                            ->image()
                            ->directory('events/qris'),
                        Forms\Components\Textarea::make('payment_instructions')
                            ->label(__('messages.fields.payment_instruction_label'))
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('event_date')
                    ->label(__('messages.fields.start_date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label(__('messages.fields.location'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('committee_name')
                    ->label(__('messages.fields.committee'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('messages.fields.description_label'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('registration_fee')
                    ->label(__('messages.fields.registration_fee_label'))
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('messages.fields.status'))
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('is_locked')
                    ->label(__('messages.fields.locked')),
                Tables\Columns\ToggleColumn::make('is_finished')
                    ->label('Selesai'),
                Tables\Columns\TextColumn::make('judging_standard')
                    ->label(__('messages.fields.judging_standard'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sni' => 'warning',
                        'ibc' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sni' => __('messages.fields.standard_sni'),
                        'ibc' => __('messages.fields.standard_ibc'),
                        default => strtoupper($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('print_standings')
                    ->label('Cetak Posisi Juara')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function ($record) {
                        $printUrl = route('print.champion-standings', [
                            'eventId' => $record->id
                        ]);

                        return redirect()->route('open.print.new.tab')
                            ->with('url', $printUrl);
                    }),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\JudgesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
