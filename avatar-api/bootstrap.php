<?php

use App\Models\Player;
use App\Services\Hook;

/**
 * Return binary data of avatar image.
 *
 * @param string $hash
 * @param int    $size
 *
 * @return mixed
 */
function generate_avatar($hash, $size)
{
    // Should be compatible with BS <= 3.4.0
    if (version_compare(config('app.version'), '3.4.0', '>')) {
        $png = Minecraft::generateAvatarFromSkin(Storage::disk('textures')->read($hash), $size);
    } else {
        $png = Minecraft::generateAvatarFromSkin(storage_path("textures/$hash"), $size);
    }

    ob_start();
    imagepng($png);
    $image_data = ob_get_contents();
    ob_end_clean();

    imagedestroy($png);

    return $image_data;
}

return function () {
    Hook::addRoute(function ($router) {
        $router->get('/getavatar/{size}/{player_name}.png', function ($size, $player_name) {
            $player = Player::where('player_name', $player_name)->first();

            if ($player) {
                $hash = $player->getTexture('skin');

                if (Storage::disk('textures')->has($hash)) {
                    $key = "avatar-{$hash}-{$size}";
                    // cache the binary data of avatar
                    $content = Cache::rememberForever($key, function () use ($hash, $size) {
                        return generate_avatar(
                            $hash,
                            $size
                        );
                    });

                    return Response::png($content);
                } else {
                    abort(404, '头像未设置');
                }
            } else {
                abort(404, '角色不存在');
            }
        })->middleware('web');
    });
};
