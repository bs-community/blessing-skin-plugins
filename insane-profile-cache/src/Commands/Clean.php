<?php

namespace InsaneProfileCache\Commands;

use Illuminate\Console\Command;

class Clean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all cache files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Cleaning...');
        cleanProfileFileCache();

        $this->info('Cache file deleted.');
    }
}
