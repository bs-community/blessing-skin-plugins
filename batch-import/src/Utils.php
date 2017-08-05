<?php
/**
 * @Author: printempw
 * @Date:   2017-01-01 16:13:39
 * @Last Modified by:   printempw
 * @Last Modified time: 2017-01-13 22:16:16
 */

namespace Blessing\BatchImport;

class Utils
{
    public static function isValidTexture($path) {
        try {
            if ($size = getimagesize($path)) {
                $ratio = $size[0] / $size[1];

                if ($ratio == 2 | $ratio == 1) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function prepareImportSource($dir)
    {
        $file_num = 0;
        $resource = opendir($dir);

        while($filename = readdir($resource)) {
            if ($filename != "." && $filename != ".." && substr($filename,-4) == ".png") {
                $full_path = "$dir/$filename";

                if (file_exists($full_path) && !is_dir($full_path)) {
                    try {
                        if ($size = getimagesize($full_path)) {
                            $ratio = $size[0] / $size[1];

                            if ($ratio == 2 | $ratio == 1)
                                $file_num++;
                        }
                    } catch (Exception $e) {
                        Log::error("[BatchImport] Error occured!", ['exception' => $e]);
                    }
                }
            }
        }

        session(['import-file-num' => $file_num]);

        closedir($resource);

        return $file_num;
    }

    public static function prepareImportTempDir() {
        if (session()->has('import-tmp-dir')) {
            $tmp_dir = session('import-tmp-dir');
        } else {
            $tmp_dir = storage_path('import-tmp-dir-'.time());

            session(['import-tmp-dir' => $tmp_dir]);
            session()->save();
        }

        if (!is_dir($tmp_dir)) {
            mkdir($tmp_dir);
        }

        $dir = session('import-dir');
        $resource = opendir($dir);

        // copy files from src to temp dir
        while($filename = readdir($resource)) {
            if ($filename != "." && $filename != ".." && substr($filename,-4) == ".png") {
                $full_path = "$dir/$filename";

                if (file_exists($full_path) && !is_dir($full_path)) {
                    if (self::isValidTexture($full_path)) {
                        @copy($full_path, "$tmp_dir/$filename");
                    }
                }
            }
        }

        closedir($resource);

        return $tmp_dir;
    }

    /**
     * Recursively count files of specified directory
     *
     * @param  string $dir
     * @param  $file_num
     * @return int, total size in bytes
     */
    public static function getFileNum($dir, $file_num = 0)
    {
        $resource = opendir($dir);
        while($filename = readdir($resource)) {
            if ($filename != "." && $filename != "..") {
                $path = "$dir/$filename";
                if (is_dir($path)) {
                    // recursion
                    $file_num = self::getFileNum($path, $file_num);
                } else {
                    $file_num++;
                }
            }
        }
        closedir($resource);
        return $file_num;
    }

}
