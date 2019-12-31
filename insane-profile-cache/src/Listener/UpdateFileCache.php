<?php

namespace InsaneProfileCache\Listener;

use File;

class UpdateFileCache
{
    public function handle($event)
    {
        $dir = storage_path('insane-profile-cache');
        if (File::missing($dir)) {
            File::makeDirectory($dir);
        }

        $player = $event->player;
        File::put(
            storage_path('insane-profile-cache/'.$player->name.'.json'),
            $player->toJson()
        );
    }
}
