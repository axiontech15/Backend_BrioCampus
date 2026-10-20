<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'action', 'description', 'ip_address', 'user_agent', 'is_archived', 'archived_at'])]
class Log extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }
}
