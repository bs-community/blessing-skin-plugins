<?php

return function () {
    $disk = [
        'driver' => 'qiniu',
        'domains' => [
            'default' => env('QINIU_DOMAIN'),
            'https' => env('QINIU_HTTPS_DOMAIN'),
            'custom' => '',
         ],
        'access_key' => env('QINIU_ACCESS_KEY'),
        'secret_key' => env('QINIU_SECRET_KEY'),
        'bucket' => env('QINIU_BUCKET'),
        'access' => env('QINIU_BUCKET_ACCESS', 'public'),
        'notify_url' => '',
    ];

    app()->register(zgldh\QiniuStorage\QiniuFilesystemServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
