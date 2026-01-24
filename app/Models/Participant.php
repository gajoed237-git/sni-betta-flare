<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'team_name',
        'category',
        'notes',
        'payment_status',
        'payment_proof',
        'total_fee',
        'handler_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(Handler::class);
    }

    public function fishes()
    {
        return $this->hasMany(Fish::class);
    }
}
