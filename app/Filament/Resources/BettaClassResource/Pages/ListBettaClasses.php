<?php

namespace App\Filament\Resources\BettaClassResource\Pages;

use App\Filament\Resources\BettaClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBettaClasses extends ListRecords
{
    protected static string $resource = BettaClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('info')
                        ->content(new \Illuminate\Support\HtmlString('Silakan unduh template CSV di bawah ini, isi data sesuai format, lalu unggah kembali.')),
                    \Filament\Forms\Components\Placeholder::make('template_link')
                        ->label('Template')
                        ->content(new \Illuminate\Support\HtmlString('<a href="' . route('template.import-classes') . '" class="text-primary-600 font-bold underline cursor-pointer">Download Template CSV</a>')),
                    \Filament\Forms\Components\Select::make('event_id')
                        ->label('Event')
                        ->options(function () {
                            $user = \Illuminate\Support\Facades\Auth::user();
                            $query = \App\Models\Event::query();
                            if ($user && $user->isEventAdmin()) {
                                $eventIds = $user->managed_events()->pluck('events.id');
                                $query->whereIn('id', $eventIds);
                            }
                            return $query->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('File CSV')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain'])
                        ->helperText('Pastikan urutan kolom: division_code, division_name, class_code, class_name'),
                ])
                ->action(function (array $data) {
                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['file']);
                    $eventId = $data['event_id'];

                    if (($handle = fopen($filePath, "r")) !== FALSE) {
                        $count = 0;

                        // Detect delimiter from first line (header)
                        $firstLine = fgets($handle);
                        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

                        // Return to beginning of file
                        rewind($handle);

                        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                            // Skip header
                            if (empty($row) || !isset($row[0]) || empty(trim($row[0]))) continue;
                            if (str_contains(strtolower($row[0]), 'division_code') || str_contains(strtolower($row[0]), 'kode_divisi')) continue;
                            if (count($row) < 4) continue;

                            $divCode = trim($row[0]);
                            $divName = trim($row[1]);
                            $clsCode = trim($row[2]);
                            $clsName = trim($row[3]);

                            if (empty($divCode) || empty($clsCode)) continue;

                            // 1. Find or Create Division
                            $division = \App\Models\Division::updateOrCreate(
                                ['event_id' => $eventId, 'code' => $divCode],
                                ['name' => $divName]
                            );

                            // 2. Create BettaClass
                            \App\Models\BettaClass::updateOrCreate(
                                ['event_id' => $eventId, 'division_id' => $division->id, 'code' => $clsCode],
                                ['name' => $clsName]
                            );

                            $count++;
                        }
                        fclose($handle);

                        if ($count > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Selesai')
                                ->body("Berhasil mengimport $count data Kelas & Divisi.")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Gagal')
                                ->body("Tidak ada data yang terbaca. Pastikan format kolom sesuai template.")
                                ->danger()
                                ->send();
                        }
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
