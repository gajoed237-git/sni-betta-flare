<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title('Gagal Menyimpan!')
            ->body('Terdapat beberapa kolom wajib yang belum diisi atau formatnya salah. Silakan periksa bagian bertanda merah.')
            ->danger()
            ->send();
    }

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
            EventResource\Widgets\EventListWidget::class,
        ];
    }
}
