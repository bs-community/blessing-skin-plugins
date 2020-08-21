<?php

namespace Blessing\TextureDesc\Listeners;

use App\Models\Texture;
use Blessing\TextureDesc\Models\Description;

class AddDescriptionOnUpload
{
    public function handle(Texture $texture)
    {
        Description::create([
            'tid' => $texture->tid,
            'desc' => request('description', ''),
        ]);
    }
}
