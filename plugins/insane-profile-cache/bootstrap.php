<?php

use App\Events;
use Illuminate\Contracts\Events\Dispatcher;
use InsaneProfileCache\Listener\DeleteFileCache;
use InsaneProfileCache\Listener\UpdateFileCache;

return function (Dispatcher $events) {
    $events->listen(Events\PlayerWasAdded::class, UpdateFileCache::class);
    $events->listen(Events\PlayerProfileUpdated::class, UpdateFileCache::class);
    $events->listen(Events\PlayerWasDeleted::class, DeleteFileCache::class);

    $events->listen('Illuminate\Console\Events\ArtisanStarting', function ($event) {
        $event->artisan->resolveCommands([
            'InsaneProfileCache\Commands\Clean',
            'InsaneProfileCache\Commands\Generate',
        ]);
    });
};
