<?php

use App\Models\Player;

if (! function_exists('generateProfileFileCache')) {

    function generateProfileFileCache(Player $player) {
        $filename = PROFILE_CACHE_PATH."/{$player->player_name}.json";

        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $filename = iconv("utf-8", "gb2312", $filename);
        }

        return file_put_contents($filename, $player->getJsonProfile(option('api_type')));
    }
}
