<?php

namespace GPlane\ShareRegistrationLink;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $table = 'reg_link';

    protected $casts = [
        'id' => 'integer',
        'sharer' => 'integer',
    ];
}
