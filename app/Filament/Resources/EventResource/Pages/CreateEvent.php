<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Inputan Belum Lengkap!')
            ->body('Harap periksa kembali form. Pastikan semua kolom bertanda bintang (*) sudah diisi dengan benar.')
            ->danger()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            EventResource\Widgets\EventListWidget::class,
        ];
    }
}
