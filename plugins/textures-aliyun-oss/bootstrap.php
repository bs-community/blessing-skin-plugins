<?php

return function () {
    $disk = [
        'driver' => 'oss',
        'access_id' => env('OSS_ACCESS_ID'),
        'access_key' => env('OSS_ACCESS_KEY'),
        'bucket' => env('OSS_BUCKET'),
        'endpoint' => env('OSS_ENDPOINT'),
        'ssl' => env('OSS_SSL', true),
        'cdnDomain' => env('OSS_CDN_DOMAIN'),
        'isCName' => env('OSS_IS_CNAME', false),
        'debug' => false,
    ];

    app()->register(Jacobcyl\AliOSS\AliOssServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
