<?php

namespace App\Filament\Resources\HandlerResource\Pages;

use App\Filament\Resources\HandlerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHandler extends CreateRecord
{
    protected static string $resource = HandlerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
