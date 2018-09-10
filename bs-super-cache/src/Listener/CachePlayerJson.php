<?php

namespace SuperCache\Listener;

use App\Events\GetPlayerJson;
use App\Events\PlayerProfileUpdated;
use Cache;
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
     * @param GetPlayerJson $event
     *
     * @return void
     */
    public function cachePlayerJson(GetPlayerJson $event)
    {
        $key = "json-{$event->player->pid}-{$event->apiType}";

        $content = Cache::rememberForever($key, function () use ($event) {
            return $event->player->generateJsonProfile($event->apiType);
        });

        return $content;
    }

    public function freshPlayerJson(PlayerProfileUpdated $event)
    {
        $keys = [
            "json-{$event->player->pid}-0",
            "json-{$event->player->pid}-1",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
