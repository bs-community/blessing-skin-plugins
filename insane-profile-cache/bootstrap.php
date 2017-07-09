<?php

use App\Models\Player;
use App\Services\Hook;
use App\Services\Storage;
use Illuminate\Contracts\Events\Dispatcher;
use InsaneProfileCache\Listener\UpdateFileCache;

require __DIR__.'/src/common_functions.php';

define('PROFILE_CACHE_PATH', realpath(__DIR__.'/cache'));

if (!PROFILE_CACHE_PATH || !is_writable(PROFILE_CACHE_PATH)) {
    die("没有对文件缓存目录 ".PROFILE_CACHE_PATH." 的写入权限");
}

return function (Dispatcher $events) {

    Hook::addMenuItem('admin', 5, [
        'title' => '文件缓存',
        'link'  => 'admin/generate-profile-cache',
        'icon'  => 'fa-file-archive-o'
    ]);

    $events->subscribe(UpdateFileCache::class);

    $events->listen('Illuminate\Console\Events\ArtisanStarting', function ($event) {
        $event->artisan->resolveCommands([
            'InsaneProfileCache\Commands\Clean',
            'InsaneProfileCache\Commands\Generate'
        ]);
    });

    Hook::addRoute(function ($router) {

        $router->get('/admin/generate-profile-cache', function () {

            if (isset($_GET['continue'])) {
                // Delete all cache file first
                array_map('unlink', glob(PROFILE_CACHE_PATH."/*"));

                $indicator = 0;

                foreach (Player::all() as $player) {
                    generateProfileFileCache($player);

                    $indicator++;
                }

                return "在目录 ".PROFILE_CACHE_PATH." 下成功生成了 $indicator 个缓存文件。";
            }

            return view('InsaneProfileCache::generate');
        })->middleware(['web', 'auth', 'admin']);

    });
};
