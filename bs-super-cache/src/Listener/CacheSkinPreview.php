<?php

namespace SuperCache\Listener;

use Cache;
use Storage;
use Minecraft;
use App\Events\GetSkinPreview;
use Illuminate\Contracts\Events\Dispatcher;

class CacheSkinPreview
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetSkinPreview::class, [$this, 'cacheSkinPreview']);
    }

    /**
     * Handle the event.
     *
     * @param  GetSkinPreview  $event
     * @return void
     */
    public function cacheSkinPreview(GetSkinPreview $event)
    {
        $key = "preview-{$event->texture->tid}-{$event->size}";

        $content = Cache::rememberForever($key, function () use ($event) {
            return $this->generateTexturePreview(
                $event->texture->type,
                $event->texture->hash,
                $event->size
            );
        });

        return \Response::png($content, 200, [
            'Last-Modified' => Storage::disk('textures')->lastModified($event->texture->hash)
        ]);
    }

    /**
     * Generate texture preview and return raw image data.
     *
     * @param  string $type 'steve', 'alex' or 'cape'.
     * @param  string $hash
     * @param  int    $size
     * @return mixed
     */
    protected function generateTexturePreview($type, $hash, $size)
    {
        $path = storage_path("textures/$hash");

        if ($type == "cape") {
            $png = Minecraft::generatePreviewFromCape($path, $size);
        } else {
            $png = Minecraft::generatePreviewFromSkin($path, $size);
        }

        ob_start();
        imagepng($png);
        $image_data = ob_get_contents();
        ob_end_clean();

        imagedestroy($png);

        return $image_data;
    }

}
