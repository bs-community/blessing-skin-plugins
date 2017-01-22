<?php

namespace SuperCache\Listener;

use Cache;
use Storage;
use App\Models\Texture;
use App\Events\GetAvatarPreview;
use Illuminate\Contracts\Events\Dispatcher;

class CacheAvatarPreview
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetAvatarPreview::class, [$this, 'cacheAvatarPreview']);
    }

    /**
     * Handle the event.
     *
     * @param  GetAvatarPreview  $event
     * @return void
     */
    public function cacheAvatarPreview(GetAvatarPreview $event)
    {
        $key = "avatar-{$event->texture->tid}-{$event->size}";

        $content = Cache::rememberForever($key, function () use ($event) {
            return $this->generateAvatar(
                $event->texture->hash,
                $event->size
            );
        });

        return \Response::png($content);
    }

    /**
     * Generate avatar from given texture and return raw image data.
     *
     * @param  string $hash
     * @param  int    $size
     * @return mixed
     */
    protected function generateAvatar($hash, $size)
    {
        $png = \Minecraft::generateAvatarFromSkin(
            storage_path("textures/$hash"),
            $size
        );

        ob_start();
        imagepng($png);
        $image_data = ob_get_contents();
        ob_end_clean();

        imagedestroy($png);

        return $image_data;
    }
}
