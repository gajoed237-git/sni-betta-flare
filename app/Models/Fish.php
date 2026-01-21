<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fish extends Model
{
    protected $table = 'fishes';
    protected $fillable = [
        'event_id',
        'class_id',
        'registration_no',
        'participant_name',
        'team_name',
        'phone',
        'status',
        'is_nominated',
        'participant_id',
        'final_rank',
        'winner_type',
        'original_class_id',
        'admin_note'
    ];

    protected $casts = [
        'is_nominated' => 'boolean',
        'winner_type' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bettaClass(): BelongsTo
    {
        return $this->belongsTo(BettaClass::class, 'class_id');
    }

    public function originalClass(): BelongsTo
    {
        return $this->belongsTo(BettaClass::class, 'original_class_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(FishScore::class);
    }

    public function myScore(): HasOne
    {
        return $this->hasOne(FishScore::class)->where('judge_id', auth()->id());
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(ScoreSnapshot::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
