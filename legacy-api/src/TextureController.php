<?php

namespace Blessing\Legacy;

use App\Models\Player;
use App\Models\Texture;
use Storage;

class TextureController
{
    public function skin($player)
    {
        return $this->texture($player, 'skin');
    }

    public function cape($player)
    {
        return $this->texture($player, 'cape');
    }

    protected function texture($player, $type)
    {
        $player = Player::where('name', $player)->first();
        if (empty($player)) {
            return abort(404, 'Player not found.');
        }

        $tid = $player->getAttribute("tid_$type");
        $texture = Texture::find($tid);
        if (empty($texture)) {
            return abort(404, 'Texture not found.');
        }

        $hash = $texture->hash;
        if (Storage::disk('textures')->missing($hash)) {
            return abort(404, 'Texture not found.');
        }

        return response(Storage::disk('textures')->get($hash))
            ->header('Content-Type', 'image/png');
    }
}
