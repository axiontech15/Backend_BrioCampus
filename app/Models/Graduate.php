<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['university_id', 'palmares_id', 'matricule', 'name', 'faculty', 'diploma_date'])]
class Graduate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diploma_date' => 'date',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function palmares(): BelongsTo
    {
        return $this->belongsTo(Palmares::class);
    }

    public function profileSearches(): HasMany
    {
        return $this->hasMany(ProfileSearch::class);
    }
}
