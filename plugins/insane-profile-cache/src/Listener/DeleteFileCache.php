<?php

namespace InsaneProfileCache\Listener;

use File;

class DeleteFileCache
{
    public function handle($event)
    {
        if (File::missing(storage_path('insane-profile-cache'))) {
            return;
        }

        File::delete(storage_path('insane-profile-cache/'.$event->playerName.'.json'));
    }
}
