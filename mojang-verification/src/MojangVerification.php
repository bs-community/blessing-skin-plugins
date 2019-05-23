<?php

namespace GPlane\Mojang;

use Illuminate\Database\Eloquent\Model;

class MojangVerification extends Model
{
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'verified' => 'boolean',
    ];
}
