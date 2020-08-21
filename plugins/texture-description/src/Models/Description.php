<?php

namespace Blessing\TextureDescription\Models;

use App\Models\Texture;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $id
 * @property int     $tid
 * @property string  $description
 * @property Texture $texture
 */
class Description extends Model
{
    protected $table = 'textures_description';

    public $timestamps = false;

    protected $casts = [
        'id' => 'int',
        'tid' => 'int',
        'description' => 'string',
    ];

    protected $fillable = ['tid', 'description'];

    public function texture()
    {
        return $this->belongsTo(Texture::class, 'tid', 'tid');
    }
}
