<?php

return function () {
    $disk = [
        'driver'        => 'oss',
        'access_id'     => menv('OSS_ACCESS_ID'),
        'access_key'    => menv('OSS_ACCESS_KEY'),
        'bucket'        => menv('OSS_BUCKET'),
        'endpoint'      => menv('OSS_ENDPOINT'),
        'ssl'           => menv('OSS_SSL', true),
        'cdnDomain'     => menv('OSS_CDN_DOMAIN'),
        'isCName'       => menv('OSS_IS_CNAME', false),
        'debug'         => false,
    ];

    app()->register(Jacobcyl\AliOSS\AliOssServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
