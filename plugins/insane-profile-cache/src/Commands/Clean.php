<?php

namespace InsaneProfileCache\Commands;

use Illuminate\Console\Command;

class Clean extends Command
{
    protected $signature = 'profile:clean';

    protected $description = 'Delete all cache files.';

    public function handle()
    {
        $dir = storage_path('insane-profile-cache');
        if (\File::exists($dir)) {
            \File::deleteDirectory(storage_path('insane-profile-cache'));
        }

        $this->info('Cache deleted.');
    }
}
