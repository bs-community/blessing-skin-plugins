<?php

return function () {
    $disk = [
        'driver'  => 'qiniu',
        'domains' => [
            'default' => menv('QINIU_DOMAIN'),
            'https'   => menv('QINIU_HTTPS_DOMAIN'),
            'custom'  => '',
         ],
        'access_key'  => menv('QINIU_ACCESS_KEY'),
        'secret_key'  => menv('QINIU_SECRET_KEY'),
        'bucket'      => menv('QINIU_BUCKET'),
        'access'      => menv('QINIU_BUCKET_ACCESS', 'public'),
        'notify_url'  => '',
    ];

    app()->register(zgldh\QiniuStorage\QiniuFilesystemServiceProvider::class);
    config(['filesystems.disks.textures' => $disk]);
};
