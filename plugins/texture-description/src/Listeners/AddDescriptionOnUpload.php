<?php

namespace Blessing\TextureDescription\Listeners;

use App\Models\Texture;
use Blessing\TextureDescription\Models\Description;

class AddDescriptionOnUpload
{
    public function handle(Texture $texture)
    {
        Description::create([
            'tid' => $texture->tid,
            'description' => request('description', ''),
        ]);
    }
}
