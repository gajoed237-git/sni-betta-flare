<?php

namespace App\Filament\Resources\FishResource\Pages;

use App\Filament\Resources\FishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFish extends ListRecords
{
    protected static string $resource = FishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_all')
                ->label('Cetak Semua Label')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn(): string => route('print.labels'))
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
