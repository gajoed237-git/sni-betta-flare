<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'event_id',
        'title',
        'message',
        'type',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    protected static function booted()
    {
        static::created(function ($notification) {
            // Get target tokens
            $tokens = [];

            if ($notification->user_id) {
                // Single user notification
                $token = \App\Models\User::find($notification->user_id)?->fcm_token;
                if ($token) $tokens[] = $token;
            } else {
                // Broadcast notification
                if ($notification->event_id) {
                    // All users in an event
                    $tokens = \App\Models\User::whereHas('events', function ($q) use ($notification) {
                        $q->where('events.id', $notification->event_id);
                    })->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
                } else {
                    // Global broadcast
                    $tokens = \App\Models\User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
                }
            }

            if (!empty($tokens)) {
                \App\Services\PushNotificationService::send(
                    $tokens,
                    $notification->title,
                    $notification->message,
                    array_merge(['id' => $notification->id], $notification->data ?? [])
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
