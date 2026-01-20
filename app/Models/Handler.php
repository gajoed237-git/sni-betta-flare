<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Handler extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
