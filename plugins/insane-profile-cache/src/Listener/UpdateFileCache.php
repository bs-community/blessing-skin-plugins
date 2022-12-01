<?php

namespace InsaneProfileCache\Listener;

class UpdateFileCache
{
    public function handle($event)
    {
        $dir = storage_path('insane-profile-cache');
        if (\File::missing($dir)) {
            \File::makeDirectory($dir);
        }

        $player = $event->player;
        $cachePath = storage_path('insane-profile-cache/'.$player->name.'.json');

        if (\File::dirname($cachePath) === $dir) {
            \File::put($cachePath, $player->toJson());
        }
    }
}
