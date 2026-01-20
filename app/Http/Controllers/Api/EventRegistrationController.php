<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Fish;
use App\Models\BettaClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventRegistrationController extends Controller
{
    /**
     * List all active events for participants.
     */
    public function index()
    {
        try {
            $events = Event::withCount(['fishes', 'likes', 'comments'])
                ->orderBy('event_date', 'desc')
                ->get();

            if (Auth::check()) {
                $userId = Auth::id();
                $events->each(function ($event) use ($userId) {
                    $event->liked_by_me = $event->isLikedByUser($userId);
                });
            }

            return response()->json([
                'status' => 'success',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get details of a specific event including divisions and classes.
     */
    public function show(Event $event)
    {
        $event->load(['divisions.classes']);
        $event->loadCount('likes');
        $event->liked_by_me = $event->isLikedByUser(Auth::id());

        return response()->json([
            'status' => 'success',
            'data' => $event
        ]);
    }

    /**
     * Register for an event using the "Bagging List" method.
     */
    /**
     * Get list of handlers.
     */
    public function getHandlers()
    {
        $handlers = \App\Models\Handler::orderBy('name')->get(['id', 'name']);
        return response()->json([
            'status' => 'success',
            'data' => $handlers
        ]);
    }

    /**
     * Register for an event using the "Bagging List" method.
     */
    public function register(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'category' => 'required|in:team,single_fighter',
            'item_count' => 'required|integer|min:1',
            'handler_id' => 'nullable|exists:handlers,id',
            'handler_name' => 'nullable|string|max:255',
        ]);

        // 0. Pre-transaction checks for Event Status
        $event = Event::findOrFail($request->event_id);

        if ($event->is_locked) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, pendaftaran untuk event ini telah ditutup karena event terkunci.'
            ], 403);
        }

        // Check if judging activity has started (Scores, Move Class, DQ, etc)
        $hasScores = DB::table('fish_scores')
            ->join('fishes', 'fish_scores.fish_id', '=', 'fishes.id')
            ->where('fishes.event_id', $event->id)
            ->exists();

        $hasActivity = \App\Models\Fish::where('event_id', $event->id)
            ->whereIn('status', ['moved', 'disqualified', 'judging', 'completed'])
            ->exists();

        if ($hasScores || $hasActivity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, pendaftaran ditutup karena proses penjurian sudah dimulai.'
            ], 403);
        }

        try {
            return DB::transaction(function () use ($request, $event) {
                // Handle Handler Logic
                $handlerId = $request->handler_id;

                if (!$handlerId && $request->filled('handler_name')) {
                    $handler = \App\Models\Handler::firstOrCreate(
                        ['name' => $request->handler_name],
                        ['phone' => $request->phone]
                    );
                    $handlerId = $handler->id;
                } elseif (!$handlerId && $request->handler_id) {
                    // This handles the case where handler_id was provided but not found in DB (though validation exists)
                    $handlerId = null;
                }

                // 1. Create Participant entry
                // $event is already fetched outside

                // Validation checks moved to pre-transaction block

                $participant = Participant::create([
                    'event_id' => $request->event_id,
                    'user_id' => auth()->id(),
                    'name' => $request->name,
                    'team_name' => $request->team_name,
                    'phone' => $request->phone,
                    'category' => $request->category,
                    'payment_status' => 'unpaid',
                    'total_fee' => $request->item_count * ($event->registration_fee ?? 50000),
                    'handler_id' => $handlerId,
                ]);

                // 2. Create Fish entries (Unclassified Slots)
                // Use pure sequential numbering per event (no S/T prefix for privacy)

                for ($i = 0; $i < $request->item_count; $i++) {
                    // Find the highest registration number in this event
                    $maxRegNo = Fish::where('event_id', $request->event_id)
                        ->orderByRaw('CAST(registration_no AS UNSIGNED) DESC')
                        ->value('registration_no');

                    // Extract number and increment
                    $nextNumber = 1;
                    if ($maxRegNo) {
                        $currentNumber = (int) $maxRegNo;
                        $nextNumber = $currentNumber + 1;
                    }

                    // Pure sequential number without prefix
                    $regNo = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                    Fish::create([
                        'event_id' => $request->event_id,
                        'participant_id' => $participant->id,
                        'class_id' => null,
                        'registration_no' => $regNo,
                        'participant_name' => $request->name,
                        'team_name' => $request->team_name,
                        'phone' => $request->phone,
                        'status' => 'registered',
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Registration successful',
                    'data' => [
                        'participant_id' => $participant->id,
                        'total_fee' => $participant->total_fee,
                        'total_fish' => $request->item_count,
                        'handler_id' => $handlerId
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            \Log::error('Registration Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload payment proof for a registration.
     */
    public function uploadPayment(Request $request, Participant $participant)
    {
        if ($participant->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('payments', 'public');

            $participant->update([
                'payment_proof' => $path,
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment proof uploaded successfully',
                'data' => [
                    'proof_url' => asset('storage/' . $path)
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Upload failed'], 400);
    }

    /**
     * List events where the user is a participant.
     */
    public function myParticipations()
    {
        $participations = Participant::where('user_id', auth()->id())
            ->with(['event', 'fishes.bettaClass', 'fishes.originalClass'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $participations
        ]);
    }

    public function dashboardStats()
    {
        try {
            $userId = auth()->id();

            $totalUnpaid = Participant::where('user_id', $userId)
                ->whereIn('payment_status', ['unpaid', 'rejected'])
                ->sum('total_fee');

            $totalFish = Fish::whereHas('participant', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();

            $activeParticipations = Participant::where('user_id', $userId)
                ->where('payment_status', '!=', 'paid')
                ->with('event:id,name')
                ->get();

            // Get involved event IDs for notification filtering
            $participatedEventIds = Participant::where('user_id', $userId)->pluck('event_id')->toArray();
            $assignedEventIds = DB::table('event_user')->where('user_id', $userId)->pluck('event_id')->toArray();
            $allInvolvedEventIds = array_unique(array_merge($participatedEventIds, $assignedEventIds));

            $unreadNotifications = \App\Models\Notification::where(function ($query) use ($userId, $allInvolvedEventIds) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($sub) use ($allInvolvedEventIds) {
                        $sub->whereNull('user_id')
                            ->whereIn('event_id', $allInvolvedEventIds);
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNull('user_id')
                            ->whereNull('event_id');
                    });
            })
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_unpaid' => (int)$totalUnpaid,
                    'total_fish' => $totalFish,
                    'active_participations' => $activeParticipations,
                    'unread_notifications' => $unreadNotifications
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in EventRegistrationController@dashboardStats: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show details of a specific participation.
     */
    public function showParticipant(Participant $participant)
    {
        if ($participant->user_id !== auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $participant->load(['event', 'fishes.bettaClass', 'fishes.originalClass']);

        return response()->json([
            'status' => 'success',
            'data' => $participant
        ]);
    }

    /**
     * Get event history for the authenticated user.
     */
    public function myHistory()
    {
        $userId = auth()->id();
        $participations = \App\Models\Participant::where('user_id', $userId)
            ->with(['event', 'fishes.bettaClass'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $participations->map(function ($part) {
            $points = 0;
            $gold = 0;
            $silver = 0;
            $bronze = 0;
            $gc = 0;

            foreach ($part->fishes as $fish) {
                $standard = $part->event->judging_standard ?? 'sni';

                if ($standard === 'ibc') {
                    // IBC points
                    if ($fish->final_rank == 1) {
                        $points += 10;
                        $gold++;
                    } elseif ($fish->final_rank == 2) {
                        $points += 6;
                        $silver++;
                    } elseif ($fish->final_rank == 3) {
                        $points += 4;
                        $bronze++;
                    }

                    if ($fish->winner_type === 'gc') {
                        $points += 20;
                        $gc++;
                    }
                    if ($fish->winner_type === 'bob') {
                        $points += 40;
                    }
                } else {
                    // Default SNI points
                    if ($fish->final_rank == 1) {
                        $points += 15;
                        $gold++;
                    } elseif ($fish->final_rank == 2) {
                        $points += 7;
                        $silver++;
                    } elseif ($fish->final_rank == 3) {
                        $points += 3;
                        $bronze++;
                    }

                    if ($fish->winner_type === 'gc') {
                        $points += 30;
                        $gc++;
                    }
                    if ($fish->winner_type === 'bob') {
                        $points += 50;
                    }
                }
            }

            return [
                'id' => $part->id,
                'event_name' => $part->event->name,
                'event_date' => $part->event->event_date,
                'judging_standard' => $part->event->judging_standard,
                'team_name' => $part->team_name,
                'payment_status' => $part->payment_status,
                'summary' => [
                    'points' => $points,
                    'gold' => $gold,
                    'silver' => $silver,
                    'bronze' => $bronze,
                    'gc' => $gc,
                ],
                'fishes' => $part->fishes
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
