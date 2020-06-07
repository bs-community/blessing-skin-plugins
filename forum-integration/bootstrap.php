<?php

use Illuminate\Support\Arr;
use Integration\Forum\Listener;

require __DIR__.'/src/helpers.php';

return function () {
    $oldConfig = option('forum_db_config', '');
    if ($oldConfig) {
        $oldConfig = (array) @unserialize($oldConfig);
        option([
            'forum_db_host' => Arr::get($oldConfig, 'host'),
            'forum_db_port' => Arr::get($oldConfig, 'port'),
            'forum_db_database' => Arr::get($oldConfig, 'database'),
            'forum_db_username' => Arr::get($oldConfig, 'username'),
            'forum_db_password' => Arr::get($oldConfig, 'password'),
            'forum_db_table' => Arr::get($oldConfig, 'table'),
        ]);
        DB::table('options')->where('option_name', 'forum_db_config')->delete();
    }

    $config = [
        'host' => option('forum_db_host', '127.0.0.1'),
        'port' => option('forum_db_port', 3306),
        'database' => option('forum_db_database', 'forum'),
        'username' => option('forum_db_username', 'default'),
        'password' => option('forum_db_password', 'secret'),
        'table' => option('forum_db_table', 'users'),
    ];

    // 绑定 Query Builder 至容器，方便之后直接调用
    app()->bind('db.local', function () {
        return DB::connection()->table('users');
    });
    app()->bind('db.remote', function () use ($config) {
        config(['database.connections.remote' => array_merge(
            forum_get_default_db_config(), $config
        )]);

        return DB::connection('remote')->table($config['table']);
    });

    try {
        app('db.remote')->getConnection()->getPdo();
        if (!Schema::connection('remote')->hasTable($config['table'])) {
            return;
        }
    } catch (Exception $e) {
        // 目标数据库没配置好之前啥也不干
        return;
    }

    // 兼容动态 salt，以及监听事件同步用户数据
    Event::subscribe(Listener\HashAlgorithms::class);
    Event::subscribe(Listener\SynchronizeUser::class);
};
