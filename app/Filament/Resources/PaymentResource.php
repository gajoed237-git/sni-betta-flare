<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Participant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.payments');
    }

    public static function getModelLabel(): string
    {
        return __('messages.resources.payments');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Event admins can only see payments from their managed events
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event']);
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
        return $form
            ->schema([
                Forms\Components\Section::make(__('messages.resources.payments'))
                    ->description(__('messages.fields.payment_management'))
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label(__('messages.resources.events'))
                            ->relationship('event', 'name')
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('name')
                            ->label(__('messages.fields.participant'))
                            ->disabled(),
                        Forms\Components\TextInput::make('total_fee')
                            ->label(__('messages.fields.total_fee'))
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Forms\Components\Select::make('payment_status')
                            ->label(__('messages.fields.payment_status'))
                            ->options([
                                'unpaid' => 'Unpaid',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('payment_proof')
                            ->label(__('messages.fields.payment_proof'))
                            ->image()
                            ->directory('payments')
                            ->disabled()
                            ->columnSpanFull()
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),
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
                    ->label(__('messages.fields.participant'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_fee')
                    ->label(__('messages.fields.total_fee'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('messages.fields.payment_status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'unpaid' => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label(__('messages.fields.payment_proof'))
                    ->square()
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.fields.registration_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('messages.fields.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(Participant $record) => $record->payment_status !== 'paid')
                    ->action(fn(Participant $record) => $record->update(['payment_status' => 'paid'])),
                Tables\Actions\Action::make('reject')
                    ->label(__('messages.fields.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(Participant $record) => $record->payment_status === 'pending')
                    ->action(fn(Participant $record) => $record->update(['payment_status' => 'rejected'])),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
