<?php

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $events->listen(ArtisanStarting::class, function (ArtisanStarting $event) {
        $event->artisan->resolveCommands([
            \Blessing\TextureTypeCast\CastTextureType::class,
        ]);
    });
};
