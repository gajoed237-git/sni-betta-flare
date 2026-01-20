<?php

namespace App\Filament\Resources\ParticipantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\BettaClass;
use App\Models\Fish;

class FishesRelationManager extends RelationManager
{
    protected static string $relationship = 'fishes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_id')
                    ->label('Kelas Ikan')
                    ->options(function (RelationManager $livewire): array {
                        $participant = $livewire->getOwnerRecord();
                        return BettaClass::where('event_id', $participant->event_id)
                            ->get()
                            ->mapWithKeys(fn($item) => [$item->id => "{$item->code}. {$item->name}"])
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('registration_no')
                    ->label('No. Registrasi')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(function () {
                        $participant = $this->getOwnerRecord();
                        $eventId = $participant->event_id;

                        // Get last registration number for this event
                        $lastFish = Fish::where('event_id', $eventId)
                            ->orderBy('registration_no', 'desc')
                            ->first();

                        if ($lastFish && $lastFish->registration_no) {
                            $lastNumber = (int) $lastFish->registration_no;
                            return str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                        }

                        return '0001';
                    }),
                Forms\Components\Hidden::make('event_id')
                    ->default(fn() => $this->getOwnerRecord()->event_id),
                Forms\Components\Hidden::make('participant_name')
                    ->default(fn() => $this->getOwnerRecord()->name),
                Forms\Components\Hidden::make('team_name')
                    ->default(fn() => $this->getOwnerRecord()->team_name),
                Forms\Components\Hidden::make('phone')
                    ->default(fn() => $this->getOwnerRecord()->phone),
                Forms\Components\Hidden::make('participant_id')
                    ->default(fn() => $this->getOwnerRecord()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('registration_no')
            ->columns([
                Tables\Columns\TextColumn::make('registration_no')
                    ->label('No. Registrasi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bettaClass.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'registered' => 'warning',
                        'qualified' => 'success',
                        'disqualified' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $participant = $this->getOwnerRecord();
                        $eventId = $participant->event_id;

                        // Auto-generate registration number
                        $lastFish = Fish::where('event_id', $eventId)
                            ->orderBy('registration_no', 'desc')
                            ->first();

                        if ($lastFish && $lastFish->registration_no) {
                            $lastNumber = (int) $lastFish->registration_no;
                            $data['registration_no'] = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                        } else {
                            $data['registration_no'] = '0001';
                        }

                        // Auto-fill participant data
                        $data['event_id'] = $participant->event_id;
                        $data['participant_name'] = $participant->name;
                        $data['team_name'] = $participant->team_name;
                        $data['phone'] = $participant->phone;
                        $data['participant_id'] = $participant->id;
                        $data['status'] = 'registered';

                        return $data;
                    }),
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
}
