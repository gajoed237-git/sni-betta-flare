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
        'is_finished',
        'views_count',
        'shares_count',
        'share_url',
        'sf_max_fish',
        'ju_max_fish',
        'point_rank1',
        'point_rank2',
        'point_rank3',
        'point_gc',
        'point_bob',
        'point_bof',
        'point_bod',
        'point_boo',
        'point_bov',
        'point_bos',
        'ibc_minus_ringan',
        'ibc_minus_kecil',
        'ibc_minus_besar',
        'ibc_minus_berat',
        'ots_fee',
        'label_gc',
        'label_bob',
        'label_bof',
        'label_bod',
        'label_boo',
        'label_bov',
        'label_bos',
        'point_accumulation_mode',
        'early_bird_fee',
        'early_bird_date',
        'normal_date',
    ];

    protected $casts = [
        'brochure_image' => 'array',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'is_finished' => 'boolean',
        'point_rank1' => 'integer',
        'point_rank2' => 'integer',
        'point_rank3' => 'integer',
        'point_gc' => 'integer',
        'point_bob' => 'integer',
        'point_bof' => 'integer',
        'ibc_minus_ringan' => 'integer',
        'ibc_minus_kecil' => 'integer',
        'ibc_minus_besar' => 'integer',
        'ibc_minus_berat' => 'integer',
        'point_bod' => 'integer',
        'point_boo' => 'integer',
        'point_bov' => 'integer',
        'point_bos' => 'integer',
        'early_bird_date' => 'date',
        'normal_date' => 'date',
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
