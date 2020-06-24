<?php

namespace BatchImport;

use App\Models\Texture;
use Blessing\TextureTypeCast\CastTextureType;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;
use Symfony\Component\Finder\SplFileInfo;

class ImportCommand extends Command
{
    protected $signature = '
        texture:import
        {uploader : Specify texture uploader.}
        {dir : The directory which contains textures files.}
        {--cape : Treat all textures as capes.}
        {--gbk : Perform file name encoding conversion.}
    ';

    protected $description = 'Import textures from external directory.';

    public function handle(Filesystem $fs)
    {
        $files = collect($fs->files($this->argument('dir')));
        $castCommand = new CastTextureType();
        $disk = Storage::disk('textures');
        $invalid = [];

        $files = $files->filter(function (SplFileInfo $file) {
            return Str::endsWith($file->getExtension(), 'png');
        });

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        $files->each(function (SplFileInfo $file) use ($bar, $castCommand, $disk, &$invalid) {
            try {
                $image = Image::make($file->getPathname());
                $width = $image->width();
                $height = $image->height();
                $ratio = $width / $height;
                if (($ratio !== 2 && $ratio !== 1) || $width % 64 != 0) {
                    $invalid[] = $file;
                    $bar->advance();

                    return;
                }

                $hash = hash_file('sha256', $file->getPathname());
                if (Texture::where('hash', $hash)->count() > 0) {
                    $bar->advance();

                    return;
                }

                $name = $this->option('gbk')
                    ? iconv('GBK', 'UTF-8//IGNORE', $file->getFilenameWithoutExtension())
                    : $file->getFilenameWithoutExtension();
                $type = $this->option('cape')
                    ? 'cape'
                    : ($castCommand->isAlex($image) ? 'alex' : 'steve');

                $disk->put($hash, $file->getContents());
                $texture = new Texture();
                $texture->name = $name;
                $texture->type = $type;
                $texture->size = ceil($file->getSize() / 1024);
                $texture->hash = $hash;
                $texture->uploader = $this->argument('uploader');
                $texture->public = true;
                $texture->save();
            } catch (\Exception $e) {
                $invalid[] = $file;
            }

            $bar->advance();
        });

        $bar->finish();
        $this->info("\nCompleted.");

        if (count($invalid) > 0) {
            $this->error('Invalid textures:');
            array_walk($invalid, function (SplFileInfo $file) {
                $this->error($file->getFilename());
            });
        }
    }
}
