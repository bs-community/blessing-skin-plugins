<?php
/**
 * @Author: printempw
 * @Date:   2016-11-13 12:37:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-13 22:15:52
 */

use App\Services\Hook;
use App\Models\Texture;
use Blessing\BatchImport\Utils as MyUtils;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    Hook::addMenuItem('admin', 3, [
        'title' => '批量导入',
        'link'  => 'admin/batch-import',
        'icon'  => 'fa-truck'
    ]);

    Hook::addRoute(function ($router) {
        $router->any('/admin/batch-import', function() {
            $request = app('request');
            $action  = $request->input('action');

            switch ($action) {
                case 'check-dir':
                    if (file_exists($request->input('dir'))) {
                        if (is_writable($request->input('dir'))) {
                            session(['import-dir' => $request->input('dir')]);
                            session(['import-gbk' => $request->input('gbk')]);

                            return json('目录权限正确', 0);
                        } else {
                            return json('指定目录没有写权限', 1);
                        }
                    } else {
                        return json('指定目录不存在', 1);
                    }
                    break;

                case 'prepare-import':

                    return json(['tmp_dir' => MyUtils::prepareImportTempDir()]);

                    break;

                case 'start-import':

                    $tmp_dir  = session('import-tmp-dir');
                    $resource = opendir($tmp_dir);
                    $imported = 0;

                    Log::info("[Batch Import] Importing started, tmp dir => $tmp_dir");
                    Log::info("=========================================================");

                    while($filename = readdir($resource)) {
                        if ($filename != "." && $filename != "..") {
                            $full_path = "$tmp_dir/$filename";

                            if (MyUtils::isValidTexture($full_path)) {

                                if (session('import-gbk')) {
                                    // damn GBK
                                    $filename = mb_convert_encoding($filename, 'UTF-8', 'GBK');
                                }

                                $hash = hash_file('sha256', $full_path);
                                $new_path = storage_path("textures/$hash");

                                if (false === rename($full_path, $new_path)) {
                                    throw new \Exception("Failed to rename $full_path to $new_path.");
                                }

                                if (Texture::where('hash', $hash)->get()->isEmpty()) {

                                    DB::table('textures')->insert([
                                        'name'      => $filename,
                                        'type'      => $_POST['type'],
                                        'likes'     => 0,
                                        'hash'      => $hash,
                                        'size'      => filesize($new_path) / 1024,
                                        'uploader'  => $_POST['uploader'],
                                        'public'    => '1',
                                        'upload_at' => Utils::getTimeFormatted()
                                    ]);

                                    $imported++;

                                    Log::info("[Batch Import] Texture $filename as {$_POST['type']} imported, uploader {$_POST['uploader']}, size ".filesize($new_path));

                                } else {
                                    Log::info("[Batch Import] Texture duplicated with hash [$hash]");
                                }
                            }
                        }
                    }

                    closedir($resource);

                    Log::info("[Batch Import] Importing done, imported $imported textures");
                    Log::info("=========================================================");

                    File::deleteDirectory($tmp_dir);

                    return json(['imported' => $imported]);

                    break;

                case 'get-progress':

                    $total = session('import-file-num');

                    if (!is_dir(session('import-tmp-dir'))) {
                        session()->forget('import-tmp-dir');
                        return '缓存文件夹不存在，请重新执行导入操作';
                    }

                    $remain = MyUtils::getFileNum(session('import-tmp-dir'));

                    $progress = ($total - $remain) / $total * 100;

                    return json(compact('total', 'remain', 'progress'));

                    break;

                default:
                    # code...
                    break;
            }

            $step = $request->input('step', 1);

            return view('Blessing\BatchImport::steps.'.$step);

        })->middleware(['web', 'auth']);
    });
};
