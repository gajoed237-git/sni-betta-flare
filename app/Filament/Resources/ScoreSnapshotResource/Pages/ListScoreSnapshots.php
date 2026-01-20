<?php

namespace App\Filament\Resources\ScoreSnapshotResource\Pages;

use App\Filament\Resources\ScoreSnapshotResource;
use App\Models\BettaClass;
use App\Models\ScoreSnapshot;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListScoreSnapshots extends ListRecords
{
    protected static string $resource = ScoreSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate_all')
                ->label('Recalculate Rankings')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $classes = BettaClass::all();
                    foreach ($classes as $class) {
                        ScoreSnapshot::refreshRankings($class->id);
                    }
                    
                    Notification::make()
                        ->title('Rankings Recalculated')
                        ->success()
                        ->send();
                }),
        ];
    }
}
