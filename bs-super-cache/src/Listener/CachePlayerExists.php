<?php

namespace SuperCache\Listener;

use Cache;
use Storage;
use App\Models\Player;
use App\Events\PlayerWasAdded;
use App\Events\CheckPlayerExists;
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
     * @param  CheckPlayerExists  $event
     * @return void
     */
    public function cachePlayerExists(CheckPlayerExists $event)
    {
        $key = "notfound-{$event->player_name}";

        // if the player name haven't been marked as notfound
        if ($event->player_name && is_null(Cache::get($key))) {
            $player = Player::where('player_name', $event->player_name)->first();

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
