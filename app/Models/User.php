<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'status', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function profileSearches(): HasMany
    {
        return $this->hasMany(ProfileSearch::class, 'rh_user_id');
    }

    public function university(): HasOne
    {
        return $this->hasOne(University::class, 'agent_id');
    }

    public function roleName(): string
    {
        return strtolower((string) $this->role?->name);
    }

    public function isAdmin(): bool
    {
        return $this->roleName() === 'admin';
    }

    public function isRh(): bool
    {
        return $this->roleName() === 'rh';
    }

    public function isUniversity(): bool
    {
        return in_array($this->roleName(), ['university', 'universite'], true);
    }

    public function ticketBalance(): int
    {
        $purchased = (int) $this->tickets()->where('status', 'approved')->sum('quantity');
        $used = $this->profileSearches()->count();

        return max(0, $purchased - $used);
    }
}
