<?php

namespace BatchImport;

use App\Http\Controllers\Controller;
use App\Models\Texture;
use Cache;
use DB;
use Exception;
use File;
use Log;
use Utils;

class BatchImportController extends Controller
{
    public function index()
    {
        switch (request('step', 1)) {
            case 1:
                return view('BatchImport::check');
                break;

            case 2:
                return $this->confirm();
                break;

            case 3:
                return $this->import();
                break;
        }

        return '参数错误';
    }

    public function confirm()
    {
        $files = $this->getAvailableFiles(
            Cache::get('import-source-dir'),
            Cache::get('import-gbk')
        );

        $preview = array_reduce(array_slice($files, 0, 50), function ($carry, $item) {
            return $carry.basename($item)."\n";
        }, '');

        return view('BatchImport::confirm', compact('files', 'preview'));
    }

    public function import()
    {
        $files = $this->getAvailableFiles(Cache::get('import-source-dir'));

        return view('BatchImport::import', compact('files'));
    }

    public function checkImportDir()
    {
        if (file_exists(request('dir'))) {
            Cache::put('import-source-dir', request('dir'), 60);
            Cache::put('import-gbk', request('gbk') === 'true', 60);

            return json('目录准备就绪', 0);
        } else {
            return json('指定目录不存在', 1);
        }
    }

    public function chunkImport()
    {
        $this->validate(request(), [
            'begin'    => 'required',
            'end'      => 'required',
            'type'     => 'required',
            'uploader' => 'required',
        ]);

        $dir = Cache::get('import-source-dir');
        $begin = request('begin');
        $end = request('end');
        $type = request('type', 'steve');
        $uploader = request('uploader', 1);
        $files = $this->getAvailableFiles($dir, Cache::get('import-gbk'));

        Log::info('[Batch Import] ===================================');
        Log::info('[Batch Import] Start importing ...');
        Log::info("[Batch Import] Source dir: \"$dir\"");
        Log::info('[Batch Import] Type: "'.request('type').'", uploader: '.request('uploader'));
        Log::info("[Batch Import] Index range from [$begin] to [$end]");

        $imported = 0;
        $response = [];

        foreach (range($begin, $end) as $index) {
            Log::info("[Batch Import][$index] Importing: \"".basename($files[$index]).'"');

            $result = $this->doImportTexture($files[$index], compact('type', 'uploader'));

            if ($result === true) {
                $response[$index] = '导入成功';
                $imported++;
            } else {
                $response[$index] = '导入失败：'.$result;
                Log::info("[Batch Import][$index] Failed to import, reason: $result");
            }
        }

        $response['errno'] = 0;
        Log::info("[Batch Import] Done, imported $imported textures.");

        return json($response);
    }

    /**
     * @param string $file   Full path of file to import.
     * @param array  $option ["type" => "steve|alex|cape", "uploader" => uid]
     *
     * @return string|bool Return true on success, reason string on failure.
     */
    protected function doImportTexture($file, $option)
    {
        $hash = hash_file('sha256', $file);
        $path = storage_path("textures/$hash");

        if (false === copy($file, $path)) {
            return '文件复制失败';
        }

        if (Texture::where('hash', $hash)->first()) {
            return '材质重复';
        }

        try {
            DB::table('textures')->insert([
                'name'      => str_replace('.png', '', basename($file)),
                'type'      => $option['type'],
                'likes'     => 0,
                'hash'      => $hash,
                'size'      => ceil(filesize($path) / 1024),
                'uploader'  => $option['uploader'],
                'public'    => 1,
                'upload_at' => Utils::getTimeFormatted(),
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    protected function getAvailableFiles($dir, $gbk = false)
    {
        $available = [];

        foreach (File::files($dir) as $file) {
            if (substr($file, -4) == '.png') {
                try {
                    if ($size = getimagesize($file)) {
                        $ratio = $size[0] / $size[1];

                        if ($ratio == 2 || $ratio == 1) {
                            $available[] = $gbk ? iconv('GBK', 'UTF-8//IGNORE', $file) : $file;
                        }
                    }
                } catch (Exception $e) {
                    //
                }
            }
        }

        return $available;
    }
}
