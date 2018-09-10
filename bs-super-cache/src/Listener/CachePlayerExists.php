<?php

namespace SuperCache\Listener;

use App\Events\CheckPlayerExists;
use App\Events\PlayerWasAdded;
use App\Models\Player;
use Cache;
use Illuminate\Contracts\Events\Dispatcher;

class CachePlayerExists
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(CheckPlayerExists::class, [$this, 'cachePlayerExists']);
        $events->listen(PlayerWasAdded::class, [$this, 'freshNotFoundCache']);
    }

    /**
     * Handle the event.
     *
     * @param CheckPlayerExists $event
     *
     * @return void
     */
    public function cachePlayerExists(CheckPlayerExists $event)
    {
        $key = "notfound-{$event->playerName}";

        // if the player name haven't been marked as notfound
        if ($event->playerName && is_null(Cache::get($key))) {
            $player = Player::where('player_name', $event->playerName)->first();

            if (!$player) {
                // cache if player does not exist
                Cache::forever($key, '1');

                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function freshNotFoundCache(PlayerWasAdded $event)
    {
        Cache::forget("notfound-{$event->player->player_name}");
    }
}
