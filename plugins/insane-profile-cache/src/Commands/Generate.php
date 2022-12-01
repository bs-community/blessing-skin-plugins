<?php

namespace InsaneProfileCache\Commands;

use App\Models\Player;
use Illuminate\Console\Command;

class Generate extends Command
{
    protected $signature = 'profile:cache';

    protected $description = 'Generate file cache for the player profiles.';

    public function handle()
    {
        $dir = storage_path('insane-profile-cache');
        if (\File::missing($dir)) {
            \File::makeDirectory($dir);
        }

        $players = Player::all();
        $bar = $this->output->createProgressBar($players->count());

        $players->each(function ($player) use ($dir, $bar) {
            $cachePath = storage_path('insane-profile-cache/'.$player->name.'.json');

            if (\File::dirname($cachePath) === $dir) {
                \File::put($cachePath, $player->toJson());
            }

            $bar->advance();
        });

        $bar->finish();
        $this->info("\n");
        $this->info('Cache generated successfully.');
    }
}
