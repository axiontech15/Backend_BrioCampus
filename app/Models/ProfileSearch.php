<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Graduate;
use App\Models\University;
use App\Models\Ticket;
class ProfileSearch extends Model
{
    protected $fillable = [
        'rh_user_id',
        'graduate_id',
        'university_id',
        'ticket_id',
    ];

    public function rhUser()
    {
        return $this->belongsTo(User::class, 'rh_user_id');
    }

    public function graduate()
    {
        return $this->belongsTo(Graduate::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
