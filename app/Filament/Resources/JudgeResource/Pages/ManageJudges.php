<?php

namespace App\Filament\Resources\JudgeResource\Pages;

use App\Filament\Resources\JudgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJudges extends ManageRecords
{
    protected static string $resource = JudgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['role'] = 'judge';
                    return $data;
                }),
        ];
    }
}
