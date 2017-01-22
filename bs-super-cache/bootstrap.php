<?php
/**
 * @Author: printempw
 * @Date:   2016-10-25 21:28:34
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-10 19:37:42
 */

use SuperCache\Listener;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    $cache_path = storage_path('framework/cache');

    if (!is_writable($cache_path)) {
        exit("[BS Super Cache] 错误：$cache_path 不可写，请检查目录权限。");
    }

    $options = [
        'enable_avatar_cache'   => 'true',
        'enable_preview_cache'  => 'true',
        'enable_json_cache'     => 'true',
        'enable_notfound_cache' => 'true'
    ];

    foreach ($options as $key => $value) {
        if (!Option::has($key)) {
            Option::set($key, $value);
        }
    }

    if (option('enable_json_cache'))     $events->subscribe(Listener\CachePlayerJson::class);
    if (option('enable_preview_cache'))  $events->subscribe(Listener\CacheSkinPreview::class);
    if (option('enable_notfound_cache')) $events->subscribe(Listener\CachePlayerExists::class);
    if (option('enable_avatar_cache'))   $events->subscribe(Listener\CacheAvatarPreview::class);
};
