<?php

namespace SuperCache\Listener;

use Cache;
use Storage;
use Response;
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

            if (version_compare(config('app.version'), '3.4.0', '>')) {
                $methodName = 'generateTexturePreview';
            } else {
                $methodName = 'generateTexturePreviewLegacy';
            }

            $png = $this->{$methodName}(
                $event->texture->type,
                $event->texture->hash,
                $event->size
            );

            ob_start();
            imagepng($png);
            imagedestroy($png);
            $image = ob_get_contents();
            ob_end_clean();

            return $image;
        });

        return Response::png($content, 200, [
            'Last-Modified' => Storage::disk('textures')->lastModified($event->texture->hash)
        ]);
    }

    /**
     * Generate texture preview and return raw image data.
     *
     * @param  string $type 'steve', 'alex' or 'cape'.
     * @param  string $hash
     * @param  int    $size
     * @return resources
     */
    protected function generateTexturePreview($type, $hash, $size)
    {
        $binary = Storage::disk('textures')->read($hash);

        if ($type == "cape") {
            $png = Minecraft::generatePreviewFromCape($binary, $size*0.8, $size*1.125, $size);
        } else {
            $png = Minecraft::generatePreviewFromSkin($binary, $size, ($type == 'alex'), 'both', 4);
        }

        return $png;
    }

    /**
     * Generate texture preview, compatible with BS <= 3.4.0.
     *
     * @param  string $type 'steve', 'alex' or 'cape'.
     * @param  string $hash
     * @param  int    $size
     * @return resources
     */
    protected function generateTexturePreviewLegacy($type, $hash, $size)
    {
        $path = storage_path("textures/$hash");

        if ($type == "cape") {
            $png = Minecraft::generatePreviewFromCape($path, $size);
        } else {
            $png = Minecraft::generatePreviewFromSkin($path, $size, false, false, 4, $type == 'alex');
        }

        return $png;
    }
}
