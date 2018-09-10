<?php

use Illuminate\Contracts\Events\Dispatcher;
use SuperCache\Listener;

return function (Dispatcher $events) {
    $cache_path = storage_path('framework/cache');

    if (!is_writable($cache_path)) {
        die_with_utf8_encoding("[BS Super Cache] 错误：$cache_path 不可写，请检查目录权限。");
    }

    $options = [
        'enable_avatar_cache'   => 'true',
        'enable_preview_cache'  => 'true',
        'enable_json_cache'     => 'true',
        'enable_notfound_cache' => 'true',
    ];

    foreach ($options as $key => $value) {
        if (!Option::has($key)) {
            Option::set($key, $value);
        }
    }

    if (option('enable_json_cache')) {
        $events->subscribe(Listener\CachePlayerJson::class);
    }
    if (option('enable_preview_cache')) {
        $events->subscribe(Listener\CacheSkinPreview::class);
    }
    if (option('enable_notfound_cache')) {
        $events->subscribe(Listener\CachePlayerExists::class);
    }
    if (option('enable_avatar_cache')) {
        $events->subscribe(Listener\CacheAvatarPreview::class);
    }
};
