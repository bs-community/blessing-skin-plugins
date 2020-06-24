<?php

if (!function_exists('forum_get_default_db_config')) {
    function forum_get_default_db_config()
    {
        return [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'forum',
            'username' => 'default',
            'password' => 'secret',
            'table' => 'users',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
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
