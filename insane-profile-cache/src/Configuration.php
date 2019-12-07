<?php

namespace InsaneProfileCache;

use App\Models\Player;
use Illuminate\Http\Request;

class Configuration
{
    public function render(Request $request)
    {
        require __DIR__.'/common_functions.php';

        if ($request->has('continue')) {
            // Delete all cache file first
            cleanProfileFileCache();

            $indicator = 0;

            foreach (Player::all() as $player) {
                generateProfileFileCache($player);

                $indicator++;
            }

            return json("生成了 $indicator 个缓存文件。", 0);
        }

        $path = plugin('insane-profile-cache')->getPath()."/README.md";
        $markdown = @file_get_contents($path);
        if (! $markdown) {
            $readme =  "<p>无法加载 README.md</p>";
        } else {
            $readme =  app('parsedown')->text($markdown);
        }

        return view('InsaneProfileCache::generate', compact('readme'));
    }
}
