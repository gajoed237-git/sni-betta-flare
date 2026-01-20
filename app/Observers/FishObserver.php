<?php

namespace App\Observers;

use App\Models\Fish;
use App\Models\Notification;

class FishObserver
{
    public function updated(Fish $fish): void
    {
        // 1. Notif Ikan Pindah (Jika class_id berubah)
        // Hanya dikirim jika perubahan dilakukan oleh Juri/Admin (bukan oleh peserta itu sendiri saat input data)
        if ($fish->isDirty('class_id') && $fish->class_id != $fish->getOriginal('class_id')) {
            $currentUser = auth()->user();
            if ($currentUser && in_array($currentUser->role, ['admin', 'event_admin', 'judge'])) {
                $newClass = $fish->bettaClass;
                $this->createNotification(
                    $fish->participant->user_id,
                    'Ikan Dipindahkan',
                    "Ikan #{$fish->registration_no} dipindahkan ke kelas " . ($newClass->code ?? 'Baru'),
                    'fish_status'
                );

                // Target: Superadmin + Admin Khusus Event ini
                $event = $fish->event;
                $admins = \App\Models\User::where('role', 'admin')->get();
                $eventAdmins = $event->event_admins;
                $allTargets = $admins->concat($eventAdmins)->unique('id');

                // Notif ke Admin Panel
                \Filament\Notifications\Notification::make()
                    ->title('Ikan Dipindahkan')
                    ->body("Ikan #{$fish->registration_no} dipindahkan ke " . ($newClass->code ?? 'Baru'))
                    ->info()
                    ->sendToDatabase($allTargets);
            }
        }

        // 2. Notif DQ
        if ($fish->isDirty('status') && $fish->status === 'DQ') {
            $this->createNotification(
                $fish->participant->user_id,
                'Ikan Diskualifikasi (DQ)',
                "Ikan #{$fish->registration_no} telah didiskualifikasi. Cek catatan admin.",
                'fish_status'
            );

            // Target: Superadmin + Admin Khusus Event ini
            $event = $fish->event;
            $admins = \App\Models\User::where('role', 'admin')->get();
            $eventAdmins = $event->event_admins;
            $allTargets = $admins->concat($eventAdmins)->unique('id');

            \Filament\Notifications\Notification::make()
                ->title('Ikan DQ')
                ->body("Ikan #{$fish->registration_no} telah di-Diskualifikasi.")
                ->danger()
                ->sendToDatabase($allTargets);
        }

        // 3. Notif Nominasi
        if ($fish->isDirty('is_nominated') && $fish->is_nominated) {
            $this->createNotification(
                $fish->participant->user_id,
                'Ikan Masuk Nominasi',
                "Selamat! Ikan #{$fish->registration_no} masuk nominasi juara.",
                'fish_status'
            );
        }

        // 4. Notif Juara 123
        if ($fish->isDirty('final_rank') && in_array($fish->final_rank, [1, 2, 3])) {
            $this->createNotification(
                $fish->participant->user_id,
                '🎉 Juara Ditemukan!',
                "Selamat! Ikan #{$fish->registration_no} meraih Juara {$fish->final_rank} di kelasnya.",
                'fish_status'
            );
        }

        // 5. Notif Grand Champion (GC)
        if ($fish->isDirty('winner_type') && $fish->winner_type === 'GC') {
            $this->createNotification(
                $fish->participant->user_id,
                '🏆 GRAND CHAMPION!',
                "Luar Biasa! Ikan #{$fish->registration_no} meraih gelar Grand Champion.",
                'fish_status'
            );
        }
    }

    private function createNotification($userId, $title, $message, $type)
    {
        if (!$userId) return;

        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }
}
