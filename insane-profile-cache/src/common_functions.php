<?php

use App\Models\Player;

if (! function_exists('generateProfileFileCache')) {

    function generateProfileFileCache(Player $player) {

        foreach (['usm', 'csl'] as $apiType) {
            $filename = PROFILE_CACHE_PATH."/$apiType/{$player->name}.json";

            try {
                if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                    $filename = iconv('utf-8', 'gb2312', $filename);
                }
            } catch (Exception $e) {
                Log::error("Failed to convert [$filename] from utf-8 to gb2312");
            }

            file_put_contents($filename, $player->getJsonProfile(
                ($apiType == 'csl') ? Player::CSL_API : Player::USM_API
            ));
        }

        return true;
    }
}

if (! function_exists('cleanProfileFileCache')) {

    function cleanProfileFileCache() {
        // Delete all cache file first
        foreach (['usm', 'csl'] as $apiType) {
            array_map('unlink', glob(PROFILE_CACHE_PATH."/$apiType/*"));
        }

        return true;
    }
}
