<?php

namespace Blessing\TextureTypeCast;

use App\Models\Texture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Image;

class CastTextureType extends Command
{
    protected $signature = 'texture:cast';

    protected $description = 'Perform texture type casting for all textures.';

    public function handle()
    {
        $skins = Texture::where('type', '<>', 'cape')->get();

        $disk = Storage::disk('textures');

        $bar = $this->output->createProgressBar($skins->count());
        $bar->start();

        $modified = 0;

        $skins->each(function ($skin) use ($disk, $bar, &$modified) {
            if (!$disk->exists($skin->hash)) {
                $bar->advance();
                return;
            }

            $image = Image::make($disk->get($skin->hash));
            $width = $image->width();
            $type = 'alex';

            if ($width == $image->height()) {
                $ratio = $width / 64;
                for ($x = 46 * $ratio; $x < 48 * $ratio; $x += 1) {
                    for ($y = 52 * $ratio; $y < 64 * $ratio; $y += 1) {
                        if (!$this->checkPixel($image->pickColor($x, $y))) {
                            $type = 'steve';
                            break 2;
                        }
                    }
                }
            } else {
                $type = 'steve';
            }

            if ($skin->type !== $type) {
                $skin->type = $type;
                $skin->save();
                $modified += 1;
            }

            $bar->advance();
        });

        $bar->finish();
        switch ($modified) {
            case 0:
                $this->info("\n Completed! No textures were updated.");
                break;
            case 1:
                $this->info("\n Completed! 1 texture was updated.");
                break;
            default:
                $this->info("\n Completed! $modified textures were updated.");
                break;
        }
    }

    protected function checkPixel(array $color): bool
    {
        return $color[0] === 0 && $color[1] === 0 && $color[2] === 0;
    }
}
