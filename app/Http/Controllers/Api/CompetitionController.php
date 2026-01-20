<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BettaClass;
use App\Models\Fish;
use App\Models\FishScore;
use App\Models\ScoreSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetitionController extends Controller
{
    /**
     * Get list of events assigned to the current user (Judge/Admin).
     */
    public function getAssignedEvents(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $events = \App\Models\Event::where('is_active', true)->get();
        } else {
            $managed = $user->managed_events()->where('is_active', true)->get();
            $judging = $user->assigned_judging_events()->where('is_active', true)->get();
            $events = $managed->merge($judging)->unique('id')->values();
        }

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * Get list of past (inactive) events judged by the user.
     */
    public function getJudgeHistory(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $events = \App\Models\Event::where('is_active', false)->orderBy('event_date', 'desc')->get();
        } else {
            $managed = $user->managed_events()->where('is_active', false)->get();
            $judging = $user->assigned_judging_events()->where('is_active', false)->get();
            $events = $managed->merge($judging)->unique('id')->sortByDesc('event_date')->values();
        }

        // Add judging stats for each event
        $events->map(function ($event) use ($user) {
            $event->judged_count = FishScore::where('judge_id', $user->id)
                ->whereHas('fish', function ($q) use ($event) {
                    $q->where('event_id', $event->id);
                })
                ->distinct('fish_id')
                ->count('fish_id');

            $event->dq_count = Fish::where('event_id', $event->id)
                ->where('status', 'disqualified')
                ->whereHas('scores', function ($q) use ($user) {
                    $q->where('judge_id', $user->id);
                })
                ->count();

            return $event;
        });

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * Get judging statistics for the current judge.
     */
    public function getJudgeStats(Request $request)
    {
        $user = auth()->user();

        // Get all assigned event IDs
        $eventIds = [];
        if ($request->has('event_id')) {
            $eventIds = [$request->event_id];
        } elseif ($user->isAdmin()) {
            $eventIds = \App\Models\Event::where('is_active', true)->pluck('id')->toArray();
        } else {
            $managed = $user->managed_events()->pluck('events.id')->toArray();
            $judging = $user->assigned_judging_events()->pluck('events.id')->toArray();
            $eventIds = array_unique(array_merge($managed, $judging));
        }

        // Get total fish in assigned events
        $totalFish = Fish::whereIn('event_id', $eventIds)->count();

        // SCORED FISH: Ikan yang sudah dinilai oleh SIAPAPUN juri
        // Kriteria: Ada penilaian dari juri manapun ATAU sudah DQ ATAU sudah dinominasikan
        $scoredFish = Fish::whereIn('event_id', $eventIds)
            ->where(function ($q) {
                $q->whereHas('scores') // Ada penilaian dari juri manapun
                    ->orWhere('status', 'disqualified') // Atau sudah DQ
                    ->orWhere('is_nominated', true); // Atau sudah dinominasikan
            })
            ->count();

        // UNSCORED FISH: Ikan yang BELUM dinilai sama sekali
        // Kriteria: Tidak ada penilaian dari juri manapun DAN belum DQ DAN belum dinominasikan
        $unscoredFish = Fish::whereIn('event_id', $eventIds)
            ->whereDoesntHave('scores') // Belum ada penilaian dari juri manapun
            ->where('status', '!=', 'disqualified') // Belum DQ
            ->where('is_nominated', false) // Belum dinominasikan
            ->count();

        // Get active events count
        $activeEvents = count($eventIds);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_fish' => $totalFish,
                'scored_fish' => $scoredFish,
                'unscored_fish' => $unscoredFish,
                'active_events' => $activeEvents
            ]
        ]);
    }

    /**
     * Get list of fishes that haven't been scored by the current judge.
     */
    public function getUnscoredFishes(Request $request)
    {
        $user = auth()->user();
        $eventIds = [];

        if ($request->has('event_id')) {
            $eventIds = [$request->event_id];
        } elseif ($user->isAdmin()) {
            $eventIds = \App\Models\Event::where('is_active', true)->pluck('id')->toArray();
        } else {
            $managed = $user->managed_events()->pluck('events.id')->toArray();
            $judging = $user->assigned_judging_events()->pluck('events.id')->toArray();
            $eventIds = array_unique(array_merge($managed, $judging));
        }

        $fishes = Fish::whereIn('event_id', $eventIds)
            ->where('status', '!=', 'disqualified')
            ->where('is_nominated', false)
            ->whereDoesntHave('scores', function ($query) use ($user) {
                $query->where('judge_id', $user->id);
            })
            ->with(['bettaClass.division', 'event'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $fishes
        ]);
    }

    /**
     * Get list of classes, optionally filtered by event.
     */
    public function getClasses(Request $request)
    {
        $user = auth()->user();
        $query = BettaClass::with(['division', 'event'])->withCount('fishes');

        // Scope by user assignments if not a super admin AND no specific event_id is requested by a participant
        // If event_id is provided (e.g. from participant app), we should trust the event_id filter (participant access control is handled elsewhere or implicitly by being public data)
        if ($user && !$user->isAdmin() && !$request->has('event_id')) {
            $assignedEventIds = $user->managed_events()->pluck('events.id')
                ->merge($user->assigned_judging_events()->pluck('events.id'))
                ->unique();

            $query->whereIn('event_id', $assignedEventIds);
        }

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $classes = $query->get();
        return response()->json([
            'status' => 'success',
            'data' => $classes
        ]);
    }

    /**
     * Get list of fishes in a specific class.
     */
    public function getFishesByClass($classId)
    {
        $user = auth()->user();
        $fishes = Fish::where('class_id', $classId)
            ->with(['snapshot', 'myScore', 'event', 'bettaClass.event'])
            ->get()
            ->map(function ($fish) use ($user) {
                $fish->is_judged_by_me = $fish->myScore ? true : false;
                return $fish;
            });

        return response()->json([
            'status' => 'success',
            'data' => $fishes
        ]);
    }

    /**
     * Get details of a single fish by registration_no or ID.
     */
    public function getFishDetails($id)
    {
        $fish = $this->resolveFish($id);

        if (!$fish) {
            return response()->json(['message' => 'Fish not found or you do not have access to this event'], 404);
        }

        $fish->load(['event', 'bettaClass.event', 'snapshot', 'scores.judge', 'myScore']);

        // Add judge names as a string
        $fish->judged_by_name = $fish->scores->map(function ($s) {
            return $s->judge ? $s->judge->name : 'Juri';
        })->unique()->implode(', ');

        return response()->json($fish);
    }

    /**
     * Submit a score (Judge action).
     */
    public function submitScore(Request $request)
    {
        // Resolve fish (allows registration_no from scanner)
        $fish = $this->resolveFish($request->fish_id);
        if (!$fish) {
            return response()->json(['message' => 'Ikan tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }

        $user = auth()->user();
        if (!$user->canJudgeEvent($fish->event_id)) {
            return response()->json(['message' => 'Anda tidak memiliki otoritas penjurian untuk event ini.'], 403);
        }

        // Use the real primary ID for the rest of the logic
        $request->merge(['fish_id' => $fish->id]);

        $request->validate([
            'fish_id' => 'required|exists:fishes,id',
            // IBC Numeric
            'minus_kepala' => 'nullable|numeric|min:0',
            'minus_badan' => 'nullable|numeric|min:0',
            'minus_dorsal' => 'nullable|numeric|min:0',
            'minus_anal' => 'nullable|numeric|min:0',
            'minus_ekor' => 'nullable|numeric|min:0',
            'minus_dasi' => 'nullable|numeric|min:0',
            'minus_kerapihan' => 'nullable|numeric|min:0',
            'minus_warna' => 'nullable|numeric|min:0',
            'minus_lain_lain' => 'nullable|numeric|min:0',
            // SNI Notes
            'kepala_notes' => 'nullable|string',
            'kedokan_notes' => 'nullable|string',
            'badan_notes' => 'nullable|string',
            'dorsal_notes' => 'nullable|string',
            'anal_notes' => 'nullable|string',
            'ekor_notes' => 'nullable|string',
            'dasi_notes' => 'nullable|string',
            'warna_notes' => 'nullable|string',
            'mental_notes' => 'nullable|string',
            'kerapihan_notes' => 'nullable|string',
            'proporsi_notes' => 'nullable|string',
            // Common
            'final_rank' => 'nullable|integer',
        ]);

        if ($fish->event->is_locked && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Penilaian terkunci untuk event ini. Hubungi Admin Pusat jika ada kesalahan.'
            ], 403);
        }

        // WINNER LOCK: If fish already has a final rank or is GC, prevent non-admin from changing anything
        if (($fish->final_rank || $fish->winner_type === 'gc') && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ikan ini sudah memiliki peringkat juara (Juara 1/2/3/GC). Data dikunci untuk menjaga integritas. Hubungi Admin jika ada kesalahan.'
            ], 403);
        }

        $score = FishScore::updateOrCreate(
            [
                'fish_id' => $fish->id,
                'judge_id' => $user->id,
            ],
            $request->all()
        );

        // Update fish status to judged
        $fish->update(['status' => 'judging']);
        if ($request->filled('final_rank')) {
            $fish->update(['final_rank' => $request->final_rank]);
        }

        return response()->json([
            'message' => 'Score submitted successfully',
            'score' => $score
        ]);
    }

    /**
     * Helper to resolve fish by ID or registration_no with security scoping.
     */
    private function resolveFish($id)
    {
        $user = auth()->user();
        if (!$user) return null;

        $query = Fish::with(['event', 'bettaClass.event']);

        if (!$user->isAdmin()) {
            // Include events where user has staff roles (Admin/Judge)
            $staffEventIds = $user->managed_events()->pluck('events.id')
                ->merge($user->assigned_judging_events()->pluck('events.id'));

            // ALSO include events where user is a registered participant
            $participantEventIds = \App\Models\Participant::where('user_id', $user->id)->pluck('event_id');

            $allowedEventIds = $staffEventIds->merge($participantEventIds)->unique();

            // Override allowedEventIds for Judges who need to move fish in bulk or access specific fish
            // If the user has 'judge' role and is assigned to the event of the requested fish, allow it.
            // This is handled by retrieving the fish first and then checking event access if strictly needed,
            // but the query-level filtering is safer.
            // The issue is likely that assignments might be cached or relationship issues.
            // Let's broaden the scope: if I am a judge for Event X, I can see ANY fish in Event X.
            $query->whereIn('event_id', $allowedEventIds);
        }

        $fish = (clone $query)->where('registration_no', $id)->first();
        if (!$fish) {
            $fish = $query->where('id', $id)->first();
        }

        return $fish;
    }

    /**
     * Move a fish to a different class (Management action).
     */
    public function moveFishClass(Request $request, $id)
    {
        $request->validate([
            'new_class_id' => 'required|exists:betta_classes,id',
            'reason' => 'nullable|string'
        ]);

        $fish = $this->resolveFish($id);
        if (!$fish) {
            return response()->json(['message' => 'Fish not found or unauthorized'], 404);
        }

        $user = auth()->user();
        // Allow if user is Manager (Admin/Event Admin) OR a Judge assign to this event
        if (!$user->canManageEvent($fish->event_id) && !$user->canJudgeEvent($fish->event_id)) {
            return response()->json(['message' => 'Unauthorized: Anda tidak memiliki akses manajemen atau penjurian untuk event ini.'], 403);
        }

        if ($fish->event->is_locked && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operasi dilarang. Event ini sedang dalam status terkunci.'
            ], 403);
        }

        if (($fish->final_rank || $fish->winner_type === 'gc') && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ikan ini sudah menjadi juara. Tidak dapat dipindahkan kelas kecuali oleh Admin.'
            ], 403);
        }

        $oldClassId = $fish->class_id;
        $fish->update([
            'original_class_id' => $fish->original_class_id ?? $fish->class_id,
            'class_id' => $request->new_class_id,
            'status' => 'moved',
            'admin_note' => $request->reason
        ]);

        // Refresh rankings for both classes
        if ($oldClassId) ScoreSnapshot::refreshRankings($oldClassId);
        ScoreSnapshot::refreshRankings($request->new_class_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Ikan berhasil dipindah ke kelas baru',
            'data' => $fish
        ]);
    }

    /**
     * Disqualify a fish (Management action).
     */
    public function disqualifyFish(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $fish = $this->resolveFish($id);
        if (!$fish) {
            return response()->json(['message' => 'Fish not found or unauthorized'], 404);
        }

        $user = auth()->user();
        // Allow if user is Manager (Admin/Event Admin) OR a Judge assign to this event
        if (!$user->canManageEvent($fish->event_id) && !$user->canJudgeEvent($fish->event_id)) {
            return response()->json(['message' => 'Unauthorized: Anda tidak memiliki akses manajemen atau penjurian untuk event ini.'], 403);
        }

        if ($fish->event->is_locked && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operasi dilarang. Event ini sedang dalam status terkunci.'
            ], 403);
        }

        if (($fish->final_rank || $fish->winner_type === 'gc') && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ikan ini sudah menjadi juara. Tidak dapat didiskualifikasi kecuali oleh Admin.'
            ], 403);
        }

        $fish->update([
            'status' => 'disqualified',
            'admin_note' => $request->reason
        ]);

        if ($fish->class_id) {
            ScoreSnapshot::refreshRankings($fish->class_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ikan telah didiskualifikasi',
            'data' => $fish
        ]);
    }

    /**
     * Set nomination status for a fish (Judge/Admin action).
     */
    public function toggleNomination(Request $request, $id)
    {
        $fish = $this->resolveFish($id);
        if (!$fish) {
            return response()->json(['message' => 'Fish not found or unauthorized'], 404);
        }

        $user = auth()->user();
        if (!$user->canJudgeEvent($fish->event_id) && !$user->canManageEvent($fish->event_id)) {
            return response()->json(['message' => 'Unauthorized: Anda tidak memiliki akses untuk mengubah status nominasi.'], 403);
        }

        if ($fish->event->is_locked && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operasi dilarang. Event ini sedang dalam status terkunci.'
            ], 403);
        }

        if (($fish->final_rank || $fish->winner_type === 'gc') && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ikan ini sudah menjadi juara. Nominasi tidak dapat diubah.'
            ], 403);
        }

        $newNominated = !$fish->is_nominated;
        $updateData = ['is_nominated' => $newNominated];

        if ($newNominated && in_array($fish->status, ['registered', 'checking'])) {
            $updateData['status'] = 'judging';
        }

        $fish->update($updateData);

        if ($fish->class_id) {
            ScoreSnapshot::refreshRankings($fish->class_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => $fish->is_nominated ? 'Ikan masuk nominasi' : 'Ikan keluar dari nominasi',
            'data' => [
                'is_nominated' => $fish->is_nominated
            ]
        ]);
    }
    /**
     * Get nominated fishes grouped by event and class for winner selection.
     */
    public function getNominatedFishes(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $query = Fish::where('is_nominated', true)
            ->with(['event', 'bettaClass.division', 'snapshot']);

        // Filter by assigned events if not super admin
        if (!$user->isAdmin()) {
            $assignedEventIds = $user->managed_events()->pluck('events.id')
                ->merge($user->assigned_judging_events()->pluck('events.id'))
                ->unique();
            $query->whereIn('event_id', $assignedEventIds);
        }

        $fishes = $query->get();

        // Grouping: Event -> Division -> Class -> Fishes
        $grouped = $fishes->groupBy('event_id')->map(function ($eventFishes) {
            $event = $eventFishes->first()->event;
            return [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'divisions' => $eventFishes->groupBy('bettaClass.division_id')->map(function ($divisionFishes) use ($event) {
                    $firstFish = $divisionFishes->first();
                    $division = $firstFish->bettaClass->division;

                    // Check if this division already has a GC
                    $gcFish = Fish::whereHas('bettaClass', function ($q) use ($division) {
                        $q->where('division_id', $division->id);
                    })->where('winner_type', 'gc')->first();

                    return [
                        'division_id' => $division->id ?? null,
                        'division_name' => $division?->name ?? 'Unclassified',
                        'has_gc' => !!$gcFish,
                        'gc_fish_id' => $gcFish?->id,
                        'classes' => $divisionFishes->groupBy('class_id')->map(function ($classFishes) {
                            $class = $classFishes->first()->bettaClass;
                            return [
                                'class_id' => $class->id,
                                'class_name' => $class->name,
                                'fishes' => $classFishes->values()
                            ];
                        })->values()
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped
        ]);
    }
    /**
     * Get Juara 1 from all classes in a division for GC selection.
     */
    public function getDivisionWinners(Request $request, $divisionId)
    {
        $fishes = Fish::whereHas('bettaClass', function ($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        })
            ->where('final_rank', 1)
            ->with(['bettaClass', 'snapshot'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $fishes
        ]);
    }

    /**
     * Set winner type (Class or GC).
     */
    public function setWinnerType(Request $request, $id)
    {
        $request->validate([
            'winner_type' => 'required|in:class,gc,none',
        ]);

        $fish = $this->resolveFish($id);
        if (!$fish) {
            return response()->json(['message' => 'Fish not found or unauthorized'], 404);
        }

        if ($fish->event->is_locked && !auth()->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operasi dilarang. Event ini sedang dalam status terkunci.'
            ], 403);
        }

        // Check if other fish in the SAME DIVISION is already GC
        if ($request->winner_type === 'gc') {
            $divisionId = $fish->bettaClass?->division_id;
            if ($divisionId) {
                $existingGC = Fish::whereHas('bettaClass', function ($q) use ($divisionId) {
                    $q->where('division_id', $divisionId);
                })
                    ->where('winner_type', 'gc')
                    ->where('id', '!=', $fish->id)
                    ->exists();

                if ($existingGC && !auth()->user()->isAdmin()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Divisi ini sudah memiliki pemenang Grand Champion. Harap batalkan GC sebelumnya jika ingin mengubah.'
                    ], 403);
                }
            }
        }

        // Individual Fish Lock: Prevent changing a winner/GC unless we are unsetting it ('none')
        // EXCEPT: Allow setting 'gc' type for a fish that is already Juara 1 (which is the normal GC flow)
        if ($request->winner_type !== 'none' && !($request->winner_type === 'gc' && $fish->final_rank === 1)) {
            if (($fish->final_rank || $fish->winner_type === 'gc') && !auth()->user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ikan ini sudah memiliki gelar juara. Hubungi Admin untuk perubahan.'
                ], 403);
            }
        }

        $fish->update([
            'winner_type' => $request->winner_type === 'none' ? null : $request->winner_type
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tipe juara diperbarui',
            'data' => $fish
        ]);
    }

    /**
     * Update fish class (Participant self-service).
     */
    public function updateFishClass(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:betta_classes,id',
        ]);

        // Resolve fish (Scoping handles judge/admin event assignments)
        $fish = $this->resolveFish($id);
        if (!$fish) {
            return response()->json(['message' => 'Fish not found or unauthorized'], 404);
        }

        $user = auth()->user();
        $participant = $fish->participant;

        if (!$participant) {
            $participant = \App\Models\Participant::where('user_id', $user->id)
                ->where('event_id', $fish->event_id)
                ->first();

            if (!$participant) {
                // If user is Admin or Event Admin of this event, we can proceed
                if (!$user->isAdmin() && !$user->canManageEvent($fish->event_id)) {
                    return response()->json(['message' => 'Data peserta tidak ditemukan untuk ikan ini.'], 403);
                }
            } else {
                $fish->update(['participant_id' => $participant->id]);
            }
        }

        // Authorization check for non-admin move
        if (!$user->isAdmin() && !$user->canManageEvent($fish->event_id) && (!$participant || $participant->user_id !== $user->id)) {
            return response()->json([
                'message' => 'Unauthorized: Ikan ini bukan milik Anda.',
            ], 403);
        }

        if ($fish->event->is_locked && !$user->isAdmin()) {
            $judgingHasStarted = \App\Models\FishScore::whereHas('fish', function ($q) use ($fish) {
                $q->where('event_id', $fish->event_id);
            })->exists();

            if ($judgingHasStarted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat mengubah kelas. Event terkunci dan sesi penjurian telah dimulai.'
                ], 403);
            }
        }

        $fish->update([
            'class_id' => $request->class_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas ikan berhasil diperbarui',
            'data' => $fish
        ]);
    }
}
