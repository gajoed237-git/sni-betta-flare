<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Participant;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send H-1 reminders for upcoming events to participants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $this->info("Checking for events starting on {$tomorrow}...");

        $events = Event::whereDate('event_date', $tomorrow)->get();

        if ($events->isEmpty()) {
            $this->info("No events found for tomorrow.");
            return;
        }

        foreach ($events as $event) {
            $this->info("Processing Event: {$event->name}");

            // Get unique users participating
            $userIds = Participant::where('event_id', $event->id)
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            $count = 0;
            foreach ($userIds as $userId) {
                // Prevent duplicate notifications if run multiple times
                $exists = Notification::where('user_id', $userId)
                    ->where('event_id', $event->id)
                    ->where('type', 'event_reminder')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $userId,
                        'event_id' => $event->id,
                        'title' => 'Reminder: Event Besok! ⏰',
                        'message' => "Event '{$event->name}' akan dimulai besok. Pastikan ikan Anda sudah siap!",
                        'type' => 'event_reminder',
                        'data' => ['event_id' => $event->id]
                    ]);
                    $count++;
                }
            }

            $this->info("Sent reminders to {$count} participants.");
        }
    }
}
