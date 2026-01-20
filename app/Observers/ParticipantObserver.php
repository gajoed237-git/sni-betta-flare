<?php

namespace App\Observers;

use App\Models\Participant;
use App\Models\Notification;

class ParticipantObserver
{
    public function updated(Participant $participant): void
    {
        // Notif Approve Pembayaran ke User
        if (
            $participant->isDirty('payment_status') &&
            $participant->payment_status === 'paid' &&
            $participant->getOriginal('payment_status') !== 'paid'
        ) {

            Notification::create([
                'user_id' => $participant->user_id,
                'title' => 'Pembayaran Dikonfirmasi',
                'message' => "Pembayaran untuk event #{$participant->event->name} telah dikonfirmasi. Selamat berkompetisi!",
                'type' => 'payment',
            ]);
        }

        // Notif Bukti Bayar Baru ke Admin (Hanya jika ada upload bukti baru)
        if ($participant->wasChanged('payment_proof') && $participant->payment_proof) {
            $event = $participant->event;

            // Target: Superadmin + Admin Khusus Event ini
            $admins = \App\Models\User::where('role', 'admin')->get();
            $eventAdmins = $event->event_admins;
            $allTargets = $admins->concat($eventAdmins)->unique('id');

            \Filament\Notifications\Notification::make()
                ->title('Bukti Pembayaran Baru')
                ->body("Peserta {$participant->name} telah mengunggah bukti pembayaran untuk event {$event->name}.")
                ->success()
                ->sendToDatabase($allTargets);
        }
    }
}
