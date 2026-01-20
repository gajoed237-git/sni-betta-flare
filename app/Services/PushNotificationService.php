<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send push notification using Expo Push API
     *
     * @param string|array $to Expo Push Token(s)
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function send($to, $title, $body, $data = [])
    {
        if (empty($to)) {
            return false;
        }

        try {
            $response = Http::post('https://exp.host/--/api/v2/push/send', [
                'to' => $to,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'sound' => 'default',
                'priority' => 'high',
                'channelId' => 'siknusa-alerts',
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Expo Push Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Expo Push Exception: ' . $e->getMessage());
            return false;
        }
    }
}
