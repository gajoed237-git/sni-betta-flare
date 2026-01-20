<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    protected $fillable = ['event_id', 'name', 'code'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(BettaClass::class, 'division_id');
    }
}
