<?php

return function () {
    $disk = [
        'driver' => 'cosv5',
        'region'          => menv('COS_REGION', 'ap-shanghai'),
        'credentials'     => [
            'appId'       => menv('COS_APP_ID'),
            'secretId'    => menv('COS_SECRET_ID'),
            'secretKey'   => menv('COS_SECRET_KEY'),
        ],
        'timeout'         => menv('COS_TIMEOUT', 60),
        'connect_timeout' => menv('COS_CONNECT_TIMEOUT', 60),
        'bucket'          => menv('COS_BUCKET'),
        'cdn'             => menv('COS_CDN'),
        'read_from_cdn'   => menv('COS_READ_FROM_CDN', true),
        'scheme'          => menv('COS_SCHEME', 'https'),
    ];

    app()->register(Freyo\Flysystem\QcloudCOSv5\ServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
