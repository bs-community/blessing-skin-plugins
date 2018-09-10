<?php

if (!function_exists('forum_init_options')) {
    function forum_init_options()
    {
        $entries = [
            'forum_db_config' => serialize([
                // 存储序列化后的数组至 option
                'host'     => 'localhost',
                'port'     => '3306',
                'database' => '',
                'username' => '',
                'password' => '',
                'table'    => '',
            ]),
            // remote - 以论坛的用户数据为准，数据冲突的情况下皮肤站的数据将被覆盖
            // local  - 以皮肤站的用户数据为准，数据冲突的情况下论坛的数据将被覆盖
            'forum_duplicated_prefer' => 'remote',
        ];

        foreach ($entries as $key => $value) {
            if (!Option::has($key)) {
                Option::set($key, $value);
            }
        }
    }
}

if (!function_exists('forum_get_default_db_config')) {
    function forum_get_default_db_config()
    {
        return [
            'driver'    => 'mysql',
            'host'      => '',
            'port'      => '',
            'database'  => '',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ];
    }
}

if (!function_exists('forum_generate_random_salt')) {
    function forum_generate_random_salt()
    {
        // Format /^[0-9a-f]{6}$/
        return bin2hex(random_bytes(3));
    }
}
