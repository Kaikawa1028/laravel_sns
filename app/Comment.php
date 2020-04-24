<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\User');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo('App\Article');
    }
}
