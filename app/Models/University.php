<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['agent_id', 'name', 'logo', 'address', 'status'])]
class University extends Model
{
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function graduates(): HasMany
    {
        return $this->hasMany(Graduate::class);
    }

    public function palmares(): HasMany
    {
        return $this->hasMany(Palmares::class);
    }

    public function profileSearches(): HasMany
    {
        return $this->hasMany(ProfileSearch::class);
    }
}
