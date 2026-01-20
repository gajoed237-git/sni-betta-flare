<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log($action, $modelType = null, $modelId = null, $details = null, $eventId = null)
    {
        // Auto-resolve event_id if not provided
        if (!$eventId && $modelType && $modelId) {
            try {
                if ($modelType === 'FishScore') {
                    $eventId = \App\Models\FishScore::find($modelId)?->fish?->event_id;
                } elseif ($modelType === 'Fish') {
                    $eventId = \App\Models\Fish::find($modelId)?->event_id;
                } elseif ($modelType === 'Participant') {
                    $eventId = \App\Models\Participant::find($modelId)?->event_id;
                }
            } catch (\Exception $e) {
                // Ignore errors in auto-resolution
            }
        }

        return AuditTrail::create([
            'user_id' => Auth::id(),
            'event_id' => $eventId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
