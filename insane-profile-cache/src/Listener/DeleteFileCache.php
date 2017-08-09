<?php

namespace InsaneProfileCache\Listener;

use File;
use Illuminate\Contracts\Events\Dispatcher;

class DeleteFileCache
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(\App\Events\PlayerWasDeleted::class, function ($event) {
            try {
                File::delete(PROFILE_CACHE_PATH."/csl/{$event->playerName}.json");
                File::delete(PROFILE_CACHE_PATH."/usm/{$event->playerName}.json");
            } catch (\Exception $e) {
                Log::error('Failed to delete profile cache', [$event, $e]);
            }
        });
    }
}
