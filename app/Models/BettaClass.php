<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BettaClass extends Model
{
    protected $table = 'betta_classes';
    protected $fillable = ['event_id', 'division_id', 'name', 'code'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function fishes(): HasMany
    {
        return $this->hasMany(Fish::class, 'class_id');
    }
}
