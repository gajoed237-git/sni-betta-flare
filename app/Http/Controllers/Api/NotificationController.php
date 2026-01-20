<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private function baseQuery()
    {
        $userId = auth()->id();

        // Ambil ID event di mana user terlibat (peserta atau juri/admin event)
        $participatedEventIds = Participant::where('user_id', $userId)->pluck('event_id')->toArray();
        $assignedEventIds = DB::table('event_user')->where('user_id', $userId)->pluck('event_id')->toArray();
        $allInvolvedEventIds = array_unique(array_merge($participatedEventIds, $assignedEventIds));

        return Notification::where(function ($query) use ($userId, $allInvolvedEventIds) {
            $query->where('user_id', $userId)
                ->orWhere(function ($sub) use ($allInvolvedEventIds) {
                    $sub->whereNull('user_id')
                        ->whereIn('event_id', $allInvolvedEventIds);
                })
                ->orWhere(function ($sub) {
                    $sub->whereNull('user_id')
                        ->whereNull('event_id');
                });
        });
    }

    public function index(Request $request)
    {
        try {
            $notifications = $this->baseQuery()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $unreadCount = $this->baseQuery()
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in NotificationController@index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        $notification = $this->baseQuery()->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        $this->baseQuery()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function destroy($id)
    {
        // Cari notifikasi yang bisa diakses user (milik sendiri OR broadcast event terkait)
        $notification = $this->baseQuery()->findOrFail($id);

        // Jika ini notifikasi broadcast (user_id is null)
        if ($notification->user_id === null) {
            // Jika user adalah admin, perbolehkan menghapus secara fisik (global)
            if (auth()->user()->role === 'admin') {
                $notification->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Broadcast notification deleted globally by admin'
                ]);
            }

            // Jika bukan admin, hanya return success agar app mobile menyembunyikannya dari state.
            return response()->json([
                'success' => true,
                'message' => 'Broadcast notification hidden locally'
            ]);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function deleteAll()
    {
        Notification::where('user_id', auth()->id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted'
        ]);
    }
}
