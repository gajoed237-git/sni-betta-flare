<?php

namespace App\Observers;

use App\Models\Fish;
use App\Models\FishScore;
use App\Models\ScoreSnapshot;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class FishScoreObserver
{
    public function saved(FishScore $fishScore): void
    {
        $this->updateSnapshot($fishScore->fish_id);

        // Get event_id from fish
        $eventId = Fish::find($fishScore->fish_id)?->event_id;

        AuditLogService::log(
            action: $fishScore->wasRecentlyCreated ? 'submitted_score' : 'updated_score',
            modelType: 'FishScore',
            modelId: $fishScore->id,
            details: ['fish_id' => $fishScore->fish_id, 'total_score' => $fishScore->total_score],
            eventId: $eventId
        );
    }

    public function deleted(FishScore $fishScore): void
    {
        $this->updateSnapshot($fishScore->fish_id);

        $eventId = Fish::find($fishScore->fish_id)?->event_id;

        AuditLogService::log(
            action: 'deleted_score',
            modelType: 'FishScore',
            modelId: $fishScore->id,
            details: ['fish_id' => $fishScore->fish_id],
            eventId: $eventId
        );
    }

    protected function updateSnapshot($fishId): void
    {
        $fish = Fish::find($fishId);
        if (!$fish) return;

        $stats = DB::table('fish_scores')
            ->where('fish_id', $fishId)
            ->select(
                DB::raw('AVG(total_score) as average_score'),
                DB::raw('COUNT(*) as total_judges')
            )
            ->first();

        $snapshot = ScoreSnapshot::updateOrCreate(
            ['fish_id' => $fishId],
            [
                'average_score' => $stats->average_score ?? 0,
                'total_judges' => $stats->total_judges ?? 0,
            ]
        );

        // After updating the snapshot for this fish, refresh rankings for its class
        ScoreSnapshot::refreshRankings($fish->class_id);
    }
}
