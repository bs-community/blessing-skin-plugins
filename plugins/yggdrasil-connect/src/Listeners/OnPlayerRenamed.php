<?php

namespace LittleSkin\YggdrasilConnect\Listeners;

use App\Models\Player;
use Illuminate\Support\Facades\Cache;
use LittleSkin\YggdrasilConnect\Models\UUID;

class OnPlayerRenamed
{
    public function handle(Player $player, string $old): void
    {
        $row = UUID::where('pid', $player->pid)->first();
        if ($row) {
            $row->name = $player->name;
            $row->save();
        }
        Cache::put("player-renamed-$row->uuid", now(), option('ygg_token_expire_1'));
    }
}
