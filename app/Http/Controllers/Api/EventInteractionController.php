<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLike;
use App\Models\EventComment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventInteractionController extends Controller
{
    /**
     * Increment view count for an event.
     */
    public function incrementView(Event $event)
    {
        $event->increment('views_count');
        return response()->json([
            'status' => 'success',
            'views_count' => $event->views_count
        ]);
    }

    /**
     * Increment share count for an event.
     */
    public function incrementShare(Event $event)
    {
        $event->increment('shares_count');
        return response()->json([
            'status' => 'success',
            'shares_count' => $event->shares_count
        ]);
    }

    /**
     * Toggle like for an event.
     */
    public function toggleLike(Event $event)
    {
        $userId = Auth::id();
        $like = EventLike::where('user_id', $userId)
            ->where('event_id', $event->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            EventLike::create([
                'user_id' => $userId,
                'event_id' => $event->id
            ]);
            $liked = true;
        }

        return response()->json([
            'status' => 'success',
            'liked' => $liked,
            'likes_count' => $event->likes()->count()
        ]);
    }

    /**
     * Add a comment to an event.
     */
    public function addComment(Request $request, Event $event)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment = EventComment::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'content' => $request->content
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Komentar berhasil ditambahkan',
            'data' => $comment->load('user:id,name,profile_photo_path')
        ]);
    }

    /**
     * Get comments for an event.
     */
    public function getComments(Event $event)
    {
        $userId = Auth::id();
        $comments = $event->comments()
            ->with(['user:id,name,profile_photo_path'])
            ->withCount(['reactions as love_count' => function ($q) {
                $q->where('reaction_type', 'love');
            }])
            ->withCount(['reactions as care_count' => function ($q) {
                $q->where('reaction_type', 'care');
            }])
            ->withCount(['reactions as haha_count' => function ($q) {
                $q->where('reaction_type', 'haha');
            }])
            ->withCount(['reactions as wow_count' => function ($q) {
                $q->where('reaction_type', 'wow');
            }])
            ->withCount(['reactions as sad_count' => function ($q) {
                $q->where('reaction_type', 'sad');
            }])
            ->withCount(['reactions as angry_count' => function ($q) {
                $q->where('reaction_type', 'angry');
            }])
            ->withCount(['reactions as like_count' => function ($q) {
                $q->where('reaction_type', 'like');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($userId) {
            $comments->getCollection()->transform(function ($comment) use ($userId) {
                $myReaction = $comment->reactions()->where('user_id', $userId)->first();
                $comment->my_reaction = $myReaction ? $myReaction->reaction_type : null;
                return $comment;
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $comments
        ]);
    }

    /**
     * React to a comment.
     */
    public function reactToComment(Request $request, EventComment $comment)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,love,care,haha,wow,sad,angry'
        ]);

        $userId = Auth::id();
        $reaction = CommentReaction::where('user_id', $userId)
            ->where('comment_id', $comment->id)
            ->first();

        if ($reaction) {
            if ($reaction->reaction_type === $request->reaction_type) {
                $reaction->delete();
                $action = 'removed';
            } else {
                $reaction->update(['reaction_type' => $request->reaction_type]);
                $action = 'updated';
            }
        } else {
            CommentReaction::create([
                'user_id' => $userId,
                'comment_id' => $comment->id,
                'reaction_type' => $request->reaction_type
            ]);
            $action = 'added';
        }

        return response()->json([
            'status' => 'success',
            'action' => $action,
            'my_reaction' => $action === 'removed' ? null : $request->reaction_type,
            'counts' => [
                'like' => $comment->reactions()->where('reaction_type', 'like')->count(),
                'love' => $comment->reactions()->where('reaction_type', 'love')->count(),
                'care' => $comment->reactions()->where('reaction_type', 'care')->count(),
                'haha' => $comment->reactions()->where('reaction_type', 'haha')->count(),
                'wow' => $comment->reactions()->where('reaction_type', 'wow')->count(),
                'sad' => $comment->reactions()->where('reaction_type', 'sad')->count(),
                'angry' => $comment->reactions()->where('reaction_type', 'angry')->count(),
            ]
        ]);
    }
}
