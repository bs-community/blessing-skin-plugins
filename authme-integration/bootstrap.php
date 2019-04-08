<?php

use Integration\Authme\Listener;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events) {
    if (! option('single_player')) {
        option(['single_player' => true]);
    }

    // 在皮肤站 users 表上添加 Authme 需要的字段
    authme_init_table();

    // 保证 Authme 新增的 username 等字段与皮肤站原有的 player_name 同步
    $events->subscribe(Listener\SyncWithAuthme::class);

    // 适配 Authme 奇怪的 SHA256 算法
    // 适配动态 salt（皮肤站使用静态 salt 是历史遗留问题）
    $events->subscribe(Listener\HashAlgorithms::class);
};
