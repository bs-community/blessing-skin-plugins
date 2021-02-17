<?php

namespace Blessing\TextureTypeCast;

use App\Models\Texture;
use Blessing\Renderer\TextureUtil;
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

            $type = TextureUtil::isAlex($disk->get($skin->hash)) ? 'alex' : 'steve';

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
}
