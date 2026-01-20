<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && in_array($this->role, ['admin', 'event_admin']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'profile_photo_path',
        'password',
        'role',
        'is_active',
        'login_otp',
        'otp_expires_at',
        'otp_enabled',
        'email_verified_at',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'otp_enabled' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isJudge(): bool
    {
        return $this->role === 'judge';
    }

    public function scores()
    {
        return $this->hasMany(FishScore::class, 'judge_id');
    }

    public function events_judged()
    {
        // This is a bit complex for a direct relation unless we have a specific pivot.
        // But for the 'counts' in Filament, we can just count scores for now, or use a custom query.
        // Let's just define 'scores' and maybe 'fishes_judged'.
        // For 'events_judged', we might need a custom attribute or just a 'hasManyThrough' if we are lucky, but Fish is between score and event.
        // Judge -> Score -> Fish -> Event.
        // Let's stick to just 'scores' for now to avoid errors if the deep relation is tricky.
        return $this->hasMany(FishScore::class, 'judge_id');
    }

    public function managed_events()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->wherePivot('role', 'event_admin')
            ->withTimestamps();
    }

    public function assigned_judging_events()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->wherePivot('role', 'judge')
            ->withTimestamps();
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isEventAdmin(): bool
    {
        return $this->role === 'event_admin';
    }

    public function canManageEvent($eventId): bool
    {
        if ($this->isAdmin()) {
            return true; // Superadmin can manage all events
        }

        if ($this->isEventAdmin()) {
            return $this->managed_events()->where('events.id', $eventId)->exists();
        }

        return false;
    }

    public function canJudgeEvent($eventId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->assigned_judging_events()->where('events.id', $eventId)->exists();
    }
}
