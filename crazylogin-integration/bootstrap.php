<?php

use Integration\CrazyLogin\Listener;
use Illuminate\Contracts\Events\Dispatcher;

require __DIR__.'/src/helpers.php';

return function (Dispatcher $events) {
    // 兼容 BS <= 3.4.0
    if (!plugin('single-player-limit') || !plugin('single-player-limit')->isEnabled()) {
        abort(500, '[错误] 必须安装「单角色限制」插件才能使用 CrazyLogin 数据对接（删除插件以消除此错误）');
    }

    // 通过 Laravel Migration 修改数据库中的表字段需要安装 doctrine/dbal 依赖，
    // 可是要让这破东西支持旧版 PHP 很麻烦，所以我是直接写的原生 SQL。
    // 但是 SQLite 不支持 ALTER COLUMN，PostgreSQL 语法又微妙地有点不同，
    // 所以我懒得全部支持了，直接限定 MySQL 完事儿。
    if (config('database.default') !== 'mysql') {
        abort(500, '[错误] CrazyLogin 数据对接仅支持 MySQL 数据库（删除插件以消除此错误）');
    }

    // 在皮肤站 users 表上添加 CrazyLogin 需要的字段
    crazylogin_init_table();

    // 适配操蛋的 CrazyCrypt1 算法
    $events->subscribe(Listener\HashAlgorithms::class);

    // 保证 CrazyLogin 新增的 username 等字段与皮肤站原有的 player_name 字段同步
    $events->subscribe(Listener\SyncWithCrazyLogin::class);
};
