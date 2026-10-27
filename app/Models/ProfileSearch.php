<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rh_user_id', 'graduate_id', 'university_id', 'ticket_id'])]
class ProfileSearch extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function rhUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rh_user_id');
    }

    public function graduate(): BelongsTo
    {
        return $this->belongsTo(Graduate::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
