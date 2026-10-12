<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Graduate extends Model
{
  public function university()
  {
      return $this->belongsTo(University::class);
  }
  
  public function palmares()
  {
      return $this->belongsTo(Palmares::class);
  }
}
