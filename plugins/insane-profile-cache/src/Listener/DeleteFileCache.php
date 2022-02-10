<?php

namespace InsaneProfileCache\Listener;

use File;

class DeleteFileCache
{
    public function handle($event)
    {
        $dir = storage_path('insane-profile-cache');
        if (File::missing($dir)) {
            return;
        }

        $cachePath = storage_path('insane-profile-cache/'.$event->playerName.'.json');

        if (File::dirname($cachePath) === $dir) {
            File::delete($cachePath);
        }
    }
}
