<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Fish;

class ScoreSnapshot extends Model
{
    protected $fillable = [
        'fish_id',
        'average_score',
        'total_judges',
        'rank_in_class',
        'is_gc',
        'is_bob'
    ];

    public function fish(): BelongsTo
    {
        return $this->belongsTo(Fish::class);
    }

    /**
     * Refresh rankings for all fishes in a specific class.
     */
    public static function refreshRankings($classId)
    {
        // 1. Get total fishes in this class
        $totalFishes = Fish::where('class_id', $classId)->count();

        // 2. Determine nomination limit (10-20)
        $nominationLimit = $totalFishes > 30 ? 20 : 10;

        // Reset all ranks for this class first
        self::query()
            ->whereHas('fish', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->update(['rank_in_class' => null]);

        // 3. Get all score snapshots for this class, ordered by average_score DESC
        // We also join with FishScore to get total sum of minuses for tiebreaker
        $snapshots = self::query()
            ->whereHas('fish', function ($q) use ($classId) {
                $q->where('class_id', $classId)
                    ->where('is_nominated', true);
            })
            ->with(['fish'])
            ->get()
            ->sortByDesc(function ($snapshot) {
                // Primary: average_score DESC
                // Secondary: total_minus (sum across all judges for this fish) ASC
                $sumMinus = DB::table('fish_scores')->where('fish_id', $snapshot->fish_id)->sum('total_minus');
                return [$snapshot->average_score, -$sumMinus];
            })
            ->values();

        // 4. Update rankings
        foreach ($snapshots as $index => $snapshot) {
            $rank = $index + 1;

            // Only assign rank if within nomination limit
            $snapshot->rank_in_class = $rank <= $nominationLimit ? $rank : null;
            $snapshot->save();
        }
    }
}
