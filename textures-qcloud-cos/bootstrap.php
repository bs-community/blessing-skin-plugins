<?php

return function () {
    $disk = [
        'driver' => 'cosv5',
        'region'          => env('COS_REGION', 'ap-shanghai'),
        'credentials'     => [
            'appId'       => env('COS_APP_ID'),
            'secretId'    => env('COS_SECRET_ID'),
            'secretKey'   => env('COS_SECRET_KEY'),
        ],
        'timeout'         => env('COS_TIMEOUT', 60),
        'connect_timeout' => env('COS_CONNECT_TIMEOUT', 60),
        'bucket'          => env('COS_BUCKET'),
        'cdn'             => env('COS_CDN'),
        'read_from_cdn'   => env('COS_READ_FROM_CDN', true),
        'scheme'          => env('COS_SCHEME', 'https'),
    ];

    app()->register(Freyo\Flysystem\QcloudCOSv5\ServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
