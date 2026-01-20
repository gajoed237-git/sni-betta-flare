<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventComment extends Model
{
    protected $fillable = ['user_id', 'event_id', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class, 'comment_id');
    }
}
