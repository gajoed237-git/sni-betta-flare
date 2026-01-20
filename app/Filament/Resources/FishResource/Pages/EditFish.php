<?php

namespace App\Filament\Resources\FishResource\Pages;

use App\Filament\Resources\FishResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFish extends EditRecord
{
    protected static string $resource = FishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            FishResource\Widgets\FishListWidget::class,
        ];
    }
}
