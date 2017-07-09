<?php

namespace InsaneProfileCache\Listener;

use Illuminate\Contracts\Events\Dispatcher;

class UpdateFileCache
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen([
            \App\Events\PlayerProfileUpdated::class,
            \App\Events\PlayerWasAdded::class
        ], function ($event) {
            generateProfileFileCache($event->player);
        });
    }
}
