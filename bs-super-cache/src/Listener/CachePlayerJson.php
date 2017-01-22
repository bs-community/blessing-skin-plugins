<?php

namespace SuperCache\Listener;

use Cache;
use Storage;
use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use Illuminate\Contracts\Events\Dispatcher;

class CachePlayerJson
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetPlayerJson::class, [$this, 'cachePlayerJson']);
        $events->listen(PlayerProfileUpdated::class, [$this, 'freshPlayerJson']);
    }

    /**
     * Handle the event.
     *
     * @param  GetPlayerJson  $event
     * @return void
     */
    public function cachePlayerJson(GetPlayerJson $event)
    {
        $key = "json-{$event->player->pid}-{$event->api_type}";

        $content = Cache::rememberForever($key, function () use ($event) {
            return $event->player->generateJsonProfile($event->api_type);
        });

        return $content;
    }

    public function freshPlayerJson(PlayerProfileUpdated $event)
    {
        $keys = [
            "json-{$event->player->pid}-0",
            "json-{$event->player->pid}-1"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
