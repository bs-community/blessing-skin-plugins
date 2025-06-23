<?php

namespace LittleSkin\YggdrasilConnect\Listeners;

use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid as RamseyUuid;
use LittleSkin\YggdrasilConnect\Models\UUID;

class OnPlayerAdded
{
    public function handle(Player $player): void
    {
        $uuid = option('ygg_uuid_algorithm') === 'v3' ? UUID::generateUuidV3($player->name) : RamseyUuid::uuid4()->getHex()->toString();
        DB::table('uuid')->insert([
            'pid' => $player->pid,
            'name' => $player->name,
            'uuid' => $uuid,
        ]);
    }
}
