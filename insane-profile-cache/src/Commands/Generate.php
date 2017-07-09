<?php

namespace InsaneProfileCache\Commands;

use App\Models\Player;
use Illuminate\Console\Command;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate file cache for the fucking player profiles.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Cleaning...');
        // Delete all cache file first
        array_map('unlink', glob(PROFILE_CACHE_PATH."/*"));
        $this->info('Expired cache deleted. Caculating...');

        $players = Player::all();

        $bar = $this->output->createProgressBar(count($players));

        $this->info('There\'s totally '.count($players).' file to be generated.');

        if ($this->confirm('It may take some time. Do you want to continue?')) {
            foreach ($players as $player) {
                generateProfileFileCache($player);
                // Increase the progress bar
                $bar->advance();
            }

            $bar->finish();

            $this->info("\n");
            $this->info('File cache successfully generated.');
        }
    }
}
