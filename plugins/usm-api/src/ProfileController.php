<?php

namespace Blessing\Usm;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Texture;
use App\Models\User;

class ProfileController extends Controller
{
    public function json($player)
    {
        $player = Player::where('name', $player)->first();
        if (empty($player)) {
            return response()
                ->json(['errno' => 1, 'msg' => 'Player not found.'])
                ->setStatusCode(404);
        }

        abort_if($player->user->permission === User::BANNED, 403);

        $skin = Texture::find($player->tid_skin);
        $model = empty($skin)
            ? 'default'
            : ($skin->type === 'steve' ? 'default' : 'slim');

        return response()->json([
            'player_name' => $player->name,
            'last_update' => $player->last_modified->timestamp,
            'model_preference' => [$model],
            'skins' => [
                $model => optional($skin)->hash,
            ],
            'cape' => optional(Texture::find($player->tid_cape))->hash,
        ]);
    }
}
