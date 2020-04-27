<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
      'room_id',
      'user_id',
      'text'
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo('App\Room');
    }
}
