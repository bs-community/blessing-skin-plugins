<?php

namespace InsaneProfileCache\Commands;

use App\Models\Player;
use File;
use Illuminate\Console\Command;

class Generate extends Command
{
    protected $signature = 'profile:cache';

    protected $description = 'Generate file cache for the player profiles.';

    public function handle()
    {
        $dir = storage_path('insane-profile-cache');
        if (File::missing($dir)) {
            File::makeDirectory($dir);
        }

        $players = Player::all();
        $bar = $this->output->createProgressBar($players->count());

        $players->each(function ($player) use ($bar) {
            File::put(
                storage_path('insane-profile-cache/'.$player->name.'.json'),
                $player->toJson()
            );

            $bar->advance();
        });

        $bar->finish();
        $this->info("\n");
        $this->info('Cache generated successfully.');
    }
}
