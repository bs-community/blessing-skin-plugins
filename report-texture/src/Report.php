<?php

namespace ReportTexture;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    const STATUS_PENDING  = 0;
    const STATUS_RESOLVED = 1;
    const STATUS_REJECTED = 2;

    public $timestamps = false;

    protected $fillable = ['status'];

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }
}
