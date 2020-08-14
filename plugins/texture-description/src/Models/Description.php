<?php

namespace Blessing\TextureDesc\Models;

use App\Models\Texture;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $id
 * @property int     $tid
 * @property string  $desc
 * @property Texture $texture
 */
class Description extends Model
{
    protected $table = 'textures_desc';

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'tid' => 'int',
        'desc' => 'string',
    ];

    protected $fillable = ['tid', 'desc'];

    public function texture()
    {
        return $this->belongsTo(Texture::class, 'tid', 'tid');
    }
}
