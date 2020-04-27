<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    public function messages(): HasMany
    {
        return $this->hasMany('App\Message');
    }
}
