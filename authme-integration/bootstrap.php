<?php

use Integration\Authme\Listener;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events) {
    // 通过 Laravel Migration 修改数据库中的表字段需要安装 doctrine/dbal 依赖，
    // 可是要让这破东西支持旧版 PHP 很麻烦，所以我是直接写的原生 SQL。
    // 但是 SQLite 不支持 ALTER COLUMN，PostgreSQL 语法又微妙地有点不同，
    // 所以我懒得全部支持了，直接限定 MySQL 完事儿。
    if (config('database.default') !== 'mysql') {
        abort(500, '[错误] Authme 数据对接仅支持 MySQL 数据库（删除插件以消除此错误）');
    }

    // 在皮肤站 users 表上添加 Authme 需要的字段
    authme_init_table();

    // 保证 Authme 新增的 username 等字段与皮肤站原有的 player_name 字段同步
    $events->subscribe(Listener\SyncWithAuthme::class);

    // 适配 Authme 奇怪的 SHA256 算法
    // 适配动态 salt（皮肤站使用静态 salt 是历史遗留问题）
    $events->subscribe(Listener\HashAlgorithms::class);
};
