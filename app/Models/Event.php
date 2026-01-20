<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'event_date',
        'location',
        'description',
        'is_active',
        'judging_standard',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'qris_image',
        'payment_instructions',
        'registration_fee',
        'ticket_price',
        'committee_name',
        'brochure_image',
        'is_locked',
        'views_count',
        'shares_count',
        'share_url'
    ];

    protected $casts = [
        'brochure_image' => 'array',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function fishes(): HasMany
    {
        return $this->hasMany(Fish::class);
    }

    public function event_admins()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->wherePivot('role', 'event_admin')
            ->withTimestamps();
    }

    public function judges()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('role')
            ->wherePivot('role', 'judge')
            ->withTimestamps();
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(EventLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
